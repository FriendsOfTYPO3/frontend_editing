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
 * Module: TYPO3/CMS/FrontendEditing/Utils/Logger
 * Simple base logger to get extend ready
 */
define([
    '../Contrib/ulog/ulog',
], function createLogger (ulog) {
    'use strict';

    var ulogger = ulog('FEditing:Main');

    window.addEventListener('error', errorExceptionHandler);
    window.addEventListener('unhandledrejection', unhandledRejectionHandler);

    return function getLogger (name) {
        return ulog(name);
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
