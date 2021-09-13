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
use TYPO3\CMS\Frontend\ContentObject\TypolinkModifyLinkConfigForPageLinksHookInterface;

/**
 * Hook is for adding the parameter to all links when GET parameter "frontend_editing" is present
 */
class ModifyTypoLinkConfig implements TypolinkModifyLinkConfigForPageLinksHookInterface
{
    /**
     * Apply the additionalParams "frontend_editing"
     *
     * @param array $linkConfiguration
     * @param array $linkDetails
     * @param array $pageRow
     * @return array
     */
    public function modifyPageLinkConfiguration(array $linkConfiguration, array $linkDetails, array $pageRow): array
    {
        $isFrontendEditingEnabled = GeneralUtility::_GET('frontend_editing');
        if ((bool)$isFrontendEditingEnabled === true) {
            $linkConfiguration['additionalParams'] .= '&frontend_editing=true';
        }

        return $linkConfiguration;
    }
}
