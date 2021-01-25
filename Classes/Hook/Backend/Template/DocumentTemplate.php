<?php
declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Hook\Backend\Template;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class DocumentTemplate
 *
 * @package Wazum\PagetreeResizable\Hooks\Backend\Template
 * @author Wolfgang Klinger <wolfgang@wazum.com>
 */
class DocumentTemplate
{
    /**
     * Executed when makeInstance is used (= in the link handler)
     * Used in TYPO3 v10, but can replace all other parts as well.
     */
    public function __construct()
    {
        /*if (TYPO3_MODE === 'BE') {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            if ($width = $this->getUserElementBrowserTreeWidth()) {
                $pageRenderer->addCssInlineBlock('wazum/pagetree-resizable', '
                .element-browser-main .element-browser-main-sidebar {
                    width: ' . $width . 'px;
                }
            ');
            }
            $this->includeRequiredResources();
        }*/
    }

    /**
     * Used within the main backend module to add JS + CSS.
     */
    public function mainBackendModule()
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/PageTree');
        /*$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        if ($width = $this->getUserPageTreeWidth()) {
            $pageRenderer->addCssInlineBlock('wazum/pagetree-resizable', '
            .scaffold-content-navigation-expanded .scaffold-content-navigation {
                width: ' . $width . 'px;
            }
            .scaffold-content-navigation-expanded .scaffold-content-module {
                left: ' . $width . 'px;
            }
        ');
        }
        $this->includeRequiredResources();*/
    }

    /**
     * Load CSS & JS resources into PageRenderer
     */
    protected function includeRequiredResources()
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        list($version) = explode('.', VersionNumberUtility::getCurrentTypo3Version());
        $pageRenderer->addCssFile('EXT:pagetree_resizable/Resources/Public/Stylesheet/PagetreeResizable.css');
        if ((int)$version === 8) {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/PagetreeResizable/PagetreeResizable8');
        } else {
            // TYPO3 9, 10
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/PagetreeResizable/PagetreeResizable9');
        }
    }

    /**
     * @param array $parameters
     * @param \TYPO3\CMS\Backend\Template\DocumentTemplate $parent
     * @return void
     */
    public function preHeaderRenderHook(array $parameters, \TYPO3\CMS\Backend\Template\DocumentTemplate $parent)
    {
        // var_dump('MATTIAS');die;
        /** @var PageRenderer $pageRenderer */
        /*$pageRenderer = $parameters['pageRenderer'];

        if ($parent->scriptID === 'rte/wizard/browselinks') {
            if ($width = $this->getUserElementBrowserTreeWidth()) {
                $pageRenderer->addCssInlineBlock('wazum/pagetree-resizable', '
                .element-browser .element-browser-main .element-browser-main-sidebar {
                    width: ' . $width . 'px;
                }
            ');
            }
        } else {
            if ($width = $this->getUserPageTreeWidth()) {
                $pageRenderer->addCssInlineBlock('wazum/pagetree-resizable', '
                .scaffold-content-navigation-expanded .scaffold-content-navigation {
                    width: ' . $width . 'px;
                }
                .scaffold-content-navigation-expanded .scaffold-content-module {
                    left: ' . $width . 'px;
                }
            ');
            }
        }

        list($version) = explode('.', VersionNumberUtility::getCurrentTypo3Version());
        $pageRenderer->addCssFile('EXT:pagetree_resizable/Resources/Public/Stylesheet/PagetreeResizable.css');
        if ((int)$version === 8) {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/PagetreeResizable/PagetreeResizable8');
        } else {
            // TYPO3 9, 10
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/PagetreeResizable/PagetreeResizable9');
        }*/
    }

    /**
     * @return int
     */
    protected function getUserPageTreeWidth(): int
    {
        return (int)($GLOBALS['BE_USER']->uc['Backend']['PagetreeResizable']['width'] ?? 0);
    }

    /**
     * @return int
     */
    protected function getUserElementBrowserTreeWidth(): int
    {
        return (int)($GLOBALS['BE_USER']->uc['Backend']['PagetreeResizable']['Browser']['width'] ?? 0);
    }
}
