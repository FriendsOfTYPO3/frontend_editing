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
 * Module: TYPO3/CMS/FrontendEditing/Utils/LoggerWindowError
 * Add window and promises error handler to log them since nobody is in charge.
 */
define(['../Contrib/ulog/ulog'], function addLoggerWindowErrorHandler (ulog) {
    'use strict';

    var ulogger = ulog('FEditing:Main');
    var eventListeners = [{
        target: window,
        name: 'error',
        listener: errorExceptionHandler,
    }, {
        target: window,
        name: 'unhandledrejection',
        listener: unhandledRejectionHandler,
    }];

    return {
        register: function () {
            eventListeners.forEach(function addEventListener (record) {
                record.target.addEventListener(record.name, record.listener);
            });
        },
        unregister: function () {
            eventListeners.forEach(function addEventListener (record) {
                record.target.removeEventListener(record.name, record.listener);
            });
        }
    };

    /**
     * Log global unhandled errors and prevent default output.
     * @return {boolean}
     */
    function errorExceptionHandler () {
        try {
            ulogger.error(arguments);
            return false;
        } catch (exception) { /*let fallback to default error behaviour*/ }

        return true;
    }

    /**
     * Log unhandled rejection from promises and prevent default output.
     * @param event
     * @return {boolean}
     */
    function unhandledRejectionHandler (event) {
        try {
            ulogger.error('Unhandled rejection occured.', event.reason);

            event.preventDefault();
            return false;
        } catch (exception) { /*let fallback to default error behaviour*/ }

        return true;
    }
});
