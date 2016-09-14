<?php
namespace TYPO3\CMS\FrontendEditing\Hook;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;

class ContentPostProc
{

    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected $typoScriptFrontendController = NULL;

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
    protected $configuration = array();

    /**
     * ContentPostProc constructor.
     */
    public function __construct() {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Hook to change page output to add the topbar
     *
     * @param array $params
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $parentObject
     * @throws \UnexpectedValueException
     * @return void
     */
    public function main(array $params, \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $parentObject)
    {
        if (
            \TYPO3\CMS\FrontendEditing\Utility\Access::isEnabled()
            && $parentObject->type === 0
            && !$this->httpRefererIsFromBackendViewModule()
        ) {

            $isFrontendEditing = GeneralUtility::_GET('frontend_editing');
            if (isset($isFrontendEditing) && (bool)$isFrontendEditing === true) {
                // To prevent further rendering
            } else {

                $this->typoScriptFrontendController = $parentObject;

                $output = $this->loadResources();

                $userIcon =
                    '<span title="User">' .
                        $this->iconFactory->getIcon('avatar-default', Icon::SIZE_DEFAULT)->render() .
                    '</span>';

                $output .= '
                    <div class="frontend-editing-top-bar">
                        <div class="frontend-editing-topbar-inner">
                            <div class="frontend-editing-top-bar-left">
                                <a href="/typo3">
                                    <img src="/typo3/sysext/backend/Resources/Public/Images/typo3-topbar@2x.png" height="22" width="22" />
                                    To backend
                                </a>
                            </div>
                            <div class="frontend-editing-top-bar-right">
                                <button type="submit" id="frontend-editing-save">Save</button> 
                                ' . $userIcon . $GLOBALS['BE_USER']->user['username'] . '
                            </div>
                        </div>
                    </div>
                    <!--<div class="frontend-editing-right-bar">
                    </div>-->';

                $loadingIcon = '
                    <div class="loading-screen">' .
                        $this->iconFactory->getIcon('spinner-circle-dark', Icon::SIZE_LARGE)->render() .
                    '</div>';

                $iframeUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') .
                    'index.php?id=' . $this->typoScriptFrontendController->id .
                    '&frontend_editing=true'
                ;

                $iframe = sprintf(
                    '%s<script type="text/javascript">var iframeUrl = "%s";</script><div class="frontend-editing-iframe-wrapper inactive"><iframe src="" frameborder="%s" border="%s"></iframe></div>%s',
                    $output,
                    $iframeUrl,
                    '0',
                    '0',
                    $loadingIcon
                );

                $frontendEditingJavascript =  '<script src="/typo3conf/ext/frontend_editing/Resources/Public/Javascript/FrontendEditing.js" type="text/javascript"></script>';
                $frontendEditingJavascript .= '<script src="/typo3conf/ext/frontend_editing/Resources/Public/Javascript/InlineEditing.js" type="text/javascript"></script>';

                $parentObject->content = $iframe . $frontendEditingJavascript;

            }
        }
    }

    /**
     * Load the necessary resources for the toolbars
     *
     * @return string
     */
    private function loadResources() {
        $resources = '<link rel="stylesheet" type="text/css" href="/typo3conf/ext/frontend_editing/Resources/Public/Styles/FrontendEditing.css" />';
        $resources .= '<link rel="stylesheet" type="text/css" href="/typo3conf/ext/frontend_editing/Resources/Public/Javascript/toastr/build/toastr.min.css" />';
        $resources .= '<script src="typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery-' . PageRenderer::JQUERY_VERSION_LATEST . '.min.js" type="text/javascript"></script>';
       // $resources .= '<script src="typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery-2.2.3.min.js" type="text/javascript"></script>';
        $resources .= '<script src="/typo3conf/ext/frontend_editing/Resources/Public/Javascript/ckeditor/ckeditor.js" type="text/javascript"></script>';
        $resources .= '<script src="/typo3conf/ext/frontend_editing/Resources/Public/Javascript/ckeditor/adapters/jquery.js" type="text/javascript"></script>';
        $resources .= '<script src="/typo3conf/ext/frontend_editing/Resources/Public/Javascript/toastr/build/toastr.min.js" type="text/javascript"></script>';

        return $resources;
    }

    /**
     * Get the icon path
     *
     * @param string $icon
     * @return string
     * @todo do it correctly
     */
    public function getIcon($icon) {
        return \TYPO3\CMS\Backend\Utility\IconUtility::skinImg('/' . TYPO3_mainDir, 'sysext/t3skin/icons/gfx/' . $icon, 'width="16" height="16"');
    }

    /**
     * Determine if page is loaded from the TYPO3 BE
     *
     * @return bool
     */
    protected function httpRefererIsFromBackendViewModule() {
        $parsedReferer = parse_url($_SERVER['HTTP_REFERER']);
        $pathArray = explode('/', $parsedReferer['path']);
        $viewPageView = preg_match('/web_ViewpageView/i', $parsedReferer['query']);
        return (strtolower($pathArray[1]) === 'typo3' && $viewPageView);
    }
}
