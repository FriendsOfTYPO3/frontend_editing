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
use TYPO3\CMS\Backend\Form\Exception\AccessDeniedEditInternalsException;
use TYPO3\CMS\Backend\Form\Exception\AccessDeniedTableModifyException;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\FrontendEditing\Utility\ConfigurationUtility;

/**
 * Class for fetching the proper RTE configuration of a given field
 */
class EditorController
{

    /**
     * Processed values from FormEngine
     * @var array
     */
    protected array $formData;

    /**
     * @var array
     */
    protected array $rteConfiguration;

    /**
     * Loads the CKEditor configuration for a specific field of a record
     * kicks FormEngine in since this is used to resolve the proper record type
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface|null $response
     * @return Response|ResponseInterface|null
     * @throws RouteNotFoundException
     * @noinspection PhpUnused
     */
    public function getConfigurationAction(ServerRequestInterface $request, ResponseInterface $response = null): Response|ResponseInterface|null
    {
        if ($response === null) {
            $response = new Response();
        }

        // Set frontend form data compilation
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'] =
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['frontendTcaDatabaseRecord'];

        /** @var TcaDatabaseRecord $formDataGroup */
        $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        /** @var FormDataCompiler $formDataCompiler */
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);

        $queryParameters = $request->getParsedBody();

        $configurations = [];
        $elements = [];
        if (isset($queryParameters['elements'])) {
            foreach ($queryParameters['elements'] as $element) {
                $table = $element['table'];
                $uid = (int)$element['uid'];
                $fieldName = $element['field'];

                $formDataCompilerInput = [
                    'vanillaUid' => $uid,
                    'tableName' => $table,
                    'command' => 'edit',
                    // done intentionally to speed up the compilation of the processedTca
                    'disabledWizards' => true
                ];

                try {
                    $this->formData = $formDataCompiler->compile($formDataCompilerInput);

                    $formDataFieldName = $this->formData['processedTca']['columns'][$fieldName] ?? null;
                    $this->rteConfiguration = (isset($formDataFieldName['config']['richtextConfiguration']))
                        ? $formDataFieldName['config']['richtextConfiguration']['editor'] : [];
                    $hasCkeditorConfiguration = !empty($this->rteConfiguration);

                    $editorConfiguration = $this->prepareConfigurationForEditor();

                    $externalPlugins = '';
                    foreach ($this->getExtraPlugins() as $pluginName => $config) {
                        $editorConfiguration[$pluginName] = $config['config'];
                        $editorConfiguration['extraPlugins'] = (isset($editorConfiguration['extraPlugins']))
                            ? $editorConfiguration['extraPlugins'] : '';
                        if ($editorConfiguration['extraPlugins'] !== null && $editorConfiguration['extraPlugins'] !== '') {
                            $editorConfiguration['extraPlugins'] .= ',';
                        }
                        $editorConfiguration['extraPlugins'] .= $pluginName;

                        $externalPlugins .= 'CKEDITOR.plugins.addExternal(';
                        $externalPlugins .= GeneralUtility::quoteJSvalue($pluginName) . ',';
                        $externalPlugins .= GeneralUtility::quoteJSvalue($config['resource']) . ',';
                        $externalPlugins .= '\'\');';
                    }

                    $configuration = [
                        'configuration' => $editorConfiguration,
                        'hasCkeditorConfiguration' => $hasCkeditorConfiguration,
                        'externalPlugins' => $externalPlugins,
                    ];

                    $configurationKey = '';
                    foreach ($configurations as $existingConfigurationKey => $existingConfiguration) {
                        if (json_encode($existingConfiguration) === json_encode($configuration)) {
                            $configurationKey = $existingConfigurationKey;
                            break;
                        }
                    }

                    if ($configurationKey === '') {
                        $configurationKey = count($configurations);
                        $configurations[$configurationKey] = $configuration;
                    }

                    $elements[$uid . '_' . $table . '_' . $fieldName] = $configurationKey;
                } catch (AccessDeniedEditInternalsException|AccessDeniedTableModifyException $exception) {
                    // The editor does not have access to the table of this specific field or to the field itself so,
                    // instead of displaying a toast with an error, we simply intercept the exception and go on.
                }
            }
        }

