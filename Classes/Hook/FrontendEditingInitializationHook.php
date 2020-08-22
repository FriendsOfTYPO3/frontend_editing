<?php
declare(strict_types = 1);

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3\CMS\FrontendEditing\Service\ExtensionManagerConfigurationService;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Hook class using the "ContentPostProc" hook in TSFE for rendering the panels
 * and iframe for inline editing
 */
class FrontendEditingInitializationHook
{
    /**
     * @var AccessService
     */
    protected $accessService;

    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController = null;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var IconRegistry
     */
    protected $iconRegistry;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var array
     */
    protected $pluginConfiguration;

    /**
     * Flag if can use router for URL generation
     *
     * @var bool
     */
    protected $isSiteConfigurationFound = false;

    /**
     * ContentPostProc constructor.
     */
    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        $this->pluginConfiguration = [];

        // If this is TYPO3 9 and site configuration was found
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) > 9000000
            // @extensionScannerIgnoreLine
            && isset($GLOBALS['TYPO3_REQUEST'])
            // @extensionScannerIgnoreLine
            && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site
            && $this->isFrontendEditingEnabled($GLOBALS['TSFE'])
        ) {
            $this->isSiteConfigurationFound = true;

            $GLOBALS['TSFE']->fePreview = 0;

            // Allow hidden pages for links generation
            $context = GeneralUtility::makeInstance(Context::class);
            $context->setAspect('visibility', GeneralUtility::makeInstance(VisibilityAspect::class, true));
        }
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
        $this->accessService = GeneralUtility::makeInstance(AccessService::class);

        if ($this->accessService->isEnabled() && $tsfe->type === 0) {
            $isFrontendEditing = GeneralUtility::_GET('frontend_editing');
            if (!isset($isFrontendEditing) && (bool)$isFrontendEditing !== true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Hook to unset page setup before render the toolbars to speed up the render
     * the real frontend page will be called and rendered later
     * with query parameter 'frontend_editing=true'
     *
     * @param array $params
     * @param TypoScriptFrontendController $parentObject
     */
    public function unsetPageSetup(array $params, TypoScriptFrontendController $parentObject)
    {
        if (!$this->isFrontendEditingEnabled($parentObject)) {
            return;
        }
        $parentObject->pSetup = [];
        $parentObject->config['config']['disableAllHeaderCode'] = true;
    }

    /**
     * Hook to change page output to add the topbar
     *
     * @param array $params
     * @param TypoScriptFrontendController $parentObject
     * @throws \Exception
     */
    public function main(array $params, TypoScriptFrontendController $parentObject)
    {
        if (!$this->isFrontendEditingEnabled($parentObject)) {
            $dom = new \DOMDocument();
            $dom->loadHTML($parentObject->content, LIBXML_NOWARNING | LIBXML_NOERROR);

            $domWasModified = false;

            /** @var \DOMElement $element */
            foreach ($dom->getElementsByTagName('a') as $element) {
                $parsedUrl = parse_url($element->getAttribute('href'));

                if ($parsedUrl['query'] !== null) {
                    $queryArguments = GeneralUtility::explodeUrl2Array($parsedUrl['query']);

                    if (isset($queryArguments['frontend_editing'])) {
                        unset($queryArguments['frontend_editing']);

                        $parsedUrl['query'] = GeneralUtility::implodeArrayForUrl('', $queryArguments);

                        $element->setAttribute('href', HttpUtility::buildUrl($parsedUrl));
                        $domWasModified = true;
                    }
                }
            }

            if ($domWasModified) {
                $parentObject->content = $dom->saveHTML();
            }

            return;
        }

        if (!GeneralUtility::_GP('id')) {
            $_GET['id'] = $parentObject->getRequestedId();
        }

        $this->typoScriptFrontendController = $parentObject;

        // Special content is about to be shown, so the cache must be disabled.
        $this->typoScriptFrontendController->set_no_cache('Display frontend editing', true);

        $requestUrl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        // Check if url has a ?, then decide on URL separator
        if (strpos($requestUrl, '?') !== false) {
            $urlSeparator = '&';
        } else {
            $urlSeparator = '?';
        }
        $requestUrl = $requestUrl . $urlSeparator . 'frontend_editing=true&no_cache=1';

        // If not language service is set then create one
        if ($GLOBALS['LANG'] === null) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
            $GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
        }

        // Initialize backend routes
        /** @var UriBuilder $uriBuilder */
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

        $returnUrl = PathUtility::getAbsoluteWebPath(
            GeneralUtility::getFileAbsFileName(
                'EXT:frontend_editing/Resources/Public/Templates/Close.html'
            ) . '?'
        );

        // Define the window size of the popups within the RTE
        $rtePopupWindowSize = $GLOBALS['BE_USER']->getTSConfig()['options.']['rte.']['popupWindowSize'];

        if (!empty($rtePopupWindowSize)) {
            list(, $rtePopupWindowHeight) = GeneralUtility::trimExplode('x', $rtePopupWindowSize);
        }
        $rtePopupWindowHeight = !empty($rtePopupWindowHeight) ? (int)$rtePopupWindowHeight : 600;

        // Define DateTimePicker dateformat
        $dateFormat = ($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ?
            ['MM-DD-YYYY', 'HH:mm MM-DD-YYYY'] : ['DD-MM-YYYY', 'HH:mm DD-MM-YYYY']);

        // Load available content elements right here, because it adds too much stuff to PageRenderer,
        // so it has to be loaded before
        $availableContentElementTypes = $this->getContentItems();

        $baseUrl = ($this->getPluginConfiguration()['baseUrl']) ? $this->getPluginConfiguration()['baseUrl'] : '/';

        // PageRenderer needs to be completely reinitialized
        // Thus, this hack is necessary for now
        $this->pageRenderer = new PageRenderer();
        $this->pageRenderer->setBaseUrl($baseUrl);
        $this->pageRenderer->setCharset('utf-8');

        $this->pageRenderer->setMetaTag('name', 'viewport', 'width=device-width, initial-scale=1');
        $this->pageRenderer->setMetaTag('http-equiv', 'X-UA-Compatible', 'IE=edge');

        $this->pageRenderer->setHtmlTag('<!DOCTYPE html><html lang="en">');

        $resourcePath = 'EXT:frontend_editing/Resources/Public/';
        $this->loadStylesheets();
        $this->loadJavascriptResources();
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/GUI', 'function(FrontendEditing) {

            // The global F object for API calls
            window.F = new FrontendEditing();
            window.F.initGUI({
                content: ' . GeneralUtility::quoteJSvalue($this->typoScriptFrontendController->content) . ',
                resourcePath: ' . GeneralUtility::quoteJSvalue($this->getAbsolutePath($resourcePath)) . ',
                iframeUrl: ' . GeneralUtility::quoteJSvalue($requestUrl) . ',
                editorConfigurationUrl: ' . GeneralUtility::quoteJSvalue($configurationEndpointUrl) . '
            });
            window.F.setEndpointUrl(' . GeneralUtility::quoteJSvalue($endpointUrl) . ');
            window.F.setBESessionId(' . GeneralUtility::quoteJSvalue($this->getBeSessionKey()) . ');
            window.F.setTranslationLabels(' . json_encode($this->getLocalizedFrontendLabels()) . ');
            window.F.setDisableModalOnNewCe(' .
                (int)ExtensionManagerConfigurationService::getSettings()['enablePlaceholders'] .
            ');
            window.FrontendEditingMode = true;
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
            'currentTime' => $GLOBALS['EXEC_TIME'],
            'currentPage' => $this->typoScriptFrontendController->id,
            'contentItems' => $availableContentElementTypes,
            'contentElementsOnPage' => $this->getContentElementsOnPage((int)$this->typoScriptFrontendController->id),
            'customRecords' => $this->getCustomRecords(),
            'loadingIcon' => $this->iconFactory->getIcon('spinner-circle-dark', Icon::SIZE_LARGE)->render(),
            'showHiddenItemsUrl' => $requestUrl . '&show_hidden_items=' . $this->showHiddenItems(),
        ]);

        // Assign the content
        $this->pageRenderer->setBodyContent($view->render());
        $parentObject->content = $this->pageRenderer->render();
        $parentObject->setAbsRefPrefix();
        // Remove any preview info
        // @extensionScannerIgnoreLine
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
            'EXT:frontend_editing/Resources/Public/Css/frontend_editing.css',
            'EXT:backend/Resources/Public/Css/backend.css'
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
        $this->pageRenderer->addJsFile(
            'EXT:core/Resources/Public/JavaScript/Contrib/jquery/jquery.js'
        );

        $this->pageRenderer->loadRequireJs();

        $this->pageRenderer->addRequireJsConfiguration(
            [
                'shim' => [
                    'ckeditor' => ['exports' => 'CKEDITOR'],
                    'ckeditor-jquery-adapter' => ['jquery', 'ckeditor'],
                ],
                'paths' => [
                    'TYPO3/CMS/FrontendEditing/Contrib/toastr' =>
                        'EXT:frontend_editing/Resources/Public/JavaScript/Contrib/toastr',
                    'TYPO3/CMS/FrontendEditing/Contrib/immutable' =>
                        'EXT:frontend_editing/Resources/Public/JavaScript/Contrib/immutable'
                ]
            ]
        );

        $this->pageRenderer->addJsFile(
            'EXT:backend/Resources/Public/JavaScript/backend.js'
        );
        // Load CKEDITOR and CKEDITOR jQuery adapter independent for global access
        $this->pageRenderer->addJsFile('EXT:rte_ckeditor/Resources/Public/JavaScript/Contrib/ckeditor.js');
        $this->pageRenderer->addJsFile(
            'EXT:frontend_editing/Resources/Public/JavaScript/Contrib/ckeditor-jquery-adapter.js'
        );

        $configuration = $this->getPluginConfiguration();
        if (is_array($configuration['includeJS'])) {
            foreach ($configuration['includeJS'] as $file) {
                $this->pageRenderer->addJsFile($file);
            }
        }
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
     * Get the available content elements from TYPO3 backend
     *
     * @return array
     */
    protected function getContentItems(): array
    {
        $contentController = $this->getNewContentElementController();

        $wizardItems = $contentController->getWizards();

        $this->wizardItemsHook($wizardItems, $contentController);

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
     * Call registered hooks to manipulate wizard items
     *
     * @param array &$wizardItems
     * @param NewContentElementController $contentController
     */
    protected function wizardItemsHook(array &$wizardItems, NewContentElementController $contentController)
    {
        $newContentElement = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el'];
        // Wrapper for wizards
        // Hook for manipulating wizardItems, wrapper, onClickEvent etc.
        if (is_array($newContentElement['wizardItemsHook'])) {
            foreach ($newContentElement['wizardItemsHook'] as $classData) {
                $hookObject = GeneralUtility::makeInstance($classData);
                if (!$hookObject instanceof NewContentElementWizardHookInterface) {
                    throw new \UnexpectedValueException(
                        $classData . ' must implement interface ' . NewContentElementWizardHookInterface::class,
                        1227834741
                    );
                }

                $hookObject->manipulateWizardItems($wizardItems, $contentController);
            }
        }
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
                'pidInList' => $pageId,
                'orderBy' => 'sorting'
            ]
        );
        foreach ($contentElements as &$contentElement) {
            $contentElement['_recordTitle'] = BackendUtility::getRecordTitle('tt_content', $contentElement);
        }
        return $contentElements;
    }

    /**
     * Get custom records defined in TypoScript
     *
     * @return array
     */
    protected function getCustomRecords(): array
    {
        $records = [];
        $configuration = $this->getPluginConfiguration();
        if (is_array($configuration['customRecords'])) {
            /** @var ContentEditableWrapperService $wrapperService */
            $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);
            foreach ($configuration['customRecords'] as $record) {
                $pid = (int)$record['pid'] ?: $this->typoScriptFrontendController->id;
                $page = BackendUtility::getRecord('pages', $pid);
                if (is_array($record) &&
                    $record['table'] &&
                    isset($GLOBALS['TCA'][$record['table']]) &&
                    $GLOBALS['BE_USER']->check('tables_modify', $record['table']) &&
                    $page && $GLOBALS['BE_USER']->doesUserHaveAccess($page, 16)
                ) {
                    $records[] = [
                        'title' => $GLOBALS['TCA'][$record['table']]['ctrl']['title'],
                        'table' => $record['table'],
                        'url' => $wrapperService->renderEditOnClickReturnUrl($wrapperService->renderNewUrl(
                            $record['table'],
                            (int)$pid,
                            0,
                            is_array($record['defVals']) ? $record['defVals'] : [],
                            true
                        )),
                    ];
                }
            }
        }
        return $records;
    }

    /**
     * Initializes the Fluid standalone view with the paths etc.
     *
     * @return StandaloneView
     */
    protected function initializeView(): StandaloneView
    {
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $renderingContext = $view->getRenderingContext();
        $renderingContext->getTemplatePaths()->fillDefaultsByPackageName('frontend_editing');
        $renderingContext->setControllerName('Toolbars');
        $renderingContext->setControllerAction('Toolbars');

        return $view;
    }

    /**
     * Generate Be user session key to transfer between domains
     *
     * @return string
     */
    protected function getBeSessionKey(): string
    {
        return rawurlencode(
            $GLOBALS['BE_USER']->id .
            '-' .
            md5(
                $GLOBALS['BE_USER']->id .
                '/' .
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
            )
        );
    }

    /**
     * Get plugin configuration from TypoScript
     *
     * @return array
     */
    protected function getPluginConfiguration(): array
    {
        if (!$this->pluginConfiguration) {
            /** @var TypoScriptService $typoScriptService */
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $configuration = $typoScriptService->convertTypoScriptArrayToPlainArray(
                $this->typoScriptFrontendController->tmpl->setup
            );
            if (is_array($configuration['plugin']['tx_frontendediting'])) {
                $this->pluginConfiguration = $configuration['plugin']['tx_frontendediting'];
            }
        }
        return $this->pluginConfiguration;
    }

    /**
     * Get if to display hidden items or not in the rendering
     *
     * @return int
     */
    protected function showHiddenItems(): int
    {
        $defaultState = 1;
        $showHiddenItems = 0;
        if (GeneralUtility::_GET('show_hidden_items')) {
            $showHiddenItems = GeneralUtility::_GET('show_hidden_items');
            if ($showHiddenItems !== $defaultState) {
                $cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
                $cacheManager->flushCaches();
            }
        }

        $showHiddenItems = ($showHiddenItems === $defaultState) ? 0 : $defaultState;

        return $showHiddenItems;
    }

    /**
     * Return instance of NewContentElementController
     * For TYPO3 >= 9 init NewContentElementController with given server request
     *
     * @return NewContentElementController|\TYPO3\CMS\FrontendEditing\Backend\Controller\ContentElement\NewContentElementController
     */
    protected function getNewContentElementController()
    {
        $typo3VersionNumber = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        );

        $contentController = GeneralUtility::makeInstance(
            \TYPO3\CMS\FrontendEditing\Backend\Controller\ContentElement\NewContentElementController::class
        );
        if ($typo3VersionNumber > 10000000) {
            $contentController->wizardAction(
                $this->requestWithSimulatedQueryParams()
            );
        } else {
            $contentController->init(
                $this->requestWithSimulatedQueryParams()
            );
        }

        return $contentController;
    }

    /**
     * Simulate request with "id" and "sys_language_uid" parameters for NewContentElementController
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function requestWithSimulatedQueryParams(): \Psr\Http\Message\ServerRequestInterface
    {
        $languageUid = GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId();

        // @extensionScannerIgnoreLine
        return $GLOBALS['TYPO3_REQUEST']->withQueryParams([
            'id' => (int)$this->typoScriptFrontendController->id,
            'sys_language_uid' => $languageUid,
        ]);
    }
}
