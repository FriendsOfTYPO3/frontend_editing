<?php
namespace TYPO3\CMS\FrontendEditing\Utility;

/**
 * Check access of the user to display only those actions which are allowed
 * and needed
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Access
{

    /**
     * Checks if aloha editor is enabled, checking UserTsConfig and TS
     *
     * @return boolean
     */
    public static function isEnabled()
    {
        // aloha needs to be enabled also by admins
        // this is the only way how to temporarly turn on/off the editor
        if (isset($GLOBALS['BE_USER']) && $GLOBALS['TSFE']->config['config']['aloha'] == 1) {
            return ($GLOBALS['BE_USER']->uc['tx_aloha_enable'] == 1);
        }

        return false;
    }


    public static function checkAccess($table, array $dataArray, $config)
    {
        if (empty($table) || empty($dataArray) || empty($config)) {
            return false;
        }

        if (!isset($GLOBALS['BE_USER'])) {
            return false;
        }

        if ($GLOBALS['BE_USER']->isAdmin()) {
            return true;
        }

        // not needed: $GLOBALS['TSFE']->displayFieldEditIcons
        if (self::allowedToEdit($table, $dataArray, $config)
            && self::allowedToEditLanguage($table, $dataArray)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Checks whether the user is allowed to edit the requested table.
     *
     * @param    string    The name of the table.
     * @param    array    The data array.
     * @param    array    The configuration array for the edit panel.
     * @param    boolean    Boolean indicating whether recordEditAccessInternals should not be checked. Defaults
     *                     to TRUE but doesn't makes sense when creating new records on a page.
     * @return    boolean
     */
    protected function allowedToEdit($table, array $dataArray, array $conf, $checkEditAccessInternals = true)
    {
        // Unless permissions specifically allow it, editing is not allowed.
        $mayEdit = false;

        // Basic check if use is allowed to edit a record of this kind (based on TCA configuration)
        if ($checkEditAccessInternals) {
            $editAccessInternals = $GLOBALS['BE_USER']->recordEditAccessInternals($table, $dataArray, false, false);
        } else {
            $editAccessInternals = true;
        }


        if ($editAccessInternals) {
            if ($table === 'pages') {
                // 2 = permission to edit the page
                if ($GLOBALS['BE_USER']->isAdmin() || $GLOBALS['BE_USER']->doesUserHaveAccess($dataArray, 2)) {
                    $mayEdit = true;
                }
            } elseif ($table === 'tt_content') {
                // 16 = permission to edit content on the page
                if ($GLOBALS['BE_USER']->isAdmin() || $GLOBALS['BE_USER']->doesUserHaveAccess(
                    \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('pages', $dataArray['pid']),
                    16
                )) {
                    $mayEdit = true;
                }
            } else {
                $mayEdit = true;
                // neither page nor content
            }

            // Permissions
            if (!$conf['onlyCurrentPid'] || ($dataArray['pid'] == $GLOBALS['TSFE']->id)) {
                $types = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
                    ',',
                    \TYPO3\CMS\Core\Utility\GeneralUtility::strtolower($conf['allow']),
                    1
                );
                $allow = array_flip($types);

                $perms = $GLOBALS['BE_USER']->calcPerms($GLOBALS['TSFE']->page);

                if ($table === 'pages') {
                    $allow = $this->getAllowedEditActions($table, $conf, $dataArray['pid'], $allow);

                    // Can only display editbox if there are options in the menu
                    if (count($allow)) {
                        $mayEdit = true;
                    }
                } else {
                    if ($table === 'tt_content') {
                        // user may edit the content if he has an allowed edit action and if the permission
                        // for the content is odd and not 1 explanation of permissions:
                        // show=1,edit=2,delete=4,new=8,editcontent=16
                        // assuming that show must be set to have content editable,
                        // each permission is odd, but show itself isn't sufficient
                        $mayEdit = count($allow) && ($perms & 1 && $perms !== 1) ? true : false;
                    } else {
                        // user may edit the content if he has an allowed edit action and
                        // if the permission for the content is odd and not 1
                        // explanation of permissions: show=1,edit=2,delete=4,new=8,editcontent=16
                        // assuming that show must be set to have content editable,
                        // each permission is odd, but show itself isn't sufficient
                        $mayEdit = ($perms & 1 && $perms !== 1);
                    }
                }
            }
        }

        return $mayEdit;
    }

    /**
     * Checks whether the user has access to edit the language for the
     * requested record.
     *
     * @param    string        The name of the table.
     * @param    array        The record.
     * @return    boolean
     */
    protected function allowedToEditLanguage($table, array $currentRecord)
    {
        $languageUid = -1;
        $languageAccess = false;

        // If no access right to record languages, return immediately
        if ($table === 'pages') {
            $languageUid = $GLOBALS['TSFE']->sys_language_uid;
        } elseif ($table === 'tt_content') {
            $languageUid = $GLOBALS['TSFE']->sys_language_content;
        } elseif ($GLOBALS['TCA'][$table]['ctrl']['languageField']) {
            $languageUid = $currentRecord[$GLOBALS['TCA'][$table]['ctrl']['languageField']];
        }

        if ($GLOBALS['BE_USER']->checkLanguageAccess($languageUid)) {
            $languageAccess = true;
        }

        return $languageAccess;
    }
}
