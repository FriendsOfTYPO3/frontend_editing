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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
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
class ContentEditableViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

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
    }

    /**
     * Add a content-editable div around the content
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string Rendered email link
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $content = $renderChildrenClosure();
        $content = ($content != null ? $content : '');
        $record = BackendUtility::getRecord($arguments['table'], (int)$arguments['uid']);

        $access = GeneralUtility::makeInstance(AccessService::class);
        if ($access->isBackendContext()) {
            $isPageContentEditAllowed = false;
            try {
                $isPageContentEditAllowed = $access->isPageContentEditAllowed(
                    GeneralUtility::makeInstance(PageRepository::class)
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
        }
        return $content;
    }
}
