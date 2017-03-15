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
 * FrontendEditing.Storage: A wrapper class for using LocalStorage
 * has no dependencies to any other Frontend Editing related functionality
 * but is used in the main Frontend Editing app js.
 */
define(['immutable'], function (Immutable) {
    'use strict';

    var Storage = function(storageKey) {
        this.storageKey = storageKey;

        // Always empty the storage when it's constructed
        this.clear();
    };

    Storage.prototype = {
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

    return Storage;
});
