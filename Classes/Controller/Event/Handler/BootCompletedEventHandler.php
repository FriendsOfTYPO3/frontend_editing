<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Controller\Event\Handler;

use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Event Handler Boot loading complete.
 */
final class BootCompletedEventHandler
{
    /**
     * @param BootCompletedEvent $event
     * @return void
     */
    public function __invoke(BootCompletedEvent $event): void
    {
        // Load JS ParentWindow when user opens the element browser (file selector)
        $requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');
        if ($requestUri !== '') {
            $phpUrlPath = parse_url($requestUri, PHP_URL_PATH);
            if ($phpUrlPath) {
                $path = urldecode($phpUrlPath);
                if (str_ends_with($path, '/browse')) {
                    $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
                    $pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/ParentWindow');
                }
            }
        }
    }
}
