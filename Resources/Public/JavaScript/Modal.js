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

    function translate (key) {
        switch (key) {
        case 'title.navigate':
            return 'Navigate';
        case 'button.discard_navigate':
            return 'Discard and Navigate';
        case 'button.save':
            return 'Save All';
        case 'button.cancel':
            return 'Cancel';
        case 'button.okay':
            return 'OK';
        default:
        }
        throw new TypeError('key was not found in translate table');
    }

    function createButtonBuilder (name) {
        return {
            text: name,
            name: name,
            active: false,

            setLabel: function (label) {
                this.text = label;
                return this;
            },
            setActive: function () {
                this.active = true;
                return this;
            },
            onClick: function (clickCallback) {
                this.trigger = clickCallback;
                return this;
            },
            setSeverity: function (severity) {
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

    // used if custom with non-default btn variant is used
    var argIndex = 4;

    function getButton (text, confirmCallback, active, btnVariant) {
        var btnClass = 'btn-';
        if (Number.isInteger(btnVariant)) {
            btnClass += Severity.getCssClass(btnVariant);
            btnVariant = arguments.length > argIndex
                ? arguments[argIndex] : null;
        } else {
            // add default variant as base
            btnClass += 'default';
        }
        if (btnVariant) {
            // add custom variant
            btnClass += ' btn-' + btnVariant;
        }
        return {
            text: text,
            trigger: function () {
                $(this)
                    .trigger('modal-dismiss');
                if (confirmCallback) {
                    confirmCallback();
                }
            },
            active: active,
            btnClass: btnClass
        };
    }

    // Simple modal close after button clicked
    function attachButtonClickedListener (currentModal) {
        currentModal.on('button.clicked', function buttonClicked () {
            $(this)
                .trigger('modal-dismiss');
        });
    }

    return {
        /**
         * Simple modal notification popup to annoy some users ... has to be an
         * important message - maybe written in uppercase. XD
         * @param message
         * @param callbacks
         */
        warning: function (message, callbacks) {
            callbacks = callbacks || {};

            if (!message) {
                throw new TypeError("'message' is undefined");
            }

            var title = message;
            var okLabel = 'OK';
            var buttons = [
                getButton(okLabel, callbacks.yes, true)
            ];

            return Modal.show(
                title,
                message,
                Severity.warning,
                buttons
            );
        },
        /**
         * Simple confirm to make non critical decisions
         * @param message
         * @param callbacks
         */
        confirm: function (message, callbacks) {
            callbacks = callbacks || {};

            if (!message) {
                throw new TypeError("'message' is undefined");
            }

            var title = message;
            var cancelLabel = 'Cancel';
            var okLabel = 'OK';
            var buttons = [
                getButton(cancelLabel, callbacks.no, true, 'left'),
                getButton(okLabel, callbacks.yes)
            ];

            return Modal.confirm(
                title,
                message,
                Severity.warning,
                buttons
            );
        },
        /**
         * Confirm to leave page and lose data
         * @param message
         * @param saveCallback
         * @param callbacks
         */
        confirmNavigate: function (message, saveCallback, callbacks) {
            callbacks = callbacks || {};

            if (!message) {
                throw new TypeError("'message' is undefined");
            }
            if (typeof saveCallback !== 'function') {
                throw new TypeError("'saveCallback' is not a function");
            }

            var buttons = [
                createButtonBuilder(translateKeys.cancelLabel)
                    .setLabel(translate(translateKeys.cancelLabel))
                    .onClick(callbacks.no)
                    .setActive()
                    .setVariant('left'),
                createButtonBuilder(translateKeys.saveLabel)
                    .setLabel(translate(translateKeys.saveLabel))
                    .onClick(saveCallback),
                createButtonBuilder(translateKeys.discardLabel)
                    .setLabel(translate(translateKeys.discardLabel))
                    .onClick(callbacks.yes)
                    .setSeverity(Severity.error),
            ];

            return Modal.advanced({
                title: translate(translateKeys.titleNavigate),
                content: message,
                severity: Severity.warning,
                buttons: buttons,
                // bad callback naming, callback if modal is ready
                /*eslint-disable-next-line id-denylist*/
                callback: attachButtonClickedListener
            });
        },
    };
});
