(function(w) {

    'use strict';

    var FrontendEditing = w.FrontendEditing || {};

    FrontendEditing.Storage = function(storageKey) {
        this.storageKey = storageKey;

        // Always empty the storage when it's constructed
        this.clear();
    };

    FrontendEditing.Storage.prototype = {
        addSaveItem: function (id, item) {
            var processedItems = {
                saveItems: this.getSaveItems().set(id, item)
            };
            localStorage.setItem(this.storageKey, JSON.stringify(processedItems));
        },

        getSaveItems: function() {
            var saveItems = localStorage.getItem(this.storageKey);
            saveItems = JSON.parse(saveItems);
            if (saveItems === null || saveItems === '') {
                saveItems = Immutable.Map({});
            } else {
                saveItems = Immutable.Map(saveItems.saveItems);
            }

            return saveItems;
        },

        getAllData: function() {
            return JSON.parse(localStorage.getItem(this.storageKey));
        },

        addItem: function(key, data) {
            var items = localStorage.getItem(this.storageKey);
            items = JSON.parse(items);
            items = Immutable.Map(items);
            var addedItems = items.set(key, data);
            localStorage.setItem(this.storageKey, JSON.stringify(addedItems));
        },

        clear: function() {
            var items = localStorage.getItem(this.storageKey);
            items = JSON.parse(items);
            items = Immutable.Map(items);
            // Reset the saveItems but keep the rest of the states
            var addedItems = items.set('saveItems', null);
            localStorage.setItem(this.storageKey, JSON.stringify(addedItems));
        },

        isEmpty: function() {
            return this.getSaveItems().count() === 0;
        }
    };

    w.FrontendEditing = FrontendEditing;

}(window));