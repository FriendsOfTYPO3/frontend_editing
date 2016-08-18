<?php
namespace TYPO3\CMS\FrontendEditing\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Check access of the user to display only those actions which are allowed
 * and needed
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Integration
{

    /**
     * Get count of changed elements per page
     *
     * @param integer $pageId page uid
     * @return integer
     */
    public static function getCountOfUnsavedElements($pageId)
    {
        $elements = $GLOBALS['BE_USER']->uc['aloha'][$pageId];
        if (is_array($elements)) {
            return count($elements);
        }
        return 0;
    }

    /**
     * Remove staged changes
     *
     * @param integer $id page uid
     * @return void
     */
    public static function removeStagedElements($id)
    {
        /*if (\Pixelant\Aloha\Utility\Access::isEnabled()) {
            $GLOBALS['BE_USER']->uc['aloha'][$id] = array();
            $GLOBALS['BE_USER']->writeUC();
        }*/
    }

    /**
     * Render an element (div/span) around given content,
     * including given attributes
     *
     * @param string $content
     * @param array $attributes
     * @param string $tag
     * @return string
     */
    public static function renderAlohaWrap($content, array $attributes, $tag = '')
    {
        $tag = (empty($tag)) ? 'div' : $tag;

        $attributesAsString = '';
        foreach ($attributes as $attributeKey => $value) {
            if (!empty($value)) {
                $attributesAsString .= ' ' . $attributeKey . '="' . htmlspecialchars($value) . '"';
            }
        }
        return '<' . $tag . $attributesAsString . '>' . $content . '</' . $tag . '>';
    }

    /**
     * Modify content if it is RTE base by using RteHtmlParser functions
     *
     * @param string $table table name
     * @param string $field field name
     * @param integer $id id of record
     * @param integer $pageId current page id
     * @param string $fieldContent content
     * @return string
     */
    public static function rteModification($table, $field, $id, $pageId, $fieldContent)
    {
        $fieldConfig = $GLOBALS['TCA'][$table]['columns'][$field]['config']['wizards'];
        if (isset($fieldConfig['RTE'])) {
            $currentRecord = self::recordInfo($table, $id);

            $theTypeString = BackendUtility::getTCAtypeValue($table, $currentRecord);

            $types_fieldConfig2 = BackendUtility::getTCAtypes($table, $currentRecord);
            $vconf = [];
            foreach ($types_fieldConfig2 as $config) {
                if ($config['field'] == $field) {
                    $vconf = $config;
                }
            }

            $RTEsetup = $GLOBALS['BE_USER']->getTSConfig('RTE', BackendUtility::getPagesTSconfig($pageId));
            $thisConfig = BackendUtility::RTEsetup($RTEsetup['properties'], $table, $vconf['field'], $theTypeString);

            $rteHtmlParser = new \TYPO3\CMS\Core\Html\RteHtmlParser();
            $fieldContent = $rteHtmlParser->RTE_transform($fieldContent, $vconf['spec'], 'db', $thisConfig);
        }

        return $fieldContent;
    }

    /**
     * Returns the row of a record given by $table and $id and $fieldList (list of fields, may be '*')
     * NOTICE: No check for deleted or access!
     *
     * @param    string        Table name
     * @param    integer        UID of the record from $table
     * @param    string        Field list for the SELECT query, eg. "*" or "uid,pid,..."
     * @return    mixed        Returns the selected record on success, otherwise FALSE.
     */
    private static function recordInfo($table, $id, $fieldList = '*')
    {
        if (is_array($GLOBALS['TCA'][$table])) {
            $record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow($fieldList, $table, 'uid=' . intval($id));
            return $record;
        }

        return false;
    }
}
