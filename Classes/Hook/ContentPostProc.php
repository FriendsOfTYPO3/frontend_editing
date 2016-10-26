<?php
namespace TYPO3\CMS\FrontendEditing\Hook;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
            && !$this->httpRefererIsFromBackendViewModule()
        ) {
            $isFrontendEditing = GeneralUtility::_GET('frontend_editing');
            if (isset($isFrontendEditing) && (bool)$isFrontendEditing === true) {
                // To prevent further rendering
            } else {
                // Special content is about to be shown, so the cache must be disabled.
                $parentObject->set_no_cache('Display frontend editing', true);

                $this->typoScriptFrontendController = $parentObject;

                $output = $this->loadResources();

                //\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance
                $objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
                $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
                $settings = $configurationManager->getConfiguration(
                    \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
                );

                // No configuration found
                if (!isset($settings['plugin.']['tx_frontendediting.'])) {
                    $layoutPath =  'EXT:frontend_editing/Resources/Private/Layouts/';
                    $templatePath = 'EXT:frontend_editing/Resources/Private/Templates/Toolbars/Toolbars.html';
                    $partialPath = 'EXT:frontend_editing/Resources/Private/Partials/';
                } else {
                    $layoutPath = $settings['plugin.']['tx_frontendediting.']['view.']['layoutRootPath'];
                    $templatePath = $settings['plugin.']['tx_frontendediting.']['view.']['templateRootPath'];
                    $partialPath = $settings['plugin.']['tx_frontendediting.']['view.']['partialRootPath'];
                }

                $view = new \TYPO3\CMS\Fluid\View\StandaloneView();
                $view->setTemplatePathAndFilename($templatePath);
                $view->setLayoutRootPaths([
                    10 => $layoutPath
                ]);
                $view->setPartialRootPaths([
                    10 => $partialPath
                ]);

                $view->assign(
                    'FrontendEditing',
                    json_encode($this->getJavascriptForFrontendEditing())
                );
                $view->getRenderingContext()->setLegacyMode(false);
                $renderedHtml = $view->render();

                $output .= $renderedHtml;

                $parentObject->content = $output;
            }
        }
    }

    /**
     * Get all kind of javascript needed in FE
     *
     * @return array
     */
    protected function getJavascriptForFrontendEditing()
    {
        $jsArray = [
            'userIcon' => $this->iconFactory->getIcon('avatar-default', Icon::SIZE_DEFAULT)->render(),
            'userName' => $GLOBALS['BE_USER']->user['username'],
            'loadingIcon' => $this->iconFactory->getIcon('spinner-circle-dark', Icon::SIZE_LARGE)->render(),
            'iframeUrl' => GeneralUtility::getIndpEnv('TYPO3_SITE_URL') .
                    'index.php?id=' . $this->typoScriptFrontendController->id .
                    '&frontend_editing=true',
            'pageTree' => $this->getPageTreeStructure(),
            'currentTime' => time(),
            'labels' => $this->getLocalizedFrontendLabels(),
        ];

        return $jsArray;
    }

    /**
     * Localize labels
     *
     * @return array
     */
    protected function getLocalizedFrontendLabels()
    {
        $languageFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Localization\\LocalizationFactory');
        $parsedLocallang = $languageFactory->getParsedData(
            'EXT:frontend_editing/Resources/Private/Language/locallang.xlf',
            'default'
        );
        $localizedLabels = [];
        foreach (array_keys($parsedLocallang['default']) as $key) {
            if (strpos($key, 'notifications.') === 0 ||
                strpos($key, 'top-bar.') === 0) {
                $localizedLabels[$key] = LocalizationUtility::translate($key, 'FrontendEditing');
            }
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
            '/typo3conf/ext/frontend_editing/Resources/Public/App/Main.css" />';
        $resources .= '<link rel="stylesheet" type="text/css" href="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/toastr/build/toastr.min.css" />';
        $resources .= '<script src="typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery-' .
            PageRenderer::JQUERY_VERSION_LATEST . '.min.js" type="text/javascript"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/ckeditor/ckeditor.js"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/ckeditor/adapters/jquery.js"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/toastr/build/toastr.min.js"></script>';
        $resources .= '<script type="text/javascript" src="' .
            '/typo3conf/ext/frontend_editing/Resources/Public/Javascript/immutable/dist/immutable.min.js"></script>';

        return $resources;
    }

    /**
     * Determine if page is loaded from the TYPO3 BE
     *
     * @return bool
     */
    protected function httpRefererIsFromBackendViewModule()
    {
        $parsedReferer = parse_url($_SERVER['HTTP_REFERER']);
        $pathArray = explode('/', $parsedReferer['path']);
        $viewPageView = preg_match('/web_ViewpageView/i', $parsedReferer['query']);
        return (strtolower($pathArray[1]) === 'typo3' && $viewPageView);
    }

    /**
     * Get the page tree structure of the current tree
     *
     * @return array
     */
    public function getPageTreeStructure()
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
}
