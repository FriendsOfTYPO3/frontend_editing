<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\Mvc\View;

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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;

/**
 * Check access of the user to display only those actions which are allowed and needed
 */
class NotFoundView extends \TYPO3\CMS\Extbase\Mvc\View\NotFoundView
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Renders the not found view with ContentEditable wrapping
     *
     * @return string The rendered view
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception if no request has been set
     * @api
     */
    public function render()
    {
        $template = parent::render();

        $contentObject = $this->configurationManager->getContentObjectRenderer();
        /** @var ContentEditableWrapperService $wrapperService */
        $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);

        return $wrapperService->wrapContent(
            $contentObject->getCurrentTable(),
            (int)$contentObject->data['uid'],
            [],
            $template
        );
    }
}