        $response->getBody()->write(json_encode([
            'elementToConfiguration' => $elements,
            'configurations' => $configurations,
        ]));

        return $response;
    }

    /**
     * Get configuration of external/additional plugins
     *
     * @return array
     * @throws RouteNotFoundException
     */
    protected function getExtraPlugins(): array
    {
        $urlParameters = [
            'P' => [
                'table'      => $this->formData['tableName'],
                'uid'        => $this->formData['databaseRow']['uid'],
                'fieldName'  => $this->formData['fieldName'] ?? '',
                'recordType' => $this->formData['recordTypeValue'],
                'pid'        => $this->formData['effectivePid'],
            ]
        ];

        if (!isset($this->rteConfiguration['externalPlugins'])
            || !is_array($this->rteConfiguration['externalPlugins'])) {
            $this->rteConfiguration['externalPlugins'] = [];
        }

        // Register CKEditor openlink plugin used to handle frontend links navigation
        $this->rteConfiguration['externalPlugins']['openlink'] = [
            'resource' => 'EXT:frontend_editing/Resources/Public/JavaScript/Plugins/openlink/plugin.js'
        ];

        if (ConfigurationUtility::getExtensionConfiguration()['enablePlaceholders']) {
            $this->rteConfiguration['externalPlugins']['confighelper'] = [
                'resource' => 'EXT:frontend_editing/Resources/Public/JavaScript/Plugins/confighelper/plugin.js'
            ];
        }

        $pluginConfiguration = [];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        foreach ($this->rteConfiguration['externalPlugins'] as $pluginName => $configuration) {
            $pluginConfiguration[$pluginName] = [
                'resource' => $this->resolveUrlPath($configuration['resource'])
            ];
            unset($configuration['resource']);

            if (isset($configuration['route'])) {
                $configuration['routeUrl'] = (string)$uriBuilder->buildUriFromRoute(
                    $configuration['route'],
                    $urlParameters
                );
            }

            $pluginConfiguration[$pluginName]['config'] = $configuration;
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

        if (isset($this->rteConfiguration['config']) && is_array($this->rteConfiguration['config'])) {
            $configuration = array_replace_recursive($configuration, $this->rteConfiguration['config']);
        }
        $configuration['contentsLanguage'] = $this->getLanguageIsoCodeOfContent();

        // replace all paths
        $configuration = $this->replaceAbsolutePathsToRelativeResourcesPath($configuration);

        // there are some places where we define an array, but it needs to be a list in order to work
        if (isset($configuration['extraPlugins']) && is_array($configuration['extraPlugins'])) {
            $configuration['extraPlugins'] = implode(',', array_filter($configuration['extraPlugins']));
        }
        if (isset($configuration['removePlugins']) && is_array($configuration['removePlugins'])) {
            $configuration['removePlugins'] = implode(',', array_filter($configuration['removePlugins']));
        }
        if (isset($configuration['removeButtons']) && is_array($configuration['removeButtons'])) {
            $configuration['removeButtons'] = implode(',', array_filter($configuration['removeButtons']));
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
        $currentLanguageUid = $this->formData['databaseRow']['sys_language_uid'] ?? 0;
        if (is_array($currentLanguageUid)) {
            $currentLanguageUid = $currentLanguageUid[0];
        }
        $contentLanguageUid = (int)max($currentLanguageUid, 0);
        if ($contentLanguageUid) {
            $contentLanguage = $this->formData['systemLanguageRows'][$currentLanguageUid]['iso'];
        } else {
            $contentLanguage = $this->rteConfiguration['config']['defaultContentLanguage'] ?? 'en_US';
            $languageCodeParts = explode('_', $contentLanguage);
            if (isset($languageCodeParts[0]) && isset($languageCodeParts[1])) {
                $contentLanguage = strtolower($languageCodeParts[0]) . ($languageCodeParts[1]
                        ? '_' . strtoupper($languageCodeParts[1]) : '');
            }
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
