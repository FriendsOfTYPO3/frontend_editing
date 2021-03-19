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
    ['jquery', './Translator', './Logger'],
    function TranslatorLoader ($, createTranslatorFactory, Logger) {
        'use strict';

        var log = Logger('FEditing:Utils:TranslatorLoader');
        log.trace('--> TranslatorLoader');

        /*eslint-disable max-len*/
        // because of resource declaration
        var fallbackTranslation = {
            'notifications.save-title': 'Content saved',
            'notifications.save-went-wrong': 'Something went wrong',
            'notifications.no-changes-title': 'No changes made',
            'notifications.no-changes-description': 'There are currently no changes made to the content on the page!',
            'notifications.remove-all-changes': 'Are you sure you want to remove all unsaved changes?',
            'notifications.unsaved-changes': 'You have some unsaved changes. They will disappear if you navigate away!',
            'notifications.delete-content-element': 'Are you sure you want to delete the content element?',
            'notifications.change_site_root': 'You are going to switch to another site. Are you sure ?',
            'notifications.save-pages-title': 'Page saved',
            'notifications.request.configuration.fail': 'Could not fetch editor configurations due to a request error. ({0}, "{1}")',
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
            frontendEditing: {
                confirmNavigateWithChange: 'notifications.unsaved-changes',
            },
            editor: {
                confirmOpenModalWithChange: 'notifications.unsaved-changes',
                confirmDeleteContentElement:
                    'notifications.delete-content-element',
                informRequestFailed: 'notifications.request.configuration.fail'
            },
            gui: {
                updatedContentTitle: 'notifications.save-title',
                updatedPageTitle: 'notifications.save-pages-title',
                updateRequestErrorTitle: 'notifications.save-went-wrong',
                saveWithoutChange: 'notifications.no-changes-description',
                saveWithoutChangeTitle: 'notifications.no-changes-title',
                confirmDiscardChanges: 'notifications.remove-all-changes',
                confirmChangeSiteRoot: 'notifications.change_site_root',
                confirmChangeSiteRootWithChange:
                    'notifications.unsaved-changes',
            }
        };

        var conf;
        var translationLabels = $.extend({}, fallbackTranslation);
        var namespaceMapping = $.extend({}, defaultNamespaceMapping);

        var translatorFactory = createTranslatorFactory(
            translationLabels,
            namespaceMapping
        );

        var namespacesContext = {};

        /**
         * Compares two objects, arrays or strings and compare the value
         * recursively.
         * @param {*} obj1 accepts object, array and string
         * @param {*} obj2 accepts object, array and string
         * @returns {boolean} true if they are truly equal, otherwise false
         */
        function compareObjects (obj1, obj2) {
            log.trace('compare objects', obj1, obj2);

            var type1 = typeof obj1;
            var type2 = typeof obj2;
            if (type1 !== type2) {
                log.trace('compare failed. types are not equal');

                return false;
            }

            if (Array.isArray(obj1)) {
                if (obj1.length !== obj2.length) {
                    log.trace('compare failed. arrays are not equal');

                    return false;
                }
                for (var x = 0; x < obj1.length; x++) {
                    var found = false;
                    for (var y = 0; y < obj2.length; y++) {
                        if (compareObjects(obj1[x], obj2[y])) {
                            found = true;
                            break;
                        }
                    }
                    if (!found) {
                        log.trace('Compare failed. Value not found', obj1[x]);

                        return false;
                    }
                }
            } else if (type1 === 'object') {
                var keys1 = Object.keys(obj1);
                var keys2 = Object.keys(obj2);

                if (keys1.length !== keys2.length) {
                    log.trace('compare failed. objects are not equal');

                    return false;
                }
                for (var i = 0; i < keys1.length; i++) {
                    if (!compareObjects(obj1[keys1[i]], obj2[keys1[i]])) {
                        log.trace(
                            'Compare failed. Value not found',
                            obj1[keys1[i]]
                        );

                        return false;
                    }
                }
            } else if (obj1 !== obj2) {
                log.trace('compare failed. objects are not equal');

                return false;
            }
            log.trace('compare succeed. objects are truly equal.');

            return true;
        }

        /**
         * reset configurations and forward them to the factory
         * @param {{translationLabels, namespaceMapping}} newConfiguration
         */
        function configure (newConfiguration) {
            log.debug('configure with new configuration', newConfiguration);

            conf = newConfiguration;

            translationLabels = $.extend(
                {},
                fallbackTranslation,
                (conf && conf.translationLabels) ? conf.translationLabels : {}
            );
            translatorFactory.setTranslationLabels(translationLabels);

            namespaceMapping = $.extend(
                true,
                {},
                defaultNamespaceMapping,
                (conf && conf.namespaceMapping) ? conf.namespaceMapping : {}
            );
            translatorFactory.setNamespaceMappings(namespaceMapping);
        }

        function triggerConfigureCallbacks () {
            log.debug('trigger configure callbacks');

            var namespaces = Object.keys(namespacesContext);
            for (var x = 0; x < namespaces.length; x++) {
                var namespace = namespaces[x];
                var context = namespacesContext[namespace];
                for (var y = 0; y < context.configureCallbacks.length; y++) {
                    triggerConfigureCallback(
                        context.configureCallbacks[y],
                        context.translator
                    );
                }
            }
        }

        function getNamespacesContext (namespace) {
            var namespaceKey = namespace ? namespace : 'GLOBAL';
            if (!namespacesContext[namespaceKey]) {
                log.debug('create Translator');
                namespacesContext[namespaceKey] = {
                    configureCallbacks: [],
                    translator:
                        translatorFactory.createTranslator(namespace),
                };

            }
            return namespacesContext[namespaceKey];
        }

        function registerConfigureCallback (context, configureCallback) {
            log.debug('register configure callback');

            var index = -1;

            if (configureCallback) {
                index = context.length;
                context.configureCallbacks.push(configureCallback);
                triggerConfigureCallback(
                    configureCallback,
                    context.translator,
                    true
                );
            }

            return function unregister () {
                if (index >= 0 && index < context.length) {
                    log.debug(
                        'unregister configure callback',
                        context.configureCallbacks[index]
                    );

                    context.configureCallbacks[index] = null;
                }
            };
        }

        function triggerConfigureCallback (
            configureCallback,
            translator,
            initial
        ) {
            if (typeof configureCallback === 'function') {
                configureCallback(translator, initial === true);
            }
        }

        return {
            mergeStrategy: {
                none: 1,
                merge: 2,
                mergeDeep: 6, // merge (2) + mergeDeep (4)
                override: 8,
            },
            /**
             * Configures languageLabels and namespaceMapping.
             * If already defined it needs to be forced by a merge strategy
             * to define the new configuration. Or else it skips configuration.
             * @param {{translationLabels, namespaceMapping}} newConfiguration
             * @param {string?} mergeStrategy can be 'override' or 'merge'
             * @returns {boolean} true if successfully configured,
             * otherwise false
             */
            configure: function (newConfiguration, mergeStrategy) {
                log.info('configure', newConfiguration, mergeStrategy);

                mergeStrategy = this.mergeStrategy[mergeStrategy];
                if (!mergeStrategy) {
                    mergeStrategy = this.mergeStrategy.none;
                }

                //skip if config already defined and no merge strategy was given
                if (!conf || mergeStrategy > this.mergeStrategy.none) {
                    if ((mergeStrategy & this.mergeStrategy.merge) !== 0) {
                        var mergeDeep =
                            mergeStrategy === this.mergeStrategy.mergeDeep;

                        log.debug('merge config deep', mergeDeep);

                        newConfiguration.translationLabels = $.extend(mergeDeep,
                            {},
                            conf.translationLabels,
                            newConfiguration.translationLabels
                        );
                        newConfiguration.namespaceMapping = $.extend(mergeDeep,
                            {},
                            conf.namespaceMapping,
                            newConfiguration.namespaceMapping
                        );
                    }
                    if (!compareObjects(conf, newConfiguration)) {
                        //configuration changed
                        configure(newConfiguration);
                        triggerConfigureCallbacks();
                    }
                    return true;
                }

                return false;
            },
            /**
             * Register an configure callback to get informed about
             * configuration changes like key mapping or translation label
             * change.
             * @param {string} namespace if no namespace is defined the global
             * translator get used
             * @param {function?} configureCallback
             * @returns {{translator, unregister}} translator context
             */
            useTranslator: function (namespace, configureCallback) {
                log.debug('use Translator', namespace);

                var context = getNamespacesContext(namespace);

                return {
                    translator: context.translator,
                    unregister:
                        registerConfigureCallback(context, configureCallback),
                };
            },
            /**
             * Used to handle single translation (maybe testing ;)
             * @param {string} namespace
             * @returns Translator with the given namespace
             */
            getTranslator: function (namespace) {
                log.debug('get Translator', namespace);

                return getNamespacesContext(namespace).translator;
            },
        };
    }
);
