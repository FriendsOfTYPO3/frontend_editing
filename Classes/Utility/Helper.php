<?php
namespace TYPO3\CMS\FrontendEditing\Utility;

/**
 * Basic helper class
 */
class Helper
{

    /**
     * Get a unique string by given arguments
     *
     * @param string $table table name
     * @param string $field fieldname
     * @param string $id uid of element
     * @return string
     */
    public static function getUniqueId($table, $field, $id)
    {
        $out = implode('--', array($table, $field, $id));

        return $out;
    }

    /**
     * Helper function to translate
     *
     * @param string $key
     * @return string
     */
    public static function ll($key, $file = 'locallang.xml')
    {
        $text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, "Aloha");
        $text = (!empty($text)) ? $text : '??? ' . $key . ' ???';

        return $text;
    }

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
