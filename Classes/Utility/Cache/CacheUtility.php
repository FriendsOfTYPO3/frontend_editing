<?php
namespace TYPO3\CMS\FrontendEditing\Utility\Cache;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\CacheService;

/**
 * Class CacheUtility
 * Handling cache operations for TYPO3
 *
 * @package TYPO3\CMS\FrontendEditing\Utility\Cache
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
