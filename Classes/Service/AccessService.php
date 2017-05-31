<?php
declare(strict_types=1);
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

use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\SingletonInterface;

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
        if (isset($GLOBALS['BE_USER']) && $GLOBALS['TSFE']->config['config']['frontend_editing'] == 1) {
            $this->isEnabled = (bool)$GLOBALS['BE_USER']->uc['frontend_editing'] === true;
        }
        // Determine if page is loaded from the TYPO3 BE
        if ($this->isEnabled && !empty($_SERVER['HTTP_REFERER'])) {
            $parsedReferer = parse_url($_SERVER['HTTP_REFERER']);
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
     * Has the user edit rights for page?
     *
     * @return bool
     */
    public function isPageEditAllowed(): bool
    {
        return $GLOBALS['BE_USER']->doesUserHaveAccess($GLOBALS['TSFE']->page, Permission::PAGE_EDIT);
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
}
