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

/**
 * Check access of the user to display only those actions which are allowed and needed
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
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
