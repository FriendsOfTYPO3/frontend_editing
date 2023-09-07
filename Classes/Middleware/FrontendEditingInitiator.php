<?php

declare(strict_types=1);
namespace TYPO3\CMS\FrontendEditing\Middleware;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Html\FrontendEditingSanitizerBuilder;
use TYPO3\CMS\FrontendEditing\Service\AccessService;

/**
 * This middleware initialize the Frontend Editing
 * adding the necessary Javascript to the frontend page
 */
class FrontendEditingInitiator implements MiddlewareInterface
{
    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var array
     */
    protected $pluginConfiguration = [];

    /**
     * ContentPostProc constructor.
     */
    public function __construct()
    {
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $this->pageRenderer = new PageRenderer();
    }

    /**
     * Modify the frontend request if Frontend Editing is enabled
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (AccessService::isEnabled()) {
            // Get TSFE controller from the request
            $this->typoScriptFrontendController = $request->getAttribute('frontend.controller');

            // Make sure edit icons are displayed
            $GLOBALS['TSFE']->displayFieldEditIcons = 1;

            // Change the sanitizer to allow the attributes used in Frontend Editing.
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['htmlSanitizer']['default'] =
                FrontendEditingSanitizerBuilder::class;

            // Special content is about to be shown, so the cache must be disabled.
            $this->typoScriptFrontendController->set_no_cache('Frontend Editing enabled', true);

            // Set the preview info message as an HTML comment, so it doesn't display
            $this->typoScriptFrontendController->config['config']['message_preview'] = '<!-- FRONTEND EDITING PREVIEW -->';
        }

        return $handler->handle($request);
    }
}
