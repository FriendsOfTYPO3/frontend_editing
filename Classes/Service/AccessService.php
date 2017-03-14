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

/**
 * Check access of the user to display only those actions which are allowed and needed
 */
class AccessService
{
    /**
     * Checks if frontend editing is enabled, checking UserTsConfig and TS
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $isEnabled = false;
        // Frontend editing needs to be enabled also by admins
        if (isset($GLOBALS['BE_USER']) && $GLOBALS['TSFE']->config['config']['frontend_editing'] == 1) {
            $isEnabled = (bool)$GLOBALS['BE_USER']->uc['frontend_editing'] === true;
        }
        // Determine if page is loaded from the TYPO3 BE
        if ($isEnabled && !empty($_SERVER['HTTP_REFERER'])) {
            $parsedReferer = parse_url($_SERVER['HTTP_REFERER']);
            $pathArray = explode('/', $parsedReferer['path']);
            $viewPageView = isset($parsedReferer['query']) && preg_match('/web_ViewpageView/i', $parsedReferer['query']);
            $refererFromBackend = strtolower($pathArray[1]) === 'typo3' && $viewPageView;
            if ($refererFromBackend) {
                $isEnabled = false;
            }
        }
        return $isEnabled;
    }
}
