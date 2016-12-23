<?php
namespace TYPO3\CMS\FrontendEditing\Utility;

/**
 * Basic helper class
 */
class Helper
{

    /**
     * Determine if page is loaded from the TYPO3 BE
     *
     * @return bool
     */
    public static function httpRefererIsFromBackendViewModule()
    {
        $parsedReferer = parse_url($_SERVER['HTTP_REFERER']);
        $pathArray = explode('/', $parsedReferer['path']);
        $viewPageView = preg_match('/web_ViewpageView/i', $parsedReferer['query']);
        return (strtolower($pathArray[1]) === 'typo3' && $viewPageView);
    }
}
