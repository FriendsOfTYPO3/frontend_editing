<?php

declare(strict_types=1);

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

namespace TYPO3\CMS\FrontendEditing\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Convenience methods related to configuration
 */
class ConfigurationUtility
{
    /**
     * Returns the Extension Manager configuration array
     *
     * @return array
     */
    public static function getExtensionConfiguration(): array
    {
        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('frontend_editing');

        if (is_array($configuration)) {
            return $configuration;
        }

        return [];
    }
}
