<?php
namespace TYPO3\CMS\FrontendEditing\Utility\ContentEditable;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class ContentEditableWrapper
 * A class for adding wrapping for a content element to be editable
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
            '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
            $table,
            $field,
            $uid,
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

        // @TODO: include config as parameter and make cid (columnIdentifier) able to set by combining fields
        // Could make it would make it possible to configure cid for use with extensions that create columns by content
        $class = 't3-frontend-editing__inline-actions';
        $content = sprintf(
            '<div class="t3-frontend-editing__ce" title="%s">' .
                '<span class="%s" data-table="%s" data-uid="%s" data-cid="%s" data-edit-url="%s">%s</span>' .
                '%s' .
            '</div>',
            $uid,
            $class,
            $table,
            $uid,
            $dataArr['colPos'],
            self::renderEditOnClickReturnUrl(self::renderEditUrl($table, $uid)),
            self::renderInlineActionIcons(),
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
     * @return string
     */
    public static function renderInlineActionIcons()
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $inlineIcons =
            $iconFactory->getIcon('actions-edit-add', Icon::SIZE_SMALL)->render() .
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
}
