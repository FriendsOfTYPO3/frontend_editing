<?php
declare(strict_types=1);

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

use Exception;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

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
    public function initializeArguments(): void
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
            'The database table field name to be used for saving the content'
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
            'An optional tag name, e.g. "div" or "span".'
        );
    }

    /**
     * Add a content-editable tag around the content.
     *
     * @return string Rendered HTML markup
     * @throws RouteNotFoundException
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function render(): string
    {
        $content = $this->renderChildren();

        if (!AccessService::isBackendContext()) {
            return $this->renderAsTag($content);
        }

        $pageRepositoryClassName = PageRepository::class;
        $record = BackendUtility::getRecord($this->arguments['table'], (int)$this->arguments['uid']);
        try {
            $isPageContentEditAllowed = AccessService::isPageContentEditAllowed(
                GeneralUtility::makeInstance($pageRepositoryClassName)
                    ->getPage_noCheck($record['pid'])
            );
        } catch (Exception $exception) {
            // Suppress Exception and no database access
            $isPageContentEditAllowed = true;
        }

        if (!AccessService::isEnabled() || !$isPageContentEditAllowed) {
            $content = $content ?: '';
            return $this->renderAsTag($content);
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
        if (empty($this->arguments['field'])) {
            $content = $wrapperService->wrapContent(
                $this->arguments['table'],
                (int)$this->arguments['uid'],
                ($record ?: []),
                (string)$content
            );
        } else {
            $content = $wrapperService->wrapContentToBeEditable(
                $this->arguments['table'],
                $this->arguments['field'],
                (int)$this->arguments['uid'],
                (string)$content,
                $this->arguments['tag'],
                $filteredArguments
            );
        }

        return $content;
    }

    /**
     * Render as a non-editable tag or just content if $this->arguments[tag] is not set.
     *
     * @param string|null $content
     * @return string
     */
    protected function renderAsTag(?string $content): string
    {
        if ($this->arguments['tag'] !== null) {
            $this->tagName = $this->arguments['tag'];
            $this->tag->setTagName($this->tagName);
            $this->tag->setContent($content);

            $content = $this->tag->render();
        } elseif ($content === null) {
            $content = '';
        }

        return $content;
    }
}
