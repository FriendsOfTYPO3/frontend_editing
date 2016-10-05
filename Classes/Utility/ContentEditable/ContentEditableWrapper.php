<?php
namespace TYPO3\CMS\FrontendEditing\Utility\ContentEditable;

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
     */
    public static function wrapContentToBeEditable($table, $field, $uid, $content)
    {
        $content = sprintf(
            '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
            $table,
            $field,
            $uid,
            $content
        );

        return $content;
    }
}
