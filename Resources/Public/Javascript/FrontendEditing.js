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
 * FrontendEditing: The foundation for the frontend editing interactions
 */
var FrontendEditing = (function($){

    'use strict';

    // Hold event listeners and the callbacks
    var listeners = {};

    // LocalStorage for changes that are to be saved
    var storage = null;

    // JSON object holding key => label for labels
    var translationLabels = {};

    // Default for event-listening and triggering
    var events = {
        CONTENT_CHANGE: 'CONTENT_CHANGE'
    };

    // TimeoutId used in indicateCeStart()
    var indicateCeScrollTimeoutId = null;

    // Add default events and a function to add other events
    FrontendEditing.events = events;
    FrontendEditing.addEvent = function(key, value) {
        FrontendEditing.events[key] = value;
    };

    function FrontendEditing(options) {
        this.init(options);

        // Assign every event to the FrontendEditing class for API use
        for (var key in events) {
            this[key] = events[key];
        }
    }

    // Public API
    FrontendEditing.prototype = {
        init: function(options) {
            // Create an array of listeners for every event and assign it to the instance
            for (var key in FrontendEditing.events) {
                listeners[events[key]] = [];
                this[key] = events[key];
            }

            storage = options.storage;
        },

        error: function (message) {
            console.error(message);
        },

        trigger: function(event, data) {
            if (!listeners[event]) {
                this.error('Invalid event', event);
                return;
            }
            for (var i = 0; i < listeners[event].length; i++) {
                listeners[event][i](data);
            }
        },

        getStorage: function() {
            return storage;
        },

        confirm: function(message, callbacks) {
            var confirmed = confirm(message);

            callbacks = callbacks || {};
            if (confirmed && typeof callbacks.yes === 'function') {
                callbacks.yes();
            }
            if (!confirmed && typeof callbacks.no === 'function') {
                callbacks.no();
            }
        },

        on: function(event, callback) {
            if (typeof callback === 'function') {
                if (listeners[event]) {
                    listeners[event].push(callback);
                } else {
                    this.error('On called with invalid event:', event);
                }
            } else {
                this.error('Callback is not a function');
            }
        },

        navigate: function(linkUrl) {
            if (linkUrl && linkUrl !== '#') {
                if (this.getStorage().isEmpty()) {
                    window.location.href = linkUrl;
                } else {
                    this.confirm(F.translate('notifications.unsaved-changes'), {
                        yes: function() {
                            window.location.href = linkUrl;
                        }
                    });
                }
            }
        },

        setTranslationLabels: function(labels) {
            translationLabels = labels;
        },

        translate: function(key) {
            if (translationLabels[key]) {
                return translationLabels[key];
            } else {
                F.error('Invalid translation key: ' + key);
            }
        },

        post: function (url, data, callbacks) {
            callbacks = callbacks || {};
            var done = callbacks.done || function(){};
            var fail = callbacks.fail || function(){};
            var always = callbacks.always || function(){};

            $.ajax({
                type: 'POST',
                url: url,
                dataType: 'JSON',
                data: data
            })
                .done(done)
                .fail(fail)
                .always(always);
        },

        get: function(url, callbacks) {
            callbacks = callbacks || {};
            var done = callbacks.done || function(){};
            var fail = callbacks.fail || function(){};
            var always = callbacks.always || function(){};

            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'JSON'
            })
                .done(done)
                .fail(fail)
                .always(always);
        },

        dragNewCeStart: function(ev) {
            ev.dataTransfer.setData('params', ev.currentTarget.dataset.params);
            var $iframe = F.iframe();
            $iframe.contents().find('body').addClass('dropzones-enabled');
        },

        dragNewCeEnd: function(ev) {
            var $iframe = F.iframe();
            $iframe.contents().find('body').removeClass('dropzones-enabled');
        },

        dragNewCeOver: function(ev) {
            ev.preventDefault();
            $(ev.currentTarget).addClass('active');
        },

        dragNewCeLeave: function(ev) {
            ev.preventDefault();
            $(ev.currentTarget).removeClass('active');
        },

        dropNewCe: function(ev) {
            ev.preventDefault();
            var params = ev.dataTransfer.getData('params');
            var newUrl = $(ev.currentTarget).data('new-url');
            var fullUrl = newUrl + params;
            F.quickEditOpen(fullUrl);
        },

        indicateCeStart: function(ev) {
            var $iframe = F.iframe();
            var uid = ev.currentTarget.dataset.uid;
            $iframe.contents().find('#c' + uid).parent().addClass('indicate-element');
            window.clearTimeout(this.indicateCeScrollTimeoutId);

            this.indicateCeScrollTimeoutId = window.setTimeout(function() {
                var $iframe = F.iframe();
                $iframe.contents().find('body, html').animate(
                    {
                        scrollTop: $iframe.contents().find('#c' + uid).parent().offset().top - 10
                    },
                    500
                );
            }, 1000);
        },

        indicateCeEnd: function(ev) {
            var $iframe = F.iframe();
            $iframe.contents().find('#c' + ev.currentTarget.dataset.uid).parent().removeClass('indicate-element');
            window.clearTimeout(this.indicateCeScrollTimeoutId);
        }

    };

    return FrontendEditing;

}(jQuery));
