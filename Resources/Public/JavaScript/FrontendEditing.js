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
 * Module: TYPO3/CMS/FrontendEditing/FrontendEditing
 * FrontendEditing: The foundation for the frontend editing interactions
 */
define([
    'jquery',
    './Storage',
    './Scroller',
    './Utils/TranslatorLoader',
    './Modal',
    './Utils/Logger'
], function createFrontendEditing (
    $,
    Storage,
    Scroller,
    TranslatorLoader,
    Modal,
    Logger
) {
    'use strict';

    var log = Logger('FEditing:FrontendEditing');
    log.trace('--> createFrontendEditing');

    var translateKeys = {
        confirmNavigateWithChange: 'notifications.unsaved-changes',
    };

    var t = TranslatorLoader
        .useTranslator('frontendEditing', function reload (t) {
            translateKeys = $.extend(translateKeys, t.getKeys());
        }).translator;


    // Hold event listeners and the callbacks
    var listeners = {};

    // LocalStorage for changes that are to be saved
    var storage = null;

    // Disable the modal pop-up when a new content element is created
    var disableModalOnNewCe = false;

    // Default for event-listening and triggering
    var events = {
        CONTENT_CHANGE: 'CONTENT_CHANGE'
    };

    var scrollToIndicateCeDelay = 1000;
    var scrollToIndicateCeSpeed = 500;
    var scrollToIndicateCeOffsetTop = 10;

    var FrontendEditing = function () {
        log.trace('new FrontendEditing');

        this.init();
    };

    // TimeoutId used in indicateCeStart()
    var indicateCeScrollTimeoutId = null;

    // Add default events and a function to add other events
    FrontendEditing.events = events;
    FrontendEditing.addEvent = function addEvent (key, value) {
        log.debug('addEvent', key, value);

        FrontendEditing.events[key] = value;
    };

    // Scroll function when dragging content elements to top or bottom of window
    var scrollSpeed = 4;
    var $iframe = $('iframe');
    var $iframeWrapper = $('.t3-frontend-editing__iframe-wrapper');
    var scrollAreaBaseClasses = 'scrollarea scrollarea--arrow';

    var $scrollAreaTop = $iframeWrapper.find('.scrollarea-top');
    if (!$scrollAreaTop) {
        $scrollAreaTop = $('<div/>')
            .addClass(scrollAreaBaseClasses)
            .addClass('scrollarea-top scrollarea--arrow-up')
            .hide()
            .insertAfter($iframe);
    }
    var $scrollAreaBottom = $iframeWrapper.find('.scrollarea-bottom');
    if (!$scrollAreaBottom) {
        $scrollAreaBottom = $('<div/>')
            .addClass(scrollAreaBaseClasses)
            .addClass('scrollarea-bottom scrollarea--arrow-down')
            .hide()
            .insertAfter($iframe);
    }

    var scroller = Scroller($iframe, $scrollAreaTop, $scrollAreaBottom);

    function stopScrolling () {
        $(this)
            .removeClass('scrollarea--arrow__mouseover');
        scroller.stopScrolling();
    }

    $scrollAreaTop
        .on('dragleave', stopScrolling)
        .on('dragenter', function startScrollUp () {
            $scrollAreaTop.addClass('scrollarea--arrow__mouseover');
            scroller.startScrolling(-scrollSpeed);
        });
    $scrollAreaBottom
        .on('dragleave', stopScrolling)
        .on('dragenter', function startScrollDown () {
            $scrollAreaBottom.addClass('scrollarea--arrow__mouseover');
            scroller.startScrolling(scrollSpeed);
        });

    $('[draggable]')
        .on('dragstart', scroller.enable)
        .on('dragend', scroller.disable);

    // Public API
    FrontendEditing.prototype = {
        init: function () {
            log.trace('init FrontendEditing');

            // Create an array of listeners for every event and
            // assign it to the instance
            for (var key in FrontendEditing.events) {
                if (!FrontendEditing.events.hasOwnProperty(key)) {
                    continue;
                }

                listeners[events[key]] = [];
                this[key] = events[key];
            }

            // LocalStorage for changes in editors
            storage = new Storage('TYPO3:FrontendEditing');
        },

        /**
         * Log an error.
         * @param message
         * @deprecated use TYPO3/CMS/FrontendEditing/Utils/Logger instead
         */
        error: function (message) {
            log.warn(
                'error: Deprecated function call.' +
                'Use TYPO3/CMS/FrontendEditing/Utils/Logger instead.'
            );

            log.error(message);
        },

        /**
         * Trigger an event and send the args to the registered listeners.
         * @param {string} event
         * @param {*} args every arguments except the first
         */
        trigger: function (event) {
            if (!listeners[event]) {
                log.error('Invalid event', event);
                return;
            }
            var args = Array.prototype.slice.call(arguments, 1);

            log.debug('trigger event', event, args);

            for (var i = 0; i < listeners[event].length; i++) {
                listeners[event][i].apply(this, args);
            }
        },

        /**
         * Returns the base storage
         * @return {TYPO3/CMS/FrontendEditing/Storage} the base storage
         * @deprecated Instead use own Storage
         */
        getStorage: function () {
            return storage;
        },

        /**
         * Register a listener to an event.
         * @param {string} event
         * @param {function} listener
         */
        on: function (event, listener) {
            if (typeof listener !== 'function') {
                throw new TypeError('listener is not a function');
            }

            if (listeners[event]) {
                log.debug('add listener to event', event, listener);

                listeners[event].push(listener);
            } else {
                log.error('On called with invalid event:', event);
            }
        },

        navigate: function (linkUrl) {
            log.trace('navigate', linkUrl);

            if (linkUrl && linkUrl.indexOf('#') !== 0) {
                if (storage.isEmpty()) {
                    log.debug('navigate away', linkUrl);

                    window.location.href = linkUrl;
                } else {
                    Modal.confirmNavigate(
                        t.translate(translateKeys.confirmNavigateWithChange),
                        function save () {
                            F.saveAll();

                            //TODO wait until finished save items!!!

                            log.debug('navigate away', linkUrl);

                            window.location.href = linkUrl;
                        },
                        {
                            yes: function () {
                                log.debug('navigate away', linkUrl);

                                window.location.href = linkUrl;
                            },
                            no: function () {
                                F.hideLoadingScreen();
                            }
                        });
                }
            }
        },

        loadInModal: function (url) {
            log.info('open modal iframe', url);

            require(['TYPO3/CMS/Backend/Modal'],
                function createModalIFrame (Modal) {
                    Modal.advanced({
                        type: Modal.types.iframe,
                        title: '',
                        content: url,
                        size: Modal.sizes.large,
                        // eslint-disable-next-line id-denylist
                        callback: function (currentModal) {

                            var modalIframe = currentModal
                                .find(Modal.types.iframe);
                            modalIframe.attr('name', 'list_frame');
                            modalIframe.on('load', function propagateTypo3 () {
                                log.debug(
                                    'propagate Typo3 variables',
                                    window.TYPO3
                                );

                                $.extend(
                                    window.TYPO3,
                                    modalIframe[0].contentWindow.TYPO3 || {}
                                );
                            });

                            currentModal.on('hidden.bs.modal', F.refreshIframe);
                        }
                    });
                });
        },

        setTranslationLabels: function (labels) {
            log.trace('setTranslationLabels', labels);

            TranslatorLoader.configure({
                translationLabels: labels
            });
        },

        setDisableModalOnNewCe: function (disable) {
            log.trace('setDisableModalOnNewCe', disable);

            disableModalOnNewCe = disable;
        },

        /**
         * Used to get translated strings by key.
         * @deprecated Use TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader or
         * a high level function.
         * @param key
         * @returns {*}
         */
        translate: function (key) {
            log.warn(
                'translate: Deprecated function call.' +
                'Use TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader instead.'
            );

            try {
                var translator = TranslatorLoader.getTranslator();
                return translator.translate.apply(translator, arguments);
            } catch (exception) {
                F.error(exception.toString());
            }
            return key;
        },

        parseQuery: function (queryString) {
            log.trace('parseQuery', queryString);

            if (queryString[0] === '?') {
                queryString = queryString.substr(1);
            }

            var query = {};

            var queries = queryString.split('&');
            for (var i = 0; i < queries.length; i++) {
                var queryEntry = queries[i].split('=');

                var queryKey = decodeURIComponent(queryEntry[0]);
                var queryValue = decodeURIComponent(queryEntry[1] || '');

                query[queryKey] = queryValue;
            }

            log.debug('parsed query', queryString, query);

            return query;
        },

        serializeObj: function (obj) {
            var stringArray = [];
            for (var p in obj) {
                if (!obj.hasOwnProperty(p)) {
                    continue;
                }

                stringArray.push(
                    encodeURIComponent(p) + '=' + encodeURIComponent(obj[p])
                );
            }

            log.trace('serializeObj', obj, stringArray);

            return stringArray.join('&');
        },

        dragCeStart: function (ev) {
            log.info('start drag Ce', ev.currentTarget);

            ev.stopPropagation();
            var movable = parseInt(ev.currentTarget.dataset.movable, 10);

            ev.dataTransfer.setData('params', ev.currentTarget.dataset.params);
            ev.dataTransfer.setData('movable', movable);
            ev.dataTransfer.setData('movableUid', $(ev.currentTarget)
                .find('span.t3-frontend-editing__inline-actions')
                .data('uid'));

            if (movable === 1) {
                var $currentTarget = $(ev.currentTarget);

                $currentTarget.prev('.t3-frontend-editing__dropzone')
                    .addClass('t3-frontend-editing__dropzone-hidden');
                $currentTarget.next('.t3-frontend-editing__dropzone')
                    .addClass('t3-frontend-editing__dropzone-hidden');
            }

            var $iframe = F.iframe();
            $iframe.contents()
                .find('.t3-frontend-editing__dropzone[data-tables]')
                .addClass('t3-frontend-editing__dropzone-hidden');
            $iframe.contents()
                .find('body')
                .addClass('dropzones-enabled');
        },

        dragCeEnd: function (ev) {
            log.info('end drag Ce');
            log.debug('Ce drop', ev.currentTarget, ev.dataTransfer);

            ev.stopPropagation();
            var movable = parseInt(ev.currentTarget.dataset.movable, 10);

            if (movable === 1) {
                var $currentTarget = $(ev.currentTarget);

                $currentTarget.prev('.t3-frontend-editing__dropzone')
                    .removeClass('t3-frontend-editing__dropzone-hidden');
                $currentTarget.next('.t3-frontend-editing__dropzone')
                    .removeClass('t3-frontend-editing__dropzone-hidden');
            }

            var $iframe = F.iframe();
            $iframe.contents()
                .find('.t3-frontend-editing__dropzone[data-tables]')
                .removeClass('t3-frontend-editing__dropzone-hidden');
            $iframe.contents()
                .find('body')
                .removeClass('dropzones-enabled');
        },

        dragCeOver: function (ev) {
            log.info('Ce dragging over');

            ev.preventDefault();
            $(ev.currentTarget)
                .addClass('active');
        },

        dragCeLeave: function (ev) {
            log.info('Ce dragging leave');

            ev.preventDefault();
            $(ev.currentTarget)
                .removeClass('active');
        },

        dropCe: function (ev) {
            log.info('Ce drop');
            log.debug('Ce drop', ev.currentTarget, ev.dataTransfer);

            ev.preventDefault();
            var movable = parseInt(ev.dataTransfer.getData('movable'), 10);

            if (movable === 1) {
                var $currentTarget = $(ev.currentTarget);
                var ceUid = parseInt(ev.dataTransfer.getData('movableUid'), 10);
                var moveAfter = parseInt($currentTarget.data('moveafter'), 10);
                var colPos = parseInt($currentTarget.data('colpos'), 10);
                var defVals = $currentTarget.data('defvals');

                if (ceUid !== moveAfter) {
                    F.moveContent(
                        ceUid,
                        'tt_content',
                        moveAfter,
                        colPos,
                        defVals
                    );
                }
            } else {
                this.dropNewCe(ev);
            }
        },

        dropNewCe: function (ev) {
            log.debug('create Ce drop');

            // Merge drop zone query string with new CE query string
            // without override any parameter
            var newUrlParts = $(ev.currentTarget)
                .data('new-url')
                .split('?');
            var params = ev.dataTransfer.getData('params');

            var newUrlQueryStringObj = F.parseQuery(newUrlParts[1]);
            var paramsObj = F.parseQuery(params.substr(1));
            $.extend(true, paramsObj, newUrlQueryStringObj);

            var fullUrlQueryString = F.serializeObj(paramsObj);

            if (disableModalOnNewCe) {
                F.newContent(fullUrlQueryString);
            } else {
                F.loadInModal(newUrlParts[0] + '?' + fullUrlQueryString);
            }
        },

        indicateCeStart: function (ev) {
            log.debug('start indicate ce', ev.currentTarget);

            var $iframe = F.iframe();
            var uid = ev.currentTarget.dataset.uid;
            $iframe.contents()
                .find('#c' + uid)
                .parent()
                .addClass('indicate-element');
            window.clearTimeout(indicateCeScrollTimeoutId);

            indicateCeScrollTimeoutId = window.setTimeout(function scrollCe () {
                log.info('scroll to Ce', uid);

                var $iframe = F.iframe();
                var offset = $iframe.contents()
                    .find('#c' + uid)
                    .parent()
                    .offset();

                $iframe.contents()
                    .find('body, html')
                    .animate({
                        scrollTop: offset
                            ? offset.top - scrollToIndicateCeOffsetTop
                            : offset
                    }, scrollToIndicateCeSpeed);
            }, scrollToIndicateCeDelay);
        },

        indicateCeEnd: function (ev) {
            log.debug('end indicate ce', ev.currentTarget);

            var $iframe = F.iframe();
            var uid = ev.currentTarget.dataset.uid;

            $iframe.contents()
                .find('#c' + uid)
                .parent()
                .removeClass('indicate-element');

            window.clearTimeout(indicateCeScrollTimeoutId);
        },

        dropCr: function (ev) {
            log.debug('drop cr', ev.currentTarget, ev.dataTransfer);

            ev.preventDefault();

            var url = ev.dataTransfer.getData('new-url');
            if (!ev.dataTransfer.getData('table') && url) {
                return false;
            }

            var $target = $(ev.currentTarget);

            var pageUid = parseInt($target.data('pid'), 10) || 0;
            if (pageUid > 0) {
                // TODO: Find a better solution than simply replace in URL
                url = url.replace(
                    /%5D%5B\d+%5D=new/,
                    '%5D%5B' + pageUid + '%5D=new'
                );
            }
            try {
                var newUrlParts = url.split('?');
                var newUrlQueryStringObj = F.parseQuery(newUrlParts[1]);
                var defaultValues = $target.data('defvals');

                var fullUrlObj = {};
                $.extend(true, fullUrlObj, defaultValues, newUrlQueryStringObj);
                var fullUrlQueryString = F.serializeObj(fullUrlObj);

                F.loadInModal(newUrlParts[0] + '?' + fullUrlQueryString);
            } catch (exception) {
                log.warn('failed to load iframe with edit form', exception);

                F.loadInModal(url);
            }

            return true;
        },

        dragCrStart: function (ev) {
            log.info('start drag cr', ev.currentTarget);

            ev.stopPropagation();
            var table = ev.currentTarget.dataset.table;
            var $iframe = F.iframe();

            ev.dataTransfer.setData('table', table);
            ev.dataTransfer.setData('new-url', ev.currentTarget.dataset.url);

            $iframe.contents()
                .find('.t3-frontend-editing__dropzone')
                .not('[data-tables~="' + table + '"]')
                .addClass('t3-frontend-editing__dropzone-hidden');
            $iframe.contents()
                .find('body')
                .addClass('dropzones-enabled');
        },

        dragCrEnd: function (ev) {
            log.debug('end drag cr', ev.currentTarget);

            ev.stopPropagation();
            var table = ev.currentTarget.dataset.table;
            var $iframe = F.iframe();

            $iframe.contents()
                .find('.t3-frontend-editing__dropzone')
                .not('[data-tables~="' + table + '"]')
                .removeClass('t3-frontend-editing__dropzone-hidden');
            $iframe.contents()
                .find('body')
                .removeClass('dropzones-enabled');
        }
    };

    return FrontendEditing;
});
