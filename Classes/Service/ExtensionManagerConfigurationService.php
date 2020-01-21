<?php
declare(strict_types = 1);

namespace TYPO3\CMS\FrontendEditing\Service;

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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Service class to get the settings from Extension Manager
 */
class ExtensionManagerConfigurationService
{
    /**
     * Parse settings and return it as an array
     *
     * @return array unserialized extconf settings
     */
    public static function getSettings(): array
    {
        $typo3VersionNumber = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        );

        if ($typo3VersionNumber >= 9000000) {
            $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('frontend_editing');
        } else {
            $settings = [];
            // @extensionScannerIgnoreLine
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['frontend_editing'])) {
                // @extensionScannerIgnoreLine
                $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['frontend_editing']);
            }
        }

        if (!is_array($settings)) {
            $settings = [];
        }

        return $settings;
    }
}
