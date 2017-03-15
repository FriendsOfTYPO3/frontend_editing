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
 * FrontendEditing.Crud: Handling the CRUD requests for content
 */
define(['jquery', 'TYPO3/CMS/FrontendEditing/FrontendEditing'], function ($, FrontendEditing) {

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
	FrontendEditing.prototype.hideContent = hideRecord;
	FrontendEditing.prototype.moveContent = moveRecord;
	FrontendEditing.prototype.setEndpointUrl = function (url) {
		this._endpointUrl = url;
	};

	var numberOfRequestsLeft;

	function getEndpointUrl(action) {
		var url = F._endpointUrl;
		if (action) {
			url += '&action=' + action;
		}
		return url;
	}

	function saveAll() {
		var storage = F.getStorage();
		var items = storage.getSaveItems();
		if (items.count() === 0) {
			return this.error('Function saveAll called but no items to save');
		}

		F.trigger(F.REQUEST_START);

		numberOfRequestsLeft = items.count();
		items.forEach(function (item) {
			// Check if a record is locked
			$.when(checkIfRecordIsLocked(item)).done(function(data) {
				// If user prompted yes or there was no locked record found
				if (data === false) {
					// Save the content
					var saveItemData = {
						'action': item.action,
						'table': item.table,
						'uid': item.uid,
						'field': item.field,
						'content': CKEDITOR.instances[item.editorInstance].getData()
					};
					$.ajax({
						url: getEndpointUrl(),
						method: 'POST',
						data: saveItemData
					}).done(function (data) {
						F.trigger(
							F.UPDATE_CONTENT_COMPLETE,
							{
								message: data.message
							}
						);
					}).fail(function (jqXHR) {
						F.trigger(
							F.REQUEST_ERROR,
							{
								message: jqXHR.responseText
							}
						);
					}).always(function () {
						numberOfRequestsLeft--;
						if (numberOfRequestsLeft === 0) {
							storage.clear();
							F.trigger(F.REQUEST_COMPLETE);
							F.trigger(F.CONTENT_CHANGE);
						}
					});
				} else {
					storage.clear();
					F.trigger(F.REQUEST_COMPLETE);
					F.trigger(F.CONTENT_CHANGE);
				}
			});
		});
	}

	function checkIfRecordIsLocked(item) {
		var lockedRecordData = {
			'action': 'lockedRecord',
			'table': item.table,
			'uid': item.uid
		};
		$.ajax({
			url: getEndpointUrl('lockedRecord'),
			method: 'POST',
			data: lockedRecordData
		}).done(function (data) {
			if (data.success === true) {
				F.confirm(data.message, {
					yes: function () {
						return false;
					},
					no: function () {
						return true;
					}
				});
			}
		});

		return false;
	}

	function deleteRecord(uid, table) {
		this.trigger(F.REQUEST_START);

		$.ajax({
			url: getEndpointUrl(),
			method: 'DELETE',
			data: {
				table: table,
				uid: uid
			}
		}).done(function (data) {
			F.trigger(
				F.UPDATE_CONTENT_COMPLETE,
				{
					message: data.message
				}
			);
		}).fail(function (jqXHR) {
			F.trigger(
				F.REQUEST_ERROR,
				{
					message: jqXHR.responseText
				}
			);
		}).always(function () {
			F.trigger(F.REQUEST_COMPLETE);
		});
	}

	function hideRecord(uid, table, hide) {
		this.trigger(F.REQUEST_START);

		$.ajax({
			url: getEndpointUrl('hide'),
			method: 'POST',
			data: {
				uid: uid,
				table: table,
				hide: hide
			}
		}).done(function (data) {
			F.trigger(
				F.UPDATE_CONTENT_COMPLETE,
				{
					message: data.message
				}
			);
		}).fail(function (jqXHR) {
			F.trigger(
				F.REQUEST_ERROR,
				{
					message: jqXHR.responseText
				}
			);
		}).always(function () {
			F.trigger(F.REQUEST_COMPLETE);
		});
	}

	function moveRecord(uid, table, beforeUid) {
		this.trigger(F.REQUEST_START);

		$.ajax({
			url: getEndpointUrl('move'),
			method: 'POST',
			data: {
				uid: uid,
				table: table,
				beforeUid: beforeUid
			}
		}).done(function (data) {
			F.trigger(
				F.UPDATE_CONTENT_COMPLETE,
				{
					message: data.message
				}
			);
		}).fail(function (jqXHR) {
			F.trigger(
				F.REQUEST_ERROR,
				{
					message: jqXHR.responseText
				}
			);
		}).always(function () {
			F.trigger(F.REQUEST_COMPLETE);
		});
	}

	return FrontendEditing;
});
