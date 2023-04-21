<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Controller\Event\Handler;

use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Page\PageRenderer;

final class BootCompletedEventHandler
{
    public function __invoke(BootCompletedEvent $event): void
    {
        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(PageRenderer::class);
        $path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        if ('/typo3/wizard/record/browse' === $path) {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/ParentWindow');
        }
    }
}