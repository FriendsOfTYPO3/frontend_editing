<?php

namespace TYPO3\CMS\FrontendEditing\Utility;

/**
 * Check access of the user to display only those actions which are allowed
 * and needed
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class CkeditorUtilityIntegration
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
        //if (Tx_Aloha_Utility_Access::isEnabled()) {
            $GLOBALS['BE_USER']->uc['aloha'][$id] = array();
        $GLOBALS['BE_USER']->writeUC();
        //}
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
                $attributesAsString.= ' ' . $attributeKey . '="' . htmlspecialchars($value) . '"';
            }
        }
        return '<' . $tag . $attributesAsString . '>' . $content . '</' . $tag . '>';
    }

    /**
     * Modifiy content if it is RTE based
     * by using core internal sick functions
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
        t3lib_div::loadTCA($table);
        $fieldConfig = $GLOBALS['TCA'][$table]['columns'][$field]['config']['wizards'];
        if (isset($fieldConfig['RTE'])) {
            $currentRecord = self::recordInfo($table, $id);

            $RTEsetup = $GLOBALS['BE_USER']->getTSConfig('RTE', t3lib_BEfunc::getPagesTSconfig($pageId));

            $theTypeString = t3lib_BEfunc::getTCAtypeValue($table, $currentRecord);

            $types_fieldConfig2 = t3lib_BEfunc::getTCAtypes($table, $currentRecord);
            $vconf = array();
            foreach ($types_fieldConfig2 as $config) {
                if ($config['field'] == $field) {
                    $vconf = $config;
                }
            }

            $RTEsetup = $GLOBALS['BE_USER']->getTSConfig('RTE', t3lib_BEfunc::getPagesTSconfig($pageId));
            $thisConfig = t3lib_BEfunc::RTEsetup($RTEsetup['properties'], $table, $vconf['field'], $theTypeString);

            // @todo check that
            $RTErelPath = is_array($eFile) ? dirname($eFile['relEditFile']) : '';

            $RTEobj = t3lib_BEfunc::RTEgetObj();
            if (is_object($RTEobj)) {
                $fieldContent = $RTEobj->transformContent(
                    'db',
                    $fieldContent,
                    $table,
                    $vconf['field'],
                    $currentRecord,
                    $vconf['spec'],
                    $thisConfig,
                    $RTErelPath,
                    $currentRecord['pid']
                );
            } else {
                // @todo
                debug('NO RTE OBJECT FOUND!');
            }
        }

        return $fieldContent;
    }

    /**
     * Returns the row of a record given by $table and $id and $fieldList (list of fields, may be '*')
     * NOTICE: No check for deleted or access!
     *
     * @param string Table name
     * @param integer UID of the record from $table
     * @param string Field list for the SELECT query, eg. "*" or "uid,pid,..."
     * @return mixed Returns the selected record on success, otherwise FALSE.
     */
    private function recordInfo($table, $id, $fieldList = '*')
    {
        if (is_array($GLOBALS['TCA'][$table])) {
            $record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow($fieldList, $table, 'uid=' . intval($id));
            return $record;
        }

        return false;
    }
}
