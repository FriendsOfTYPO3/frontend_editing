<?php

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

namespace TYPO3\CMS\FrontendEditing\Core\Page;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Service\AccessService;

/**
 * A class for being able to load RequireJs in combination with Frontend Editing and TYPO3 11.
 * The $accessService->isEnabled() have been added to enforce the backend simulation
 * for Frontend Editing requests.
 *
 * An additional if is also present to check if javaScriptRenderer is not null on line 58.
 * (isset($this->javaScriptRenderer) && $this->javaScriptRenderer !== null)
 */
class PageRenderer extends \TYPO3\CMS\Core\Page\PageRenderer
{
    /**
     * @inheritDoc
     */
    public function renderMainJavaScriptLibraries()
    {
        $out = '';
        $accessService = GeneralUtility::makeInstance(AccessService::class);

        // Include RequireJS
        if ($this->addRequireJs) {
            $out .= $this->getRequireJsLoader();
        }

        $this->loadJavaScriptLanguageStrings();
        if ($this->getApplicationType() === 'BE' || $accessService->isEnabled()) {
            $noBackendUserLoggedIn = empty($GLOBALS['BE_USER']->user['uid']);
            $this->addAjaxUrlsToInlineSettings($noBackendUserLoggedIn);
        }
        $assignments = array_filter([
            'settings' => $this->inlineSettings,
            'lang' => $this->parseLanguageLabelsForJavaScript(),
        ]);
        if ($assignments === []) {
            return '';
        }
        if (
            ($this->getApplicationType() === 'BE' || $accessService->isEnabled())
            && isset($this->javaScriptRenderer) && $this->javaScriptRenderer !== null
        ) {
            $this->javaScriptRenderer->addGlobalAssignment(['TYPO3' => $assignments]);
            $out .= $this->javaScriptRenderer->render();
        } else {
            $out .= sprintf(
                "%svar TYPO3 = Object.assign(TYPO3 || {}, %s);\r\n%s",
                $this->inlineJavascriptWrap[0],
                // filter potential prototype pollution
                sprintf(
                    'Object.fromEntries(Object.entries(%s).filter((entry) => '
                    . "!['__proto__', 'prototype', 'constructor'].includes(entry[0])))",
                    json_encode($assignments)
                ),
                $this->inlineJavascriptWrap[1],
            );
        }
        return $out;
    }
}
