<?php
declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * A class for adding wrapping for a content element to be editable
 */
class ContentEditableWrapperService
{
    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var LanguageService
     */
    protected $languageService;

    /**
     * Add the proper wrapping (html tag) to make the content editable by CKEditor
     *
     * @param string $table
     * @param string $field
     * @param int $uid
     * @param string $content
     * @return string
     * @throws \InvalidArgumentException
     */
    public function wrapContentToBeEditable(string $table, string $field, int $uid, string $content): string
    {
        // Check that data is not empty
        if (empty($table)) {
            throw new \InvalidArgumentException('Property "table" can not to be empty!', 1486163277);
        } elseif (empty($field)) {
            throw new \InvalidArgumentException('Property "field" can not to be empty!', 1486163282);
        } elseif (empty($uid)) {
            throw new \InvalidArgumentException('Property "uid" can not to be empty!', 1486163287);
        }

        $content = sprintf(
            '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%d" class="%s">%s</div>',
            $table,
            $field,
            $uid,
            $this->checkIfContentElementIsHidden($table, (int)$uid),
            $content
        );

        return $content;
    }

    /**
     * Wrap content
     *
     * @param string $table
     * @param int $uid
     * @param array $dataArr
     * @param string $content
     * @return string
     * @throws \InvalidArgumentException
     */
    public function wrapContent(string $table, int $uid, array $dataArr, string $content): string
    {
        // Check that data is not empty
        if (empty($table)) {
            throw new \InvalidArgumentException('Property "table" can not to be empty!', 1486163297);
        } elseif (empty($uid)) {
            throw new \InvalidArgumentException('Property "uid" can not to be empty!', 1486163305);
        }

        $hiddenElementClassName = $this->checkIfContentElementIsHidden($table, (int)$uid);
        $elementIsHidden = $hiddenElementClassName !== '';

        // @TODO: include config as parameter and make cid (columnIdentifier) able to set by combining fields
        // Could make it would make it possible to configure cid for use with extensions that create columns by content
        $class = 't3-frontend-editing__inline-actions';
        $content = sprintf(
            '<div class="t3-frontend-editing__ce %s" title="%s" draggable="true"' .
                'data-movable="1"' .
                'ondragstart="window.parent.F.dragCeStart(event)"' .
                'ondragend="window.parent.F.dragCeEnd(event)">' .
                '<span class="%s" data-table="%s" data-uid="%d" data-hidden="%s"' .
                    ' data-cid="%d" data-edit-url="%s" data-new-url="%s">%s</span>' .
                '%s' .
            '</div>',
            $hiddenElementClassName,
            $this->recordTitle($table, (int)$uid),
            $class,
            $table,
            $uid,
            (int)$elementIsHidden,
            $dataArr['colPos'],
            $this->renderEditOnClickReturnUrl($this->renderEditUrl($table, $uid)),
            $this->renderEditOnClickReturnUrl($this->renderNewUrl($table, $uid)),
            $this->renderInlineActionIcons($table, $elementIsHidden),
            $content
        );

        return $content;
    }

    /**
     * Add a drop zone before/after the content
     *
     * @param string $table
     * @param int $uid
     * @param string $content
     * @return string
     * @param int $colPos
     * @param array $defaultValues
     * @param bool $prepend
     * @throws \InvalidArgumentException
     */
    public function wrapContentWithDropzone(
        string $table,
        int $uid,
        string $content,
        int $colPos = 0,
        array $defaultValues = [],
        bool $prepend = false
    ): string {
        // Check that data is not empty
        if (empty($table)) {
            throw new \InvalidArgumentException('Property "table" can not to be empty!', 1486163430);
        } elseif ($uid < 0) {
            throw new \InvalidArgumentException('Property "uid" is not valid!', 1486163439);
        }

        $jsFuncOnDrop = 'window.parent.F.dropCe(event)';
        $jsFuncOnDragover = 'window.parent.F.dragCeOver(event)';
        $jsFuncOnDragLeave = 'window.parent.F.dragCeLeave(event)';
        $class = 't3-frontend-editing__dropzone';

        $dropZone = sprintf(
            '<div class="%s" ondrop="%s" ondragover="%s" ondragleave="%s" data-new-url="%s" data-moveafter="%d" data-colpos="%d"></div>',
            $class,
            $jsFuncOnDrop,
            $jsFuncOnDragover,
            $jsFuncOnDragLeave,
            $this->renderEditOnClickReturnUrl($this->renderNewUrl($table, (int)$uid, (int)$colPos, $defaultValues)),
            $uid,
            $colPos
        );

        return $prepend ? ($dropZone . $content) : ($content . $dropZone);
    }

    /**
     * Renders the inline action icons
     *
     * @param string $table
     * @param bool $elementIsHidden
     * @return string
     */
    public function renderInlineActionIcons(string $table, bool $elementIsHidden): string
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->languageService = GeneralUtility::makeInstance(LanguageService::class);

