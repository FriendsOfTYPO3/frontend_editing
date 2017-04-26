<?php
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;

/**
 * Hook is called in ContentObjectRenderer when rendering CONTENT
 * It's used to determine if content column is empty and add drop zone
 *
 * @package TYPO3\CMS\FrontendEditing\Hook
 */
class ContentObjectRendererHook
{
    /**
     * Render content like in parent object
     * If there is not content and table is tt_content - add drop zone
     *
     * @param string $name
     * @param array $conf
     * @param string $TSkey
     * @param ContentObjectRenderer $pObject
     * @return string
     */
    public function cObjGetSingleExt(string $name, array $conf, string $TSkey, ContentObjectRenderer $pObject): string
    {
        $content = '';

        $contentObject = $pObject->getContentObject($name);
        if ($contentObject) {
            $content .= $pObject->render($contentObject, $conf);

            // If not content found wrap with drop zone
            // Add drop zone only of colPos is set
            if (empty($content)
                && $conf['table'] === 'tt_content'
                && !empty($conf['select.']['where'])
                && GeneralUtility::isFirstPartOfStr(ltrim($conf['select.']['where']), 'colPos')
            ) {
                list(, $colPos) = GeneralUtility::intExplode('=', $conf['select.']['where'], true);

                /** @var ContentEditableWrapperService $wrapperService */
                $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);

                $content = $wrapperService->wrapContentWithDropzone(
                    $conf['table'],
                    -1,
                    $content,
                    $colPos
                );
            }
        }

        return $content;
    }
}
