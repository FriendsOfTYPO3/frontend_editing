<?php
namespace TYPO3\CMS\FrontendEditing\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\CMS\FrontendEditing\Utility\Access;
use TYPO3\CMS\FrontendEditing\Utility\ContentEditable\ContentEditableWrapper;

/**
 * Viewhelper to enable frontend editing for records in fluid
 *
 * Example:
 * {namespace fe=TYPO3\CMS\FrontendEditing\ViewHelpers}
 *
 * <fe:editable table="tt_content" field="bodytext" uid="{item.uid}">
 *     {item.bodytext}
 * </fe:editable>
 *
 * Output:
 * <div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">
 *     This is the content text to edit
 * </div>
 */
class EditableViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = '';

    /**
     * Initialize arguments
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'table',
            'string',
            'The database table name to be used for saving the content',
            true
        );
        $this->registerArgument(
            'field',
            'string',
            'The database table field name to be used for saving the content',
            true
        );
        $this->registerArgument(
            'uid',
            'string',
            'The database uid (identifier) to be used for the record and saving the content',
            true
        );
        $this->registerArgument(
            'disableAccessCheck',
            'boolean',
            'Deactive/Activate the access check for TYPO3 backend',
            false
        );
        $this->registerUniversalTagAttributes();
    }

    /**
     * Render CKEditor integration for a single field
     *
     * @return string
     */
    public function render()
    {
        $content = $this->renderChildren();
        // @TODO: Find out why the HTML is not rendered properly
        $content = htmlspecialchars_decode($content);

        if (Access::isEnabled() || $this->arguments['disableAccessCheck'] === true) {
            $content = ContentEditableWrapper::wrapContentToBeEditable(
                $this->arguments['table'],
                $this->arguments['field'],
                $this->arguments['uid'],
                $content
            );

            $this->tag->setContent($content);
            $this->tag->forceClosingTag(true);

            return $this->tag->getContent();
        }

        return $content;
    }
}