        $visibilityIcon = ($elementIsHidden === true) ?
            $this->renderIconWithWrap('unHide', 'actions-edit-unhide') : $this->renderIconWithWrap('hide', 'actions-edit-hide');

        $moveIcons = ($table === 'tt_content') ?
            $this->renderIconWithWrap('moveUp', 'actions-move-up') . $this->renderIconWithWrap("moveDown", 'actions-move-down') : '';

        $inlineIcons =
            $this->renderIconWithWrap('edit', 'actions-open') .
            $visibilityIcon .
            $this->renderIconWithWrap('delete', 'actions-edit-delete') .
            $this->renderIconWithWrap('new', 'actions-document-new') .
            $moveIcons;

        return $inlineIcons;
    }

    /**
     * Wraps an inline action icon
     *
     * @param string $titleKey
     * @param string $iconKey
     * @return string
     */
    private function renderIconWithWrap(string $titleKey, string $iconKey): string
    {
        return '<span title="' . $this->languageService->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf:' . $titleKey) . '">' . $this->iconFactory->getIcon($iconKey, Icon::SIZE_SMALL)->render() . '</span>';
    }

    /**
     * Render a edit url to the backend content wizard
     *
     * @param string $table
     * @param string $uid
     * @return string
     */
    public function renderEditUrl($table, $uid): string
    {
        $newUrl = BackendUtility::getModuleUrl(
            'record_edit',
            [
                'edit[' . $table . '][' . $uid . ']' => 'edit',
                'noView' => (GeneralUtility::_GP('ADMCMD_view') ? 1 : 0),
                'feEdit' => 1
            ]
        );
        return (string)$newUrl;
    }

    /**
     * Render a new content element url to the backend content wizard
     *
     * @param string $table
     * @param int $uid
     * @param int $colPos
     * @param array $defaultValues
     * @return string
     */
    public function renderNewUrl(string $table, int $uid = 0, int $colPos = 0, array $defaultValues = []): string
    {
        // Default to top of 'page'
        $newId = (int)$GLOBALS['TSFE']->id;

        // If content uid is supplied, set new content to be 'after'
        if ($uid > 0) {
            $newId = $uid * -1;
        }

        $urlParameters = [
            'edit[' . $table . '][' . $newId . ']' => 'new',
            'noView' => (GeneralUtility::_GP('ADMCMD_view') ? 1 : 0),
            'feEdit' => 1
        ];

        // If there is no any content in drop zone we need to set colPos
        if ($colPos !== 0) {
            $urlParameters['defVals'][$table]['colPos'] = $colPos;
        }
        // If there are any fields to set
        if (!empty($defaultValues)) {
            $urlParameters['defVals'][$table] = array_merge($urlParameters['defVals'][$table] ?? [], $defaultValues);
        }

        $newUrl = BackendUtility::getModuleUrl(
            'record_edit',
            $urlParameters
        );

        return (string)$newUrl;
    }

    /**
     * Render the onclick return url for when open an edit window
     *
     * @param string $url
     * @return string
     */
    public function renderEditOnClickReturnUrl(string $url): string
    {
        $closeUrl = GeneralUtility::getFileAbsFileName('EXT:frontend_editing/Resources/Public/Templates/Close.html');
        if (!empty($closeUrl)) {
            $url .= '&returnUrl=' . PathUtility::getAbsoluteWebPath($closeUrl);
        }
        return $url;
    }

    /**
     * Check if the content element is hidden and return a proper class name
     *
     * @param string $table
     * @param int $uid
     * @return string $hiddenClassName
     */
    public function checkIfContentElementIsHidden(string $table, int $uid): string
    {
        $hiddenClassName = '';
        $row = BackendUtility::getRecord($table, $uid);
        $tcaCtrl = $GLOBALS['TCA'][$table]['ctrl'];
        if ($tcaCtrl['enablecolumns']['disabled'] && $row[$tcaCtrl['enablecolumns']['disabled']] ||
            $tcaCtrl['enablecolumns']['fe_group'] && $GLOBALS['TSFE']->simUserGroup &&
            $row[$tcaCtrl['enablecolumns']['fe_group']] == $GLOBALS['TSFE']->simUserGroup ||
            $tcaCtrl['enablecolumns']['starttime'] && $row[$tcaCtrl['enablecolumns']['starttime']] > $GLOBALS['EXEC_TIME'] ||
            $tcaCtrl['enablecolumns']['endtime'] && $row[$tcaCtrl['enablecolumns']['endtime']] &&
            $row[$tcaCtrl['enablecolumns']['endtime']] < $GLOBALS['EXEC_TIME']
        ) {
            $hiddenClassName = 't3-frontend-editing__hidden-element';
        }
        return $hiddenClassName;
    }

    /**
     * Returns the title label used in Backend lists
     *
     * @param string $table of the record
     * @param int $uid of the record
     * @return string
     */
    public function recordTitle(string $table, int $uid): string
    {
        $rawRecord = BackendUtility::getRecord($table, $uid);
        return BackendUtility::getRecordTitle(
            $table,
            $rawRecord
        );
    }
}
