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
 * Module: TYPO3/CMS/FrontendEditing/Notification
 * Notification: Notification (toastr) wrapper
 */
define([
    'TYPO3/CMS/FrontendEditing/Contrib/toastr',
    './Utils/Logger'
], function NotificationFactory (toastr, Logger) {
    'use strict';

    var log = Logger('FEditing:Component:Widget:Notification');
    log.trace('--> NotificationFactory');

    var toastrOptions = {
        'positionClass': 'toast-top-left',
        'preventDuplicates': true
    };

    return {
        success: function (message, title) {
            log.debug('success', title, message);

            toastr.success(message, title, toastrOptions);
        },

        error: function (message, title) {
            log.debug('error', title, message);

            toastr.error(message, title, toastrOptions);
        },

        warning: function (message, title) {
            log.debug('warning', title, message);

            toastr.warning(message, title, toastrOptions);
        },
    };
});
