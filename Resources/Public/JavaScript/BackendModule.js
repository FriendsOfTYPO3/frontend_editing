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
 * Module: TYPO3/CMS/FrontendEditing/BackendModule
 * Main logic for resizing the view of the frame
 */
define([
  'require',
  'exports',
  'jquery',
  'TYPO3/CMS/Backend/Storage/Persistent',
  'jquery-ui/resizable'
], function(require, exports, $, PersistentStorage) {
  'use strict';

  /**
   * @type {{resizableContainerIdentifier: string, moduleBodySelector: string, storagePrefix: string, $iframe: null, $resizableContainer: null}}
   * @exports TYPO3/CMS/FrontendEditing/BackendModule
   */
  var FrontedEditing = {

    resizableContainerIdentifier: '.t3js-frontendediting-resizeable',
    moduleBodySelector: '.t3js-module-body',

    defaultLabel: 'Custom',
    minimalHeight: 300,
    minimalWidth: 300,

    storagePrefix: 'moduleData.web_view.States.',
    $iframe: null,
    $resizableContainer: null,

    customSelector: '.t3js-preset-custom',

    saveAllSelector: '.t3-frontend-editing__save',

    discardSelector: '.t3-frontend-editing__discard',

    toggleContentsToolbarSelector: '.t3-frontend-editing__toggle-contents-toolbar',

    hiddenItemsToggleButtonSelector: '.t3-frontend-editing__show-hidden-items',

    changePresetSelector: '.t3js-change-preset',

    inputWidthSelector: '.t3js-frontendediting-input-width',
    inputHeightSelector: '.t3js-frontendediting-input-height',

    currentButtonSelector: '.t3js-preset-current',
    currentLabelSelector: '.t3js-frontendediting-current-label',

    queue: [],
    queueIsRunning: false,
    queueDelayTimer: null

  };

  FrontedEditing.persistQueue = function() {
    if (FrontedEditing.queueIsRunning === false && FrontedEditing.queue.length >= 1) {
      FrontedEditing.queueIsRunning = true;
      var item = FrontedEditing.queue.shift();
      PersistentStorage.set(item.storageIdentifier, item.data).done(function() {
        FrontedEditing.queueIsRunning = false;
        FrontedEditing.persistQueue();
      });
    }
  }

  FrontedEditing.addToQueue = function(storageIdentifier, data) {
    var item = {
      'storageIdentifier': storageIdentifier,
      'data': data
    };
    FrontedEditing.queue.push(item);
    if (FrontedEditing.queue.length >= 1) {
      FrontedEditing.persistQueue();
    }
  }

  FrontedEditing.setSize = function(width, height) {
    if (isNaN(width) || isNaN(height)) {
      // Both width and height not set => go full size
      $(FrontedEditing.currentButtonSelector).removeData('width');
      $(FrontedEditing.currentButtonSelector).removeData('height')

      FrontedEditing.$resizableContainer.css({
        width: '100%',
        height: '100%',
        left: 0
      });

      $(FrontedEditing.inputWidthSelector).val('');
      $(FrontedEditing.inputHeightSelector).val('');
    } else {
      if (isNaN(width)) {
        width = FrontedEditing.calculateContainerMaxWidth();
      }
      if (width < FrontedEditing.minimalWidth) {
        width = FrontedEditing.minimalWidth;
      }
      if (isNaN(height)) {
        height = FrontedEditing.calculateContainerMaxHeight();
      }
      if (height < FrontedEditing.minimalHeight) {
        height = FrontedEditing.minimalHeight;
      }

      $(FrontedEditing.currentButtonSelector).data('width', width);
      $(FrontedEditing.currentButtonSelector).data('height', height);

      $(FrontedEditing.inputWidthSelector).val(width);
      $(FrontedEditing.inputHeightSelector).val(height);

      FrontedEditing.$resizableContainer.css({
        width: width,
        height: height,
        left: 0
      });
    }
  }

  FrontedEditing.getCurrentWidth = function() {
    return $(FrontedEditing.inputWidthSelector).val();
  }

  FrontedEditing.getCurrentHeight = function() {
    return $(FrontedEditing.inputHeightSelector).val();
  }

  FrontedEditing.setLabel = function(label) {
    $(FrontedEditing.currentButtonSelector).data('label', label);
    $(FrontedEditing.currentLabelSelector).html(label);
  }

  FrontedEditing.getCurrentLabel = function() {
    return $(FrontedEditing.currentLabelSelector).html().trim();
  }

  FrontedEditing.persistCurrentPreset = function() {
    var data = {
      width: FrontedEditing.getCurrentWidth(),
      height: FrontedEditing.getCurrentHeight(),
      label: FrontedEditing.getCurrentLabel()
    }
    FrontedEditing.addToQueue(FrontedEditing.storagePrefix + 'current', data);
  }

  FrontedEditing.persistCustomPreset = function() {
    var data = {
      width: FrontedEditing.getCurrentWidth(),
      height: FrontedEditing.getCurrentHeight()
    }
    $(FrontedEditing.customSelector).data("width", data.width);
    $(FrontedEditing.customSelector).data("height", data.height);

    var newCustomLabel = 'Custom (' + data.width + 'x' + data.height + ')';
    $(FrontedEditing.customSelector).attr("title", newCustomLabel);
    $(FrontedEditing.customSelector).contents().filter(function(){ return this.nodeType === 3; }).first().replaceWith(newCustomLabel);

    FrontedEditing.addToQueue(FrontedEditing.storagePrefix + 'current', data);
    FrontedEditing.addToQueue(FrontedEditing.storagePrefix + 'custom', data);
  }

  FrontedEditing.persistCustomPresetAfterChange = function() {
    clearTimeout(FrontedEditing.queueDelayTimer);
    FrontedEditing.queueDelayTimer = setTimeout(function() {
      FrontedEditing.persistCustomPreset();
    }, 1000);
  };

  /**
   * Initialize
   */
  FrontedEditing.initialize = function() {
    // Mark current preset button (main split btn) label as currentLabelSelector
    $(FrontedEditing.currentButtonSelector).contents().eq(2).wrap('<span class="t3js-frontendediting-current-label"/>');

    FrontedEditing.$iframe = $('#tx_frontendediting_iframe');
    FrontedEditing.$resizableContainer = $(FrontedEditing.resizableContainerIdentifier);

    // Set current preset button data with current state
    $(FrontedEditing.currentButtonSelector).data('width', $(FrontedEditing.inputWidthSelector).val());
    $(FrontedEditing.currentButtonSelector).data('height', $(FrontedEditing.inputHeightSelector).val());

    // Save All button
    $(document).on('click', FrontedEditing.saveAllSelector, function() {
      // Use the save function in TYPO3/CMS/FrontendEditing/GUI
      F.save();
    });

    // Discard button
    $(document).on('click', FrontedEditing.discardSelector, function() {
      // Use the discard function in TYPO3/CMS/FrontendEditing/GUI
      F.discard();
    });

    // Toggle contents toolbar button
    $(document).on('click', FrontedEditing.toggleContentsToolbarSelector, function() {
      // Use the toggleContentsToolbar function in TYPO3/CMS/FrontendEditing/GUI
      F.toggleContentsToolbar();
    });

    // Toggle hidden items button
    $(document).on('click', FrontedEditing.hiddenItemsToggleButtonSelector, function() {
      // Use the toggleHiddenItems function in TYPO3/CMS/FrontendEditing/GUI
      F.toggleHiddenItems();
    });

    // On change
    $(document).on('change', FrontedEditing.inputWidthSelector, function() {
      var width = $(FrontedEditing.inputWidthSelector).val();
      var height = $(FrontedEditing.inputHeightSelector).val();
      FrontedEditing.setSize(width, height);
      FrontedEditing.setLabel(FrontedEditing.defaultLabel);
      FrontedEditing.persistCustomPresetAfterChange();
    });

    $(document).on('change', FrontedEditing.inputHeightSelector, function() {
      var width = $(FrontedEditing.inputWidthSelector).val();
      var height = $(FrontedEditing.inputHeightSelector).val();
      FrontedEditing.setSize(width, height);
      FrontedEditing.setLabel(FrontedEditing.defaultLabel);
      FrontedEditing.persistCustomPresetAfterChange();
    });

    // Add event to width selector so the container is resized
    $(document).on('click', FrontedEditing.changePresetSelector, function() {
      var data = $(this).data();
      $(FrontedEditing.currentButtonSelector).data('key', data.key)
      FrontedEditing.setSize(parseInt(data.width), parseInt(data.height));
      FrontedEditing.setLabel(data.label);
      FrontedEditing.persistCurrentPreset();
    });

    // Initialize the jQuery UI Resizable plugin
    FrontedEditing.$resizableContainer.resizable({
      handles: 'w, sw, s, se, e'
    });

    FrontedEditing.$resizableContainer.on('resizestart', function() {
      // Add iframe overlay to prevent losing the mouse focus to the iframe while resizing fast
      $(this).append('<div id="FrontedEditing-iframe-cover" style="z-index:99;position:absolute;width:100%;top:0;left:0;height:100%;"></div>');
    });

    FrontedEditing.$resizableContainer.on('resize', function(evt, ui) {
      ui.size.width = ui.originalSize.width + ((ui.size.width - ui.originalSize.width) * 2);
      if (ui.size.height < FrontedEditing.minimalHeight) {
        ui.size.height = FrontedEditing.minimalHeight;
      }
      if (ui.size.width < FrontedEditing.minimalWidth) {
        ui.size.width = FrontedEditing.minimalWidth;
      }
      $(FrontedEditing.inputWidthSelector).val(ui.size.width);
      $(FrontedEditing.inputHeightSelector).val(ui.size.height);
      FrontedEditing.$resizableContainer.css({
        left: 0
      });
      FrontedEditing.setLabel(FrontedEditing.defaultLabel);
    });

    FrontedEditing.$resizableContainer.on('resizestop', function() {
      $('#FrontedEditing-iframe-cover').remove();
      FrontedEditing.persistCurrentPreset();
      FrontedEditing.persistCustomPreset();
    });
  };

  /**
   * @returns {Number}
   */
  FrontedEditing.calculateContainerMaxHeight = function() {
    FrontedEditing.$resizableContainer.hide();
    var $moduleBody = $(FrontedEditing.moduleBodySelector);
    var padding = $moduleBody.outerHeight() - $moduleBody.height(),
      documentHeight = $(document).height();
    FrontedEditing.$resizableContainer.show();
    return documentHeight - padding - 8;
  };

  /**
   * @returns {Number}
   */
  FrontedEditing.calculateContainerMaxWidth = function() {
    FrontedEditing.$resizableContainer.hide();
    var $moduleBody = $(FrontedEditing.moduleBodySelector);
    var padding = $moduleBody.outerWidth() - $moduleBody.width(),
      documentWidth = $(document).width();
    FrontedEditing.$resizableContainer.show();
    return parseInt(documentWidth - padding);
  };

  /**
   * @param {String} url
   * @returns {{}}
   */
  FrontedEditing.getUrlVars = function(url) {
    var vars = {};
    var hash;
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
      hash = hashes[i].split('=');
      vars[hash[0]] = hash[1];
    }
    return vars;
  };

  $(FrontedEditing.initialize);

  return FrontedEditing;
});
