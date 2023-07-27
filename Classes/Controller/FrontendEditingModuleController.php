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
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\FullyRenderedButton;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Routing\UnableToLinkToPageException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Controller for FrontendEditing in the backend as a module
 * @internal This is a specific Backend Controller implementation and is not considered part of the Public TYPO3 API.
 */
class FrontendEditingModuleController
{
    /**
     * ModuleTemplate object
     *
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * View
     *
     * @var ViewInterface
     */
    protected $view;

    /**
     * Page Repository
     */
    protected $pageRepository;

    /**
     * Initialize module template and language service
     */
    public function __construct()
    {
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->getLanguageService()->includeLLFile('EXT:frontend_editing/Resources/Private/Language/locallang.xlf');
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addInlineLanguageLabelFile('EXT:frontend_editing/Resources/Private/Language/locallang.xlf');
        $pageRepositoryClassName = PageRepository::class;
        $this->pageRepository = GeneralUtility::makeInstance($pageRepositoryClassName);
    }

    /**
     * Initialize view
     *
     * @param string $templateName
     */
    protected function initializeView(string $templateName)
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->getRequest()->setControllerExtensionName('FrontendEditing');
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:frontend_editing/Resources/Private/Templates/FrontendEditingModule']);
        $this->view->setPartialRootPaths(['EXT:frontend_editing/Resources/Private/Partials/']);
        $this->view->setLayoutRootPaths(['EXT:frontend_editing/Resources/Private/Layouts/']);
    }

    /**
     * Registers the docheader
     *
     * @param int $pageId
     * @param int $languageId
     * @param string $targetUrl
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function registerDocHeader(int $pageId, int $languageId, string $targetUrl)
    {
        $languages = $this->getPreviewLanguages($pageId);
        if (count($languages) > 1) {
            $languageMenu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $languageMenu->setIdentifier('_langSelector');
            /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            foreach ($languages as $value => $label) {
                $href = (string)$uriBuilder->buildUriFromRoute(
                    'web_FrontendEditing',
                    [
                        'id' => $pageId,
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

        $this->addActionsUI($pageId, $buttonBar);

        $this->addResponsiveUI($pageId, $buttonBar);

        $showButton = $buttonBar->makeLinkButton()
            ->setHref($targetUrl)
            ->setOnClick('window.open(this.href, \'newTYPO3frontendWindow\').focus();return false;')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.showPage'))
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-view-page', Icon::SIZE_SMALL));
        $buttonBar->addButton($showButton);

        $refreshButton = $buttonBar->makeLinkButton()
            ->setHref('javascript:document.getElementById(\'tx_frontendediting_iframe\').contentWindow.location.reload(true);')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:frontend_editing/Resources/Private/Language/locallang.xlf:refreshPage'))
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);

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
     * @param int $pageId
     * @param ButtonBar $buttonBar
     */
    private function addActionsUI(int $pageId, ButtonBar $buttonBar)
    {
        $saveAllButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3-frontend-editing__save')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:frontend_editing/Resources/Private/Language/locallang.xlf:top-bar.save-all'))
            ->setShowLabelText(true)
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-document-save', Icon::SIZE_SMALL));
        $buttonBar->addButton($saveAllButton, ButtonBar::BUTTON_POSITION_LEFT, -10);

        $discardButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3-frontend-editing__discard')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:frontend_editing/Resources/Private/Language/locallang.xlf:top-bar.discard-all'))
            ->setShowLabelText(true)
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-close', Icon::SIZE_SMALL));
        $buttonBar->addButton($discardButton, ButtonBar::BUTTON_POSITION_LEFT, -9);

        $fullScreenButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setClasses('t3-frontend-editing__full-view')
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:frontend_editing/Resources/Private/Language/locallang.xlf:top-bar.full-view'))
            ->setShowLabelText(true)
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-fullscreen', Icon::SIZE_SMALL));
        $buttonBar->addButton($fullScreenButton, ButtonBar::BUTTON_POSITION_LEFT, -8);
    }

    /**
     * Add responsive UI to module docheader
     *
     * @param int $pageId
     * @param ButtonBar $buttonBar
     */
    private function addResponsiveUI(int $pageId, ButtonBar $buttonBar)
    {
        // Add preset menu to module docheader
        $presetSplitButtonElement = $buttonBar->makeSplitButton();

        $maximizeButtonLabel = $this->getLanguageService()->getLL('maximized');

        // Current web_view state of the BE user
        $current = $this->getBackendUser()->uc['moduleData']['web_view']['States']['current'] ?? null;

        // If BE user never changed the web_view state then default to maximized
        if (!isset($current)) {
            $current['key'] = 'maximized';
            $current['label'] = $maximizeButtonLabel;
            $current['width'] = '';
            $current['height'] = '';
        }

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
        $customButtonLabel = $this->getLanguageService()->getLL('custom');
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
        $presetGroups = $this->getPreviewPresets($pageId);
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
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function showAction(ServerRequestInterface $request): ResponseInterface
    {
        $pageId = (int)($request->getParsedBody()['id'] ?? $request->getQueryParams()['id'] ?? 0);

        $this->initializeView('show');
        $this->moduleTemplate->setBodyTag('<body class="typo3-module-frontendediting">');
        $this->moduleTemplate->setModuleName('typo3-module-frontendediting');
        $this->moduleTemplate->setModuleId('typo3-module-frontendediting');

        if (!$this->isValidDoktype($pageId)) {
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->getLanguageService()->getLL('noValidPageSelected'),
                '',
                FlashMessage::INFO
            );
            return $this->renderFlashMessage($flashMessage);
        }

        $languageId = $this->getCurrentLanguage(
            $pageId,
            $request->getParsedBody()['language'] ?? $request->getQueryParams()['language'] ?? null
        );
        try {
            $targetUrl = BackendUtility::getPreviewUrl(
                $pageId,
                '',
                null,
                '',
                '',
                $this->getTypeParameterIfSet($pageId) . '&L=' . $languageId
            );

            // Check for what protocol to use
            $targetUrl = str_replace(['https://', 'http://'], $this->getProtocol(), $targetUrl);
        } catch (UnableToLinkToPageException $e) {
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->getLanguageService()->getLL('noSiteConfiguration'),
                '',
                FlashMessage::WARNING
            );
            return $this->renderFlashMessage($flashMessage);
        }

        $this->registerDocHeader($pageId, $languageId, $targetUrl);

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

        $current['label'] = ($current['label'] ?? $this->getLanguageService()->sL('LLL:EXT:frontend_editing/Resources/Private/Language/locallang.xlf:custom'));
        $current['width'] = (isset($current['width']) && (int)$current['width'] >= 300 ? (int)$current['width'] : null);
        $current['height'] = (isset($current['height']) && (int)$current['height'] >= 300 ? (int)$current['height'] : null);
        $custom = $this->getBackendUser()->uc['moduleData']['web_view']['States']['custom'] ?? [];
        $custom['width'] = (isset($current['custom']) && (int)$current['custom'] >= 300 ? (int)$current['custom'] : 320);
        $custom['height'] = (isset($current['custom']) && (int)$current['custom'] >= 300 ? (int)$current['custom'] : 480);

        $otherDomain = false;
        // Check if the domain I am logged into are the same as the one I try edit for
        if (strpos($targetUrl, $request->getUri()->getHost()) !== false) {
            // Apply the GET parameter "frontend_editing"
            if (parse_url($targetUrl, PHP_URL_QUERY)) {
                $targetUrl = $targetUrl . '&frontend_editing=true';
            } else {
                $targetUrl = $targetUrl . '?frontend_editing=true';
            }
        } else {
            if ($targetUrl) {
                $targetUrl = parse_url($targetUrl)['host'];
            }
            $otherDomain = true;
        }

        // Assign variables to template
        $this->view->assign('icons', $icons);
        $this->view->assign('current', $current);
        $this->view->assign('custom', $custom);
        $this->view->assign('presetGroups', $this->getPreviewPresets($pageId));
        $this->view->assign('url', $targetUrl);
        $this->view->assign('protocol', $request->getUri()->getScheme());
        $this->view->assign('otherDomain', $otherDomain);

        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

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
     * @param int $pageId
     * @return string
     */
    protected function getTypeParameterIfSet(int $pageId): string
    {
        $typeParameter = '';
        $typeId = (int)(BackendUtility::getPagesTSconfig($pageId)['mod.']['web_view.']['type'] ?? 0);
        if ($typeId > 0) {
            $typeParameter = '&type=' . $typeId;
        }
        return $typeParameter;
    }

    /**
     * Get available presets for page id
     *
     * @param int $pageId
     * @return array
     */
    protected function getPreviewPresets(int $pageId): array
    {
        $presetGroups = [
            'desktop' => [],
            'tablet' => [],
            'mobile' => [],
            'unidentified' => []
        ];
        $previewFrameWidthConfig = BackendUtility::getPagesTSconfig($pageId)['mod.']['web_view.']['previewFrameWidths.'] ?? [];
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
     * @param int $pageId
     * @return array
     */
    protected function getPreviewLanguages(int $pageId): array
    {
        $languages = [];
        $modSharedTSconfig = BackendUtility::getPagesTSconfig($pageId)['mod.']['SHARED.'] ?? [];
        if (isset($modSharedTSconfig['view.']) && $modSharedTSconfig['view.']['disableLanguageSelector'] === '1') {
            return $languages;
        }

        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
            $siteLanguages = $site->getAvailableLanguages($this->getBackendUser(), false, $pageId);

            foreach ($siteLanguages as $siteLanguage) {
                $languageAspectToTest = LanguageAspectFactory::createFromSiteLanguage($siteLanguage);
                $page = $this->pageRepository->getPageOverlay(
                    $this->pageRepository->getPage($pageId),
                    $siteLanguage->getLanguageId()
                );

                if ($this->pageRepository->isPageSuitableForLanguage($page, $languageAspectToTest)) {
                    $languages[$siteLanguage->getLanguageId()] = $siteLanguage->getTitle();
                }
            }
        } catch (SiteNotFoundException $e) {
            // do nothing
        }
        return $languages;
    }

    /**
     * Returns the current language
     *
     * @param int $pageId
     * @param string $languageParam
     * @return int
     */
    protected function getCurrentLanguage(int $pageId, string $languageParam = null): int
    {
        $languageId = (int)$languageParam;
        if ($languageParam === null) {
            $states = $this->getBackendUser()->uc['moduleData']['web_frontendediting']['States'] ?? [];
            $languages = $this->getPreviewLanguages($pageId);
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
     * Verifies if doktype of given page is valid
     *
     * @param int $pageId
     * @return bool
     */
    protected function isValidDoktype(int $pageId = 0): bool
    {
        if ($pageId === 0) {
            return false;
        }

        $page = BackendUtility::getRecord('pages', $pageId);
        $pageType = (int)($page['doktype'] ?? 0);

        return $pageType !== 0
            && !in_array($pageType, [
                $this->pageRepository::DOKTYPE_SPACER,
                $this->pageRepository::DOKTYPE_SYSFOLDER,
                $this->pageRepository::DOKTYPE_RECYCLER
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
}
