<?php
declare(strict_types=1);
namespace TYPO3\CMS\FrontendEditing\Controller;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Class for fetching the proper RTE configuration of a given field
 */
class EditorController
{

    /**
     * Processed values from FormEngine
     * @var array
     */
    protected $formData;

    /**
     * @var array
     */
    protected $rteConfiguration;

    /**
     * Loads the CKEditor configuration for a specific field of a record
     * kicks FormEngine in since this is used to resolve the proper record type
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function getConfigurationAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $queryParameters = $request->getQueryParams();
        $table = $queryParameters['table'];
        $uid = (int)$queryParameters['uid'];
        $fieldName = $queryParameters['field'];

        /** @var TcaDatabaseRecord $formDataGroup */
        $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        /** @var FormDataCompiler $formDataCompiler */
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);

        $formDataCompilerInput = [
            'vanillaUid' => (int)$uid,
            'tableName' => $table,
            'command' => 'edit',
            // done intentionally to speed up the compilation of the processedTca
            'disabledWizards' => true
        ];

        $this->formData = $formDataCompiler->compile($formDataCompilerInput);
        $this->rteConfiguration = $this->formData['processedTca']['columns'][$fieldName]['config']['richtextConfiguration']['editor'];

        $configuration = $this->prepareConfigurationForEditor();

        $externalPlugins = '';
        foreach ($this->getExtraPlugins() as $pluginName => $config) {
            $configuration[$pluginName] = $config['config'];
            $configuration['extraPlugins'] .= ',' . $pluginName;

            $externalPlugins .= 'CKEDITOR.plugins.addExternal(';
            $externalPlugins .= GeneralUtility::quoteJSvalue($pluginName) . ',';
            $externalPlugins .= GeneralUtility::quoteJSvalue($config['resource']) . ',';
            $externalPlugins .= '\'\');';
        }

        $data = [
            'configuration' => $configuration,
            'externalPlugins' => $externalPlugins
        ];

        $response->getBody()->write(json_encode($data));
        return $response;
    }

    /**
     * Get configuration of external/additional plugins
     *
     * @return array
     */
    protected function getExtraPlugins(): array
    {
        $urlParameters = [
            'P' => [
                'table'      => $this->formData['tableName'],
                'uid'        => $this->formData['databaseRow']['uid'],
                'fieldName'  => $this->formData['fieldName'],
                'recordType' => $this->formData['recordTypeValue'],
                'pid'        => $this->formData['effectivePid'],
            ]
        ];

        $pluginConfiguration = [];
        if (isset($this->rteConfiguration['externalPlugins']) && is_array($this->rteConfiguration['externalPlugins'])) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            foreach ($this->rteConfiguration['externalPlugins'] as $pluginName => $configuration) {
                $pluginConfiguration[$pluginName] = [
                    'resource' => $this->resolveUrlPath($configuration['resource'])
                ];
                unset($configuration['resource']);

                if ($configuration['route']) {
                    $configuration['routeUrl'] = (string)$uriBuilder->buildUriFromRoute($configuration['route'], $urlParameters);
                }

                $pluginConfiguration[$pluginName]['config'] = $configuration;
            }
        }
        return $pluginConfiguration;
    }

    /**
     * Add configuration to replace absolute EXT: paths with relative ones
     * @param array $configuration
     *
     * @return array
     */
    protected function replaceAbsolutePathsToRelativeResourcesPath(array $configuration): array
    {
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                $configuration[$key] = $this->replaceAbsolutePathsToRelativeResourcesPath($value);
            } elseif (is_string($value) && substr($value, 0, 4) === 'EXT:') {
                $configuration[$key] = $this->resolveUrlPath($value);
            }
        }
        return $configuration;
    }

    /**
     * Resolves an EXT: syntax file to an absolute web URL
     *
     * @param string $value
     * @return string
     */
    protected function resolveUrlPath(string $value): string
    {
        $value = GeneralUtility::getFileAbsFileName($value);
        return PathUtility::getAbsoluteWebPath($value);
    }

    /**
     * Compiles the configuration set from the outside
     * to have it easily injected into the CKEditor.
     *
     * @return array the configuration
     */
    protected function prepareConfigurationForEditor(): array
    {
        // Ensure custom config is empty so nothing additional is loaded
        // Of course this can be overriden by the editor configuration below
        $configuration = [
            'customConfig' => '',
        ];

        if (is_array($this->rteConfiguration['config'])) {
            $configuration = array_replace_recursive($configuration, $this->rteConfiguration['config']);
        }
        $configuration['contentsLanguage'] = $this->getLanguageIsoCodeOfContent();

        // replace all paths
        $configuration = $this->replaceAbsolutePathsToRelativeResourcesPath($configuration);

        // there are some places where we define an array, but it needs to be a list in order to work
        if (is_array($configuration['extraPlugins'])) {
            $configuration['extraPlugins'] = implode(',', $configuration['extraPlugins']);
        }
        if (is_array($configuration['removePlugins'])) {
            $configuration['removePlugins'] = implode(',', $configuration['removePlugins']);
        }
        if (is_array($configuration['removeButtons'])) {
            $configuration['removeButtons'] = implode(',', $configuration['removeButtons']);
        }

        return $configuration;
    }

    /**
     * Determine the contents language iso code
     *
     * @return string
     */
    protected function getLanguageIsoCodeOfContent(): string
    {
        $currentLanguageUid = $this->formData['databaseRow']['sys_language_uid'];
        if (is_array($currentLanguageUid)) {
            $currentLanguageUid = $currentLanguageUid[0];
        }
        $contentLanguageUid = (int)max($currentLanguageUid, 0);
        if ($contentLanguageUid) {
            $contentLanguage = $this->formData['systemLanguageRows'][$currentLanguageUid]['iso'];
        } else {
            $contentLanguage = $this->rteConfiguration['config']['defaultContentLanguage'] ?? 'en_US';
            $languageCodeParts = explode('_', $contentLanguage);
            $contentLanguage = strtolower($languageCodeParts[0]) . ($languageCodeParts[1] ? '_' . strtoupper($languageCodeParts[1]) : '');
            // Find the configured language in the list of localization locales
            $locales = GeneralUtility::makeInstance(Locales::class);
            // If not found, default to 'en'
            if (!in_array($contentLanguage, $locales->getLocales(), true)) {
                $contentLanguage = 'en';
            }
        }
        return $contentLanguage;
    }
}
