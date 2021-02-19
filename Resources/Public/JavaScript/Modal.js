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

    function getButton (text, confirmCallback, active, btnVariant) {
        var btnClass = 'btn-';
        if(Number.isInteger(btnVariant)){
            btnClass += Severity.getCssClass(btnVariant);
        } else {
            // add default variant as base
            btnClass += 'default';
            if (btnVariant) {
                // add custom variant
                btnClass += ' btn-' + btnVariant;
            }
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

    return {
        /**
         * Simple modal notification popup to annoy some users ... have to be an
         * important message - maybe write it in uppercase. XD
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

            var title = 'Navigate';
            var discardLabel = 'Discard and Navigate';
            var saveLabel = 'Save All';
            var cancelLabel = 'Cancel';
            var buttons = [
                getButton(cancelLabel, callbacks.no, true, 'left'),
                getButton(saveLabel, saveCallback, false),
                getButton(discardLabel, callbacks.yes, false, Severity.error)
            ];

            return Modal.confirm(
                title,
                message,
                Severity.warning,
                buttons,

            );
        },
    };
});
