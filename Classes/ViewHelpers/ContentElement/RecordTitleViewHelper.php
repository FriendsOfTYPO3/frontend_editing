<?php
namespace TYPO3\CMS\FrontendEditing\ViewHelpers\ContentElement;

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

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\FrontendEditing\Utility\Integration;

/**
 * Viewhelper for printing a content element's name
 *
 * Example:
 * {namespace fe=TYPO3\CMS\FrontendEditing\ViewHelpers}
 *
 * <fe:contentElement.recordTitle object="{contentElement}" size="default" />
 *
 * Output:
 * The Content Element's name
 */
class RecordTitleViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
	/**
	 * @var \TYPO3\CMS\Core\Imaging\IconFactory
	 * @inject
	 */
	protected $iconFactory;

	public function initializeArguments() {
		$this->registerArgument('object', '\TYPO3\CMS\FrontendEditing\Domain\Model\Content', 'The content element object', true);
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
