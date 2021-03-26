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
 * Module: TYPO3/CMS/FrontendEditing/Utils/LoggerPersist
 * Logger extension to make logs persistable.
 * Side effect:
 *  Create highorder ulog channel.
 *  Creates 2 ulog outputs to persist
 *   - persist: with indexedDb in use
 *   - server: calling the sendCallback function
 * Control:
 *  sendCallback = <server output callback function>
 *  config = {
 *      highorder_log: <log level>
 *      log_highorder: <log output definition>
 *      log: <log level>
 *      log_output: <log output definition>
 *      log_drain: <log output definition>
 *  }
 */
define([
    '../Contrib/ulog/ulog',
    './IndexedDB',
], function addLoggerPersist (ulog, db) {
    'use strict';

    var _sendCallback = null;
    var configKeys = [
        'highorder_log',
        'log_highorder',
        'log',
        'log_output',
        'log_drain'
    ];

    addPersistOutputs();
    addHighorderChannel();

    return {
        set config (cfg) {
            for (var key in configKeys ) {
                if (cfg[key] && typeof cfg[key] === 'string') {
                    ulog.set(key, cfg[key]);
                }
            }
        },
        get config () {
            return ulog.config;
        },

        set sendCallback (sendCallback) {
            if (typeof sendCallback === 'function') {
                _sendCallback = sendCallback;
            }
        },
        get sendCallback () {
            return _sendCallback;
        },
    };

    /**
     * Adds the highorder ulog channel and override the output ulog channel
     * for levels above configure highorder_log. The new highorder ulog channel
     * can be configured as usual in ulog with log_highorder config.
     * Eg. log = debug and highorder_log = warn
     *  -> warn and errors has highorder channel
     *  -> info and debug has output channel
     *  -> trace has drain channel
     */
    function addHighorderChannel () {
        ulog.use({
            channels: {
                highorder: {
                    out: [
                        console,
                    ],
                },
            },
            settings: {
                highorderLog: {
                    config: 'highorder_log',
                    prop: {
                        default: 'none',
                    },
                },
                highorder: {
                    config: 'log_highorder',
                    prop: {
                        default: 'console',
                    },
                },
            },
            ext: function (logger) {
                function highorderEnabledFor (level) {
                    var highorderLog = logger.highorderLog.toUpperCase();
                    return logger[highorderLog] >= logger[level.toUpperCase()];
                }
                logger.highorderEnabledFor = highorderEnabledFor;
            },
            after: function (logger) {
                // eslint-disable-next-line guard-for-in
                for (var level in this.levels) {
                    if (logger.enabledFor(level)) {
                        var channel = 'output';
                        if (logger.highorderEnabledFor(level)) {
                            channel = 'highorder';
                        }
                        logger[level] = logger.channels[channel].fns[level];
                    }
                }
            },
        });
    }

    /**
     * Adds ulog outputs used to persist log records.
     */
    function addPersistOutputs () {
        ulog.use({
            outputs: {
                server: serverOutput,
                persist: persistOutput
            },
        });
    }

    /**
     * Returns a function that add log entry in an indexedDB.
     */
    function persistOutput () {
        return function persistLog (rec) {
            var logRecord = createLogRecord(rec);
            try {
                db.logs.add(logRecord);
            } catch (exception) {
                error(
                    'Unable to persist log record',
                    exception,
                    logRecord
                );
            }
        };
    }

    /**
     * Returns a function that call the sendCallback function if defined.
     */
    function serverOutput () {
        return function sendLogToServer (rec) {
            var logRecord = createLogRecord(rec);
            if (_sendCallback) {
                try {
                    _sendCallback(logRecord);
                } catch (exception) {
                    error(
                        'Error occured during callback function',
                        exception,
                        logRecord
                    );
                }
            } else {
                warn(
                    'sendLogToServer failed cause _sendCallback is not defined',
                    logRecord
                );
            }
        };
    }

    /**
     * Create a log record used to persisting
     * @param rec
     * @return {{
     *     timestamp: number,
     *     url: string,
     *     name: string,
     *     level: number,
     *     channel: string,
     *     message: [],
     *     stack: (*|string)
     * }}
     */
    function createLogRecord (rec) {
        var error = new Error();
        var url = window && window.location
            ? window.location.href : 'no window.location';
        return {
            timestamp: Date.now(),
            url: url,
            name: rec.name,
            level: rec.level,
            channel: rec.channel,
            message: rec.message,
            stack: error.stack
        };
    }

    /**
     * Log error in console directly to prevent an infinite loop.
     */
    function error () {
        // eslint-disable-next-line no-console
        console.error(arguments);
    }

    /**
     * Log warn in console directly to prevent an infinite loop.
     */
    function warn () {
        // eslint-disable-next-line no-console
        console.warn(arguments);
    }
});
