<?php
namespace TYPO3\CMS\FrontendEditing\Hook;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

class ContentPostProc
{

    const ll = 'LLL:EXT:aloha/Resources/Private/Language/locallang.xml:';

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

    public function __construct() {
        $this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['aloha']);

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
                                ' . $userIcon . $GLOBALS['BE_USER']->user['username'] . '
                            </div>
                        </div>
                    </div>
                    <div class="frontend-editing-right-bar">
                    </div>';

                $iframeUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') .
                    'index.php?id=' . $this->typoScriptFrontendController->id .
                    '&frontend_editing=true'
                ;

                // $parentObject->content = str_ireplace('</body>', $output . '</body>', $parentObject->content);

                $parentObject->content = sprintf(
                    '%s<iframe src="%s" width="%s" height="%s" frameborder="%s" border="%s"></iframe>',
                    $output,
                    $iframeUrl,
                    '100%',
                    '100%',
                    '0',
                    '0'
                );
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

        return $resources;
    }

    /**
     * Create content for left side of the bar
     *
     * @return string
     */
    public function getToolbarLeft() {
        $countOfElements = \Pixelant\Aloha\Utility\Integration::getCountOfUnsavedElements($GLOBALS['TSFE']->id);

        $countMessage = sprintf(
            $this->sL(self::ll . 'headerbar.count'),
            '<span id="count" class="' . ($countOfElements > 0 ? 'tobesaved' : '') . '">' . $countOfElements . '</span>'
        );

        $content = '<div id="alohaeditor-welcome" class="welcome">' . $this->getMenu() . '</div>';

        // Get responsive buttons
        $content .= $this->getResponsiveButtons();

        // Output depends on selected save method
        if ($this->configuration['saveMethod'] === 'intermediate') {
            $content .= '<span class="count-holder">' . $countMessage . '</span>
					<span class="button-holder">
						<span id="aloha-saveButton"  class="aloha-button save">' . $this->sL(self::ll . 'headerbar.save_button') . '</span>
						<span id="aloha-discardButton" class="aloha-button discard">' . $this->sL(self::ll . 'headerbar.discard_button') . '</span>
					</span>';
        } elseif (($this->configuration['saveMethod'] === 'direct') && (!$this->settings['topBar.']['warningMessage.']['disable'])) {
            $content .= '<span class="aloha-warning">' . $this->sL(self::ll . 'headerbar.saveMethod.direct', FALSE) . '</span>';

        } elseif (($this->configuration['saveMethod'] === 'none') && (!$this->settings['topBar.']['warningMessage.']['disable'])) {
            $content .= '<span class="aloha-warning">' . $this->sL(self::ll . 'headerbar.saveMethod.none', FALSE) . '</span>';
        }

        return $content;
    }

    /**
     * Create content for right side of the bar
     *
     * @return string
     */
    public function getToolbarRight() {
        $content = '';

        $perms = $GLOBALS['BE_USER']->calcPerms($GLOBALS['TSFE']->page);
        $langAllowed = $GLOBALS['BE_USER']->checkLanguageAccess($GLOBALS['TSFE']->sys_language_uid);

        //  If mod.web_list.newContentWiz.overrideWithExtension is set, use that extension's create new content wizard instead:
        $tsConfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig($this->pageinfo['uid'], 'mod.web_list');
        $tsConfig = $tsConfig['properties']['newContentWiz.']['overrideWithExtension'];
        $newContentWizScriptPath = ExtensionManagementUtility::isLoaded($tsConfig) ? (ExtensionManagementUtility::extRelPath($tsConfig) . 'mod1/db_new_content_el.php') : (TYPO3_mainDir . 'sysext/cms/layout/db_new_content_el.php');


        $id = $GLOBALS['TSFE']->id;

        // Edit page properties
        if (($perms & 2) && (!$this->settings['topBar.']['pageButtons.']['edit.']['disable'])) {
            $params = '&edit[pages][' . $GLOBALS['TSFE']->id . ']=edit&noView=1';
            $url = TYPO3_mainDir . 'alt_doc.php?' . $params . $this->getReturnUrl();

            $content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
						<img ' . $this->getIcon('edit_page.gif') . ' title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:editPageProperties') . '" alt="" />
					</a>';

            if (isset($this->typoScriptFrontendController->page['_PAGES_OVERLAY']) && isset($this->typoScriptFrontendController->page['_PAGES_OVERLAY_UID']) && $langAllowed) {
                $params = '&edit[pages_language_overlay][' . $this->typoScriptFrontendController->page['_PAGES_OVERLAY_UID'] . ']=edit&noView=1';
                $url = TYPO3_mainDir . 'alt_doc.php?' . $params . $this->getReturnUrl();

                $content .= '<a rel="shadowbox" href="' . htmlspecialchars($url) . '">
						<img ' . $this->getIcon('edit.gif') . '  title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:editPageProperties') . '- overlay' . '" alt="" /></a>

					</a>';
            }
        }

        // @todo: add some permissions for that
        if (TRUE && (!$this->settings['topBar.']['pageButtons.']['history.']['disable'])) {
            // Record history
            $url = TYPO3_mainDir . 'show_rechis.php?element=' . rawurlencode('pages:' . $id) . $this->getReturnUrl();

            $content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
					<img ' . $this->getIcon('history2.gif') .
                ' title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:recordHistory') . '" alt="" /></a>';
        }

        // New record
        if (($perms & 16) && $langAllowed && (!$this->settings['topBar.']['pageButtons.']['newContentElement.']['disable'])) {
            $params = '';
            if ($GLOBALS['TSFE']->sys_language_uid) {
                $params = '&sys_language_uid=' . $GLOBALS['TSFE']->sys_language_uid;
            }
            $url = $newContentWizScriptPath . '?id=' . $id . $params . $this->getReturnUrl();

            $content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
					<img ' . $this->getIcon('new_record.gif') . '  title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:newContentElement') . '" alt="" /></a>';
        }

        // Move
        if (($perms & 2) && (!$this->settings['topBar.']['pageButtons.']['move.']['disable'])) {
            $url = TYPO3_mainDir . 'move_el.php?table=pages&uid=' . $GLOBALS['TSFE']->id . $this->getReturnUrl();

            $content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
					<img ' . $this->getIcon('move_page.gif') . ' title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:move_page') . '" alt="" /></a>';
        }

        // New Page
        if (($perms & 8) && (!$this->settings['topBar.']['pageButtons.']['newPage.']['disable'])) {
            $url = TYPO3_mainDir . 'db_new.php?id=' . $id . '&pagesOnly=1' . $this->getReturnUrl();
            $content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
					<img ' . $this->getIcon('new_page.gif') . ' title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:newPage') . '" alt="" /></a>';
        }

        // Wrap title and classes around
        if (!empty($content)) {
            $content = '<span class="page-edit-header">' .
                $this->sL('LLL:EXT:lang/locallang_tca.php:pages') .
                ':</span>
						<span class="page-edit-buttons">' . $content . '</span>';
        }

        return $content;
    }

    /**
     * Get responsive control buttons
     *
     * @return string
     */
    public function getResponsiveButtons() {
        $responsiveItemsControlArray = array('desktop', 'laptop', 'tablet', 'mobile');

        $output = '';

        foreach ($responsiveItemsControlArray as $key => $button) {
            if (!$this->settings['responsiveView.']['buttons.'][$button . '.']['disable']) {
                $classOfButton = $button == 'mobile' ? 'mobile-phone' : $button;
                $output .= '
					<a title="' . ucfirst($button) . ' view" onclick="pxa.aloha.resizeViewFrame(\'' . $button . '\'); return false;" href="#">
						<i class="alohaicon-' . $classOfButton . ' alohaicon-large"></i>
					</a>';
            }
        }

        if (!empty($output)) {
            $output = '<div class="aloha-viewpage-controls">' . $output . '</div>';
        }

        return $output;
    }

    /**
     *
     * @return string
     * @todo make items configurable by TsConfig
     */

    public function getMenu() {
        $content = '';

        //Logout
        $logoutUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . TYPO3_mainDir . 'logout.php?redirect=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
        $content .= '<a href="' . htmlspecialchars($logoutUrl) . '" class="btn btn-danger"><i class="alohaicon-power-off"></i></a>';

        //toggle state of aloha (on/off)
        $content .= '<a target="_top" id="aloha-onoff" class="btn btn-success" data-text-activate="'. $this->sL(self::ll . 'headerbar.activate_editing') .'" data-text-deactivate="'. $this->sL(self::ll . 'headerbar.deactivate_editing') .'">'. $this->sL(self::ll . 'headerbar.activate_editing') .' </a>';

        //Open Backend
        $backendUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . TYPO3_mainDir;
        $content .= '<a target="_top" href="' . htmlspecialchars($backendUrl) . '" class="btn btn-success">' . $this->sL('LLL:EXT:lang/locallang_login.xml:interface.backend') . '</a>';

        if (!empty($content)) {
            $content = '<div class="aloha-menu-wrap">' . $content . '</div>';
        }

        return $content;
    }

    /**
     * Generate JS url
     *
     * @param string $url
     * @return string
     */
    public function jsUrl($url) {
        $url = "var vHWin = window.open('$url','FEquickEditWindow', 'width=690,height=500,status=0,menubar=0,scrollbars=1,resizable=1');vHWin.focus();";

        return $url;
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
     * Short hand syntax for $GLOBALS['LANG']->sL() with hsc() by default
     *
     * @param string $key path to translation
     * @param boolean $hsc htmlspecialchars by default
     */
    public function sL($key, $hsc = TRUE) {
        // it can happen that this is null, no wonder why
        if (is_null($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance('language');
            $GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
        }
        return $GLOBALS['LANG']->sL($key, $hsc);
    }

    /**
     * Lightbox url
     *
     * @param string $url
     * @return string
     */
    public function lightboxUrl($url) {
        return 'Shadowbox.open({ content: \'' . htmlspecialchars($url) . '\', player:\'iframe\' }); return false;';
    }

    /**
     * Get return url with custom close.html
     *
     * @return string
     */
    public function getReturnUrl() {
        return '&returnUrl=' . TYPO3_mainDir . '../../typo3conf/ext/aloha/Resources/Public/Contrib/shadowbox/close.html?saved=1';
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

?>
