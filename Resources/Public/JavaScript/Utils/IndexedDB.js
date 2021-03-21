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
 * Module: TYPO3/CMS/FrontendEditing/Utils/IndexedDB
 * Initialize dexie (IndexedDB wrapper) and returns opened dexie
 */
define(['../Contrib/dexie.min'], function createIndexedDB (Dexie) {
    'use strict';

    var db = new Dexie('FrontendEditing');
    db.version(1)
        .stores({
            logs: '++id, timestamp, name, url, level, channel, message, stack'
        });

    db.open();

    return db;
});
