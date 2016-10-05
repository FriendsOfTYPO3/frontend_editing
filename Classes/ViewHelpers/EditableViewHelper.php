<?php
namespace TYPO3\CMS\FrontendEditing\ViewHelpers;

use TYPO3\CMS\FrontendEditing\Utility\Access;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Viewhelper to enable aloha for records in fluid
 *
 * Example:
 * {namespace fe=TYPO3\CMS\FrontendEditing\ViewHelpers}
 *
 * <fe:editable table="tt_content" field="bodytext" uid="{item.uid}"">
 *     {item.bodytext}
 * </fe:editable>
 */
class EditableViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'div';

    /**
     * Render aloha integration for a single field
     *
     * @param string $table table of record
     * @param string $field database field of record
     * @param integer $uid uid of record
     * @return string
     */
    public function render($table, $field, $uid)
    {
        $content = $this->renderChildren();
        // @TODO: Find out why the HTML is not rendered properly
        $content = htmlspecialchars_decode($content);

        if (Access::isEnabled()) {
            $content = sprintf(
                '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
                $table,
                $field,
                $uid,
                $content
            );

            $this->tag->setContent($content);

            return $this->tag->render();
        }

        return $content;
    }
}
