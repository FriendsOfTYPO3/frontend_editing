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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\FrontendEditing\Domain\Model\Content;

/**
 * Viewhelper output HTML for an icon for a content element
 *
 * Example:
 * {namespace fe=TYPO3\CMS\FrontendEditing\ViewHelpers}
 *
 * <fe:contentElement.icon object="{contentElement}" size="default" />
 *
 * Output:
 * <span class="t3js-icon icon icon-size-default icon-state-default icon-content-text" data-identifier="content-text">
 *     <span class="icon-markup">
 *         <img src="[path to icon]" width="32" height="32">
 *     </span>
 * </span>
 */
class IconViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var \TYPO3\CMS\Core\Imaging\IconFactory
     * @inject
     */
    protected $iconFactory;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'object',
            Content::class,
            'The content element object',
            true
        );
        $this->registerArgument(
            'size',
            'string',
            'The content element object',
            false,
            Icon::SIZE_DEFAULT
        );
    }

    /**
     * Render an icon for an entity object (i.e. database record)
     *
     * @return string
     */
    public function render()
    {
        $rawRecord = BackendUtility::getRecord(
            'tt_content',
            $this->arguments['object']->getUid()
        );

        return $this->iconFactory->getIconForRecord(
            'tt_content',
            $rawRecord,
            $this->arguments['size']
        );
    }
}
