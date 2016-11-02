<?php
namespace TYPO3\CMS\FrontendEditing\Utility\ContentEditable;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

/**
 * Class ContentEditableWrapper
 * A class for adding wrapping for a content element to be editable
 *
 * @package TYPO3\CMS\FrontendEditing\Utility\ContentEditable
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
            '<span class="t3-frontend-editing__inline-actions">%s</span>' .
                '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
            self::renderInlineActionIcons(),
            $table,
            $field,
            $uid,
            $content
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
            $iconFactory->getIcon('actions-edit-delete', Icon::SIZE_SMALL)->render() .
            $iconFactory->getIcon('actions-move-up', Icon::SIZE_SMALL)->render() .
            $iconFactory->getIcon('actions-move-down', Icon::SIZE_SMALL)->render();

        return $inlineIcons;
    }
}
