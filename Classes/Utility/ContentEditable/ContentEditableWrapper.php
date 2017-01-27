<?php
namespace TYPO3\CMS\FrontendEditing\Utility\ContentEditable;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\FrontendEditing\Utility\Integration;

/**
 * A class for adding wrapping for a content element to be editable
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ContentEditableWrapper
{

    /**
     * Add the proper wrapping (html tag) to make the content editable by CKEditor
     *
     * @param string $table
     * @param string $field
     * @param string $uid
     * @param string $content
     * @return string
     * @throws \Exception
     */
    public static function wrapContentToBeEditable($table, $field, $uid, $content)
    {
        // Check that data is not empty
        if (empty($table)) {
            throw new \Exception('Property "table" can not to be empty!');
        } elseif (empty($field)) {
            throw new \Exception('Property "field" can not to be empty!');
        } elseif (empty($uid)) {
            throw new \Exception('Property "uid" can not to be empty!');
        }

        $content = sprintf(
            '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s" class="%s">%s</div>',
            $table,
            $field,
            $uid,
            self::checkIfContentElementIsHidden($table, $uid),
            $content
        );

        return $content;
    }

    /**
     * Wrap content
     *
     * @param string $table
     * @param string $uid
     * @param array $dataArr
     * @param string $content
     * @return string
     * @throws \Exception
     */
    public static function wrapContent($table, $uid, $dataArr, $content)
    {
        // Check that data is not empty
        if (empty($table)) {
            throw new \Exception('Property "table" can not to be empty!');
        } elseif (empty($uid)) {
            throw new \Exception('Property "uid" can not to be empty!');
        }

        $hiddenElementClassName = self::checkIfContentElementIsHidden($table, $uid);
        $elementIsHidden = ($hiddenElementClassName === '') ? false : true;

        // @TODO: include config as parameter and make cid (columnIdentifier) able to set by combining fields
        // Could make it would make it possible to configure cid for use with extensions that create columns by content
        $class = 't3-frontend-editing__inline-actions';
        $content = sprintf(
            '<div class="t3-frontend-editing__ce %s" title="%s">' .
                '<span class="%s" data-table="%s" data-uid="%s" data-hidden="%s"' .
                    ' data-cid="%s" data-edit-url="%s">%s</span>' .
                '%s' .
            '</div>',
            $hiddenElementClassName,
            Integration::contentElementTitle($uid),
            $class,
            $table,
            $uid,
            intval($elementIsHidden),
            $dataArr['colPos'],
            self::renderEditOnClickReturnUrl(self::renderEditUrl($table, $uid)),
            self::renderInlineActionIcons($elementIsHidden),
            $content
        );

        return $content;
    }

    /**
     * Add a drop zone after the content
     *
     * @param string $table
     * @param string $uid
     * @param string $content
     * @return string
     * @throws \Exception
     */
    public static function wrapContentWithDropzone($table, $uid, $content)
    {
        // Check that data is not empty
        if (empty($table)) {
            throw new \Exception('Property "table" can not to be empty!');
        } elseif (empty($uid)) {
            throw new \Exception('Property "uid" can not to be empty!');
        }

        $jsFuncOnDrop = 'window.parent.F.dropNewCe(event)';
        $jsFuncOnDragover = 'window.parent.F.dragNewCeOver(event)';
        $jsFuncOnDragLeave = 'window.parent.F.dragNewCeLeave(event)';
        $class = 't3-frontend-editing__dropzone';

        $content = sprintf(
            '%s' . '<div class="%s" ondrop="%s" ondragover="%s" ondragleave="%s" data-new-url="%s"></div>',
            $content,
            $class,
            $jsFuncOnDrop,
            $jsFuncOnDragover,
            $jsFuncOnDragLeave,
            self::renderEditOnClickReturnUrl(self::renderNewUrl($table, $uid))
        );

        return $content;
    }

    /**
     * Renders the inline action icons
     *
     * @param boolean $elementIsHidden
     * @return string
     */
    public static function renderInlineActionIcons($elementIsHidden)
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $visibilityIcon = ($elementIsHidden === true) ?
            $iconFactory->getIcon('actions-edit-unhide', Icon::SIZE_SMALL)->render() :
            $iconFactory->getIcon('actions-edit-hide', Icon::SIZE_SMALL)->render();

        $inlineIcons =
            $iconFactory->getIcon('actions-edit-add', Icon::SIZE_SMALL)->render() .
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
    public static function renderEditUrl($table, $uid)
    {
        $editUrl = BackendUtility::getModuleUrl(
            'record_edit',
            [
                'edit[' . $table . '][' . $uid . ']' => 'edit',
                'noView' => (GeneralUtility::_GP('ADMCMD_view') ? 1 : 0),
                'feEdit' => 1
            ]
        );

        return $editUrl;
    }

    /**
     * Render a new content elememnt url to the backend content wizard
     *
     * @param string $table
     * @param int $uid
     * @return string
     */
    public static function renderNewUrl($table, $uid = 0)
    {
        // Default to top of 'page'
        $newId = $GLOBALS['TSFE']->id;

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

        return $newUrl;
    }

    /**
     * Render the onclick return url for when open an edit window
     *
     * @param string $url
     * @return string
     */
    public static function renderEditOnClickReturnUrl($url)
    {
        $returnUrl = $url . '&returnUrl=' .
            PathUtility::getAbsoluteWebPath(
                ExtensionManagementUtility::siteRelPath('frontend_editing') .
                'Resources/Public/Templates/Close.html'
            );

        return $returnUrl;
    }

    /**
     * Check if the content element is hidden and return a proper class name
     *
     * @param string $table
     * @param string $uid
     * @return string $hiddenClassName
     */
    public static function checkIfContentElementIsHidden($table, $uid)
    {
        $hiddenClassName = '';
        $hidden = Integration::recordInfo($table, $uid, '*');
        if ($hidden['hidden']) {
            $hiddenClassName = 't3-frontend-editing__hidden-element';
        }

        return $hiddenClassName;
    }
}
