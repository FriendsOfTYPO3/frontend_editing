<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Controller\Event\Handler;

use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Event Handler Boot loading complete.
 */
final class BootCompletedEventHandler
{
    public function __invoke(BootCompletedEvent $event): void
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(PageRenderer::class);
            $path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

            if (str_ends_with($path, '/browse')) {
                $pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/ParentWindow');
            }
        }
    }
}
