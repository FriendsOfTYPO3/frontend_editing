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
 * FrontendEditing.GUI: Functionality related to the GUI and events listeners
 */
define([
  'jquery',
  './Crud',
  './Editor',
  './Notification',
  'TYPO3/CMS/Backend/Modal',
  './Utils/TranslatorLoader',
  './Utils/Logger'
], function (
  $,
  FrontendEditing,
  Editor,
  Notification,
  Modal,
  TranslatorLoader,
  Logger
) {
  'use strict';

  var log = Logger('FEditing:GUI');
  log.trace('--> createGuiModule');

  var translateKeys = {
    updatedContentTitle: 'notifications.save-title',
    updatedPageTitle: 'notifications.save-pages-title',
    updateRequestErrorTitle: 'notifications.save-went-wrong',
    saveWithoutChange: 'notifications.no-changes-description',
    saveWithoutChangeTitle: 'notifications.no-changes-title',
    confirmDiscardChanges: 'notifications.remove-all-changes'
  };

  var translator = TranslatorLoader.useTranslator('gui', function reload(t) {
    translateKeys = $.extend(translateKeys, t.getKeys());
  }).translator;

  function translate() {
    return translator.translate.apply(translator, arguments);
  }

  // Extend FrontendEditing with additional events
  var events = {};

  // Add custom events to FrontendEditing
  for (var key in events) {
    FrontendEditing.addEvent(key, events[key]);
  }

  // Extend FrontendEditing with the following functions
  FrontendEditing.prototype.initGUI = init;
  FrontendEditing.prototype.showLoadingScreen = showLoadingScreen;
  FrontendEditing.prototype.hideLoadingScreen = hideLoadingScreen;
  FrontendEditing.prototype.refreshIframe = refreshIframe;
  FrontendEditing.prototype.showSuccess = showSuccess;
  FrontendEditing.prototype.showError = showError;
  FrontendEditing.prototype.showWarning = showWarning;
  FrontendEditing.prototype.confirm = confirm;
  FrontendEditing.prototype.windowOpen = windowOpen;
  FrontendEditing.prototype.iframe = getIframe;
  FrontendEditing.prototype.initCustomLoadedContent = initCustomLoadedContent;
  FrontendEditing.prototype.save = save;
  FrontendEditing.prototype.discard = discard;
  FrontendEditing.prototype.toggleContentsToolbar = toggleContentsToolbar;
  FrontendEditing.prototype.toggleHiddenItems = toggleHiddenItems;

  var CLASS_HIDDEN = 'hidden';

  var pushDuration = 200;
  var pushEasing = 'linear';

  var $iframe;
  var $loadingScreen;
  var loadingScreenLevel = 0;
  var $saveButton;
  var $discardButton;
  var $toggleContentsToolbarButton;
  var $toggleHiddenItemsButton;
  var $contentsToolbar;
  var storage;
  var editorConfigurationUrl;
  var resourcePath;

  function init(options) {
    $iframe = $('#tx_frontendediting_iframe');
    $loadingScreen = $('.t3-frontend-editing__loading-screen');
    $saveButton = window.$('.t3-frontend-editing__save');
    $discardButton = window.$('.t3-frontend-editing__discard');
    $toggleContentsToolbarButton = window.$('.t3-frontend-editing__toggle-contents-toolbar');
    $toggleHiddenItemsButton = window.$('.t3-frontend-editing__show-hidden-items');
    $contentsToolbar = window.$('.t3-frontend-editing__content-elements-toolbar');
    editorConfigurationUrl = options.editorConfigurationUrl;
    resourcePath = options.resourcePath;

    initListeners();
    bindActions();
    initGuiStates();
    storage = F.getStorage();
  }

  function initListeners() {
    F.on(F.REQUEST_START, function () {
    });

    F.on(F.UPDATE_CONTENT_COMPLETE, function (data) {
      Notification.success(
        data.message,
        translate(translateKeys.updatedContentTitle)
      );
    });

    F.on(F.UPDATE_PAGES_COMPLETE, function (data) {
      Notification.success(
        data.message,
        translate(translateKeys.updatedPageTitle)
      );
    });

    F.on(F.REQUEST_ERROR, function (data) {
      Notification.error(
        data.message,
        translate(translateKeys.updateRequestErrorTitle)
      );
    });

    F.on(F.REQUEST_COMPLETE, function () {
      refreshIframe();
    });

    F.on(F.CONTENT_CHANGE, function (items) {
      var items = storage.getSaveItems();
      if (items.count()) {
        $saveButton.addClass('active');
        $discardButton.addClass('active');
      } else {
        $saveButton.removeClass('active');
        $discardButton.removeClass('active');
      }
    });

    getIframe().on('load', function () {
      initEditorInIframe(editorConfigurationUrl);

      // Hide contents toolbar if the user clicks somewhere inside the module body or the frontend iframe
      // use mousedown to work on drag start too for when the editor starts to drag a new CE
      $(this).contents().on('mousedown', function (event) {
        hideContentsToolbar(event);
      });
      $(this).parents('.module-body').on('mousedown', function (event) {
        if (!$(event.target).closest($contentsToolbar).length) { // Don't hide if click is in contents toolbar
          hideContentsToolbar(event);
        }
      });

      function hideContentsToolbar(event) {
        $toggleContentsToolbarButton.removeClass('module-button-active');
        $contentsToolbar.stop().animate({right: -325}, pushDuration, pushEasing);
      }
    });
  }

  function save() {
    if (!storage.isEmpty()) {
      F.saveAll();
    } else {
      Notification.warning(
        translate(translateKeys.saveWithoutChange),
        translate(translateKeys.saveWithoutChangeTitle)
      );
    }
  }

  function discard() {
    if (!storage.isEmpty()) {
      Modal.confirm(
        translate(translateKeys.confirmDiscardChanges),
        translate(translateKeys.confirmDiscardChanges)
      ).on('button.clicked', function (evt) {
        if (evt.target.name === 'ok') {
          storage.clear();
          F.refreshIframe();
          F.trigger(F.CONTENT_CHANGE);
        }
        Modal.dismiss();
      });
    }
  }

  function toggleContentsToolbar() {
    $toggleContentsToolbarButton.toggleClass('module-button-active');

    var right = 0;
    if (!$toggleContentsToolbarButton.hasClass('module-button-active')) {
      right = -325;
    }

    $contentsToolbar.stop().animate({right: right}, pushDuration, pushEasing);
  }

  function toggleHiddenItems() {
    $toggleHiddenItemsButton.toggleClass('module-button-active');

    var tmpIframeUrl = $iframe.attr('src');

    var $showHiddenItems = 0;
    if ($toggleHiddenItemsButton.hasClass('module-button-active')) {
      $showHiddenItems = 1;
    }

    // Remove the show_hidden_items parameter if present in the iFrame url
    // to add it eventually immediately after so to avoid multiple show_hidden_items parameters
    const urlObject = new URL(tmpIframeUrl);
    const params = new URLSearchParams(urlObject.search);
    params.delete('show_hidden_items');

    // If user wants to display hidden items then add the show_hidden_items=1 to the iFrame url
    if ($showHiddenItems) {
      params.append('show_hidden_items', '1');
    }

    urlObject.search = params.toString();
    tmpIframeUrl = urlObject.toString();

    // Loads the new url into the iFrame
    $iframe.attr({'src': tmpIframeUrl});
  }

  function bindActions() {
    $('.accordion .trigger, .accordion .element-title').on('click', function () {
      $(this).toggleClass('active');
      $(this).closest('.accordion-container').find('.accordion-content').slideToggle(pushDuration, pushEasing);
      updateContentElementsPanelState();
    });

    $('.accordion .grid').on('click', function () {
      $(this).closest('.accordion-container')
        .removeClass('accordion-list')
        .addClass('accordion-grid');
      updateContentElementsPanelState();
    });

    $('.accordion .list-view').on('click', function () {
      $(this).closest('.accordion-container')
        .removeClass('accordion-grid')
        .addClass('accordion-list');
      updateContentElementsPanelState();
    });
  }

  function initGuiStates() {
    var states = F.getStorage().getAllData();
    if (typeof states.contentElementsPanelState !== 'undefined') {
      // Init right panel state
      for (var wizard in states.contentElementsPanelState.wizards) {
        if (states.contentElementsPanelState.wizards.hasOwnProperty(wizard)) {
          var $wizard = $('[data-wizard-type="' + wizard + '"]');
          if ($wizard.length === 1) {
            if (states.contentElementsPanelState.wizards[wizard].isListView) {
              $wizard.find('.list-view').trigger('click');
            }
            if (states.contentElementsPanelState.wizards[wizard].isExpanded) {
              $wizard.find('.trigger').trigger('click');
            }
          }
        }
      }
    }
  }

  function updateContentElementsPanelState() {
    var contentElementsPanelState = {
      isVisible: $contentsToolbar.hasClass('open'),
      wizards: {}
    };

    $('.accordion .trigger').each(function () {
      var accordionContainer = $(this).parents('.accordion-container'),
        isExpanded = $(this).hasClass('active'),
        isListView = accordionContainer.hasClass('accordion-list');

      if (isExpanded || isListView) {
        var containerType = accordionContainer.data('wizard-type');
        contentElementsPanelState.wizards[containerType] = {
          isExpanded: isExpanded,
          isListView: isListView
        }
      }
    });

    F.getStorage().addItem('contentElementsPanelState', contentElementsPanelState);
  }

  function initEditorInIframe(editorConfigurationUrl) {
    // check if LocalStorage contains any changes prior to iframe reload
    var items = storage.getSaveItems();

    if (items.count()) {
      items.forEach(function (item) {
        var content;
        var isInlineElement = item.inlineElement || false;

        if (isInlineElement) {
          content = item.text;
        } else if (item.hasCkeditorConfiguration) {
          content = CKEDITOR.instances[item.editorInstance].getData();
        } else {
          content = CKEDITOR.instances[item.editorInstance].editable().getText();
        }

        // if match is found, replace content in iframe with LocalStorage
        $iframe.contents().find('[contenteditable=\'true\']').each(function () {
          var dataSet = $(this).data();
          if ((dataSet.uid == item.uid) && (dataSet.field == item.field) && (dataSet.table == item.table)) {
            $(this).html(content);
          }
        });
      });
    }

    Editor.init($iframe, editorConfigurationUrl, resourcePath);

    if (sessionStorage.scrollTop !== 'undefined') {
      $iframe.contents().scrollTop(sessionStorage.scrollTop);
      sessionStorage.removeItem('scrollTop');
    }

    hideLoadingScreen();
  }

  function initCustomLoadedContent(customElement) {
    Editor.init(customElement, editorConfigurationUrl, resourcePath);
  }

  function refreshIframe() {
    sessionStorage.scrollTop = $iframe.contents().scrollTop();

    // Reload the iframe
    $iframe.attr('src', function (i, val) {
      return val;
    });
  }

  function showLoadingScreen() {
    if (loadingScreenLevel === 0) {
      $loadingScreen.fadeIn('fast', function () {
        $loadingScreen.removeClass(CLASS_HIDDEN);
      });
    }

    loadingScreenLevel++;
  }

  function hideLoadingScreen() {
    loadingScreenLevel--;

    if (loadingScreenLevel <= 0) {
      loadingScreenLevel = 0;
      $loadingScreen.fadeOut('slow', function () {
        $loadingScreen.addClass(CLASS_HIDDEN);
      });
    }
  }

  function getIframe() {
    return $iframe;
  }

  /**
   * Shows a success notification
   * @param message
   * @param title
   * @deprecated use TYPO3/CMS/FrontendEditing/Notification instead
   */
  function showSuccess(message, title) {
    Notification.success(message, title);
  }

  /**
   * Shows a error notification
   * @param message
   * @param title
   * @deprecated use TYPO3/CMS/FrontendEditing/Notification instead
   */
  function showError(message, title) {
    Notification.error(message, title);
  }

  /**
   * Shows a warning notification
   * @param message
   * @param title
   * @deprecated use TYPO3/CMS/FrontendEditing/Notification instead
   */
  function showWarning(message, title) {
    Notification.warning(message, title);
  }

  function windowOpen(url) {
    var vHWin = window.open(url, 'FEquickEditWindow', 'width=690,height=500,status=0,menubar=0,scrollbars=1,resizable=1');
    vHWin.focus();
    return false;
  }

  return FrontendEditing;
});
