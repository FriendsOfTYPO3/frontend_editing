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

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * A class for adding wrapping for a content element to be editable
 */
class ContentEditableWrapperService
{
    const DEFAULT_WRAPPER_TAG_NAME = 'div';

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var string
     */
    protected $contentEditableWrapperTagName;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * ContentEditableWrapperService constructor
     */
    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->contentEditableWrapperTagName = self::DEFAULT_WRAPPER_TAG_NAME;
        $tagName = ConfigurationUtility::getExtensionConfiguration()['contentEditableWrapperTagName'];
        if ($tagName) {
            $this->contentEditableWrapperTagName = $tagName;
        }
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
    }

    /**
     * Add the proper wrapping (html tag) to make the content editable by CKEditor
     *
     * @param string $table
     * @param string $field
     * @param int $uid
     * @param string $content
     * @param string|null $tag Optional tag name to use, e.g. "div"
     * @param array $additionalAttibutes An array of additional arguments for the tag.
     * @return string
     * @throws \InvalidArgumentException
     */
    public function wrapContentToBeEditable(
        string $table,
        string $field,
        int $uid,
        string $content,
        ?string $tag = null,
        array $additionalAttibutes = []
    ): string
    {
        // Check that data is not empty
        if (empty($table)) {
            throw new \InvalidArgumentException('Property "table" can not to be empty!', 1486163277);
        }
        if (empty($field)) {
            throw new \InvalidArgumentException('Property "field" can not to be empty!', 1486163282);
        }
        if (empty($uid)) {
            $this->logger->error(
                'Property "uid" can not to be empty!',
                [
                    'table' => $table,
                    'field' => $field,
                    'class' => __CLASS__
                ]
            );

            return $content;
        }

        $this->switchToLocalLanguageEquivalent($table, $uid);

        /** @var TagBuilder $tagBuilder */
        $tagBuilder = GeneralUtility::makeInstance(
            TagBuilder::class,
            $tag ?? $this->contentEditableWrapperTagName,
            $content
        );

        $tagBuilder->ignoreEmptyAttributes(true);

        $tagBuilder->addAttributes($additionalAttibutes);

        if ($this->isUserDisallowedEditingOfContentElement($this->getBackendUser(), $uid)) {
            return $tag === null ? $content : $tagBuilder->render();
        }

        $placeholderText = $this->getPlaceholderText($table, $field);

        $tagBuilder->addAttributes([
            'contenteditable' => 'true',
            'data-table' => $table,
            'data-field' => $field,
            'data-uid' => $uid,
            'class' => trim(
                $this->getContentElementClass($table, (int)$uid)
                . ' ' . $tagBuilder->getAttribute('class')
            ),
            'placeholder' => $placeholderText,
        ]);

        return $tagBuilder->render();
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
        }
        if (empty($uid)) {
            $this->logger->error(
                'Property "uid" can not to be empty!',
                [
                    'table' => $table,
                    'uid' => $uid,
                    'class' => __CLASS__
                ]
            );

            return $content;
        }

        if ($this->isUserDisallowedEditingOfContentElement($this->getBackendUser(), $uid)) {
            return $content;
        }

        $hiddenElementClassName = $this->getContentElementClass($table, (int)$uid);
        $elementIsHidden = $hiddenElementClassName !== '';

        $recordTitle = $this->recordTitle($table, $dataArr);

        // @TODO: include config as parameter and make cid (columnIdentifier) able to set by combining fields
        // Could make it would make it possible to configure cid for use with extensions that create columns by content
        $class = 't3-frontend-editing__inline-actions';

        /** @var TagBuilder $inlineActionTagBuilder */
        $inlineActionTagBuilder = GeneralUtility::makeInstance(
            TagBuilder::class,
            'span',
            $this->renderInlineActionIcons($table, $elementIsHidden, $recordTitle)
        );

        $inlineActionTagBuilder->addAttributes([
            'style' => 'display:none;',
            'class' => $class,
            'data-table' => $table,
            'data-uid' => (int)$uid,
            'data-hidden' => (int)$elementIsHidden,
            'data-cid' => $dataArr['colPos'],
            'data-edit-url' => $this->renderEditOnClickReturnUrl($this->renderEditUrl($table, $uid)),
            'data-new-url' => $this->renderEditOnClickReturnUrl($this->renderNewUrl($table, $uid))
        ]);

        /** @var TagBuilder $tagBuilder */
        $tagBuilder = GeneralUtility::makeInstance(
            TagBuilder::class,
            $this->contentEditableWrapperTagName,
            $inlineActionTagBuilder->render() . $content
        );

        $tagBuilder->addAttributes([
            'class' => 't3-frontend-editing__ce ' . $hiddenElementClassName,
            'title' => $recordTitle,
            'data-movable' => 1,
            'ondragstart' => 'window.parent.F.dragCeStart(event)',
            'ondragend' => 'window.parent.F.dragCeEnd(event)',
        ]);

        return $tagBuilder->render();
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
        }
        if ($uid < 0) {
            throw new \InvalidArgumentException('Property "uid" is not valid!', 1486163439);
        }

        if ($this->isUserDisallowedEditingOfContentElement($this->getBackendUser(), $uid)) {
            return $content;
        }

        /** @var TagBuilder $tagBuilder */
        $tagBuilder = GeneralUtility::makeInstance(
            TagBuilder::class,
            $this->contentEditableWrapperTagName
        );

        $tagBuilder->addAttributes([
            'class' => 't3-frontend-editing__dropzone',
            'ondrop' => 'window.parent.F.dropCe(event)',
            'ondragover' => 'window.parent.F.dragCeOver(event)',
            'ondragleave' => 'window.parent.F.dragCeLeave(event)',
            'data-new-url' => $this->renderEditOnClickReturnUrl(
                $this->renderNewUrl(
                    $table,
                    (int)$uid,
                    (int)$colPos,
                    $defaultValues
                )
            ),
            'data-moveafter' => (int)$uid,
            'data-colpos' => $colPos,
            'data-defvals' => json_encode($defaultValues),
        ]);

        $dropZone = $tagBuilder->render();

        return $prepend ? ($dropZone . $content) : ($content . $dropZone);
    }

    /**
     * Add a drop zone before/after the content for custom records
     *
     * @param string $tables
     * @param string $content
     * @param array $defaultValues
     * @param int $pageUid
     * @param bool $prepend
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function wrapContentWithCustomDropzone(
        string $tables,
        string $content,
        array $defaultValues = [],
        int $pageUid = 0,
        bool $prepend = false
    ): string {
        // Check that data is not empty
        if (empty($tables)) {
            throw new \InvalidArgumentException('Property "tables" can not to be empty!', 1486163430);
        }

        /** @var TagBuilder $tagBuilder */
        $tagBuilder = GeneralUtility::makeInstance(
            TagBuilder::class,
            $this->contentEditableWrapperTagName
        );

        $tagBuilder->addAttributes([
            'class' => 't3-frontend-editing__dropzone',
            'ondrop' => 'window.parent.F.dropCe(event)',
            'ondragover' => 'window.parent.F.dragCeOver(event)',
            'ondragleave' => 'window.parent.F.dragCeLeave(event)',
            'data-tables' => $tables,
            'data-pid' => (int)$pageUid,
            'data-defvals' => json_encode($defaultValues),
        ]);

        $dropZone = $tagBuilder->render();

        return $prepend ? ($dropZone . $content) : ($content . $dropZone);
    }

    /**
     * Renders the inline action icons
     *
     * @param string $table
     * @param bool $elementIsHidden
     * @param string $recordTitle
     * @return string
     */
    public function renderInlineActionIcons(string $table, bool $elementIsHidden, string $recordTitle = ''): string
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $visibilityIcon = ($elementIsHidden === true) ?
            $this->renderIconWithWrap('unHide', 'actions-edit-unhide') :
                $this->renderIconWithWrap('hide', 'actions-edit-hide');

        $moveIcons = ($table === 'tt_content') ?
            $this->renderIconWithWrap('moveUp', 'actions-move-up') .
                $this->renderIconWithWrap('moveDown', 'actions-move-down') : '';

        $inlineIcons =
            $this->renderIconWithWrap('edit', 'actions-open', $recordTitle) .
            $visibilityIcon .
            $this->renderIconWithWrap('deleteItem', 'actions-edit-delete') .
            $this->renderIconWithWrap('newRecordGeneral', 'actions-document-new') .
            $moveIcons;

        return $inlineIcons;
    }

    /**
     * Changes the $table and $uid into the record's equivalent in the current language.
     *
     * @param string $table
     * @param int $uid
     */
    protected function switchToLocalLanguageEquivalent(string &$table, int &$uid)
    {
        $typo3VersionNumber = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        );

        /** @var TypoScriptFrontendController $frontendController */
        $frontendController = $GLOBALS['TSFE'];

        /** @var LanguageAspect $languageAspect */
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        $languageId = $languageAspect->getId();

        if ($languageId !== 0) {
            $translatedRecords = BackendUtility::getRecordLocalization(
                $table,
                $uid,
                $languageId
            );

            if (is_array($translatedRecords) && count($translatedRecords) > 0) {
                $translatedRecord = array_pop($translatedRecords);

                if ($translatedRecord) {
                    if ($typo3VersionNumber < 10000000) {
                        // @extensionScannerIgnoreLine
                        $table = BackendUtility::getOriginalTranslationTable($table);
                    }
                    $uid = $translatedRecord['uid'];
                }
            }
        }
    }

    /**
     * Wraps an inline action icon
     *
     * @param string $titleKey
     * @param string $iconKey
     * @param string $recordTitle
     * @return string
     */
    private function renderIconWithWrap(string $titleKey, string $iconKey, string $recordTitle = ''): string
    {
        $editRecordTitle = $GLOBALS['LANG']->sL(
            'LLL:EXT:backend/Resources/Private/Language/locallang_layout.xlf:' . $titleKey
        );

        // Append record title to 'title' attribute
        if ($recordTitle) {
            $editRecordTitle .= ' \'' . $recordTitle . '\'';
        }

        return '<span title="' . $editRecordTitle . '">'
            . $this->iconFactory->getIcon($iconKey, Icon::SIZE_SMALL)->render() . '</span>';
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
        $newUrl = $this->uriBuilder->buildUriFromRoute(
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
     * @param bool $uidAsPid
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function renderNewUrl(
        string $table,
        int $uid = 0,
        int $colPos = 0,
        array $defaultValues = [],
        bool $uidAsPid = false
    ): string {
        if ($uidAsPid) {
            $newId = $uid > 0 ? $uid : (int)$GLOBALS['TSFE']->id;
        } else {
            // Default to top of 'page'
            $newId = (int)$GLOBALS['TSFE']->id;

            // If content uid is supplied, set new content to be 'after'
            if ($uid > 0) {
                $newId = $uid * -1;
            }
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

        $newUrl = $this->uriBuilder->buildUriFromRoute(
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
    public function getContentElementClass(string $table, int $uid): string
    {
        $hiddenClassName = '';
        $row = BackendUtility::getRecord($table, $uid);
        $tcaCtrl = $GLOBALS['TCA'][$table]['ctrl'];
        if ($tcaCtrl['enablecolumns']['disabled'] && $row[$tcaCtrl['enablecolumns']['disabled']] ||
            $tcaCtrl['enablecolumns']['fe_group'] && $GLOBALS['TSFE']->simUserGroup &&
            $row[$tcaCtrl['enablecolumns']['fe_group']] == $GLOBALS['TSFE']->simUserGroup ||
            $tcaCtrl['enablecolumns']['starttime'] &&
                $row[$tcaCtrl['enablecolumns']['starttime']] > $GLOBALS['EXEC_TIME'] ||
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
     * @param array $rawRecord
     * @return string
     */
    public function recordTitle(string $table, array $rawRecord = []): string
    {
        return BackendUtility::getRecordTitle(
            $table,
            $rawRecord
        );
    }

    /**
     * Returns a localized placeholder text based on label. If empty, a default text is returned.
     *
     * @param string $table
     * @param string $field
     * @return string
     */
    protected function getPlaceholderText(string $table, string $field): string
    {
        $placeholderText = $GLOBALS['LANG']->sL(
            $GLOBALS['TCA'][$table]['columns'][$field]['frontendEditingPlaceholder']
        );

        if ($placeholderText === '') {
            $placeholderText = $GLOBALS['LANG']->sL(
                $GLOBALS['TCA'][$table]['columns'][$field]['label']
            );
        }

        if ($placeholderText === '') {
            $placeholderText = $GLOBALS['LANG']->sL(
                'LLL:EXT:frontend_editing/Resources/Private/Language/locallang.xlf:placeholder.default-label'
            );
        } else {
            $placeholderText = sprintf(
                $GLOBALS['LANG']->sL(
                    'LLL:EXT:frontend_editing/Resources/Private/Language/locallang.xlf:placeholder.label-wrap'
                ),
                $placeholderText
            );
        }

        return $placeholderText;
    }

    /**
     * Check if content element editing is disabled by TS config
     *
     * @param BackendUserAuthentication $user
     * @param int $contentUid
     * @return bool $notEditable
     */
    protected function isUserDisallowedEditingOfContentElement(BackendUserAuthentication $user, int $contentUid): bool
    {
        $notEditable = false;
        if (!$user->isAdmin() &&
            $user->getTSConfig()['frontend_editing.']['disallow_content_editing'] &&
            GeneralUtility::inList($user->getTSConfig()['frontend_editing.']['disallow_content_editing'], $contentUid)
        ) {
            $notEditable = true;
        }

        return $notEditable;
    }

    /**
     * Return BE user from global
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        $backendUser = $GLOBALS['BE_USER'];
        if (!$backendUser) {
            $backendUser = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        }

        return $backendUser;
    }
}
