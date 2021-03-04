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
 * FrontendEditing: The foundation for the frontend editing interactions
 */
define([
    'jquery',
    'TYPO3/CMS/FrontendEditing/Storage',
    'TYPO3/CMS/FrontendEditing/Scroller',
    'TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader',
    'TYPO3/CMS/FrontendEditing/Modal'
], function createFrontendEditing (
    $,
    Storage,
    Scroller,
    TranslatorLoader,
    Modal
) {
    'use strict';

    var translateKeys = {
        confirmNavigateWithChange: 'notifications.unsaved-changes',
    };

    var t = TranslatorLoader
        .useTranslator('frontendEditing', function reload (t) {
            translateKeys = $.extend(translateKeys, t.getKeys());
        }).translator;


  // Hold event listeners and the callbacks
  var listeners = {};

  // LocalStorage for changes that are to be saved
  var storage = null;

  // JSON object holding key => label for labels
  var translationLabels = {};

  // Disable the modal pop-up when a new content element is created
  var disableModalOnNewCe = false;

  // Default for event-listening and triggering
  var events = {
    CONTENT_CHANGE: 'CONTENT_CHANGE'
  };

  var FrontendEditing = function (options) {
    this.init(options);

    // Assign every event to the FrontendEditing class for API use
    for (var key in events) {
      this[key] = events[key];
    }
  };

  // TimeoutId used in indicateCeStart()
  var indicateCeScrollTimeoutId = null;

  // Add default events and a function to add other events
  FrontendEditing.events = events;
  FrontendEditing.addEvent = function (key, value) {
    FrontendEditing.events[key] = value;
  };

    // Scroll function when dragging content elements to top or bottom of window
    var scrollSpeed = 4;
    var $iframe = $('iframe');
    var $iframeWrapper = $('.t3-frontend-editing__iframe-wrapper');
    var scrollAreaBaseClasses = 'scrollarea scrollarea--arrow';

    var $scrollAreaTop = $iframeWrapper.find('.scrollarea-top');
    if (!$scrollAreaTop) {
        $scrollAreaTop = $('<div/>')
            .addClass(scrollAreaBaseClasses)
            .addClass('scrollarea-top scrollarea--arrow-up')
            .hide()
            .insertAfter($iframe);
    }
    var $scrollAreaBottom = $iframeWrapper.find('.scrollarea-bottom');
    if (!$scrollAreaBottom) {
        $scrollAreaBottom = $('<div/>')
            .addClass(scrollAreaBaseClasses)
            .addClass('scrollarea-bottom scrollarea--arrow-down')
            .hide()
            .insertAfter($iframe);
    }

    var scroller = Scroller($iframe, $scrollAreaTop, $scrollAreaBottom);

    function stopScrolling () {
        $(this)
            .removeClass('scrollarea--arrow__mouseover');
        scroller.stopScrolling();
    }

    $scrollAreaTop
        .on('dragleave', stopScrolling)
        .on('dragenter', function startScrollUp () {
            $scrollAreaTop.addClass('scrollarea--arrow__mouseover');
            scroller.startScrolling(-scrollSpeed);
        });
    $scrollAreaBottom
        .on('dragleave', stopScrolling)
        .on('dragenter', function startScrollDown () {
            $scrollAreaBottom.addClass('scrollarea--arrow__mouseover');
            scroller.startScrolling(scrollSpeed);
        });

    $('[draggable]')
        .on('dragstart', scroller.enable)
        .on('dragend', scroller.disable);

  // Public API
  FrontendEditing.prototype = {
    init: function (options) {
      // Create an array of listeners for every event and assign it to the instance
      for (var key in FrontendEditing.events) {
        listeners[events[key]] = [];
        this[key] = events[key];
      }

      // LocalStorage for changes in editors
      storage = new Storage('TYPO3:FrontendEditing');
    },

    error: function (message) {
      console.error(message);
    },

    trigger: function (event, data) {
      if (!listeners[event]) {
        this.error('Invalid event', event);
        return;
      }
      for (var i = 0; i < listeners[event].length; i++) {
        listeners[event][i](data);
      }
    },

    getStorage: function () {
      return storage;
    },

    confirm: function (message, callbacks) {
      var confirmed = confirm(message);

      callbacks = callbacks || {};
      if (confirmed && typeof callbacks.yes === 'function') {
        callbacks.yes();
      }
      if (!confirmed && typeof callbacks.no === 'function') {
        callbacks.no();
      }
    },

    on: function (event, callback) {
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

        navigate: function (linkUrl) {
            if (linkUrl && linkUrl.indexOf('#') !== 0) {
                if (this.getStorage().isEmpty()) {
                    window.location.href = linkUrl;
                } else {
                    Modal.confirmNavigate(
                        t.translate(translateKeys.confirmNavigateWithChange),
                        function save () {
                            F.saveAll();
                            window.location.href = linkUrl;
                        },
                        {
                            yes: function () {
                                window.location.href = linkUrl;
                            },
                            no: function () {
                                F.hideLoadingScreen();
                            }
                        });
                }
            }
        },

    loadInModal: function (url) {
      require([
        'jquery',
        'TYPO3/CMS/Backend/Modal'
        ], function ($, Modal) {

        Modal.advanced({
          type: Modal.types.iframe,
          title: '',
          content: url,
          size: Modal.sizes.large,
          callback: function(currentModal) {
            var modalIframe = currentModal.find(Modal.types.iframe);
            modalIframe.attr('name', 'list_frame');
            modalIframe.on('load', function () {
              $.extend(window.TYPO3, modalIframe[0].contentWindow.TYPO3 || {});
            });

            currentModal.on('hidden.bs.modal', function (e) {
              F.refreshIframe();
            });
          }
        });
      });
    },

    setTranslationLabels: function (labels) {
        TranslatorLoader.configure({
            translationLabels: labels
        });
    },

    setDisableModalOnNewCe: function (disable) {
      disableModalOnNewCe = disable;
    },

        /**
         * Used to get translated strings by key.
         * @deprecated Use TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader or
         * a high level function.
         * @param key
         * @returns {*}
         */
        translate: function (key) {
            try {
                var translator = TranslatorLoader.getTranslator();
                return translator.translate.apply(translator, arguments);
            } catch (exception) {
                F.error(exception.toString());
            }
            return key;
        },

    parseQuery: function (queryString) {
      var query = {};
      var a = (queryString[0] === '?' ? queryString.substr(1) : queryString).split('&');
      for (var i = 0; i < a.length; i++) {
        var b = a[i].split('=');
        query[decodeURIComponent(b[0])] = decodeURIComponent(b[1] || '');
      }
      return query;
    },

    serializeObj: function (obj) {
      var str = [];
      for (var p in obj)
        if (obj.hasOwnProperty(p)) {
          str.push(encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
        }
      return str.join('&');
    },

    dragCeStart: function (ev) {
      ev.stopPropagation();
      var movable = parseInt(ev.currentTarget.dataset.movable, 10);

      ev.dataTransfer.setData('params', ev.currentTarget.dataset.params);
      ev.dataTransfer.setData('movable', movable);
      ev.dataTransfer.setData('movableUid', $(ev.currentTarget).find('span.t3-frontend-editing__inline-actions').data('uid'));

      if (movable === 1) {
        var $currentTarget = $(ev.currentTarget);

        $currentTarget.prev('.t3-frontend-editing__dropzone').addClass('t3-frontend-editing__dropzone-hidden');
        $currentTarget.next('.t3-frontend-editing__dropzone').addClass('t3-frontend-editing__dropzone-hidden');
      }

      var $iframe = F.iframe();
      $iframe.contents().find('.t3-frontend-editing__dropzone[data-tables]').addClass('t3-frontend-editing__dropzone-hidden');
      $iframe.contents().find('body').addClass('dropzones-enabled');
    },

    dragCeEnd: function (ev) {
      ev.stopPropagation();
      var movable = parseInt(ev.currentTarget.dataset.movable, 10);

      if (movable === 1) {
        var $currentTarget = $(ev.currentTarget);

        $currentTarget.prev('.t3-frontend-editing__dropzone').removeClass('t3-frontend-editing__dropzone-hidden');
        $currentTarget.next('.t3-frontend-editing__dropzone').removeClass('t3-frontend-editing__dropzone-hidden');
      }

      var $iframe = F.iframe();
      $iframe.contents().find('.t3-frontend-editing__dropzone[data-tables]').removeClass('t3-frontend-editing__dropzone-hidden');
      $iframe.contents().find('body').removeClass('dropzones-enabled');
    },

    dragCeOver: function (ev) {
      ev.preventDefault();
      $(ev.currentTarget).addClass('active');
    },

    dragCeLeave: function (ev) {
      ev.preventDefault();
      $(ev.currentTarget).removeClass('active');
    },

    dropCe: function (ev) {
      ev.preventDefault();
      var movable = parseInt(ev.dataTransfer.getData('movable'), 10);

      if (movable === 1) {
        var $currentTarget = $(ev.currentTarget);
        var ceUid = parseInt(ev.dataTransfer.getData('movableUid'), 10);
        var moveAfter = parseInt($currentTarget.data('moveafter'), 10);
        var colPos = parseInt($currentTarget.data('colpos'), 10);
        var defVals = $currentTarget.data('defvals');

        if (ceUid !== moveAfter) {
          F.moveContent(ceUid, 'tt_content', moveAfter, colPos, defVals);
        }
      } else {
        this.dropNewCe(ev);
      }
    },

    dropNewCe: function (ev) {
      // Merge drop zone query string with new CE query string without override
      var newUrlParts = $(ev.currentTarget).data('new-url').split('?');
      var newUrlQueryStringObj = F.parseQuery(newUrlParts[1]);
      var paramsObj = F.parseQuery(ev.dataTransfer.getData('params').substr(1));
      var fullUrlObj = {};
      $.extend(true, fullUrlObj, paramsObj, newUrlQueryStringObj);
      var fullUrlQueryString = F.serializeObj(fullUrlObj);
      if (disableModalOnNewCe) {
        F.newContent(fullUrlQueryString);
          } else {
            F.loadInModal(newUrlParts[0] + '?' + fullUrlQueryString);
          }
    },

    indicateCeStart: function (ev) {
      var $iframe = F.iframe();
      var uid = ev.currentTarget.dataset.uid;
      $iframe.contents().find('#c' + uid).parent().addClass('indicate-element');
      window.clearTimeout(indicateCeScrollTimeoutId);

      indicateCeScrollTimeoutId = window.setTimeout(function () {
        var $iframe = F.iframe();
        $iframe.contents().find('body, html').animate(
          {
            scrollTop: (
              $iframe.contents().find('#c' + uid).parent().offset()
                ? $iframe.contents().find('#c' + uid).parent().offset().top - 10 : $iframe.contents().find('#c' + uid).parent().offset()
            )
          },
          500
        );
      }, 1000);
    },

    indicateCeEnd: function (ev) {
      var $iframe = F.iframe();
      $iframe.contents().find('#c' + ev.currentTarget.dataset.uid).parent().removeClass('indicate-element');
      window.clearTimeout(indicateCeScrollTimeoutId);
    },

    dropCr: function (ev) {
      ev.preventDefault();
      var url = ev.dataTransfer.getData('new-url');
      if(!ev.dataTransfer.getData('table') && url){
        return false;
      }
      var pageUid = parseInt($(ev.currentTarget).data('pid'), 10) || 0;
      if (pageUid > 0) {
        // TODO: Find a better solution than simply replace in URL
        url = url.replace(/%5D%5B\d+%5D=new/, '%5D%5B' + pageUid + '%5D=new');
      }
      try{
        var defaultValues = $(ev.currentTarget).data('defvals');
        var newUrlParts = url.split('?');
        var newUrlQueryStringObj = F.parseQuery(newUrlParts[1]);
        var fullUrlObj = {};
        $.extend(true, fullUrlObj, defaultValues, newUrlQueryStringObj);
        var fullUrlQueryString = F.serializeObj(fullUrlObj);
        F.loadInModal(newUrlParts[0] + '?' + fullUrlQueryString);
      } catch(e) {
        F.loadInModal(url);
      }
    },

    dragCrStart: function (ev) {
      ev.stopPropagation();
      var table = ev.currentTarget.dataset.table;
      var $iframe = F.iframe();

      ev.dataTransfer.setData('table', table);
      ev.dataTransfer.setData('new-url', ev.currentTarget.dataset.url);

      $iframe.contents().find('.t3-frontend-editing__dropzone').not('[data-tables~="' + table + '"]').addClass('t3-frontend-editing__dropzone-hidden');
      $iframe.contents().find('body').addClass('dropzones-enabled');
    },

    dragCrEnd: function (ev) {
      ev.stopPropagation();
      var table = ev.currentTarget.dataset.table;
      var $iframe = F.iframe();

      $iframe.contents().find('.t3-frontend-editing__dropzone').not('[data-tables~="' + table + '"]').removeClass('t3-frontend-editing__dropzone-hidden');
      $iframe.contents().find('body').removeClass('dropzones-enabled');
    }
  };

  return FrontendEditing;
});
