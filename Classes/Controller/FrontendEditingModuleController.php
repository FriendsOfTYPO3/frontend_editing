<?php

declare(strict_types=1);

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

namespace TYPO3\CMS\FrontendEditing\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController as Typo3NewContentElementController;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\FullyRenderedButton;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Routing\UnableToLinkToPageException;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\FrontendEditing\Backend\Controller\ContentElement\NewContentElementController;
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3\CMS\FrontendEditing\Utility\ConfigurationUtility;
use UnexpectedValueException;

/**
 * Controller for FrontendEditing in the backend as a module
 * @internal This is a specific Backend Controller implementation and is not considered part of the Public TYPO3 API.
 */
class FrontendEditingModuleController
{
    /**
     * Page ID
     *
     * @var int
     * @internal
     */
    public int $id;

    /**
     * Current ids page record
     *
     * @var array|bool
     * @internal
     */
    public array|bool $pageinfo;

    /**
     * The name of the module
     *
     * @var string
     */
    protected string $moduleName = 'web_FrontendEditing';

    /**
     * @var ModuleTemplate
     */
    protected ModuleTemplate $moduleTemplate;

    /**
     * @var ButtonBar
     */
    protected ButtonBar $buttonBar;

    /**
     * @var SiteLanguage[]
     */
    protected array $availableLanguages;

    /**
     * @var PageLayoutContext|null
     */
    protected ?PageLayoutContext $context;

    /**
     * @var array|null $settings
     */
    private array|null $settings;

    protected IconFactory $iconFactory;
    protected PageRenderer $pageRenderer;
    protected UriBuilder $uriBuilder;
    protected PageRepository $pageRepository;
    protected ModuleTemplateFactory $moduleTemplateFactory;

    protected SiteFinder $siteFinder;

    protected StandaloneView $view;

