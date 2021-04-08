<?php

namespace TYPO3\CMS\FrontendEditing\ViewHelpers;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Page\PageRepository as DeprecatedPageRepository;
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * View helper to enable frontend editing for records in fluid
 *
 * Example:
 *
 * <core:contentEditable table="tt_content" field="bodytext" uid="{item.uid}">
 *     {item.bodytext}
 * </core:contentEditable>
 *
 * Output:
 * <div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">
 *     This is the content text to edit
 * </div>
 */
class ContentEditableViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Disable the escaping of children
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Disable that the content itself isn't escaped
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerUniversalTagAttributes();

        $this->registerArgument(
            'table',
            'string',
            'The database table name to be used for saving the content',
            true
        );
        $this->registerArgument(
            'field',
            'string',
            'The database table field name to be used for saving the content',
            false
        );
        $this->registerArgument(
            'uid',
            'string',
            'The database uid (identifier) to be used for the record when saving the content',
            true
        );
        $this->registerArgument(
            'tag',
            'string',
            'An optional tag name, e.g. "div" or "span".',
            false
        );
    }

    /**
     * Add a content-editable tag around the content.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string Rendered email link
     */
    public function render() {
        $content = $this->renderChildren();
        $access = GeneralUtility::makeInstance(AccessService::class);

        if (!$access->isBackendContext()) {
            return $this->renderAsTag($content);
        }

        $typo3VersionNumber = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        );

        if ($typo3VersionNumber < 10000000) {
            // @extensionScannerIgnoreLine
            $pageRepositoryClassName = DeprecatedPageRepository::class;
        } else {
            $pageRepositoryClassName = PageRepository::class;
        }

        $record = BackendUtility::getRecord($this->arguments['table'], (int)$this->arguments['uid']);
        $isPageContentEditAllowed = false;
        try {
            $isPageContentEditAllowed = $access->isPageContentEditAllowed(
                GeneralUtility::makeInstance($pageRepositoryClassName)
                    ->getPage_noCheck($record['pid'])
            );
        } catch (\Exception $exception) {
            // Suppress Exception and no database access
            $isPageContentEditAllowed = true;
        }

<<<<<<< HEAD
        if (!$access->isEnabled() || !$isPageContentEditAllowed) {
            return $this->renderAsTag($content);
=======
            if ($typo3VersionNumber < 10000000) {
                // @extensionScannerIgnoreLine
                $pageRepositoryClassName = DeprecatedPageRepository::class;
            } else {
                $pageRepositoryClassName = PageRepository::class;
            }

            $record = BackendUtility::getRecord($arguments['table'], (int)$arguments['uid']);
            $isPageContentEditAllowed = false;
            try {
                $isPageContentEditAllowed = $access->isPageContentEditAllowed(
                    GeneralUtility::makeInstance($pageRepositoryClassName)
                        ->getPage_noCheck($record['pid'])
                );
            } catch (\Exception $exception) {
                // Suppress Exception and no database access
                $isPageContentEditAllowed = true;
            }

            if (!$access->isEnabled() || !$isPageContentEditAllowed) {
                return $content;
            }

            $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);
            if (empty($arguments['field'])) {
                $content = $wrapperService->wrapContent(
                    $arguments['table'],
                    (int)$arguments['uid'],
                    ($record ? $record : []),
                    $content
                );
            } else {
                $content = $wrapperService->wrapContentToBeEditable(
                    $arguments['table'],
                    $arguments['field'],
                    (int)$arguments['uid'],
                    $content
                );
            }
>>>>>>> origin/develop-2.0
        }

        $filteredArguments = array_diff_key(
            $this->arguments,
            array_fill_keys(
                [
                    'table',
                    'field',
                    'uid',
                    'tag'
                ],
                ''
            )
        );

        $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);
        $content = $wrapperService->wrapContentToBeEditable(
            $this->arguments['table'],
            $this->arguments['field'],
            (int)$this->arguments['uid'],
            $content,
            $this->arguments['tag'],
            $filteredArguments
        );

        return $content;
    }

    /**
     * Render as a non-editable tag or just content if $this->arguments[tag] is not set.
     *
     * @param string $content
     */
    protected function renderAsTag(string $content)
    {
        if ($this->arguments['tag'] !== null) {
            $this->tagName = $this->arguments['tag'];
            $this->tag->setTagName($this->tagName);
            $this->tag->setContent($content);

            $content = $this->tag->render();
        }

        return $content;
    }
}
