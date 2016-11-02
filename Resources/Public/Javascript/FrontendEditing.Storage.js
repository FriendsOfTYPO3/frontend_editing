(function(w) {

    'use strict';

    var FrontendEditing = w.FrontendEditing || {};

    FrontendEditing.Storage = function(storageKey) {
        this.storageKey = storageKey

        // Always empty the storage when it's contructed
        this.clear();
    };

    FrontendEditing.Storage.prototype = {
        addSaveItem: function (id, item) {
            var processedItems = this.getSaveItems().set(id, item);
            localStorage.setItem(this.storageKey, JSON.stringify(processedItems));
        },
        getSaveItems: function() {
            var saveItems = localStorage.getItem(this.storageKey);
            if (saveItems === null || saveItems === '') {
                saveItems = Immutable.Map({});
            } else {
                saveItems = Immutable.Map(JSON.parse(saveItems));
            }

            return saveItems;
        },
        clear: function() {
            localStorage.removeItem(this.storageKey);
        },
        isEmpty: function() {
            return this.getSaveItems().count() === 0;
        }
    };

    w.FrontendEditing = FrontendEditing;

}(window));