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
 * Module: TYPO3/CMS/FrontendEditing/Modal
 * Modal: TYPO3 Modal wrapper with predefined configurations per use case
 */
define([
    'jquery',
    'TYPO3/CMS/Backend/Modal',
    'TYPO3/CMS/Backend/Severity',
    './Utils/TranslatorLoader',
    './Utils/Logger'
], function ModalFactory (
    $,
    T3Modal,
    Severity,
    TranslatorLoader,
    Logger
) {
    'use strict';

    var log = Logger('FEditing:Component:Widget:Modal');
    log.trace('--> ModalFactory');

    // simple ponyfill
    var identifiers = T3Modal.identifiers || {
        modal: '.t3js-modal',
        content: '.t3js-modal-content',
        title: '.t3js-modal-title',
        close: '.t3js-modal-close',
        body: '.t3js-modal-body',
        footer: '.t3js-modal-footer',
        iframe: '.t3js-modal-iframe',
        iconPlaceholder: '.t3js-modal-icon-placeholder'
    };

    var translateKeys = {
        titleNavigate: 'title.navigate',
        discardLabel: 'button.discard_navigate',
        saveLabel: 'button.save',
        cancelLabel: 'button.cancel',
        okayLabel: 'button.okay',
        variableNotDefined: 'error.type.undefined',
        variableNotFunction: 'error.type.not_function',
        variableNotInteger: 'error.type.not_integer',
    };

    var translator = TranslatorLoader
        .useTranslator('modal', function reload (t) {
            translateKeys = $.extend(translateKeys, t.getKeys());
        }).translator;

    function translate () {
        return translator.translate.apply(translator, arguments);
    }

    var builder = {
        constraints: {
            'required': 1,
            'func': 2,
            'int': 4,
        },

        modal: createModalBuilder,
        button: createButtonBuilder,
    };

    function createModalBuilder (title, message) {
        log.debug('builder.modal', title, message);

        testConstraints(builder.constraints.required, title, 'title');
        testConstraints(builder.constraints.required, message, 'message');

        var handleEscape = true;
        var buttonClickListener, escapeListener, readyListener;

        function attachEscapeListener (currentModal) {
            log.trace('modal attach escape listener');

            var hasModalDismissed = false;
            function triggerEscapeEvent () {
                log.debug('escape modal', escapeListener);

                if (!hasModalDismissed && escapeListener) {
                    hasModalDismissed = true;
                    escapeListener();
                } else {
                    log.debug('Unable to escape modal.');
                }
            }

            function preventTriggerEscape () {
                log.trace('modal prevent trigger escape');

                hasModalDismissed = true;
            }

            function handleBackdrop () {
                log.trace('modal handle backdrop');

                currentModal.on('click', function backdropDismiss (event) {
                    log.debug('modal backdrop click');

                    event.stopPropagation();
                    triggerEscapeEvent();
                    currentModal.trigger('modal-dismiss');
                });
                currentModal
                    .find(identifiers.content)
                    .on('click', function preventClose (event) {
                        log.trace(
                            'modal content clicked, prevent backdrop click'
                        );

                        event.stopPropagation();
                    });
            }

            // handle escape by key
            currentModal.on('modal-dismiss', preventTriggerEscape);
            currentModal.on('hidden.bs.modal', triggerEscapeEvent);

            // handle escape by close button
            currentModal
                .find(identifiers.close)
                .on('click', triggerEscapeEvent);

            // handle escape by backdrop
            handleBackdrop();
        }

        return {
            title: title,
            content: message,
            severity: Severity.info,
            buttons: [],
            additionalCssClasses: ['t3-frontend-editing__modal'],

            translateTitle: function () {
                log.trace('modal translate title');

                this.title = translate(this.title);
                return this;
            },
            setSeverity: function (severity) {
                log.trace('modal set severity', severity);

                testConstraints(builder.constraints.int, severity, 'severity');
                this.severity = severity;
                return this;
            },
            onReady: function (listener) {
                log.trace('modal on ready', listener);

                testConstraints(builder.constraints.func, listener, 'listener');
                // bad callback naming, callback if modal is ready
                /*eslint-disable-next-line id-denylist*/
                this.callback = listener;
                readyListener = listener;
                return this;
            },
            onButtonClick: function (listener) {
                log.trace('modal on button clicked', listener);

                testConstraints(builder.constraints.func, listener, 'listener');
                buttonClickListener = listener;
                return this;
            },
            onEscape: function (listener) {
                log.trace('modal on escape', listener);

                testConstraints(builder.constraints.func, listener, 'listener');
                escapeListener = listener;
                return this;
            },
            preventEscape: function () {
                log.trace('modal stop handle escape');

                handleEscape = false;
                return this;
            },
            dismissOnButtonClick: function () {
                log.trace('modal dismiss on button clicked');

                return this.onButtonClick(dismissModal);
            },
            prependButton: function (button) {
                log.trace('modal prepend button', button);

                return this.insertButton(0, button);
            },
            appendButton: function (button) {
                log.trace('modal append button', button);

                this.buttons.push(button);
                return this;
            },
            insertButton: function (index, button) {
                log.trace('modal insert button', index, button);

                this.buttons.splice(index, 0, button);
                return this;
            },

            show: function () {
                log.trace('modal show', this.title);

                //copy ready listener cause it gets reset in next func
                var ready = readyListener;
                this.onReady(function prepareListeners (currentModal) {
                    log.trace('modal ready', currentModal);

                    if (!handleEscape) {
                        currentModal
                            .find(identifiers.close)
                            .hide();
                    }

                    if (buttonClickListener) {
                        currentModal.on('button.clicked', buttonClickListener);
                    }

                    if (ready) {
                        ready(currentModal);
                    }

                    log.debug('create modal');
                    currentModal.modal({
                        keyboard: handleEscape,
                        backdrop: 'static',
                    });
                    if (handleEscape) {
                        attachEscapeListener(currentModal);
                    }
                });

                log.debug('init modal', this);
                return T3Modal.advanced(this);
            }
        };
    }

    function createButtonBuilder (name) {
        log.debug('modal button', name);

        return {
            text: name,
            name: name,
            active: false,

            setLabel: function (label, constraints) {
                log.trace('modal button set label', label, constraints);

                testConstraints(constraints, label, 'label');
                this.text = label;
                return this;
            },
            translateLabel: function () {
                log.trace('modal button translate label');

                this.text = translate(this.text);
                testConstraints(
                    builder.constraints.required,
                    this.text,
                    'translate(this.text)'
                );
                return this;
            },
            setActive: function () {
                log.trace('modal button set active');

                this.active = true;
                return this;
            },
            onClick: function (clickCallback, constraints) {
                log.trace('modal button set click callback');

                testConstraints(constraints, clickCallback, 'clickCallback');
                this.trigger = clickCallback;
                return this;
            },
            setSeverity: function (severity) {
                log.trace('modal button set severity', severity);

                testConstraints(builder.constraints.int, severity, 'severity');
                this.severity = severity;
                return this;
            },
            setVariant: function (variant) {
                log.trace('modal button set variant', variant);

                this.variant = variant;
                return this;
            },

            get btnClass () {
                var btnClass = 'btn-';
                if (this.severity) {
                    btnClass += Severity.getCssClass(this.severity);
                } else {
                    btnClass += 'default';
                }
                if (this.variant) {
                    // add custom variant
                    btnClass += ' btn-' + this.variant;
                }
                return btnClass;
            },
        };
    }

    function testConstraints (constraints, variable, name) {
        log.trace('test constraints', constraints, typeof variable, name);

        if (!constraints) {
            return;
        }
        if ((constraints & builder.constraints.required) !== 0) {
            if (!variable) {
                throw new TypeError(
                    translate(translateKeys.variableNotDefined, name));
            }
        }
        if ((constraints & builder.constraints.func) !== 0) {
            if (variable && typeof variable !== 'function') {
                throw new TypeError(
                    translate(translateKeys.variableNotFunction, name));
            }
        }
        if ((constraints & builder.constraints.int) !== 0) {
            if (variable && !Number.isInteger(variable)) {
                //maybe to picky since it is only used as decision for design
                //razz (flood) sys-admin with warning would be better
                //TODO: log notice "errors" on server
                throw new TypeError(
                    translate(translateKeys.variableNotInteger, name));
            }
        }
    }

    // Simple modal close after button clicked
    function dismissModal () {
        log.trace('modal dismiss');

        $(this)
            .trigger('modal-dismiss');
    }

    function createBaseModal (title, message) {
        log.trace('create base model');

        return builder.modal(title, message)
            .setSeverity(Severity.warning)
            .dismissOnButtonClick();
    }

    function createShowModal (title, message, callbacks) {
        log.trace('create show model');

        return createBaseModal(title, message)
            .onEscape(callbacks.yes)
            .appendButton(
                builder.button(translateKeys.okayLabel)
                    .translateLabel()
                    .onClick(callbacks.yes)
                    .setActive()
            );
    }

    function createConfirmModal (title, message, callbacks) {
        log.trace('create confirm model');

        return createShowModal(title, message, callbacks)
            .onEscape(callbacks.no)
            .prependButton(createCancelButton(callbacks.no));
    }

    function createCancelButton (clickListener) {
        log.trace('create cancel modal button');

        return builder.button(translateKeys.cancelLabel)
            .translateLabel()
            .setActive()
            .setVariant('left')
            .onClick(clickListener);
    }

    return {
        /**
         * Simple modal builder which make modal much easier and more fun.
         */
        builder: builder,
        /**
         * Simple modal notification popup to annoy some users ... has to be an
         * important message - maybe written in uppercase. XD
         * @param message
         * @param callbacks
         */
        warning: function (message, callbacks) {
            log.info('warning with message', message);

            callbacks = callbacks || {};

            return createShowModal(message, message, callbacks)
                .show();
        },
        /**
         * Simple confirm to make non critical decisions
         * @param message
         * @param callbacks
         */
        confirm: function (message, callbacks) {
            log.info('confirm with message', message);

            callbacks = callbacks || {};

            return createConfirmModal(message, message, callbacks)
                .show();
        },
        /**
         * Confirm to leave page and lose data
         * @param message
         * @param saveCallback
         * @param callbacks
         */
        confirmNavigate: function (message, saveCallback, callbacks) {
            log.info('confirm navigate with message', message);

            callbacks = callbacks || {};

            return createBaseModal(translateKeys.titleNavigate, message)
                .translateTitle()
                .preventEscape()
                .appendButton(createCancelButton(callbacks.no))
                .appendButton(
                    builder.button(translateKeys.saveLabel)
                        .translateLabel()
                        .onClick(saveCallback, builder.constraints.required)
                )
                .appendButton(
                    builder.button(translateKeys.discardLabel)
                        .translateLabel()
                        .onClick(callbacks.yes)
                        .setSeverity(Severity.error)
                )
                .show();
        },
    };
});
