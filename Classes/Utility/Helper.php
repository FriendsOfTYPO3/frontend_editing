<?php
namespace TYPO3\CMS\FrontendEditing\Utility;

/**
 * Basic helper class
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Helper {

    /**
     * Get a unique string by given arguments
     *
     * @param string $table table name
     * @param string $field fieldname
     * @param string $id uid of element
     * @return string
     */
    public static function getUniqueId($table, $field, $id) {
        $out = implode('--', array($table, $field, $id));

        return $out;
    }

    /**
     * Helper function to translate
     *
     * @param string $key
     * @return string
     */
    public static function ll($key, $file = 'locallang.xml') {
        $text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, "Aloha");
        $text = (!empty($text)) ? $text : '??? ' . $key . ' ???';

        return $text;
    }
}

?>
