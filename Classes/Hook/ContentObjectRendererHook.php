<?php
declare(strict_types = 1);
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
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;

/**
 * Hook is called in ContentObjectRenderer when rendering CONTENT
 * It's used to determine if content column is empty and add drop zone
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
        $contentObject = $pObject->getContentObject($name);

        if (!$contentObject) {
            return '';
        }

        $content = $pObject->render($contentObject, $conf);

        // If content found or not tt_content with colPos is fetched or not in
        // frontend_editing context than return the content.
        if ($content
            || $conf['table'] !== 'tt_content'
            || !isset($conf['select.']['where'])
            || empty($conf['select.']['where'])
            || strpos($conf['select.']['where'], 'colPos') === false
            || !GeneralUtility::_GET('frontend_editing')
        ) {
            return $content;
        }

        /** @var AccessService $access */
        $access = GeneralUtility::makeInstance(AccessService::class);

        if (!$access->isEnabled()) {
            return '';
        }

        // Maybe the where clause needs parsed to stdwrap before we get the colPos
        if (isset($conf['select.']['where.'])) {
            $conf['select.']['where'] = $pObject->stdWrapValue('where', $conf['select.']);
        }

        // Extract the colPos, will match {#colPos}= or colPos=
        preg_match('/colPos\}? *= *([-]?[1-9]\d*|0)/', $conf['select.']['where'], $colPosFinding);

        if (!isset($colPosFinding[1])) {
            return '';
        }

        /** @var ContentEditableWrapperService $wrapperService */
        $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);

        return $wrapperService->wrapContentWithDropzone(
            $conf['table'],
            0,
            '',
            (int)$colPosFinding[1]
        );
    }
}
