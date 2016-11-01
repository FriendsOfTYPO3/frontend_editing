(function() {

    'use strict';

    // Extend FrontendEditing with additional events
    var events = {
        SAVE_START: 'SAVE_START',
        SAVE_CONTENT_COMPLETE: 'SAVE_CONTENT_COMPLETE',
        SAVE_COMPLETE: 'SAVE_COMPLETE',
        SAVE_ERROR: 'SAVE_ERROR'
    };

    // Add custom events to FrontendEditing
    for (var key in events) {
        FrontendEditing.addEvent(key, events[key]);
    }

    // Extend FrontendEditing with the following functions
    FrontendEditing.prototype.saveAll = saveAll;

    var pageUrl = window.location.protocol + '//' + window.location.host;
    var numberOfPostRequests;
    var functionRoutes = {
        'crud': '?type=1470741815',
        'pageTreeCrud': '?type=1477569731'
    };

    function saveAll() {
        var storage = F.getStorage();
        var items = storage.getSaveItems();
        if (items.count() === 0) {
            return this.error('Function saveAll called but no items to save');
        }

        this.trigger(F.SAVE_START);

        var _this = this;
        numberOfPostRequests = items.count();
        items.forEach(function(item) {
            var data = {
                'action': item.action,
                'table': item.table,
                'uid': item.uid,
                'field': item.field,
                'content': CKEDITOR.instances[item.editorInstance].getData()
            };
            _this.post(
                functionRoutes.crud,
                data,
                {
                    done: function(data) {
                        _this.trigger(
                            F.SAVE_CONTENT_COMPLETE,
                            {  
                                uid: parseInt(data.message)
                            } 
                        );
                    },
                    fail: function(jqXHR, textStatus, errorThrown) {
                        _this.trigger(
                            F.SAVE_ERROR,
                            {  
                                message: jqXHR.responseText,
                            } 
                        );
                    },
                    always: function() {
                        numberOfPostRequests--;
                        if (numberOfPostRequests === 0) {
                            storage.clear();
                            _this.trigger(F.SAVE_COMPLETE);
                            _this.trigger(F.CONTENT_CHANGE);
                        }
                    }
                }
            );
        });
    }
})();
