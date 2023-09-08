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
 * Module: TYPO3/CMS/FrontendEditing/Crud
 * FrontendEditing.Crud: Handling the CRUD requests for content
 */
define([
  'jquery',
  './FrontendEditing',
  'TYPO3/CMS/Backend/Modal',
  './Utils/Logger'
], function createCrudModule($, FrontendEditing, Modal, Logger) {
  'use strict';

  var log = Logger('FEditing:CRUD');
  log.trace('--> createCrudModule');

  // Extend FrontendEditing with additional events
  var events = {
    REQUEST_START: 'REQUEST_START',
    REQUEST_COMPLETE: 'REQUEST_COMPLETE',
    UPDATE_CONTENT_COMPLETE: 'UPDATE_CONTENT_COMPLETE',
    UPDATE_PAGES_COMPLETE: 'UPDATE_PAGES_COMPLETE',
    REQUEST_ERROR: 'REQUEST_ERROR'
  };

  // Add custom events to FrontendEditing
  for (var key in events) {
    if (!events.hasOwnProperty(key)) {
      continue;
    }

    FrontendEditing.addEvent(key, events[key]);
  }

  // Extend FrontendEditing with the following functions
  FrontendEditing.prototype.saveAll = saveAll;
  FrontendEditing.prototype.delete = deleteRecord;
  FrontendEditing.prototype.newContent = newRecord;
  FrontendEditing.prototype.hideContent = hideRecord;
  FrontendEditing.prototype.moveContent = moveRecord;
  FrontendEditing.prototype.getBESessionId = getBESessionId;
  FrontendEditing.prototype.setBESessionId = setBESessionId;
  FrontendEditing.prototype.getEndpointUrl = getEndpointUrl;
  FrontendEditing.prototype.setEndpointUrl = setEndpointUrl;
  FrontendEditing.prototype.setAjaxRecordProcessEndpointUrl = setAjaxRecordProcessEndpointUrl;

  var numberOfRequestsLeft;

  function getEndpointUrl(action) {
    var url = F._endpointUrl;
    if (action) {
      url += '&action=' + action;
    }
    return url;
  }

  function setEndpointUrl(url) {
    log.log('setEndpointUrl', url);

    this._endpointUrl = url;
  }

  function setAjaxRecordProcessEndpointUrl(url) {
    log.log('setAjaxRecordProcessEndpointUrl', url);

    this._ajaxRecordProcessEndpointUrl = url;
  }

  function getBESessionId() {
    return F._beSessionId;
  }

  function setBESessionId(beSessionId) {
    // check if session id is top secret cause of persist
    log.log('setBESessionId', beSessionId);

    this._beSessionId = beSessionId;
  }

  function getContent(item) {
    if (item.inlineElement) {
      return item.text;
    }

    // Check if the CKEditor has configuration,
    // otherwise remove HTML tags
    var editor = CKEDITOR.instances[item.editorInstance];
    if (item.hasCkeditorConfiguration) {
      return editor.getData();
    }
    return editor.editable()
      .getText();
  }

  function saveAll() {
    log.trace('saveAll');

    var storage = F.getStorage();

    var items = storage.getSaveItems();
    if (items.count() === 0) {
      log.error('Function saveAll called but no items to save');

      throw new Error('No items to save in storage.');
    }

    F.trigger(F.REQUEST_START);

    numberOfRequestsLeft = items.count();

    log.info('items to save', items);

    items.forEach(function processSaveItem(item) {
      log.debug('processSaveItem', item);

      F.showLoadingScreen();

      $.when(checkIfRecordIsLocked(item))
        .done(function saveItem(item) {
          var saveEndpoint = getEndpointUrl();

          log.log('save item', item, saveEndpoint);

          var content = getContent(item);

          // Before save, remove the data-url-parsed and data-url-page-uid attribute from the elements in item's content
          let tempDiv = document.createElement('div');
          tempDiv.innerHTML = content;
          let elements = tempDiv.querySelectorAll('[data-url-parsed]');
          elements.forEach(element => {
            element.removeAttribute('data-url-parsed');
          });
          elements = tempDiv.querySelectorAll('[data-url-page-uid]');
          elements.forEach(element => {
            element.removeAttribute('data-url-page-uid');
          });
          content = tempDiv.innerHTML;

          var jqxhr = $.ajax({
            url: saveEndpoint,
            method: 'POST',
            // eslint-disable-next-line id-denylist
            data: {
              'action': item.action,
              'table': item.table,
              'uid': item.uid,
              'field': item.field,
              'content': content
            }
          })
            .always(requestCompleted);

          appendBaseTriggers(jqxhr);
        })
        .fail(requestCompleted);
    });

    function requestCompleted() {
      log.debug(
        'a request completed [numberOfRequestsLeft]',
        numberOfRequestsLeft
      );

      numberOfRequestsLeft--;
      log.log('number of requests left', numberOfRequestsLeft);

      if (numberOfRequestsLeft === 0) {
        log.info('all item saved');

        storage.clear();
        F.trigger(F.REQUEST_COMPLETE);
        F.trigger(F.CONTENT_CHANGE);
      }

      F.hideLoadingScreen();
    }
  }

  function checkIfRecordIsLocked(item) {
    log.debug('checkIfRecordIsLocked.', item);

    var canEdit = $.Deferred();

    var lockedRecordData = {
      'action': 'lockedRecord',
      'table': item.table,
      'uid': item.uid
    };

    var checkRecordLockedEndpoint = getEndpointUrl('lockedRecord');

    log.log(
      'request if record is locked.',
      checkRecordLockedEndpoint,
      lockedRecordData
    );

    $.ajax({
      url: checkRecordLockedEndpoint,
      method: 'POST',
      // eslint-disable-next-line id-denylist
      data: lockedRecordData
    })
      .done(function success(response) {
        log.log('check if record is locked.', response);

        // workaround to issue #506 cause of empty string
        response = response === '' ? {} : getJsonResponse(response);

        if (response.success === true) {
          // This is the last line of defence. If the code goes here
          // the game is over since someone is going to lose data.
          // There should be no reason to get into this situation
          // because it is hard to decide what to do with more than
          // one change.
          // It also indicates that maybe the locking system went
          // wrong.
          // => It is extremely possible that someone lose data!

          // log.warn(
          //     'Record(s) locked. Maybe locking system is down',
          //     response.message
          // );

          Modal.confirm(
            response.message,
            response.message
          )
            .on('button.clicked', function (evt) {
              if (evt.target.name === 'ok') {
                canEdit.resolve(item);
              } else {
                canEdit.reject();
              }
              Modal.dismiss();
            });

        } else {
          canEdit.resolve(item);
        }
      })
      .fail(function failed() {
        log.error('No answer from server to record is locked request.');

        // This is the last line of defence. So since we don't know,
        // we also don't do anything than shutting down:
        throw new Error(
          'No answer from server to record is locked request.'
        );
      });

    return canEdit;
  }

  function deleteRecord(uid, table) {
    var deleteEndpoint = getEndpointUrl();

    log.info('delete record', deleteEndpoint, uid, table);

    this.trigger(F.REQUEST_START);

    var baseTriggerData = {
      title: 'Content deleted',
    };

    var jqxhr = $.ajax({
      url: deleteEndpoint,
      method: 'DELETE',
      data: {
        table: table,
        uid: uid
      }
    });

    appendTriggers(jqxhr, baseTriggerData);
  }

  function hideRecord(uid, table, hide) {
    var changeHideEndpoint = getEndpointUrl('hide');

    log.info(
      'set hide flag on record [changeHideEndpoint, uid, table, hide]',
      changeHideEndpoint,
      uid,
      table,
      hide
    );

    this.trigger(F.REQUEST_START);

    var jqxhr = $.ajax({
      url: changeHideEndpoint,
      method: 'POST',
      data: {
        uid: uid,
        table: table,
        hide: hide
      }
    });

    appendTriggers(jqxhr);
  }

  function moveRecord(uid, beforeUid, colPos, languageUid) {
    var moveEndpoint = F._ajaxRecordProcessEndpointUrl;

    log.info(
      'move record [moveEndpoint, uid, beforeUid, colPos, languageUid]',
      moveEndpoint,
      uid,
      beforeUid,
      colPos,
      languageUid
    );

    this.trigger(F.REQUEST_START);

    moveEndpoint = moveEndpoint
      + '&cmd[tt_content][' + uid + '][move][action]=paste'
      + '&cmd[tt_content][' + uid + '][move][target]=' + beforeUid
      + '&cmd[tt_content][' + uid + '][move][update][colPos]=' + colPos
      + '&cmd[tt_content][' + uid + '][move][update][sys_language_uid]=' + languageUid
    ;

    var jqxhr = $.ajax({
      url: moveEndpoint
    });

    appendTriggers(jqxhr);
  }

  function newRecord(defVals) {
    var createEndpoint = getEndpointUrl('new');

    log.info(
      'insert new record [createEndpoint, defVals]',
      createEndpoint,
      defVals
    );

    this.trigger(F.REQUEST_START);

    var jqxhr = $.ajax({
      url: createEndpoint,
      method: 'POST',
      // eslint-disable-next-line id-denylist
      data: {data: defVals}
    })
      .always(F.refreshIframe);

    appendTriggers(jqxhr);
  }

  function appendTriggers(jqxhr, baseTriggerData) {
    return appendBaseTriggers(jqxhr, baseTriggerData)
      .always(function triggerFinish() {
        log.info('request completed');

        F.trigger(F.REQUEST_COMPLETE);
      });
  }

  /**
   * Used since multiple requests are count once.
   * @param jqxhr
   * @param baseTriggerData
   * @return {*}
   */
  function appendBaseTriggers(jqxhr, baseTriggerData) {
    return jqxhr
      .done(function triggerSuccess(response) {
        log.info('update completed', response.message);

        if (!baseTriggerData) {
          baseTriggerData = {};
        }

        response = getJsonResponse(response);

        baseTriggerData.message = response.message;
        F.trigger(F.UPDATE_CONTENT_COMPLETE, baseTriggerData);
      })
      .fail(function triggerError(jqXHR) {
        log.warn('request failed', jqXHR.responseText);

        F.trigger(F.REQUEST_ERROR, {
          message: jqXHR.responseText
        });
      });
  }

  function getJsonResponse(response) {
    if (typeof response === 'string') {
      try {
        response = JSON.parse(response);
      } catch (exception) {
        log.error(
          'response is no JSON.',
          response,
          exception
        );

        throw new TypeError(
          'response was not a JSON',
          response,
          exception
        );
      }
    }
    return response;
  }

  return FrontendEditing;
});
