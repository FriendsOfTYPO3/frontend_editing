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
 * Module: TYPO3/CMS/FrontendEditing/Storage
 * FrontendEditing.Storage: A wrapper class for using LocalStorage
 * has no dependencies to any other Frontend Editing related functionality
 * but is used in the main Frontend Editing app js.
 */
define([
    './Contrib/immutable',
    './Utils/Logger'
], function createStorage (Immutable, Logger) {
    'use strict';

    var log = Logger('FEditing:localStorage');
    log.trace('--> createStorage');

    // TODO: use reducers and action with payload to create states to store

    var Storage = function (storageKey) {
        log.info('new Storage', storageKey);

        this.storageKey = storageKey;

        // Always empty the storage when it's constructed
        this.clear();
    };

    Storage.prototype = {
        addSaveItem: function (id, saveItem) {
            log.debug('add save item', id, saveItem);

            var items = this.getAllDataAsMap();
            var saveItems = this._getSaveItems(items);

            saveItems = saveItems.set(id, saveItem);
            var addedItems = items.set('saveItems', saveItems);
            this._persistItems(addedItems);
        },

        _getSaveItems: function (map) {
            var saveItems = map.get('saveItems');
            if (!saveItems) {
                return Immutable.Map({});
            }
            return Immutable.Map(saveItems);
        },

        getSaveItems: function () {
            var items = this.getAllDataAsMap();
            return this._getSaveItems(items);
        },

        getAllData: function () {
            return JSON.parse(localStorage.getItem(this.storageKey));
        },

        getAllDataAsMap: function () {
            return Immutable.Map(this.getAllData());
        },

        addItem: function (key, item) {
            log.debug('add item', key, item);

            var items = this.getAllDataAsMap();
            var addedItems = items.set(key, item);
            this._persistItems(addedItems);
        },

        clear: function () {
            var items = this.getAllDataAsMap();

            log.debug('clear saveItems', items);

            // Reset the saveItems but keep the rest of the states
            var addedItems = items.set('saveItems', null);

            log.trace('new item', addedItems);

            this._persistItems(addedItems);
        },

        _persistItems: function (items) {
            log.trace('_persistItems', items);

            localStorage.setItem(this.storageKey, JSON.stringify(items));
        },

        isEmpty: function () {
            var isEmpty = this.getSaveItems()
                .isEmpty();

            log.trace('isEmpty', isEmpty);

            return isEmpty;
        },

        countSaveItems: function () {
            var count = this.getSaveItems()
                .count();

            log.trace('countSaveItems', count);

            return count;
        }
    };

    return Storage;
});
