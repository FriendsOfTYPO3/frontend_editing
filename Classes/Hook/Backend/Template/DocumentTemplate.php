<?php

declare(strict_types=1);
namespace TYPO3\CMS\FrontendEditing\Hook\Backend\Template;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DocumentTemplate
 */
class DocumentTemplate
{
    /**
     * Used within the main backend module to add JS.
     */
    public function mainBackendModule()
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/PageTree');
    }
}
