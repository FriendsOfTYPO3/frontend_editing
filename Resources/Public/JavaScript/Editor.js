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
 * Editor: used in iframe for DOM interaction
 */
define([
  'jquery',
  './Utils/TranslatorLoader',
  './Notification',
  './Utils/Logger',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity',
], function (
  $,
  TranslatorLoader,
  Notification,
  Logger,
  Modal,
  Severity
) {
  'use strict';

  var log = Logger('FEditing:Editor');
  log.trace('--> createEditorControl');

  var translateKeys = {
    confirmOpenModalWithChangeTitle: 'notifications.unsaved-changes-title',
    confirmOpenModalWithChangeMessage: 'notifications.unsaved-changes-message',
    confirmOpenModalSaveChanges: 'notifications.unsaved-changes-save-and-go-ahead',
    confirmOpenModalDiscardChanges: 'notifications.unsaved-changes-discard-changes',
    confirmOpenModalAbort: 'notifications.unsaved-changes-continue-editing',
    confirmDeleteContentElement: 'notifications.delete-content-element',
    informRequestFailed: 'notifications.request.configuration.fail'
  };

  var translator = TranslatorLoader
    .useTranslator('editor', function reload (t) {
      translateKeys = $.extend(translateKeys, t.getKeys());
    }).translator;

  function translate () {
    return translator.translate.apply(translator, arguments);
  }

  var defaultEditorConfig = {
    'skin': 'moono',
    'entities_latin': false,
    'htmlEncodeOutput': false,
    'allowedContent': true,
    'customConfig': '',
    'stylesSet': [],
    'autoParagraph': false
  };

  /**
   * Default simple toolbar for non CKEditor (RTE) field
   *
   * @type {{toolbarGroups: [*]}}
   */
  var defaultSimpleEditorConfig = {
    toolbarGroups: [
      {
        'name': 'clipboard',
        'groups': ['clipboard', 'undo']
      }
    ]
  };

  function init($iframe, configurationUrl, resourcePath) {
    // Storage for adding and checking if it's empty when navigating to other pages
    var storage = F.getStorage();

    // The content in the iframe will be used many times
    var $iframeContents = $iframe.contents();

    // Include Inline editing styles after iframe has loaded
    $iframeContents.find('head').append(
      $(
        '<link/>',
        {
          rel: 'stylesheet',
          href: resourcePath + 'Css/inline_editing.css',
          type: 'text/css'
        }
      ).on('load', function () {
        // Dinamically add a spacer if the inline actions toolbar is hidden under the module doc header
        // this could happen if there's not enough space between the top of the page and the first editable element
        const feIframe = document.getElementById('tx_frontendediting_iframe');
        const iframeDocument = feIframe.contentDocument;
        const firstInlineActionsToolbar = iframeDocument.querySelector('.t3-frontend-editing__inline-actions');
        if (firstInlineActionsToolbar) {
          firstInlineActionsToolbar.style.display = 'block'; // Tmp set display block or else the toolbar has height=0
          const toolbarTopYPosition = window.pageYOffset + firstInlineActionsToolbar.getBoundingClientRect().top;
          const toolbarHeight = firstInlineActionsToolbar.offsetHeight;
          firstInlineActionsToolbar.style.display = 'none'; // Reset toolbar display to none
          if (toolbarTopYPosition < toolbarHeight) {
            // Create a spacer element
            const spacer = iframeDocument.createElement('div');
            spacer.classList.add('t3-frontend-editing__spacer');
            spacer.style.height = -toolbarTopYPosition + 'px';
            // Insert the spacer as the first element inside the body of the iframe
            iframeDocument.body.insertBefore(spacer, iframeDocument.body.firstChild);
          }
        }
      })
    );

    // Links in editable CE are not navigable directly on click.
    // This is made to allow the editor to edit the link text.
    // To navigate, the editor must hold down CTRL when clicking on a link.
    // To maintain uniform behavior even in non-editable content,
    // the editor must still hold down CTRL to navigate.
    $iframeContents.find('a').click(function (event) {
      if (!event.ctrlKey) {
        event.preventDefault();
        // Don't display notification if link is a placeholder link
        if (!(this.getAttribute('href').includes('#') || this.href === "javascript:void(0)")) {
          Notification.info(
            translate(translateKeys.informNavigateWithCtrl)
          );
        }
        return;
      }

      var linkUrl = $(this).attr('href');
      if (!event.isDefaultPrevented() && linkUrl.indexOf('#') !== 0) {
        event.preventDefault();
        F.navigate(linkUrl);
      }
    });

    // Find all t3-frontend-editing__inline-actions
    var $inlineActions = $iframeContents.find('span.t3-frontend-editing__inline-actions');
    var initializeInlineAction = function (index) {
      var that = $(this);
      if (!that.data('t3-frontend-editing-initialized')) {

        // Disable dragging icons by mistake
        that.find('img').on('dragstart', function () {
          return false;
        });

        // Open/edit action
        that.find('.icon-actions-open').on('click', function () {
          if (storage.isEmpty()) {
            openModal();
          } else {
            Modal.confirm(
              translate(translateKeys.confirmOpenModalWithChangeTitle),
              translate(translateKeys.confirmOpenModalWithChangeMessage),
              Severity.warning,
              [
                {
                  text: translate(translateKeys.confirmOpenModalAbort) || 'No',
                  btnClass: 'btn-default',
                  name: 'no'
                },
                {
                  text: translate(translateKeys.confirmOpenModalDiscardChanges)  || 'Yes',
                  btnClass: 'btn-default',
                  name: 'yes'
                },
                {
                  text: translate(translateKeys.confirmOpenModalSaveChanges)  || 'Save',
                  btnClass: 'btn-warning',
                  name: 'save'
                }
              ]
            ).on('button.clicked', function(evt) {
              if (evt.target.name === 'save') {
                F.saveAll();
                openModal();
              } else if (evt.target.name === 'yes') {
                openModal();
              }
              Modal.dismiss();
            });
          }

          function openModal() {
            require([
              'jquery',
              'TYPO3/CMS/Backend/Modal',
              'TYPO3/CMS/Backend/Toolbar/ShortcutMenu'
            ], function ($, Modal, ShortcutMenu ) {

              Modal.advanced({
                type: Modal.types.iframe,
                title: '',
                content: that.data('edit-url'),
                size: Modal.sizes.large,
                callback: function (currentModal) {
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
          }

        });

        // Delete action
        that.find('.icon-actions-edit-delete')
          .on('click', function () {

            Modal.confirm(
              translate(
                translateKeys.confirmDeleteContentElement
              ),
              translate(
                translateKeys.confirmDeleteContentElement
              )
            ).on('button.clicked', function(evt) {
              if (evt.target.name === 'ok') {
                F.delete(
                  that.data('uid'),
                  that.data('table')
                );
              }
              Modal.dismiss();
            });
          });

        // Hide/Unhide action
        that.find('.icon-actions-edit-hide, .icon-actions-edit-unhide').on('click', function () {
          var hide = 1;
          if (that.data('hidden') == 1) {
            hide = 0;
          }
          F.hideContent(that.data('uid'), that.data('table'), hide);
        });

        that.data('t3-frontend-editing-initialized', true);
      }
    };
    $inlineActions.each(initializeInlineAction);
    // Make sure that elements inserted after page initialization are initialized.
    $iframeContents.on('mouseover', 'span.t3-frontend-editing__inline-actions', function () {
      // TODO: Find a clean solution to update move up/move down action
      // Index is set to -10 to make sure both action are hidden
      initializeInlineAction.call(this, [-10]);
    });


    var $ckeditorBar = $('.t3-frontend-editing__ckeditor-bar'),
        $ckeditorBarWrapper = $('.t3-frontend-editing__ckeditor-bar__wrapper');

    // Move CKEditor bar inside module doc header
    // to make CKEditor bar overlap module doc header buttons when the editor edits a content element
    // TODO: find a way to place the bar inside doc header in FrontendEditingModuleController (not possible AFAIK)
    $ckeditorBarWrapper.appendTo('div.module-docheader');

    // Add custom configuration to ckeditor
    var $contenteditable = $iframeContents.find('[contenteditable=\'true\']');
    var configurableEditableElements = [];
    $contenteditable.each(function () {
      var $el = $(this);

      if (!CKEDITOR.dtd.$editable[$el.prop('tagName').toLowerCase()]) {
        CKEDITOR.dtd.$editable[$el.prop('tagName').toLowerCase()] = 1;
      }

      configurableEditableElements.push(this);
    });

    var requestData = [];
    $(configurableEditableElements).each(function() {
      requestData.push({
        'table': $(this).data('table'),
        'uid': $(this).data('uid'),
        'field': $(this).data('field')
      });
    });

    if (requestData.length > 0) {
      F.showLoadingScreen();
      $.ajax({
        url: configurationUrl,
        method: 'POST',
        dataType: 'json',
        data: {
          'elements': requestData
        }
      }).done(function (data) {
        $(configurableEditableElements).each(function() {
          var elementIdentifier = $(this).data('uid') + '_' + $(this).data('table') + '_' + $(this).data('field');

          var elementData = data.configurations[data.elementToConfiguration[elementIdentifier]];

          if (typeof elementData === 'undefined') {
            return;
          }

          // Ensure all plugins / buttons are loaded
          if (typeof elementData.externalPlugins !== 'undefined') {
            eval(elementData.externalPlugins);
          }

          var config = {};
          if (elementData.hasCkeditorConfiguration) {
            $.extend(true, config, defaultEditorConfig, elementData.configuration);
          } else {
            $.extend(true, config, defaultEditorConfig, elementData.configuration, defaultSimpleEditorConfig);
          }

          // Initialize CKEditor now, when finished remember any change
          $(this).ckeditor(config).on('instanceReady.ckeditor', function (event, editor) {
            // This moves the dom instances of ckeditor into the ckeditor bar
            $('.' + editor.id).detach().appendTo($ckeditorBar);

            // Show/hide the CKEditor bar wrapper that contains the ckeditors when the editor gains/looses focus
            editor.on('focus', function() { $ckeditorBarWrapper.show(); });
            editor.on('blur', function() { $ckeditorBarWrapper.hide(); });

            editor.on('change', function (changeEvent) {
              if (typeof editor.element !== 'undefined') {
                var dataSet = editor.element.$.dataset;
                storage.addSaveItem(dataSet.uid + '_' + dataSet.field + '_' + dataSet.table, {
                  'action': 'save',
                  'table': dataSet.table,
                  'uid': dataSet.uid,
                  'field': dataSet.field,
                  'hasCkeditorConfiguration': elementData.hasCkeditorConfiguration,
                  'editorInstance': editor.name
                });
                F.trigger(F.CONTENT_CHANGE);
              }
            });
          });
        });
      }).fail(function (response) {
        F.trigger(
          F.REQUEST_ERROR,
          {
            message: translate(translateKeys.informRequestFailed,
              response.status,
              response.statusText
            )
          }
        );
      }).always(function () {
        F.hideLoadingScreen();
      });
    }
  }

  return {
    init: init
  }

});
