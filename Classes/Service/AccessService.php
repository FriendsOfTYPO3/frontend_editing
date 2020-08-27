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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Check access of the user to display only those actions which are allowed and needed
 */
class AccessService implements SingletonInterface
{
    /**
     * Frontend editing is enabled
     *
     * @var bool
     */
    protected $isEnabled = false;

    /**
     * Checks if frontend editing is enabled, checking UserTsConfig and TS
     */
    public function __construct()
    {
        // Frontend editing needs to be enabled also by admins
        if (isset($GLOBALS['BE_USER'])) {
            $isFrontendEditingEnabled = GeneralUtility::_GET('frontend_editing_enabled');
            if (isset($isFrontendEditingEnabled) && (bool)$isFrontendEditingEnabled === true) {
                $this->isEnabled = true;
            }
        }

        // Determine if page is loaded from the TYPO3 BE
        if ($this->isEnabled && !empty(GeneralUtility::getIndpEnv('HTTP_REFERER'))) {
            $parsedReferer = parse_url(GeneralUtility::getIndpEnv('HTTP_REFERER'));
            $pathArray = explode('/', $parsedReferer['path']);
            $viewPageView = isset($parsedReferer['query'])
                && preg_match('/web_ViewpageView/i', $parsedReferer['query']);
            $refererFromBackend = strtolower($pathArray[1]) === 'typo3' && $viewPageView;
            if ($refererFromBackend) {
                $this->isEnabled = false;
            }
        }
    }

    /**
     * Is frontend editing enabled or disabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Has the user edit rights for page? (works with current page by default)
     *
     * @param array $page
     *
     * @return bool
     */
    public function isPageEditAllowed($page = []): bool
    {
        if (!$page) {
            $page = $GLOBALS['TSFE']->page;
        }
        return $GLOBALS['BE_USER']->doesUserHaveAccess($page, Permission::PAGE_EDIT);
    }

    /**
     * Has the user create rights under current page?
     *
     * @return bool
     */
    public function isPageCreateAllowed(): bool
    {
        return $GLOBALS['BE_USER']->doesUserHaveAccess($GLOBALS['TSFE']->page, Permission::PAGE_NEW);
    }

    /**
     * Has the user edit rights for the content of the page?
     *
     * @param array $page
     *
     * @return bool
     */
    public function isPageContentEditAllowed(array $page): bool
    {
        return $GLOBALS['BE_USER']->doesUserHaveAccess($page, Permission::CONTENT_EDIT);
    }

    /**
     * Check if it is backend context
     *
     * @return bool
     */
    public function isBackendContext(): bool
    {
        return (isset($GLOBALS['BE_USER'])) ? true : false;
    }
}
