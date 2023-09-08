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

use Closure;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * View helper to enable frontend editing for records in fluid
 *
 * Example:
 * <core:customDropZone tables="{0:'tx_news_domain_model_news',1:'tx_whatever'}" pageUid="{data.uid}" defVals="{colPos: 101, tx_container_parent: data.uid}">
 *     <p>some content</p>
 * </core:customDropZone>
 *
 * Output:
 * <p>some content</p>
 * <div class="t3-frontend-editing__dropzone t3-frontend-editing__custom-dropzone"
 *      ondrop="window.parent.F.dropCe(event)"
 *      ondragover="window.parent.F.dragCeOver(event)"
 *      ondragleave="window.parent.F.dragCeLeave(event)"
 *      data-new-url="/typo3/record/edit..."
 *      data-allowed-tables="tx_news_domain_model_news,tx_whatever"
 *      data-pid="378"
 *      data-defvals="{&quot;colPos&quot;:101,&quot;tx_container_parent&quot;:378}"></div>
 */
class CustomDropZoneViewHelper extends AbstractViewHelper
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
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'tables',
            'array',
            'The database tables name allowed to be droped',
            true
        );
        $this->registerArgument(
            'defVals',
            'array',
            'Default value for new record droped in zone'
        );
        $this->registerArgument(
            'pageUid',
            'string',
            'Override storage page uid for new record droped in zone'
        );
        $this->registerArgument(
            'prepend',
            'bool',
            'Prepend the zone to the content'
        );
    }

    /**
     * Add a content-editable div around the content
     *
     * @param array $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return mixed Rendered result
     * @throws RouteNotFoundException
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): mixed {
        $content = $renderChildrenClosure();
        if (!AccessService::isEnabled()) { return $content; }

        /** @var ContentEditableWrapperService $wrapperService */
        $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);

        $defaultValues = is_array($arguments['defVals']) ? $arguments['defVals'] : [];

        return $wrapperService->wrapContentWithCustomDropzone(
            implode(',', $arguments['tables']),
            (string)$content,
            $defaultValues,
            (int)$arguments['pageUid'],
            (bool)$arguments['prepend']
        );
    }
}
