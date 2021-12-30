<?php

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

namespace TYPO3\CMS\FrontendEditing\Xclass\ContentObject;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Added a check for if $dataArray['_LOCALIZED_UID'] is set to
 * prevent error in PHP 8 on line 54.
 */
class ContentObjectRenderer extends \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
{
    /**
     * @inheritDoc
     */
    public function editIcons($content, $params, array $conf = [], $currentRecord = '', $dataArray = [], $addUrlParamStr = '')
    {
        if (!$this->getTypoScriptFrontendController()->isBackendUserLoggedIn()) {
            return $content;
        }
        if (!$this->getTypoScriptFrontendController()->displayFieldEditIcons) {
            return $content;
        }
        if (!$currentRecord) {
            $currentRecord = $this->currentRecord;
        }
        if (empty($dataArray)) {
            $dataArray = $this->data;
        }
        // Check incoming params:
        [$currentRecordTable, $currentRecordUID] = explode(':', $currentRecord);
        [$fieldList, $table] = array_reverse(GeneralUtility::trimExplode(':', $params, true));
        // Reverse the array because table is optional
        if (!$table) {
            $table = $currentRecordTable;
        } elseif ($table != $currentRecordTable) {
            // If the table is set as the first parameter, and does not match the table of the current record, then just return.
            return $content;
        }

        $editUid = isset($dataArray['_LOCALIZED_UID']) ?: $currentRecordUID;
        // Edit icons imply that the editing action is generally allowed, assuming page and content element permissions permit it.
        if (!array_key_exists('allow', $conf)) {
            $conf['allow'] = 'edit';
        }
        if ($table && $this->getFrontendBackendUser()->allowedToEdit($table, $dataArray, $conf, true) && $fieldList && $this->getFrontendBackendUser()->allowedToEditLanguage($table, $dataArray)) {
            $editClass = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/classes/class.frontendedit.php']['edit'];
            if ($editClass) {
                trigger_error('Hook "typo3/classes/class.frontendedit.php" is deprecated together with stdWrap.editIcons and will be removed in TYPO3 12.0.', E_USER_DEPRECATED);
                $edit = GeneralUtility::makeInstance($editClass);
                $content = $edit->editIcons($content, $params, $conf, $currentRecord, $dataArray, $addUrlParamStr, $table, $editUid, $fieldList);
            }
        }
        return $content;
    }
}
