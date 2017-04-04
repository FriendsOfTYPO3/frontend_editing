<?php
declare(strict_types=1);
namespace TYPO3\CMS\FrontendEditing\Hook;

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

use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Service\AccessService;

/**
 * Hook class using the "ContentPostProc" hook in TSFE for rendering the panels
 * and iframe for inline editing
 */
class FrontendEditingInitializationHook
{
    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController = null;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * ContentPostProc constructor.
     */
    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Check if this hook should actually replace the content and load the actual
     * page in an iframe
     *
     * @param TypoScriptFrontendController $tsfe
     * @return bool
     */
    protected function isFrontendEditingEnabled(TypoScriptFrontendController $tsfe): bool
    {
        $access = GeneralUtility::makeInstance(AccessService::class);
        if ($access->isEnabled() && $tsfe->type === 0) {
            $isFrontendEditing = GeneralUtility::_GET('frontend_editing');
            if (!isset($isFrontendEditing) && (bool)$isFrontendEditing !== true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Hook to change page output to add the topbar
     *
     * @param array $params
     * @param TypoScriptFrontendController $parentObject
     * @return void
     */
    public function main(array $params, TypoScriptFrontendController $parentObject)
    {
        if (!$this->isFrontendEditingEnabled($parentObject)) {
            return;
        }
        $this->typoScriptFrontendController = $parentObject;

        // Special content is about to be shown, so the cache must be disabled.
        $this->typoScriptFrontendController->set_no_cache('Display frontend editing', true);

        $iframeUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') .
            'index.php?id=' . $this->typoScriptFrontendController->id .
            '&frontend_editing=true';

        // Initialize backend routes
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $endpointUrl = $uriBuilder->buildUriFromRoute(
            'ajax_frontendediting_process',
            ['page' => $this->typoScriptFrontendController->id]
        );
        $configurationEndpointUrl = $uriBuilder->buildUriFromRoute(
            'ajax_frontendediting_editorconfiguration',
            ['page' => $this->typoScriptFrontendController->id]
        );
        $ajaxUrlIcons = $uriBuilder->buildUriFromRoute(
            'ajax_icons'
        );

        // define the window size of the popups within the RTE
        $rtePopupWindowSize = $GLOBALS['BE_USER']->getTSConfigVal('options.rte.popupWindowSize');
        if (!empty($rtePopupWindowSize)) {
            list($rtePopupWindowWidth, $rtePopupWindowHeight) = GeneralUtility::trimExplode('x', $rtePopupWindowSize);
        }
        $rtePopupWindowHeight = !empty($rtePopupWindowHeight) ? (int)$rtePopupWindowHeight : 600;

        // define DateTimePicker dateformat
        $dateFormat = ($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? ['MM-DD-YYYY', 'HH:mm MM-DD-YYYY'] : ['DD-MM-YYYY', 'HH:mm DD-MM-YYYY']);

        // Load available content elements right here, because it adds too much stuff to PageRenderer,
        // so it has to be loaded before
        $availableContentElementTypes = $this->getContentItems();

        // PageRenderer needs to be completely reinitialized
        // Thus, this hack is necessary for now
        $this->pageRenderer = new PageRenderer();
        $this->pageRenderer->setCharset('utf-8');
        $this->pageRenderer->addMetaTag('<meta name="viewport" content="width=device-width, initial-scale=1">');
        $this->pageRenderer->addMetaTag('<meta http-equiv="X-UA-Compatible" content="IE=edge">');
        $this->pageRenderer->setHtmlTag('<html lang="en">');

        $this->loadStylesheets();
        $this->loadJavascriptResources();
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/GUI', 'function(FrontendEditing) {

            // The global F object for API calls
            window.F = new FrontendEditing();
            window.F.initGUI({
                iframeUrl: ' . GeneralUtility::quoteJSvalue($iframeUrl) . ',
                editorConfigurationUrl: ' . GeneralUtility::quoteJSvalue($configurationEndpointUrl) . '
            });
            window.F.setEndpointUrl(' . GeneralUtility::quoteJSvalue($endpointUrl) . ');
            window.F.setTranslationLabels(' . json_encode($this->getLocalizedFrontendLabels()) . ');
            window.TYPO3.settings = {
                Textarea: {
                    RTEPopupWindow: {
                        height: ' . $rtePopupWindowHeight . '
                    }
                },
                ajaxUrls: {
                    icons: \'' . $ajaxUrlIcons . '\'
                },
                DateTimePicker: {
                    DateFormat: ' . json_encode($dateFormat) . '
                }
            };
        }');
        $view = $this->initializeView();
        $view->assignMultiple([
            'overlayOption' => $GLOBALS['BE_USER']->uc['frontend_editing_overlay'],
            'currentUser' => $GLOBALS['BE_USER']->user,
            'currentTime' => $GLOBALS['EXEC_TIME'],
            'currentPage' => $this->typoScriptFrontendController->id,
            'pageTree' => $this->getPageTreeStructure(),
            'contentItems' => $availableContentElementTypes,
            'contentElementsOnPage' => $this->getContentElementsOnPage((int)$this->typoScriptFrontendController->id),
            'logoutUrl'  => $uriBuilder->buildUriFromRoute('logout'),
            'backendUrl' => $uriBuilder->buildUriFromRoute('main'),
            'loadingIcon' => $this->iconFactory->getIcon('spinner-circle-dark', Icon::SIZE_LARGE)->render()
        ]);

        // Assign the content
        $this->pageRenderer->setBodyContent($view->render());
        $parentObject->content = $this->pageRenderer->render();
        // Remove any preview info
        unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_previewInfo']);
    }

    /**
     * Returns an array with labels from translation file
     *
     * @return array
     */
    protected function getLocalizedFrontendLabels(): array
    {
        $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);
        $parsedLocallang = $languageFactory->getParsedData(
            'EXT:frontend_editing/Resources/Private/Language/locallang.xlf',
            'default'
        );
        $localizedLabels = [];
        foreach (array_keys($parsedLocallang['default']) as $key) {
            $localizedLabels[$key] = LocalizationUtility::translate($key, 'FrontendEditing');
        }
        return $localizedLabels;
    }

    /**
     * Load the necessary CSS resources for the toolbars
     */
    protected function loadStylesheets()
    {
        $files = [
            'EXT:frontend_editing/Resources/Public/Css/frontend_editing.css'
        ];
        foreach ($files as $file) {
            $this->pageRenderer->addCssFile($file);
        }
    }

    /**
     * Load the necessary Javascript-resources for the toolbars
     */
    protected function loadJavascriptResources()
    {
        $this->pageRenderer->loadJquery();
        $this->pageRenderer->loadRequireJs();
        $this->pageRenderer->addRequireJsConfiguration(
            [
                'shim' => [
                    'ckeditor' => ['exports' => 'CKEDITOR'],
                    'ckeditor-jquery-adapter' => ['jquery', 'ckeditor'],
                ],
                'paths' => [
                    'ckeditor-jquery-adapter' => $this->getAbsolutePath('EXT:frontend_editing/Resources/Public/JavaScript/Contrib/ckeditor-jquery-adapter'),
                    'toastr' => $this->getAbsolutePath('EXT:frontend_editing/Resources/Public/JavaScript/Contrib/toastr'),
                    'immutable' => $this->getAbsolutePath('EXT:frontend_editing/Resources/Public/JavaScript/Contrib/immutable'),
                    'alertify' => $this->getAbsolutePath('EXT:frontend_editing/Resources/Public/JavaScript/Contrib/alertify'),
                    'ckeditor' => $this->getAbsolutePath('EXT:rte_ckeditor/Resources/Public/JavaScript/Contrib/ckeditor'),
                ]
            ]
        );
    }

    /**
     * Helper method to get the file name relative to the URL
     *
     * @param string $path
     * @return string
     */
    protected function getAbsolutePath(string $path): string
    {
        return PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($path));
    }

    /**
     * Get the page tree structure of the current tree
     *
     * @return array
     */
    protected function getPageTreeStructure(): array
    {
        // Get page record for tree starting point
        // from where we currently are navigated
        $startingPoint = $this->typoScriptFrontendController->rootLine[0]['uid'];
        $pageRecord = BackendUtility::getRecord('pages', $startingPoint);

        // Creating the icon for the current page and add it to the tree
        $html = $this->iconFactory->getIconForRecord(
            'pages',
            $pageRecord,
            Icon::SIZE_SMALL
        );

        // Create and initialize the tree object
        $tree = GeneralUtility::makeInstance(PageTreeView::class);
        $tree->init(' AND ' . $GLOBALS['BE_USER']->getPagePermsClause(1));
        $tree->tree[] = [
            'row' => $pageRecord,
            'HTML' => $html
        ];

        // Create the page tree, from the starting point, infinite levels down
        $tree->getTree($startingPoint);

        return $tree->tree;
    }

    /**
     * Get the available content elements from TYPO3 backend
     *
     * @return array
     */
    protected function getContentItems(): array
    {
        $contentController = GeneralUtility::makeInstance(NewContentElementController::class);
        $wizardItems = $contentController->wizardArray();
        $contentItems = [];
        $wizardTabKey = '';
        foreach ($wizardItems as $wizardKey => $wizardItem) {
            if (isset($wizardItem['header'])) {
                $wizardTabKey = $wizardKey;
                $contentItems[$wizardTabKey]['description'] = $wizardItem['header'];
            } else {
                $contentItems[$wizardTabKey]['items'][] = array_merge(
                    $wizardItem,
                    [
                        'iconHtml' => $this->iconFactory->getIcon($wizardItem['iconIdentifier'])->render()
                    ]
                );
            }
        }
        return $contentItems;
    }

    /**
     * Get the content elements on the page
     *
     * @param int $pageId The page id to fetch content elements from
     * @return array
     */
    protected function getContentElementsOnPage(int $pageId): array
    {
        if (!$this->typoScriptFrontendController->cObj instanceof ContentObjectRenderer) {
            $this->typoScriptFrontendController->newCObj();
        }
        $contentElements = $this->typoScriptFrontendController->cObj->getRecords(
            'tt_content',
            [
                'pidInList' => $pageId
            ]
        );
        foreach ($contentElements as &$contentElement) {
            $contentElement['_recordTitle'] = BackendUtility::getRecordTitle('tt_content', $contentElement);
        }
        return $contentElements;
    }

    /**
     * Initializes the Fluid standalone view with the paths etc.
     *
     * @return StandaloneView
     */
    protected function initializeView(): StandaloneView
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $renderingContext = $view->getRenderingContext();
        $renderingContext->getTemplatePaths()->fillDefaultsByPackageName('frontend_editing');
        $renderingContext->setControllerName('Toolbars');
        $renderingContext->setControllerAction('Toolbars');

        return $view;
    }
}
