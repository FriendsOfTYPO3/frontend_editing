var FrontendEditing = (function($){

    'use strict';

    // Private variables
    var listeners = {};
    var storage = null;

    // Default for event-listening and triggering
    var events = {
        CONTENT_CHANGE: 'CONTENT_CHANGE'
    };

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
    };

    // Public API
    FrontendEditing.prototype = {
        init: function(options) {
            // Create an array of listeners for every event and assign it to the intance
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
        confirm: function(message) {
            return confirm(message);
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
                    if (confirm(contentUnsavedChangesLabel)) {
                        window.location.href = linkUrl;
                    }
                }
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
                dataType: 'JSON',
            })
            .done(done)
            .fail(fail)
            .always(always);
        }
    };

    return FrontendEditing;

}(jQuery));