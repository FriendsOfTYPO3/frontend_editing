<?php
declare(strict_types = 1);

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

use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendEditingUtility
{
    public static function isEnabled()
    {
        if (GeneralUtility::_GET('frontend_editing') &&
            isset($GLOBALS['BE_USER']) &&
            $GLOBALS['BE_USER'] instanceof FrontendBackendUserAuthentication &&
            $GLOBALS['BE_USER']->uc['frontend_editing'] === 1
        ) {
            return true;
        }
        return false;
    }
}
