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
    '../Contrib/pino',
    '../Contrib/ulog/ulog',
], function createLogger (pino, ulog) {
    'use strict';

    var pinoLogger = pino();
    var ulogger = ulog('FrontendEditing');

    return {
        pino: pinoLogger,
        ulog: ulogger,
        // current: pinoLogger,
        current: ulogger
    };
});
