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

use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Implements hooks in TemplateService
 */
class TemplateServiceHook
{
    /**
     * Modifies $row['include_static_file'] to include TypoScript template with TYPO3 8.7-compatible conditions.
     *
     * (I.e. not Symfony Expression language.)
     *
     * @param string $functionReference
     * @param array $_params
     * @param TemplateService $self
     *
     * @deprecated For TYPO3 8 compatibility. Will be removed in frontend_editing v2.0
     */
    public function includeStaticTypoScriptSources(string $functionReference, array &$parameters, TemplateService $self)
    {
        $templateUris = GeneralUtility::trimExplode(
            ',',
            $parameters['row']['include_static_file'],
            true
        );

        foreach ($templateUris as &$line) {
            if ($line === 'EXT:frontend_editing/Configuration/TypoScript') {
                $line .= '/Compatibility8';
            }
        }
    }
}
