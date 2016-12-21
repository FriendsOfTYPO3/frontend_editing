<?php
namespace TYPO3\CMS\FrontendEditing\Utility;

/**
 * Check access of the user to display only those actions which are allowed
 * and needed
 */
class Access
{

    /**
     * Checks if frontend editing is enabled, checking UserTsConfig and TS
     *
     * @return boolean
     */
    public static function isEnabled()
    {
        // Frontend editing needs to be enabled also by admins
        if (isset($GLOBALS['BE_USER']) && $GLOBALS['TSFE']->config['config']['tx_frontend_editing'] == 1) {
            return ($GLOBALS['BE_USER']->uc['tx_frontend_editing_enable'] == 1);
        }

        return false;
    }
}
