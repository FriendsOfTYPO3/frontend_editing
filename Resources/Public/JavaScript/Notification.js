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
 * Notification: Notification (toastr) wrapper
 */
define([
    'TYPO3/CMS/FrontendEditing/Contrib/toastr'
], function NotificationFactory (toastr) {
    'use strict';

    var toastrOptions = {
        'positionClass': 'toast-top-left',
        'preventDuplicates': true
    };

    return {
        success: function (message, title) {
            toastr.success(message, title, toastrOptions);
        },

        error: function (message, title) {
            toastr.error(message, title, toastrOptions);
        },

        warning: function (message, title) {
            toastr.warning(message, title, toastrOptions);
        },
    };
});