    /**
     * Initialize module template and language service
     * @throws InvalidConfigurationTypeException
     */
    public function __construct(
        IconFactory $iconFactory,
        PageRenderer $pageRenderer,
        UriBuilder $uriBuilder,
        PageRepository $pageRepository,
        ModuleTemplateFactory $moduleTemplateFactory,
        SiteFinder $siteFinder
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->iconFactory = $iconFactory;
        $this->pageRenderer = $pageRenderer;
        $this->uriBuilder = $uriBuilder;
        $this->pageRepository = $pageRepository;

        $this->siteFinder = $siteFinder;
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $this->settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'Frontendediting');
    }

    /**
     * Initialize view
     *
     * @param string $templateName
     */
    protected function initializeView(string $templateName): void
    {
        $this->view->getRequest()->setControllerExtensionName('FrontendEditing');
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:frontend_editing/Resources/Private/Templates/FrontendEditingModule']);
        $this->view->setPartialRootPaths(['EXT:frontend_editing/Resources/Private/Partials/']);
        $this->view->setLayoutRootPaths(['EXT:frontend_editing/Resources/Private/Layouts/']);
    }

    /**
     * Registers the docheader
     *
     * @param ServerRequestInterface $request
     * @param int $languageId
     * @param string $targetUrl
     * @throws RouteNotFoundException
     */
    protected function registerDocHeader(ServerRequestInterface $request, int $languageId, string $targetUrl): void
    {
        $lang = $this->getLanguageService();
        $languages = $this->getPreviewLanguages();
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        if (count($languages) > 1) {
            $languageMenu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $languageMenu->setIdentifier('_langSelector');
            foreach ($languages as $value => $label) {
                $href = (string)$uriBuilder->buildUriFromRoute(
                    'web_FrontendEditing',
                    [
                        'id' => $this->id,
                        'language' => (int)$value
                    ]
                );
                $menuItem = $languageMenu->makeMenuItem()
                    ->setTitle($label)
                    ->setHref($href);
                if ($languageId === (int)$value) {
                    $menuItem->setActive(true);
                }
                $languageMenu->addMenuItem($menuItem);
            }
            $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($languageMenu);
        }

        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $this->addActionsUI($buttonBar, $languageId);

        $this->addResponsiveUI($buttonBar);

        // Add view page button
        $showButton = $buttonBar->makeLinkButton()
            ->setHref($targetUrl)
            ->setDataAttributes([
                'dispatch-action' => 'TYPO3.WindowManager.localOpen',
                'dispatch-args' => GeneralUtility::jsonEncodeForHtmlAttribute([
                    $targetUrl,
                    true, // switchFocus
                    'newTYPO3frontendWindow', // windowName,
                ]),
            ])
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.showPage'))
            ->setIcon($this->iconFactory->getIcon('actions-view-page', Icon::SIZE_SMALL));
        $buttonBar->addButton($showButton, ButtonBar::BUTTON_POSITION_LEFT, 3);

        // Add edit page properties button
        if ($this->isPageEditable(0)) {
            $url = (string)$uriBuilder->buildUriFromRoute(
                'record_edit',
                [
                    'edit' => [
                        'pages' => [
                            $this->id => 'edit',
                        ],
                    ],
                    'returnUrl' => $request->getAttribute('normalizedParams')->getRequestUri(),
                ]
            );
            $editPageButton = $buttonBar->makeLinkButton()
                ->setHref($url)
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:backend/Resources/Private/Language/locallang_layout.xlf:editPageProperties'))
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-page-open', Icon::SIZE_SMALL));
            $buttonBar->addButton($editPageButton, ButtonBar::BUTTON_POSITION_LEFT, 3);
        }

        // Cache
        $clearCacheButton = $this->buttonBar->makeLinkButton()
            ->setHref('#')
            ->setDataAttributes(['id' => $this->pageinfo['uid']])
            ->setClasses('t3js-clear-page-cache')
            ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.clear_cache'))
            ->setIcon($this->iconFactory->getIcon('actions-system-cache-clear', Icon::SIZE_SMALL));
        $this->buttonBar->addButton($clearCacheButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);

        // Shortcut
        $mayMakeShortcut = $this->getBackendUser()->mayMakeShortcut();
        if ($mayMakeShortcut) {
            $getVars = ['id', 'route'];

            $shortcutButton = $buttonBar->makeShortcutButton()
                ->setModuleName('web_FrontendEditing')
                ->setGetVariables($getVars);
            $buttonBar->addButton($shortcutButton, ButtonBar::BUTTON_POSITION_RIGHT);
        }

    }

    /**
     * Add action buttons to UI
     *
     * @param ButtonBar $buttonBar
     * @param int $languageId
     */
    private function addActionsUI(ButtonBar $buttonBar, int $languageId): void
    {
        $lang = $this->getLanguageService();
        $saveAllButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3-frontend-editing__save')
            ->setTitle($lang->getLL('top-bar.save-all'))
            ->setShowLabelText(true)
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-document-save', Icon::SIZE_SMALL));
        $buttonBar->addButton($saveAllButton, ButtonBar::BUTTON_POSITION_LEFT, -10);

        $discardButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3-frontend-editing__discard')
            ->setTitle($lang->getLL('top-bar.discard-all'))
            ->setShowLabelText(true)
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-close', Icon::SIZE_SMALL));
        $buttonBar->addButton($discardButton, ButtonBar::BUTTON_POSITION_LEFT, -9);

        // If the page is a translation in connected-mode, disable the new CEs button
        // because the editor must first create a new CE in the default language
        $translationMode = AccessService::getTranslationMode($this->id, $languageId);
        $disableNewCEButton = $languageId > 0 || $translationMode === 'connected';
        $contentsToolbarToggleButton = $buttonBar->makeLinkButton()
            ->setDisabled($disableNewCEButton)
            ->setHref('#')
            ->setClasses('t3-frontend-editing__toggle-contents-toolbar')
            ->setTitle($lang->getLL('top-bar.toggle-contents-toolbar'))
            ->setShowLabelText(true)
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-document-add', Icon::SIZE_SMALL));
        $buttonBar->addButton($contentsToolbarToggleButton, ButtonBar::BUTTON_POSITION_LEFT, -8);

        $hiddenItemsToggleButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3-frontend-editing__show-hidden-items')
            ->setTitle($lang->getLL('top-bar.toggle-hidden-items'))
            ->setShowLabelText(true)
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-eye', Icon::SIZE_SMALL));
        $buttonBar->addButton($hiddenItemsToggleButton, ButtonBar::BUTTON_POSITION_LEFT, -7);
    }

    /**
     * Add responsive UI to module docheader
     *
     * @param ButtonBar $buttonBar
     */
    private function addResponsiveUI(ButtonBar $buttonBar): void
    {
        $lang = $this->getLanguageService();

        // Add preset menu to module docheader
        $presetSplitButtonElement = $buttonBar->makeSplitButton();

        $maximizeButtonLabel = $lang->getLL('maximized');


        // Current web_view state of the BE user
        $current = $this->getBackendUser()->uc['moduleData']['web_view']['States']['current'] ?? null;

        // If BE user never changed the web_view state then default to maximized
        if (!isset($current)) {
            $current['key'] = 'maximized';
            $current['label'] = $maximizeButtonLabel;
            $current['width'] = '';
            $current['height'] = '';
        }

        // If BE user visited view module and set a custom size then web_view state is set but not its label,
        // so we set it to 'Custom'
        $current['label'] = $current['label'] ?? 'Custom';

        // If the current web_view state is not maximized then set the current width & height used later in width & height inputs
        if ($current['label'] !== $maximizeButtonLabel) {
            $current['width'] = (isset($current['width']) && (int)$current['width'] >= 300 ? (int)$current['width'] : 320);
            $current['height'] = (isset($current['height']) && (int)$current['height'] >= 300 ? (int)$current['height'] : 480);
        }

        // Add the current button to the preset select
        $currentButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3js-preset-current t3js-change-preset')
            ->setTitle($current['label'])
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('miscellaneous-placeholder', Icon::SIZE_SMALL))
            ->setShowLabelText(true)
            ->setDataAttributes([
                'key' => 'current',
                'label' => $current['label'],
                'width' => '',
                'height' => ''
            ]);
        $presetSplitButtonElement->addItem($currentButton, true);

        // Add the maximize button to the preset select
        $maximizeButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3js-preset-maximized t3js-change-preset')
            ->setTitle($maximizeButtonLabel . ' (100%x100%)')
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-fullscreen', Icon::SIZE_SMALL))
            ->setShowLabelText(true)
            ->setDataAttributes([
                'key' => 'maximized',
                'label' => $maximizeButtonLabel,
                'width' => '',
                'height' => ''
            ]);
        $presetSplitButtonElement->addItem($maximizeButton);

        // Add the custom button to the preset select
        $custom = $this->getBackendUser()->uc['moduleData']['web_view']['States']['custom'] ?? [];
        $custom['width'] = (isset($custom['width']) && (int)$custom['width'] >= 300 ? (int)$custom['width'] : 320);
        $custom['height'] = (isset($custom['height']) && (int)$custom['height'] >= 300 ? (int)$custom['height'] : 480);
        $customButtonLabel = $lang->getLL('custom');
        $customButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3js-preset-custom t3js-change-preset')
            ->setTitle($customButtonLabel . ' (' . $custom['width'] . 'x' . $custom['height'] . ')')
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-expand', Icon::SIZE_SMALL))
            ->setShowLabelText(true)
            ->setDataAttributes([
                'key' => 'custom',
                'label' => $customButtonLabel,
                'width' => $custom['width'],
                'height' => $custom['height']
            ]);
        $presetSplitButtonElement->addItem($customButton);

        // Add the presets buttons to the preset select
        $presetGroups = $this->getPreviewPresets();
        foreach ($presetGroups as $presetGroup => $presets) {
            $separatorButton = $buttonBar->makeLinkButton()
                ->setHref('#')
                ->setClasses('divider')
                ->setTitle('──────────────────')
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('miscellaneous-placeholder', Icon::SIZE_SMALL))
                ->setShowLabelText(true)
                ->setDisabled(true);
            $presetSplitButtonElement->addItem($separatorButton);
            foreach ($presets as $preset) {
                $presetButtonLabel = $preset['label'];
                $presetButton = $buttonBar->makeLinkButton()
                    ->setHref('#')
                    ->setClasses('t3js-change-preset')
                    ->setTitle($presetButtonLabel . ' (' . $preset['width'] . 'x' . $preset['height'] . ')')
                    ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-device-' . $presetGroup, Icon::SIZE_SMALL))
                    ->setShowLabelText(true)
                    ->setDataAttributes([
                        'key' => $preset['key'],
                        'label' => $presetButtonLabel,
                        'width' => $preset['width'],
                        'height' => $preset['height']
                    ]);
                $presetSplitButtonElement->addItem($presetButton);
            }
        }
        $buttonBar->addButton($presetSplitButtonElement, ButtonBar::BUTTON_POSITION_LEFT, 20);

        // Add width & height inputs to module docheader
        $sizeButtons = new FullyRenderedButton();
        $sizeButtons->setHtmlSource('
            <input class="t3js-frontendediting-input-width" type="number" name="width" min="300" max="9999" value="' . $current['width'] . '">
            x
            <input class="t3js-frontendediting-input-height" type="number" name="height" min="300" max="9999" value="' . $current['height'] . '">
        ');
        $buttonBar->addButton($sizeButtons, ButtonBar::BUTTON_POSITION_LEFT, 15);
    }

    /**
     * Show selected page from pagetree in iframe
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws AspectNotFoundException
     * @throws RouteNotFoundException|Exception
     */
    public function showAction(ServerRequestInterface $request): ResponseInterface
    {
        $lang = $this->getLanguageService();
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);
        $this->buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $this->getLanguageService()->includeLLFile('EXT:frontend_editing/Resources/Private/Language/locallang.xlf');
        // Setting module configuration / page select clause
        $this->id = (int)($request->getParsedBody()['id'] ?? $request->getQueryParams()['id'] ?? 0);

        // Load page info array
        $this->pageinfo = BackendUtility::readPageAccess($this->id, $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW));
        if ($this->pageinfo !== false) {
            // If page info is not resolved, user has no access or the ID parameter was malformed.
            $this->context = GeneralUtility::makeInstance(
                PageLayoutContext::class,
                $this->pageinfo,
                GeneralUtility::makeInstance(BackendLayoutView::class)->getBackendLayoutForPage($this->id)
            );
        }

        /** @var SiteInterface $currentSite */
        $currentSite = $request->getAttribute('site');
        $this->availableLanguages = $currentSite->getAvailableLanguages($this->getBackendUser(), false, $this->id);

        $this->initializeView('show');
        $this->moduleTemplate->setBodyTag('<body class="typo3-module-frontendediting">');
        $this->moduleTemplate->setModuleId('typo3-module-frontendediting');

        if (!$this->isValidDoktype()) {
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $lang->getLL('noValidPageSelected'),
                '',
                AbstractMessage::INFO
            );
            return $this->renderFlashMessage($flashMessage);
        }

        // Set meta info to display page path in module doc header
        if ($this->id && is_array($this->pageinfo)) {
            $this->moduleTemplate->getDocHeaderComponent()->setMetaInformation($this->pageinfo);
        }

        $languageId = $this->getCurrentLanguage(
            $request->getParsedBody()['language'] ?? $request->getQueryParams()['language'] ?? null
        );

        // If the module is called with fe_url query param, then use that URL in Frontend Editing iFrame
        // else get the FE URL from the page UID
        $feUrl = $request->getQueryParams()['fe_url'] ?? false;
        if ($feUrl) {
            $targetUrl = $feUrl;
        } else {
            try {
                $targetUrl = BackendUtility::getPreviewUrl(
                    $this->id,
                    '',
                    null,
                    '',
                    '',
                    $this->getTypeParameterIfSet() . '&L=' . $languageId
                );

                // Check for what protocol to use
                $targetUrl = str_replace(['https://', 'http://'], $this->getProtocol(), $targetUrl);
            } catch (UnableToLinkToPageException) {
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $lang->getLL('noSiteConfiguration'),
                    '',
                    AbstractMessage::WARNING
                );
                return $this->renderFlashMessage($flashMessage);
            }
        }

        $this->registerDocHeader($request, $languageId, $targetUrl);

        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $icons = [];
        $icons['orientation'] = $iconFactory->getIcon('actions-device-orientation-change', Icon::SIZE_SMALL)->render('inline');
        $icons['fullscreen'] = $iconFactory->getIcon('actions-fullscreen', Icon::SIZE_SMALL)->render('inline');
        $icons['expand'] = $iconFactory->getIcon('actions-expand', Icon::SIZE_SMALL)->render('inline');
        $icons['desktop'] = $iconFactory->getIcon('actions-device-desktop', Icon::SIZE_SMALL)->render('inline');
        $icons['tablet'] = $iconFactory->getIcon('actions-device-tablet', Icon::SIZE_SMALL)->render('inline');
        $icons['mobile'] = $iconFactory->getIcon('actions-device-mobile', Icon::SIZE_SMALL)->render('inline');
        $icons['unidentified'] = $iconFactory->getIcon('actions-device-unidentified', Icon::SIZE_SMALL)->render('inline');

        $current = $this->getBackendUser()->uc['moduleData']['web_view']['States']['current'] ?? [];

        $current['label'] = ($current['label'] ?? $lang->getLL('custom'));
        $current['width'] = (isset($current['width']) && (int)$current['width'] >= 300 ? (int)$current['width'] : null);
        $current['height'] = (isset($current['height']) && (int)$current['height'] >= 300 ? (int)$current['height'] : null);
        $custom = $this->getBackendUser()->uc['moduleData']['web_view']['States']['custom'] ?? [];
        $custom['width'] = (isset($current['custom']) && (int)$current['custom'] >= 300 ? (int)$current['custom'] : 320);
        $custom['height'] = (isset($current['custom']) && (int)$current['custom'] >= 300 ? (int)$current['custom'] : 480);

        // Assign variables to template
        $this->view->assign('icons', $icons);
        $this->view->assign('current', $current);
        $this->view->assign('custom', $custom);
        $this->view->assign('presetGroups', $this->getPreviewPresets());
        $this->view->assign('url', $this->addFrontendEditingParameter($targetUrl));
        $this->view->assign('protocol', $request->getUri()->getScheme());
        $this->view->assign('contentItems', $this->getContentItems());
        $this->view->assign('customRecords', $this->getCustomRecords());
        $this->view->assign('disableLoadingScreen', (int)(ConfigurationUtility::getExtensionConfiguration()['disableLoadingScreen'] ?? 0));
        $this->view->assign('loadingIcon', $this->iconFactory->getIcon('spinner-circle-dark', Icon::SIZE_LARGE)->render());

        $this->initFrontendEditingGui();

        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * @throws RouteNotFoundException
     */
    private function initFrontendEditingGui(): void
    {
        $endpointUrl = $this->uriBuilder->buildUriFromRoute(
            'ajax_frontendediting_process',
        );
        $ajaxRecordProcessEndpointUrl = $this->uriBuilder->buildUriFromRoute(
            'ajax_record_process',
        );
        $configurationEndpointUrl = $this->uriBuilder->buildUriFromRoute(
            'ajax_frontendediting_editorconfiguration',
            ['page' => $this->id]
        );
        $this->loadJavascriptResources();

        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/ContextMenu');
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Recordlist/ClearCache');
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/GUI', 'function(FrontendEditing) {
            // The global F object for API calls
            window.F = new FrontendEditing();
            window.F.initGUI({
                resourcePath: ' . GeneralUtility::quoteJSvalue($this->getAbsolutePath('EXT:frontend_editing/Resources/Public/')) . ',
                editorConfigurationUrl: ' . GeneralUtility::quoteJSvalue($configurationEndpointUrl) . '
            });
            window.F.setEndpointUrl(' . GeneralUtility::quoteJSvalue($endpointUrl) . ');
            window.F.setAjaxRecordProcessEndpointUrl(' . GeneralUtility::quoteJSvalue($ajaxRecordProcessEndpointUrl) . ');
            window.F.setBESessionId(' . GeneralUtility::quoteJSvalue($this->getBeSessionKey()) . ');
            window.F.setTranslationLabels(' . json_encode($this->getLocalizedFrontendLabels()) . ');
            window.F.setDisableModalOnNewCe(' .
                (int)ConfigurationUtility::getExtensionConfiguration()['enablePlaceholders'] .
            ');
            window.FrontendEditingMode = true;
        }');
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
     * @param FlashMessage $flashMessage
     * @return HtmlResponse
     * @throws Exception
     */
    protected function renderFlashMessage(FlashMessage $flashMessage): HtmlResponse
    {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);

        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * With page TS config it is possible to force a specific type id via mod.web_view.type
     * for a page id or a page tree.
     * The method checks if a type is set for the given id and returns the additional GET string.
     *
     * @return string
     */
    protected function getTypeParameterIfSet(): string
    {
        $typeParameter = '';
        $typeId = (int)(BackendUtility::getPagesTSconfig($this->id)['mod.']['web_view.']['type'] ?? 0);
        if ($typeId > 0) {
            $typeParameter = '&type=' . $typeId;
        }
        return $typeParameter;
    }

    /**
     * Get available presets for page id
     *
     * @return array
     */
    protected function getPreviewPresets(): array
    {
        $presetGroups = [
            'desktop' => [],
            'tablet' => [],
            'mobile' => [],
            'unidentified' => []
        ];
        $previewFrameWidthConfig = BackendUtility::getPagesTSconfig($this->id)['mod.']['web_view.']['previewFrameWidths.'] ?? [];
        foreach ($previewFrameWidthConfig as $item => $conf) {
            $data = [
                'key' => substr($item, 0, -1),
                'label' => $conf['label'] ?? null,
                'type' => $conf['type'] ?? 'unknown',
                'width' => (isset($conf['width']) && (int)$conf['width'] > 0 && strpos($conf['width'], '%') === false) ? (int)$conf['width'] : null,
                'height' => (isset($conf['height']) && (int)$conf['height'] > 0 && strpos($conf['height'], '%') === false) ? (int)$conf['height'] : null,
            ];
            $width = (int)substr($item, 0, -1);
            if (!isset($data['width']) && $width > 0) {
                $data['width'] = $width;
            }
            if (!isset($data['label'])) {
                $data['label'] = $data['key'];
            } elseif (strpos($data['label'], 'LLL:') === 0) {
                $data['label'] = $this->getLanguageService()->sL(trim($data['label']));
            }

            if (array_key_exists($data['type'], $presetGroups)) {
                $presetGroups[$data['type']][$data['key']] = $data;
            } else {
                $presetGroups['unidentified'][$data['key']] = $data;
            }
        }

        return $presetGroups;
    }

    /**
     * Returns the preview languages
     *
     * @return array
     */
    protected function getPreviewLanguages(): array
    {
        $languages = [];
        $modSharedTSconfig = BackendUtility::getPagesTSconfig($this->id)['mod.']['SHARED.'] ?? [];
        if (isset($modSharedTSconfig['view.']) && $modSharedTSconfig['view.']['disableLanguageSelector'] === '1') {
            return $languages;
        }

        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->id);
            $siteLanguages = $site->getAvailableLanguages($this->getBackendUser(), false, $this->id);

            foreach ($siteLanguages as $siteLanguage) {
                $languageAspectToTest = LanguageAspectFactory::createFromSiteLanguage($siteLanguage);
                $page = $this->pageRepository->getPageOverlay(
                    $this->pageRepository->getPage($this->id),
                    $siteLanguage->getLanguageId()
                );

                if ($this->pageRepository->isPageSuitableForLanguage($page, $languageAspectToTest)) {
                    $languages[$siteLanguage->getLanguageId()] = $siteLanguage->getTitle();
                }
            }
        } catch (SiteNotFoundException) {
            // do nothing
        }
        return $languages;
    }

    /**
     * Returns the current language
     *
     * @param string|null $languageParam
     * @return int
     */
    protected function getCurrentLanguage(string $languageParam = null): int
    {
        $languageId = (int)$languageParam;
        if ($languageParam === null) {
            $states = $this->getBackendUser()->uc['moduleData']['web_frontendediting']['States'] ?? [];
            $languages = $this->getPreviewLanguages();
            if (isset($states['languageSelectorValue']) && isset($languages[$states['languageSelectorValue']])) {
                $languageId = (int)$states['languageSelectorValue'];
            }
        } else {
            $this->getBackendUser()->uc['moduleData']['web_frontendediting']['States']['languageSelectorValue'] = $languageId;
            $this->getBackendUser()->writeUC($this->getBackendUser()->uc);
        }
        return $languageId;
    }

    /**
     * Check if page can be edited by current user
     *
     * @param int $languageId
     * @return bool
     */
    protected function isPageEditable(int $languageId): bool
    {
        if ($GLOBALS['TCA']['pages']['ctrl']['readOnly'] ?? false) {
            return false;
        }
        $backendUser = $this->getBackendUser();
        if ($backendUser->isAdmin()) {
            return true;
        }
        if ($GLOBALS['TCA']['pages']['ctrl']['adminOnly'] ?? false) {
            return false;
        }

        return $this->pageinfo !== []
            && !($this->pageinfo[$GLOBALS['TCA']['pages']['ctrl']['editlock'] ?? null] ?? false)
            && $backendUser->doesUserHaveAccess($this->pageinfo, Permission::PAGE_EDIT)
            && $backendUser->checkLanguageAccess($languageId)
            && $backendUser->check('tables_modify', 'pages');
    }

    /**
     * Verifies if doktype of given page is valid
     *
     * @return bool
     */
    protected function isValidDoktype(): bool
    {
        if ($this->id === 0) {
            return false;
        }

        $page = BackendUtility::getRecord('pages', $this->id);
        $pageType = (int)($page['doktype'] ?? 0);

        return $pageType !== 0
            && !in_array($pageType, [
                PageRepository::DOKTYPE_SPACER,
                PageRepository::DOKTYPE_SYSFOLDER,
                PageRepository::DOKTYPE_RECYCLER
            ], true);
    }

    /**
     * Get the proper http protocol used
     *
     * @return string
     */
    protected function getProtocol(): string
    {
        return isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] === 1)
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']
            === 'https' ? 'https://' : 'http://';
    }

    /**
     * Get the available content elements from TYPO3 backend
     *
     * @return array
     */
    protected function getContentItems(): array
    {
        $contentController = GeneralUtility::makeInstance(
            NewContentElementController::class,
            $this->iconFactory,
            $this->pageRenderer,
            $this->uriBuilder,
            $this->moduleTemplateFactory
        );

        $wizardItems = $contentController->publicGetWizards();

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
     * Get custom records defined in TypoScript
     *
     * @return array
     * @throws RouteNotFoundException
     */
    protected function getCustomRecords(): array
    {
        $records = [];
        if (isset($this->settings['customRecords']) && is_array($this->settings['customRecords'])) {
            /** @var ContentEditableWrapperService $wrapperService */
            $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);
            foreach ($this->settings['customRecords'] as $table => $defaultValues) {
                $pid = $this->id;
                $page = BackendUtility::getRecord('pages', $pid);

                if (
                    $table
                    && isset($GLOBALS['TCA'][$table])
                    && $GLOBALS['BE_USER']->check('tables_modify', $table)
                    && $page && $GLOBALS['BE_USER']->doesUserHaveAccess($page, 16)
                ) {
                    $records[] = [
                        'title' => $GLOBALS['TCA'][$table]['ctrl']['title'],
                        'table' => $table,
                        'url' => $wrapperService->renderEditOnClickReturnUrl($wrapperService->renderNewUrl(
                            $table,
                            $pid,
                            $defaultValues,
                            true
                        )),
                    ];
                }
            }
        }

        return $records;
    }

    /**
     * Call registered hooks to manipulate wizard items
     *
     * @param array &$wizardItems
     * @param Typo3NewContentElementController $contentController
     */
    protected function wizardItemsHook(array &$wizardItems, Typo3NewContentElementController $contentController): void
    {
        $newContentElement = isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms'])
            ? $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el'] : [];
        // Wrapper for wizards
        // Hook for manipulating wizardItems, wrapper, onClickEvent etc.
        if (
            is_array($newContentElement)
            && !empty($newContentElement)
            && array_key_exists('wizardItemsHook', $newContentElement)
        ) {
            foreach ($newContentElement['wizardItemsHook'] as $classData) {
                $hookObject = GeneralUtility::makeInstance($classData);
                if (!$hookObject instanceof NewContentElementWizardHookInterface) {
                    throw new UnexpectedValueException(
                        $classData . ' must implement interface ' . NewContentElementWizardHookInterface::class,
                        1227834741
                    );
                }

                $hookObject->manipulateWizardItems($wizardItems, $contentController);
            }
        }
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @param string $originalUrl
     * @return string
     */
    private function addFrontendEditingParameter(string $originalUrl): string
    {
        $urlParts = parse_url($originalUrl);
        parse_str($urlParts['query'] ?? '', $queryParams);
        $queryParams['fe_edit'] = 1;
        $newQueryString = http_build_query($queryParams);
        return $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'] . '?' . $newQueryString;
    }

    /**
     * Load the necessary Javascript-resources for the toolbars
     */
    protected function loadJavascriptResources(): void
    {
        $this->pageRenderer->addJsFile(
            'EXT:core/Resources/Public/JavaScript/Contrib/jquery/jquery.js'
        );
        $this->pageRenderer->addRequireJsConfiguration(
            [
                'shim' => [
                    'ckeditor' => ['exports' => 'CKEDITOR'],
                    'ckeditor-jquery-adapter' => ['jquery', 'ckeditor'],
                ],
                'paths' => [
                    'TYPO3/CMS/FrontendEditing/Contrib/toastr' => PathUtility::getAbsoluteWebPath(
                            ExtensionManagementUtility::extPath('frontend_editing', 'Resources/Public/JavaScript/Contrib/')
                        ) . 'toastr',
                    'TYPO3/CMS/FrontendEditing/Contrib/immutable' => PathUtility::getAbsoluteWebPath(
                            ExtensionManagementUtility::extPath('frontend_editing', 'Resources/Public/JavaScript/Contrib/')
                        ) . 'immutable'
                ]
            ]
        );

        // Load CKEDITOR and CKEDITOR jQuery adapter independent for global access
        $this->pageRenderer->addJsFooterFile('EXT:rte_ckeditor/Resources/Public/JavaScript/Contrib/ckeditor.js');
        $this->pageRenderer->addJsFooterFile(
            'EXT:frontend_editing/Resources/Public/JavaScript/Contrib/ckeditor-jquery-adapter.js'
        );

        // Fixes issue #377, where CKEditor dependencies fail to load if the version number is added to the file name
        $this->pageRenderer->addJsInlineCode(
            'ckeditor-basepath-config',
            'window.CKEDITOR_BASEPATH = ' . GeneralUtility::quoteJSvalue(PathUtility::getAbsoluteWebPath(
                GeneralUtility::getFileAbsFileName(
                    'EXT:rte_ckeditor/Resources/Public/JavaScript/Contrib/'
                )
            )) . ';',
            true,
            true
        );
    }
}
