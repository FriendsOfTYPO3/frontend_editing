<?php
namespace TYPO3\CMS\FrontendEditing\ViewHelpers\ContentElement;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\FrontendEditing\Utility\Integration;


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
class RecordTitleViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

	protected $escapeOutput = false;

	/**
	 * @var \TYPO3\CMS\Core\Imaging\IconFactory
	 * @inject
	 */
	protected $iconFactory;

	public function initializeArguments() {
		$this->registerArgument('object', '\TYPO3\CMS\FrontendEditing\Domain\Model\Content', 'The content element object', true);
		$this->registerArgument('size', 'string', 'The content element object', false, Icon::SIZE_DEFAULT);
	}

    /**
     * Returns the "title"-value for the record as shown in backend lists
     *
     * @return string
     */
    public function render()
    {
        return Integration::contentElementTitle($this->arguments['object']->getUid());
    }
}
