<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\UserFunc;

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

use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * Functions for assisting HtmlParser
 */
class HtmlParserUserFunc
{
    /**
     * Removes the URL parameter "frontend_editing" (e.g. "?frontend_editing=true") from the supplied url.
     *
     * @param $url
     * @param HtmlParser $htmlParser
     * @return string
     */
    public function removeFrontendEditingInUrl($url, HtmlParser $htmlParser)
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl['query'] !== null) {
            $queryArguments = GeneralUtility::explodeUrl2Array($parsedUrl['query']);

            if (isset($queryArguments['frontend_editing'])) {
                unset($queryArguments['frontend_editing']);

                $parsedUrl['query'] = GeneralUtility::implodeArrayForUrl('', $queryArguments);

                $url = HttpUtility::buildUrl($parsedUrl);
            }
        }
        return $url;
    }

    /**
     * Adds the URL parameter "frontend_editing" (e.g. "?frontend_editing=true") from the supplied url.
     *
     * @param $url
     * @param HtmlParser $htmlParser
     * @return string
     */
    public function addFrontendEditingInUrl($url, HtmlParser $htmlParser)
    {
        $parsedUrl = parse_url($url);

        $queryArguments = [];

        if ($parsedUrl['query'] !== null) {
            $queryArguments = GeneralUtility::explodeUrl2Array($parsedUrl['query']);
        }

        $queryArguments['frontend_editing'] = 'true';

        $parsedUrl['query'] = GeneralUtility::implodeArrayForUrl('', $queryArguments);

        $url = HttpUtility::buildUrl($parsedUrl);

        return $url;
    }
}
