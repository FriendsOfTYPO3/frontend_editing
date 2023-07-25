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
    public function __invoke(BootCompletedEvent $event): void
    {
        $requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');
        if ($requestUri !== '') {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $path = urldecode(parse_url($requestUri, PHP_URL_PATH));

            if (str_ends_with($path, '/browse')) {
                $pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/ParentWindow');
            }
        }
    }
}
