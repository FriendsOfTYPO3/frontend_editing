<?php
namespace TYPO3\CMS\FrontendEditing\Utility\Cache;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\CacheService;

/**
 * Class for handling cache operations for TYPO3
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CacheUtility
{

    /**
     * Clear the cache for a single page (pid)
     *
     * @param array $pageUid
     */
    public static function clearPageCache($pageUid = [])
    {
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $cacheService->clearPageCache($pageUid);
    }
}
