<?php
/** @noinspection PhpPossiblePolymorphicInvocationInspection */
declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Hook;

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

use DOMDocument;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\FrontendEditing\Service\AccessService;

/**
 * Hook into ContentObjectRenderer to modify the final links tags
 */
class TypoLinkPostProcHook
{
    /**
     * Modify final tag to save unparsed links but allow editors to still browse the website
     *
     * @param array $params
     * @param ContentObjectRenderer $contentObjectRenderer
     * @return void
     * @noinspection PhpUnused
     */
    public function modifyFinalLinkTag(array &$params, ContentObjectRenderer $contentObjectRenderer): void
    {
        if (AccessService::isEnabled()) {
            $parsedUrl = $contentObjectRenderer->lastTypoLinkUrl;
            $unparsedUrl = $contentObjectRenderer->parameters['href'] ?? false;
            $linkPageUid = $params['linkDetails']['pageuid'] ?? false;

            // Create a new DOMDocument object to manipulate the A tag
            $doc = new DOMDocument();
            $doc->loadHTML($params['finalTag']);
            $a = $doc->getElementsByTagName('a')->item(0);

            // Set href to unparsed URL if set, else set it to parsed URL
            // this is done so, when the editor modify a CE, the unparsed URL is saved instead of the parsed one
            $href = $unparsedUrl ?: $parsedUrl;
            $a->setAttribute('href', $href);

            // Add data-url-page-uid used to eventually navigate Frontend Editing module to the page of the clicked link
            if ($linkPageUid) { $a->setAttribute('data-url-page-uid', $linkPageUid); }

            // Add data-url-parsed to the final tag to allow editor to navigate the FE also in Frontend Editing mode
            $a->setAttribute('data-url-parsed', $parsedUrl);

            // Save the final tag removing the closing tag '</a>'
            $params['finalTag'] = str_replace('</a>', '', $doc->saveHTML($a));
        }
    }
}
