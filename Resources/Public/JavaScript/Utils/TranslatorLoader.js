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

/**
 * Module: TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader
 * TranslatorFactory bootstrap
 */
define(
    ['jquery', 'TYPO3/CMS/FrontendEditing/Utils/Translator'],
    function TranslatorLoader ($, createTranslatorFactory) {
        'use strict';

        /*eslint-disable max-len*/
        // because of resource declaration
        var fallbackTranslation = {
            'notifications.unsaved-changes': 'You have some unsaved changes. They will disappear if you navigate away!',
            'notifications.delete-content-element': 'Are you sure you want to delete the content element?',
            'title.navigate': 'Navigate',
            'button.discard_navigate': 'Discard and Navigate',
            'button.save': 'Save All',
            'button.cancel': 'Cancel',
            'button.okay': 'OK',
            'error.type.undefined': '\'{0}\' is undefined"',
            'error.type.not_function': '\'{0}\' is not a function',
            'error.type.not_integer': '\'{0}\' is not a integer',
            'error.type.key_invalid': 'Invalid translation key: \'{0}\'',
            'translator.error.namespace_not_found': 'Invalid namespace key: \'{0}\'',
        };
        /*eslint-enable max-len*/

        var defaultNamespaceMapping = {
            translator: {
                translationKeyInvalid: 'error.type.key_invalid',
                namespaceMappingNotFound:
                    'translator.error.namespace_not_found',
            },
            modal: {
                titleNavigate: 'title.navigate',
                discardLabel: 'button.discard_navigate',
                saveLabel: 'button.save',
                cancelLabel: 'button.cancel',
                okayLabel: 'button.okay',
                variableNotDefined: 'error.type.undefined',
                variableNotFunction: 'error.type.not_function',
                variableNotInteger: 'error.type.not_integer',
            },
            editor: {
                actionUnsavedChanges: 'notifications.unsaved-changes',
                actionEditDelete: 'notifications.delete-content-element',
                initLoadFailed: 'notifications.delete-content-element',
            },
        };

        var _configuration;
        var translationLabels = $.extend({}, fallbackTranslation);
        var namespaceMapping = $.extend({}, defaultNamespaceMapping);

        var translatorFactory = createTranslatorFactory(
            translationLabels,
            namespaceMapping
        );

        var translators = {};

        return {
            /**
             * Configure the translator only once.
             * Sets language labels and namespace mapping.
             * @param {object} configuration object
             */
            init: function (configuration) {
                if (!_configuration) {
                    _configuration = configuration;
                    if (configuration.translationLabels) {
                        translationLabels = $.extend(
                            {},
                            translationLabels,
                            configuration.translationLabels
                        );
                        translatorFactory.setTranslationLabels(
                            translationLabels
                        );
                    }
                    if (configuration.namespaceMapping) {
                        namespaceMapping = $.extend(
                            {},
                            namespaceMapping,
                            configuration.namespaceMapping
                        );
                        translatorFactory.setNamespaceMapping(namespaceMapping);
                    }
                }
            },
            getTranslator: function (namespace) {
                var namespaceKey = namespace ? namespace.toLowerCase() : 'GLOBAL';

                if (!translators[namespaceKey]) {
                    translators[namespaceKey] =
                        translatorFactory.createTranslator(namespace);
                }

                return translators[namespaceKey];
            },
        };
    }
);
