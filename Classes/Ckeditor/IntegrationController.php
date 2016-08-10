<?php

namespace TYPO3\CMS\FrontendEditing\Ckeditor;

use TYPO3\CMS\Core\Utility\GeneralUtility;

// @codingStandardsIgnoreStart

/**
 * Integration class of aloha into TYPO3
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class IntegrationController
{

    protected $table = null;
    protected $field = null;
    protected $uid = null;
    protected $dataArray = array();
    protected $alohaConfig = array();

    public function start($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject)
    {
        try {
            $alohaConfig = $configuration;
            $this->init($parentObject, $alohaConfig);

            //$access = Tx_Aloha_Utility_Access::checkAccess($this->table, $this->dataArray, $this->alohaConfig);
            //if ($access) {
                if (empty($content)) {
                    $alohaConfig['class'] .= 'aloha-empty-content';
                }
            if ($this->dataArray['hidden'] == 1) {
                $alohaConfig['class'] .= ' aloha-preview-content';
            }

            $classList = array('alohaeditable');
            $this->getAllowedActions($alohaConfig, $classList);

            $attributes = array(
                    'id' =>  11, //Tx_Aloha_Utility_Helper::getUniqueId($this->table, $this->field, $this->uid),
                    'class' => implode(' ', $classList),
                    'style' => $alohaConfig['style']
                );

            $content = \TYPO3\CMS\FrontendEditing\Utility\CkeditorUtilityIntegration::renderAlohaWrap($content, $attributes, $alohaConfig['tag']);
            //}
        } catch (Exception $e) {
            $errorMsg = sprintf('Error with AlohaEditor: %s', $e->getMessage());
            $content .= '<div style="color:red;padding:2px;margin:2px;font-weight:bold;">' . htmlspecialchars($errorMsg) . '</div>';
        }

        return $content;
    }

    /**
     * Modify classList and add the actions which are allowed
     *
     * @param array $alohaConfig
     * @param array $classList
     * @return void
     */
    private function getAllowedActions(array $alohaConfig, array &$classList)
    {
        $allowedActions = array_flip(GeneralUtility::trimExplode(',', $alohaConfig['allow']));

        // Hiding in workspaces because implementation is incomplete
        // @todo: check that
        if ((isset($allowedActions['all']) || isset($allowedActions['move'])) && $GLOBALS['TCA'][$this->table]['ctrl']['sortby'] && $GLOBALS['BE_USER']->workspace === 0) {
            array_push($classList, 'action-up');
            array_push($classList, 'action-down');
            array_push($classList, 'action-move');
        }
        // edit action
        if ($this->checkAccess($allowedActions, 'edit')) {
            array_push($classList, 'action-edit');
        }

        // link action
        if ($this->checkAccess($allowedActions, 'link')) {
            array_push($classList, 'action-link');
        }
        if (isset($GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['disabled'])) {
            $disabledField = $GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['disabled'];
            if ($this->checkAccess($allowedActions, 'hide') && $this->dataArray[$disabledField] == 0) {
                array_push($classList, 'action-hide');
            }
            if ($this->checkAccess($allowedActions, 'unhide') && $this->dataArray[$disabledField] == 1) {
                array_push($classList, 'action-unhide');
            }
        }

        // Add new content elements underneath
        if ($this->checkAccess($allowedActions, 'newContentElementBelow')) {
            array_push($classList, 'action-newContentElementBelow');
        }

        // @todo: && $GLOBALS['BE_USER']->workspace === 0 && !$dataArr['_LOCALIZED_UID']
        // still true, check that
        if ($this->checkAccess($allowedActions, 'delete')) {
            array_push($classList, 'action-delete');
        }

        // Additional class by TS
        if (isset($alohaConfig['class'])) {
            array_push($classList, htmlspecialchars($alohaConfig['class']));
        }

        // Restrict editor by removing all styles
        if ($alohaConfig['nostyles'] == 1) {
            array_push($classList, 'nostyles');
        }
    }

    /**
     * Initialize the integration to get needed configs
     *
     * @param tslib_cObj $parentObject
     * @param array $alohaConfig
     * @return void
     */
    private function init(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parentObject, array $alohaConfig)
    {
        list($table, $id) = GeneralUtility::trimExplode(':', $parentObject->currentRecord);
        $currentRecord = $parentObject->data;

        if (isset($currentRecord['_LOCALIZED_UID'])) {
            $id = $currentRecord['_LOCALIZED_UID'];
        }

       /* if (empty($table)) {
            throw new Exception(Tx_Aloha_Utility_Helper::ll('error.integration.table'));
        } elseif (empty($id)) {
            throw new Exception(Tx_Aloha_Utility_Helper::ll('error.integration.uid'));
        } elseif (empty($alohaConfig['field'])) {
            throw new Exception(Tx_Aloha_Utility_Helper::ll('error.integration.field'));
        }*/

        $this->table = $table;
        $this->field = $alohaConfig['field'];
        $this->uid = $id;
        $this->dataArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $table, 'uid=' . (int)$id);
        $this->alohaConfig = $alohaConfig;
    }

    /**
     * Check the access for a single given access
     *
     * @param array $allowedActions configuration array
     * @param string $access access type
     * @return boolean
     */
    private function checkAccess(array $allowedActions, $access)
    {
        if (isset($allowedActions['all']) || isset($allowedActions[$access])) {
            return true;
        }
        return false;
    }
}

// @codingStandardsIgnoreEnd