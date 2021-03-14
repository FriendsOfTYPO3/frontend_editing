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
    './IndexedDB',
], function createLogger (ulog, db) {
    'use strict';

    var _sendCallback = null;

    function persistOutput () {
        return function persistLog (rec) {
            var error = new Error();
            try {
                db.logs.add({
                    timestamp: Date.now(),
                    name: rec.name,
                    level: rec.level,
                    channel: rec.channel,
                    message: rec.message,
                    stack: error.stack
                });
            } catch (exception) {
                // use console directly to prevent an infinite loop, since it
                // is unclear if debug could also lead to server function call
                // eslint-disable-next-line no-console
                console.error('Unable to persist log record', exception);
            }
        };
    }

    function serverOutput () {
        return function sendLogToServer (rec) {
            if (_sendCallback) {
                var error = new Error();
                _sendCallback({
                    name: rec.name,
                    level: rec.level,
                    channel: rec.channel,
                    message: rec.message,
                    stack: error.stack
                });
            } else {
                // use console directly to prevent an infinite loop, since it
                // is unclear if debug could also lead to server function call
                // eslint-disable-next-line no-console
                console.warn(
                    'sendLogToServer failed cause _sendCallback is not defined'
                );
            }
        };
    }

    ulog.use({
        outputs: {
            server: serverOutput,
            persist: persistOutput
        },
        channels: {
            persist: {
                out: [
                    console,
                    persistOutput,
                    serverOutput,
                ],
            },
        },
        settings: {
            persistLog: {
                config: 'persist_log',
                prop: {
                    default: 'none',
                },
            },
            persist: {
                config: 'log_persist',
                prop: {
                    default: 'console persist server',
                },
            },
        },
        ext: function (logger) {
            logger.persistEnabledFor = function (level) {
                var persistLogLevel = logger[logger.persistLog.toUpperCase()];
                return persistLogLevel >= logger[level.toUpperCase()];
            };
        },
        after: function (logger) {
            // eslint-disable-next-line guard-for-in
            for (var level in this.levels) {
                if (logger.enabledFor(level)) {
                    var channel = 'output';
                    if (logger.enabledFor(level)) {
                        channel = 'persist';
                    }
                    logger[level] = logger.channels[channel].fns[level];
                }
            }
        },
    });

    var ulogger = ulog('FEditing:Main');

    function errorExceptionHandler (msg, url, lineNo, columnNo, error) {
        try {
            ulogger.error(arguments);
            return false;
        } catch (exception) { /*let fallback to default error behaviour*/ }

        return true;
    }

    function unhandledRejectionHandler (event) {
        try {
            ulogger.error('Unhandled rejection occured.', event.reason);

            event.preventDefault();
            return false;
        } catch (exception) { /*let fallback to default error behaviour*/ }

        return true;
    }

    window.addEventListener('error', errorExceptionHandler);
    window.addEventListener('unhandledrejection', unhandledRejectionHandler);

    return {
        anylogger: ulogger,
        set sendCallback (sendCallback) {
            if (typeof sendCallback === 'function') {
                _sendCallback = sendCallback;
            }
        },
        get sendCallback () {
            return _sendCallback;
        },
        get modal () {
            return ulog('FEditing:Modal');
        }
    };
});
