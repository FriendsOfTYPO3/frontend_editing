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
 * Module: TYPO3/CMS/FrontendEditing/Utils/Translator
 * Simple language translator with mapping functionality
 */
define(['jquery', './Logger'], function createTranslatorFactory ($, Logger) {
    'use strict';

    var log = Logger('FEditing:Utils:TranslatorFactory');
    log.trace('--> createTranslatorFactory');

    return function TranslatorFactory (labels, mappings) {
        log.debug('create Translator Factory');

        var defaulSettigns = {
            translationLabels: {
                'error.type.key_invalid': 'Invalid translation key: \'{0}\'',
                'translator.error.namespace_not_found':
                    'Invalid namespace key: \'{0}\'',
            },
            namespaceMappings: {
                translator: {
                    translationKeyInvalid:
                        'error.type.key_invalid',
                    namespaceMappingNotFound:
                        'translator.error.namespace_not_found',
                },
            },
        };

        var translationLabels = {};
        var namespaceMappings = {};

        setTranslationLabels(labels);
        setNamespaceMappings(mappings);

        function setTranslationLabels (newTranslationLabels) {
            log.trace('setTranslationLabels', newTranslationLabels);

            translationLabels = $.extend(
                {},
                defaulSettigns.translationLabels,
                newTranslationLabels
            );

            log.debug('set new TranslationLabels', translationLabels);
        }

        function setNamespaceMappings (newNamespaceMappings) {
            log.trace('setNamespaceMappings', newNamespaceMappings);

            namespaceMappings = $.extend(
                true,
                {},
                defaulSettigns.namespaceMappings,
                newNamespaceMappings
            );

            log.debug('set new NamespaceMappings:', namespaceMappings);
        }

        function getNamespaceMapping (namespace) {
            if (!namespace) {
                var keys = Object.keys(translationLabels);
                var retVal = {};
                for (var i = 0; i < keys.length; i++) {
                    retVal[keys[i]] = keys[i];
                }
                return retVal;
            }
            if (namespaceMappings[namespace]) {
                return namespaceMappings[namespace];
            }
            throw new TypeError(_translate(
                namespaceMappings.translator.namespaceMappingNotFound,
                namespace
            ));
        }

        function _translate (key) {
            var s = translationLabels[key];
            if (arguments.length > 1) {
                for (var i = 0; i < arguments.length - 1; i++) {
                    var reg = new RegExp('\\{' + i + '\\}', 'gm');
                    s = s.replace(reg, arguments[i + 1]);
                }
            }

            log.debug('translation:', key, s);

            return s;
        }

        return {
            setTranslationLabels: setTranslationLabels,
            setNamespaceMappings: setNamespaceMappings,
            createTranslator: function (namespace) {
                log.info('create Translator:', namespace);

                //used to get keys
                return {
                    /**
                     * Gets the namespace mapping object to override
                     * @returns {object} namespace to translation key mapping
                     */
                    getKeys: function () {
                        return $.extend({}, getNamespaceMapping(namespace));
                    },
                    /**
                     * Get translation string by passed key argument and replace
                     * curly bracket marker with passed rest arguments.
                     * If first variable (after key argument) is an array of
                     * strings this array will be used instead of functional
                     * arguments.
                     * If no translation for the key is available it will throw
                     * an TypeError exception.
                     * @param {string} key Server side translation key
                     * @returns {string} translation
                     */
                    translate: function (key) {
                        log.debug('translate:', key);

                        if (Array.isArray(arguments[1])) {
                            var parameters = [key];
                            parameters.push.apply(parameters, arguments[1]);
                            return this.translate.apply(this, parameters);
                        }
                        if (translationLabels[key]) {
                            return _translate.apply(this, arguments);
                        }
                        throw new TypeError(
                            _translate(
                                namespaceMappings.translator
                                    .translationKeyInvalid
                            )
                        );
                    }
                };
            },
        };
    };
});
