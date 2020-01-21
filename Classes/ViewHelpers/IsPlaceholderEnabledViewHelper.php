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

use TYPO3\CMS\FrontendEditing\Service\ExtensionManagerConfigurationService;
use TYPO3\CMS\FrontendEditing\Utility\FrontendEditingUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * View helper to use in conditions in connection with the placeholder feature.
 *
 * Will return true if the placeholder feature is enabled.
 *
 * Example:
 *
 * <f:if condition="{header} || {core:isPlaceholderEnabled()}">
 *     {header}
 * </f:if>
 */
class IsPlaceholderEnabledViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Returns true if the frontend editor is active and the placeholder feature is enabled
     *
     * @return bool|string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return FrontendEditingUtility::isEnabled()
            && ExtensionManagerConfigurationService::getSettings()['enablePlaceholders'];
    }
}
