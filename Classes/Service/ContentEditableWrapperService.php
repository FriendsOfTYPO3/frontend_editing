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

/**
 * A class for adding wrapping for a content element to be editable
 */
class ContentEditableWrapperService
{

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
            '<div class="t3-frontend-editing__ce %s" title="%s">' .
                '<span class="%s" data-table="%s" data-uid="%d" data-hidden="%s"' .
                    ' data-cid="%d" data-edit-url="%s">%s</span>' .
                '%s' .
            '</div>',
            $hiddenElementClassName,
            $this->contentElementTitle((int)$uid),
            $class,
            $table,
            $uid,
            (int)$elementIsHidden,
            $dataArr['colPos'],
            $this->renderEditOnClickReturnUrl($this->renderEditUrl($table, $uid)),
            $this->renderInlineActionIcons($elementIsHidden),
            $content
        );

        return $content;
    }

    /**
     * Add a drop zone after the content
     *
     * @param string $table
     * @param int $uid
     * @param string $content
     * @return string
     * @throws \InvalidArgumentException
     */
    public function wrapContentWithDropzone(string $table, int $uid, string $content): string
    {
        // Check that data is not empty
        if (empty($table)) {
            throw new \InvalidArgumentException('Property "table" can not to be empty!', 1486163430);
        } elseif (empty($uid)) {
            throw new \InvalidArgumentException('Property "uid" can not to be empty!', 1486163439);
        }

        $jsFuncOnDrop = 'window.parent.F.dropNewCe(event)';
        $jsFuncOnDragover = 'window.parent.F.dragNewCeOver(event)';
        $jsFuncOnDragLeave = 'window.parent.F.dragNewCeLeave(event)';
        $class = 't3-frontend-editing__dropzone';

        $content .= sprintf(
            '<div class="%s" ondrop="%s" ondragover="%s" ondragleave="%s" data-new-url="%s"></div>',
            $class,
            $jsFuncOnDrop,
            $jsFuncOnDragover,
            $jsFuncOnDragLeave,
            $this->renderEditOnClickReturnUrl($this->renderNewUrl($table, (int)$uid))
        );

        return $content;
    }

    /**
     * Renders the inline action icons
     *
     * @param bool $elementIsHidden
     * @return string
     */
    public function renderInlineActionIcons($elementIsHidden): string
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $visibilityIcon = ($elementIsHidden === true) ?
            $iconFactory->getIcon('actions-edit-unhide', Icon::SIZE_SMALL)->render() :
            $iconFactory->getIcon('actions-edit-hide', Icon::SIZE_SMALL)->render();

        $inlineIcons =
            $visibilityIcon .
            $iconFactory->getIcon('actions-open', Icon::SIZE_SMALL)->render() .
            $iconFactory->getIcon('actions-edit-delete', Icon::SIZE_SMALL)->render() .
            $iconFactory->getIcon('actions-move-up', Icon::SIZE_SMALL)->render() .
            $iconFactory->getIcon('actions-move-down', Icon::SIZE_SMALL)->render();

        return $inlineIcons;
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
     * @return string
     */
    public function renderNewUrl(string $table, int $uid = 0): string
    {
        // Default to top of 'page'
        $newId = (int)$GLOBALS['TSFE']->id;

        // If content uid is supplied, set new content to be 'after'
        if ($uid > 0) {
            $newId = $uid * -1;
        }

        $newUrl = BackendUtility::getModuleUrl(
            'record_edit',
            [
                'edit[' . $table . '][' . $newId . ']' => 'new',
                'noView' => (GeneralUtility::_GP('ADMCMD_view') ? 1 : 0),
                'feEdit' => 1
            ]
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
     * @param int $uid of the content element
     * @return string
     */
    public function contentElementTitle(int $uid): string
    {
        $rawRecord = BackendUtility::getRecord('tt_content', $uid);
        return BackendUtility::getRecordTitle(
            'tt_content',
            $rawRecord,
            true
        );
    }
}
