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
 * Basic helper class
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Helper
{

    /**
     * Determine if page is loaded from the TYPO3 BE
     *
     * @return bool
     */
    public static function httpRefererIsFromBackendViewModule()
    {
        $parsedReferer = parse_url($_SERVER['HTTP_REFERER']);
        $pathArray = explode('/', $parsedReferer['path']);
        $viewPageView = preg_match('/web_ViewpageView/i', $parsedReferer['query']);
        return (strtolower($pathArray[1]) === 'typo3' && $viewPageView);
    }
}
