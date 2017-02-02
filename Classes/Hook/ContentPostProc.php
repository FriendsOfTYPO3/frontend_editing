<?php
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\FrontendEditing\Domain\Repository\ContentRepository;
use TYPO3\CMS\FrontendEditing\Utility\Helper;
use TYPO3\CMS\Backend\Backend\Avatar\DefaultAvatarProvider;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Hook class ContentPostProc for rendering the panels and iframe for inline editing
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ContentPostProc
{

    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected $typoScriptFrontendController = null;

    /**
     * "Plugin" settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * @var \TYPO3\CMS\Core\Imaging\IconFactory
     */
    protected $iconFactory;

    /**
     * @var \TYPO3\CMS\FrontendEditing\Domain\Repository\ContentRepository
     */
    protected $contentRepository;

    /**
     * Extension configuration
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * ContentPostProc constructor.
     */
    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->contentRepository = $objectManager->get(ContentRepository::class);
    }

    /**
     * Hook to change page output to add the topbar
     *
     * @param array $params
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $parentObject
     * @throws \UnexpectedValueException
     * @return void
     * @throws \Exception
     */
    public function main(array $params, \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $parentObject)
    {
        if (\TYPO3\CMS\FrontendEditing\Utility\Access::isEnabled()
            && $parentObject->type === 0
            && !Helper::httpRefererIsFromBackendViewModule()
        ) {
            $isFrontendEditing = GeneralUtility::_GET('frontend_editing');
            if (isset($isFrontendEditing) && (bool)$isFrontendEditing === true) {
                // To prevent further rendering
            } else {
                // Special content is about to be shown, so the cache must be disabled.
                $parentObject->set_no_cache('Display frontend editing', true);

                $this->typoScriptFrontendController = $parentObject;

                $iframeUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') .
                    'index.php?id=' . $this->typoScriptFrontendController->id .
                    '&frontend_editing=true'
                ;

                $objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
                $configurationManager = $objectManager->get(
                    \TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class
                );
                $settings = $configurationManager->getConfiguration(
                    \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
                );

                // No configuration found
                if (!isset($settings['plugin.']['tx_frontendediting.'])) {
                    $layoutPaths = ['EXT:frontend_editing/Resources/Private/Layouts/'];
                    $templatePaths = [10 =>'EXT:frontend_editing/Resources/Private/Templates/Toolbars/Toolbars.html'];
                    $partialPaths = ['EXT:frontend_editing/Resources/Private/Partials/'];
                } else {
                    $layoutPaths = $settings['plugin.']['tx_frontendediting.']['view.']['layoutRootPaths.'];
                    $templatePaths = $settings['plugin.']['tx_frontendediting.']['view.']['templateRootPaths.'];
                    $partialPaths = $settings['plugin.']['tx_frontendediting.']['view.']['partialRootPaths.'];
                }

                $view = new \TYPO3\CMS\Fluid\View\StandaloneView();
                $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templatePaths[10]));
                $view->setLayoutRootPaths($layoutPaths);
                $view->setPartialRootPaths($partialPaths);
                $view->setTemplateRootPaths($templatePaths);

                $avatar = GeneralUtility::makeInstance(DefaultAvatarProvider::class);

                $icons = [
                    'userIcon' => $this->iconFactory->getIcon('avatar-default', Icon::SIZE_DEFAULT)->render(),
                    'userImage' => $avatar->getImage($GLOBALS['BE_USER']->user, 32),
                    'loadingIcon' => $this->iconFactory->getIcon('spinner-circle-dark', Icon::SIZE_LARGE)->render()
                ];

                $view->assignMultiple(
                    array_merge(
                        [
                            'userName' => $GLOBALS['BE_USER']->user['username'],
                            'iframeUrl' => $iframeUrl,
                            'pageTree' => $this->getPageTreeStructure(),
                            'currentTime' => time(),
                            'contentItems' => $this->getContentItems(),
                            'contentElementsOnPage' => $this->getContentElementsOnPage($GLOBALS['TSFE']->id),
                            'overlayOption' => $GLOBALS['BE_USER']->uc['tx_frontend_editing_overlay'],
                            'languageLabels' => json_encode($this->getLocalizedFrontendLabels()),
                            'currentPage' => $GLOBALS['TSFE']->id,
                            'logoutUrl' => BackendUtility::getModuleUrl('logout'),
                            'resources' => $this->loadResources(),
                            'javascriptResources' => $this->loadJavascriptResources()
                        ],
                        $icons
                    )
                );
                
                // Method getRenderingContext() available since V8
                if (method_exists($view, 'getRenderingContext')) {
                    $view->getRenderingContext()->setLegacyMode(false);
                }
               
                $parentObject->content = $view->render();
            }
        }
    }

    /**
     * Returns an array with labels from translation file
     *
     * @return array
     */
    protected function getLocalizedFrontendLabels()
    {
        $languageFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LocalizationFactory::class);
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
     * Load the necessary resources for the toolbars
     *
     * @return string
     */
    private function loadResources()
    {
        $resources = '<link rel="stylesheet" type="text/css" href="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Css/Main.css" />';
        $resources .= '<link rel="stylesheet" type="text/css" href="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/toastr/build/toastr.min.css" />';
        $resources .= '<link rel="stylesheet" type="text/css" href="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/lity/dist/lity.min.css" />';
        return $resources;
    }

     /**
     * Load the necessary Javascript-resources for the toolbars
     *
     * @return string
     */
    private function loadJavascriptResources()
    {
        $resources = '<script src="/typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery-' .
            PageRenderer::JQUERY_VERSION_LATEST . '.min.js" type="text/javascript"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/ckeditor/ckeditor.js"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/ckeditor/adapters/jquery.js"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/toastr/build/toastr.min.js"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/immutable/dist/immutable.min.js"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/lity/dist/lity.min.js"></script>';
        return $resources;
    }

    /**
     * Get the page tree structure of the current tree
     *
     * @return array
     */
    protected function getPageTreeStructure()
    {
        // Get page record for tree starting point
        // from where we currently are navigated
        $startingPoint = $GLOBALS['TSFE']->rootLine[0]['uid'];
        $pageRecord = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord(
            'pages',
            $startingPoint
        );

        // Create and initialize the tree object
        $tree = new \TYPO3\CMS\Backend\Tree\View\PageTreeView();
        $tree->init('AND ' . $GLOBALS['BE_USER']->getPagePermsClause(1));

        // Creating the icon for the current page and add it to the tree
        $html = $this->iconFactory->getIconForRecord(
            'pages',
            $pageRecord,
            Icon::SIZE_SMALL
        );

        $tree->tree[] = [
            'row' => $pageRecord,
            'HTML' => $html
        ];

        // Create the page tree, from the starting point, infinite levels down
        $tree->getTree(
            $startingPoint
        );

        return $tree->tree;
    }

    /**
     * Get the available content elements from TYPO3 backend
     *
     * @return array
     */
    protected function getContentItems()
    {
        $contentController = GeneralUtility::makeInstance(
            NewContentElementController::class
        );
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
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getContentElementsOnPage($pageId)
    {
        return $this->contentRepository->findAllOnPage($pageId);
    }
}
