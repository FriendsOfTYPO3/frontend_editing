(function() {

    'use strict';

    // Extend FrontendEditing with additional events
    var events = {
        REQUEST_START: 'REQUEST_START',
        REQUEST_COMPLETE: 'REQUEST_COMPLETE',
        UPDATE_CONTENT_COMPLETE: 'UPDATE_CONTENT_COMPLETE',
        REQUEST_ERROR: 'REQUEST_ERROR'
    };

    // Add custom events to FrontendEditing
    for (var key in events) {
        FrontendEditing.addEvent(key, events[key]);
    }

    // Extend FrontendEditing with the following functions
    FrontendEditing.prototype.saveAll = saveAll;
    FrontendEditing.prototype.delete = deleteRecord;

    var numberOfRequestsLeft;
    var pageUrl = window.location.protocol + '//' + window.location.host;
    var functionRoutes = {
        'crud': {
            'prefix': 'tx_frontendediting_frontend_editing',
            'url': '?type=1470741815&tx_frontendediting_frontend_editing[controller]=Crud&tx_frontendediting_frontend_editing[action]='
        },
        'pageTreeCrud': '?type=1477569731'
    };

    function getCrudUrl(action, data) {
        var url = pageUrl + functionRoutes.crud.url + action;
        for (var key in data) {
            url += '&' + functionRoutes.crud.prefix + '[' + key + ']=' + data[key];
        }
        return url;
    };

    function deleteRecord(uid, table) {
        this.trigger(F.REQUEST_START);

        var url = getCrudUrl('delete', {
            uid: uid,
            table: table
        });

        F.get(url, {
            done: function(data) {
                F.trigger(
                    F.UPDATE_CONTENT_COMPLETE,
                    {  
                        message: data.message
                    } 
                );
            },
            fail: function(jqXHR, textStatus, errorThrown) {
                F.trigger(
                    F.REQUEST_ERROR,
                    {  
                        message: jqXHR.responseText,
                    } 
                );
            },
            always: function() {
                F.trigger(F.REQUEST_COMPLETE);
            }
        });
    }

    function saveAll() {
        var storage = F.getStorage();
        var items = storage.getSaveItems();
        if (items.count() === 0) {
            return this.error('Function saveAll called but no items to save');
        }

        F.trigger(F.REQUEST_START);

        numberOfRequestsLeft = items.count();
        items.forEach(function(item) {
            var data = {
                'action': item.action,
                'table': item.table,
                'uid': item.uid,
                'field': item.field,
                'content': CKEDITOR.instances[item.editorInstance].getData()
            };
            F.post(
                getCrudUrl('update'), data, {
                    done: function(data) {
                        F.trigger(
                            F.UPDATE_CONTENT_COMPLETE,
                            {  
                                message: data.message
                            } 
                        );
                    },
                    fail: function(jqXHR, textStatus, errorThrown) {
                        F.trigger(
                            F.REQUEST_ERROR,
                            {  
                                message: jqXHR.responseText
                            } 
                        );
                    },
                    always: function() {
                        numberOfRequestsLeft--;
                        if (numberOfRequestsLeft === 0) {
                            storage.clear();
                            F.trigger(F.REQUEST_COMPLETE);
                            F.trigger(F.CONTENT_CHANGE);
                        }
                    }
                }
            );
        });
    }
})();
