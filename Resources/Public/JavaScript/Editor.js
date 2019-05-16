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
	'jquery'
], function (
	$
) {
	'use strict';

	var defaultEditorConfig = {
		skin: 'moono',
		entities_latin: false,
		htmlEncodeOutput: false,
		allowedContent: true,
		customConfig: '',
		stylesSet: [],
		autoParagraph: false
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
			)
		);

		// Suppress a tags (links) to redirect the normal way
		$iframeContents.find('a').click(function (event) {
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

				// Open/edit|new action
				that.find('.icon-actions-open, .icon-actions-document-new').on('click', function () {
					if (!storage.isEmpty()) {
						F.confirm(F.translate('notifications.unsaved-changes'), {
							yes: function () {
								openModal($(this));
							},
							no: function () {
								return false;
							}
						});
					} else {
						openModal($(this));
					}

					function openModal(button) {
						var url = that.data('edit-url');
						if (button.data('identifier') === 'actions-document-new') {
							url = that.data('new-url');
						}
						require([
							'jquery',
							'TYPO3/CMS/Backend/Modal'
						], function ($, Modal) {

							Modal.advanced({
								type: Modal.types.iframe,
								title: '',
								content: url,
								size: Modal.sizes.large,
								callback: function (currentModal) {
									currentModal.find('iframe').attr('name', 'list_frame');
									// Simulate BE environment with correct CKEditor instance for RteLinkBrowser
									currentModal.find(Modal.types.iframe).on('load', function () {
										top.TYPO3.Backend = top.TYPO3.Backend || {};
										top.TYPO3.Backend.ContentContainer = {
											get: function () {
												return currentModal.find(Modal.types.iframe).get(0).contentWindow;
											}
										};
									});

									currentModal.on('hidden.bs.modal', function (e) {
										delete top.TYPO3.Backend;
										F.refreshIframe();
									});
								}
							});
						});
					}

				});

				// Delete action
				that.find('.icon-actions-edit-delete').on('click', function () {
					F.confirm(F.translate('notifications.delete-content-element'), {
						yes: function () {
							F.delete(that.data('uid'), that.data('table'));
						},
						no: function () {
							// Do nothing
						}
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

				if (typeof $inlineActions[index - 1] !== 'undefined' &&
					$inlineActions[index - 1].dataset.cid == that.data('cid')
				) {
					// Move up action
					that.find('.icon-actions-move-up').on('click', function () {
						// Find the previous editor instance element
						var previousDomElementUid = $inlineActions[index - 1].dataset.uid;
						var currentDomElementUid = that.data('uid');
						var currentDomElementTable = that.data('table');
						F.moveContent(previousDomElementUid, currentDomElementTable, currentDomElementUid)
					});
				} else {
					that.find('.icon-actions-move-up').hide();
				}

				if (typeof $inlineActions[index + 1] !== 'undefined' &&
					$inlineActions[index + 1].dataset.cid == that.data('cid')
				) {
					// Move down action
					that.find('.icon-actions-move-down').on('click', function () {
						// Find the next editor instance element
						var nextDomElementUid = $inlineActions[index + 1].dataset.uid;
						var currentDomElementUid = that.data('uid');
						var currentDomElementTable = that.data('table');
						F.moveContent(currentDomElementUid, currentDomElementTable, nextDomElementUid)
					});
				} else {
					that.find('.icon-actions-move-down').hide();
				}
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


		var $topBar = $('.t3-frontend-editing__ckeditor-bar');

		// Add custom configuration to ckeditor
		var $contenteditable = $iframeContents.find('[contenteditable=\'true\']');
		$contenteditable.each(function () {
			var $el = $(this);
			var $parent = $el.parent();
			// Prevent linked content element to be clickable in the frontend editing mode
			if ($parent.is('a')) {
				$parent.attr('href', 'javascript:;');
			}

			// Only div is allowed for CKeditor instance
			if ($el.prop('tagName').toLowerCase() !== 'div') {
				$el.on('blur keyup paste input', function (event) {
					var dataSet = $el.data();
					storage.addSaveItem(dataSet.uid + '_' + dataSet.field + '_' + dataSet.table, {
						'action': 'save',
						'table': dataSet.table,
						'uid': dataSet.uid,
						'field': dataSet.field,
						'hasCkeditorConfiguration': null,
						'editorInstance': null,
						'inlineElement': true,
						'text': $el.text()
					});
					F.trigger(F.CONTENT_CHANGE);
				});
			} else {
				$.ajax({
					url: configurationUrl,
					method: 'GET',
					dataType: 'json',
					data: {
						'table': $(this).data('table'),
						'uid': $(this).data('uid'),
						'field': $(this).data('field')
					}
				}).done(function (data) {
					// Ensure all plugins / buttons are loaded
					if (typeof data.externalPlugins !== 'undefined') {
						eval(data.externalPlugins);
					}

					// If there is no CKEditor configuration.
					var config = defaultEditorConfig;
					if (data.hasCkeditorConfiguration) {
						config = $.extend(true, config, data.configuration);
					} else {
						config = $.extend(true, config, defaultSimpleEditorConfig);
					}

					// Initialize CKEditor now, when finished remember any change
					$el.ckeditor(config).on('instanceReady.ckeditor', function (event, editor) {
						// This moves the dom instances of ckeditor into the top bar
						$('.' + editor.id).detach().appendTo($topBar);

						editor.on('change', function (changeEvent) {
							if (typeof editor.element !== 'undefined') {
								var dataSet = editor.element.$.dataset;
								storage.addSaveItem(dataSet.uid + '_' + dataSet.field + '_' + dataSet.table, {
									'action': 'save',
									'table': dataSet.table,
									'uid': dataSet.uid,
									'field': dataSet.field,
									'hasCkeditorConfiguration': data.hasCkeditorConfiguration,
									'editorInstance': editor.name
								});
								F.trigger(F.CONTENT_CHANGE);
							}
						});
					});
				});
			}
		});
	}

	return {
		init: init
	}

});
