<?php
namespace TYPO3\CMS\FrontendEditing\Utility;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Html\RteHtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * A class that handles the integration of CKEditor
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Integration
{

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

            $rteHtmlParser = new RteHtmlParser();
            $fieldContent = $rteHtmlParser->RTE_transform($fieldContent, $vconf['spec'], 'db', $thisConfig);
        }

        return $fieldContent;
    }

    /**
     * Returns the row of a record given by $table and $id and $fieldList (list of fields, may be '*')
     * NOTICE: No check for deleted or access!
     *
     * @param string $table
     * @param integer $id
     * @param string $fieldList field list for the SELECT query, eg. "*" or "uid,pid,..."
     * @return mixed Returns the selected record on success, otherwise false.
     */
    public static function recordInfo($table, $id, $fieldList = '*')
    {
        if (is_array($GLOBALS['TCA'][$table])) {
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
            $queryBuilder
                ->getRestrictions()
                ->removeAll();
            $record = $queryBuilder
                ->select($fieldList)
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('uid', (int)$id)
                )
                ->execute()
                ->fetch();
                //->getSQL();var_dump($record);die;
            return $record;
        }

        return false;
    }

	/**
	 * Returns the title label used in Backend lists
	 *
	 * @param int $uid of the content element
	 * @return string
	 */
    public static function contentElementTitle($uid)
    {
    	static $titles = [];

    	//Optimized retrieval
    	if($titles[$uid] !== NULL) {
    		return $titles[$uid];
	    }

	    $rawRecord = BackendUtility::getRecord(
		    'tt_content',
		    $uid
	    );

	    $titles[$uid] = BackendUtility::getRecordTitle(
		    'tt_content',
		    $rawRecord,
		    TRUE
	    );

	    return $titles[$uid];
    }
}
