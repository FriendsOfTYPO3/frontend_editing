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

namespace TYPO3\CMS\FrontendEditing\Service;

use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\ContentFetcher;
use TYPO3\CMS\Backend\View\BackendLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service that tells if Frontend Editing is enabled or not
 */
class AccessService
{
    /**
     * Is Frontend Editing enabled or not?
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        $isEnabled = false;
        if (
            isset($GLOBALS['BE_USER'])
            && $GLOBALS['BE_USER'] instanceof FrontendBackendUserAuthentication
            && GeneralUtility::_GET('fe_edit')
        ) {
            $isEnabled = true;
        }

        return $isEnabled;
    }

    /**
     * Has the user edit rights for the content of the page?
     *
     * @param array $page
     * @param int $languageId
     * @return bool
     */
    public static function isPageEditAllowed(array $page, int $languageId = 0): bool
    {
        if ($GLOBALS['TCA']['pages']['ctrl']['readOnly'] ?? false) {
            return false;
        }
        $backendUser = $GLOBALS['BE_USER'];
        if ($backendUser->isAdmin()) {
            return true;
        }
        if ($GLOBALS['TCA']['pages']['ctrl']['adminOnly'] ?? false) {
            return false;
        }

        return $page !== []
            && !($page[$GLOBALS['TCA']['pages']['ctrl']['editlock'] ?? null] ?? false)
            && $backendUser->doesUserHaveAccess($page, Permission::PAGE_EDIT)
            && $backendUser->checkLanguageAccess($languageId)
            && $backendUser->check('tables_modify', 'pages');
    }

    /**
     * Has the user edit rights for the content of the page?
     *
     * @param array $page
     *
     * @return bool
     */
    public static function isPageContentEditAllowed(array $page): bool
    {
        return $GLOBALS['BE_USER']->doesUserHaveAccess($page, Permission::CONTENT_EDIT);
    }

    /**
     * Check if it is backend context
     *
     * @return bool
     */
    public static function isBackendContext(): bool
    {
        return isset($GLOBALS['BE_USER']);
    }

    /**
     * @param int $pageId
     * @param int $pageLanguageId
     * @return string
     */
    public static function getTranslationMode(int $pageId, int $pageLanguageId): string
    {
        $translationMode = '';

        // Get translation mode only in BE context and for not default language
        if (AccessService::isBackendContext() && $pageLanguageId > 0) {
            $pageinfo = BackendUtility::readPageAccess($pageId, $GLOBALS['BE_USER']->getPagePermsClause(Permission::PAGE_SHOW));
            $context = GeneralUtility::makeInstance(
                PageLayoutContext::class,
                $pageinfo,
                GeneralUtility::makeInstance(BackendLayoutView::class)->getBackendLayoutForPage($pageId)
            );
            $contentFetcher = GeneralUtility::makeInstance(ContentFetcher::class, $context);

            $translationInfo = $contentFetcher->getTranslationData(
                $contentFetcher->getFlatContentRecords($pageLanguageId),
                $pageLanguageId
            );

            $translationMode = $translationInfo['mode'] ?? '';
        }

        return $translationMode;
    }
}
