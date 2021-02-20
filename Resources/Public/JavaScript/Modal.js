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
 * Modal: TYPO3 Modal wrapper with predefined configurations per use case
 */
define([
    'jquery',
    'TYPO3/CMS/Backend/Modal',
    'TYPO3/CMS/Backend/Severity'
], function ModalFactory (
    $,
    Modal,
    Severity
) {
    'use strict';
    var translateKeys = {
        titleNavigate: 'title.navigate',
        discardLabel: 'button.discard_navigate',
        saveLabel: 'button.save',
        cancelLabel: 'button.cancel',
        okayLabel: 'button.okay',
    };
    var translateValues = {
        'title.navigate': 'Navigate',
        'button.discard_navigate': 'Discard and Navigate',
        'button.save': 'Save All',
        'button.cancel': 'Cancel',
        'button.okay': 'OK',
    };

    function translate (key) {
        if (translateValues[key]) {
            return translateValues[key];
        }
        throw new TypeError("'" + key + "' does not exist in translate table");
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
        testConstraints(builder.constraints.required, title, 'title');
        testConstraints(builder.constraints.required, message, 'message');

        return {
            handleEscape: true,
            configuration: {
                title: title,
                content: message,
                severity: Severity.info,
                buttons: [],
                additionalCssClasses: ['t3-frontend-editing__modal'],
            },

            translateTitle: function () {
                this.configuration.title = translate(this.configuration.title);
                return this;
            },
            setSeverity: function (severity) {
                testConstraints(builder.constraints.int, severity, 'severity');
                this.configuration.severity = severity;
                return this;
            },
            onReady: function (listener) {
                testConstraints(builder.constraints.func, listener, 'listener');
                // bad callback naming, callback if modal is ready
                /*eslint-disable-next-line id-denylist*/
                this.configuration.callback = listener;
                this.readyListener = listener;
                return this;
            },
            onButtonClick: function (listener) {
                testConstraints(builder.constraints.func, listener, 'listener');
                this.buttonClickListener = listener;
                return this;
            },
            onEscape: function (listener) {
                testConstraints(builder.constraints.func, listener, 'listener');
                this.escapeListener = listener;
                return this;
            },
            preventEscape: function () {
                this.handleEscape = false;
                return this;
            },
            dismissOnButtonClick: function () {
                return this.onButtonClick(dismissModal);
            },
            prependButton: function (button) {
                return this.insertButton(0, button);
            },
            appendButton: function (button) {
                this.configuration.buttons.push(button);
                return this;
            },
            insertButton: function (index, button) {
                this.configuration.buttons.splice(index, 0, button);
                return this;
            },

            show: function () {
                var buttonClickListener = this.buttonClickListener;
                var escapeListener = this.escapeListener;
                if (escapeListener || buttonClickListener) {
                    var readyListener = this.readyListener;
                    var handleEscape = this.handleEscape;
                    this.onReady(function prepareButtonClick (currentModal) {
                        function modalDismiss () {
                            if (!dismiss) {
                                escapeListener();
                            }
                            currentModal.trigger('modal-dismiss');
                        }

                        var dismiss = false;

                        if (handleEscape) {
                            if (escapeListener) {
                                currentModal.on(
                                    'modal-dismiss',
                                    function modalDismissListener () {
                                        dismiss = true;
                                    }
                                );

                                currentModal.on(
                                    'hidden.bs.modal',
                                    modalDismiss
                                );
                                currentModal.find(Modal.identifiers.close)
                                    .on('click', modalDismiss);
                            }
                        } else {
                            currentModal.find(Modal.identifiers.close)
                                .hide();
                        }
                        if (buttonClickListener) {
                            currentModal.on(
                                'button.clicked',
                                buttonClickListener
                            );
                        }
                        if (readyListener) {
                            readyListener(currentModal);
                        }
                        currentModal.modal({
                            keyboard: handleEscape,
                            backdrop: 'static',
                        });

                        if (escapeListener) {
                            currentModal.on(
                                'shown.bs.modal',
                                function handleBackdrop () {
                                    currentModal
                                        .on('click', dismissModal);
                                    currentModal
                                        .find('.modal-content')
                                        .on(
                                            'click',
                                            function preventClose(event) {
                                                event.stopPropagation();
                                            }
                                        );
                                }
                            );
                        }
                    });
                }

                return Modal.advanced(this.configuration);
            }
        };
    }

    function createButtonBuilder (name) {
        return {
            text: name,
            name: name,
            active: false,

            setLabel: function (label, constraints) {
                testConstraints(constraints, label, 'label');
                this.text = label;
                return this;
            },
            translateLabel: function () {
                this.text = translate(this.text);
                testConstraints(
                    builder.constraints.required,
                    this.text,
                    'translate(this.text)'
                );
                return this;
            },
            setActive: function () {
                this.active = true;
                return this;
            },
            onClick: function (clickCallback, constraints) {
                testConstraints(constraints, clickCallback, 'clickCallback');
                this.trigger = clickCallback;
                return this;
            },
            setSeverity: function (severity) {
                testConstraints(builder.constraints.int, severity, 'severity');
                this.severity = severity;
                return this;
            },
            setVariant: function (variant) {
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
        if (!constraints) {
            return;
        }
        // TODO: make errors translation ready
        if ((constraints & builder.constraints.required) !== 0) {
            if (!variable) {
                throw new TypeError("'" + name + "' is undefined");
            }
        }
        if ((constraints & builder.constraints.func) !== 0) {
            if (variable && typeof variable !== 'function') {
                throw new TypeError("'" + name + "' is not a function");
            }
        }
        if ((constraints & builder.constraints.int) !== 0) {
            if (variable && !Number.isInteger(variable)) {
                throw new TypeError("'" + name + "' is not a integer");
            }
        }
    }

    // Simple modal close after button clicked
    function dismissModal () {
        $(this)
            .trigger('modal-dismiss');
    }

    function createBaseModal (title, message) {
        return builder.modal(title, message)
            .setSeverity(Severity.warning)
            .dismissOnButtonClick();
    }

    function createShowModal (title, message, callbacks) {
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
        return createShowModal(title, message, callbacks)
            .onEscape(callbacks.no)
            .prependButton(createCancelButton(callbacks.no));
    }

    function createCancelButton (clickListener) {
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
