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
		'/typo3/sysext/backend/Resources/Public/JavaScript/backend.js',
		'jquery',
		'TYPO3/CMS/FrontendEditing/Crud',
		'TYPO3/CMS/FrontendEditing/D3IndentedTree',
		'TYPO3/CMS/FrontendEditing/Editor',
		'toastr',
		'TYPO3/CMS/Backend/Modal',
		'TYPO3/CMS/Backend/Severity'
	], function (
		backend,
		$,
		FrontendEditing,
		D3IndentedTree,
		Editor,
		toastr,
		Modal,
		Severity
	) {
	'use strict';

	// Extend FrontendEditing with additional events
	var events = {
		LEFT_PANEL_TOGGLE: 'LEFT_PANEL_TOGGLE'
	};

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
	FrontendEditing.prototype.siteRootChange = siteRootChange;

	var CLASS_HIDDEN = 'hidden';

	var pushDuration = 200;
	var pushEasing = 'linear';

	var messageTypes = {
		OK: 'OK',
		ERROR: 'ERROR',
		WARNING: 'WARNING'
	};

	var toastrOptions = {
		'positionClass': 'toast-top-left',
		'preventDuplicates': true
	};

	var $itemCounter;
	var $iframe;
	var $loadingScreen;
	var $saveButton;
	var $discardButton;
	var iframeUrl;
	var storage;
	var editorConfigurationUrl;
	var resourcePath;
	var Modal;

	function init(options) {
		$itemCounter = $('.top-bar-action-buttons .items-counter');
		$iframe = $('.t3-frontend-editing__iframe-wrapper iframe');
		$loadingScreen = $('.t3-frontend-editing__loading-screen');
		$saveButton = $('.t3-frontend-editing__save');
		$discardButton = $('.t3-frontend-editing__discard');
		editorConfigurationUrl = options.editorConfigurationUrl;
		resourcePath = options.resourcePath;

		initListeners();
		bindActions();
		D3IndentedTree.init(options.pageTree);
		initGuiStates();
		loadPageIntoIframe(options.iframeUrl, editorConfigurationUrl);
		storage = F.getStorage();
	}

	function initListeners() {
		F.on(F.REQUEST_START, function () {
			showLoadingScreen();
		});

		F.on(F.UPDATE_CONTENT_COMPLETE, function (data) {
			showSuccess(
				data.message,
				F.translate('notifications.save-title')
			);
		});

		F.on(F.UPDATE_PAGES_COMPLETE, function (data) {
			showSuccess(
				data.message,
				F.translate('notifications.save-pages-title')
			);
		});

		F.on(F.REQUEST_ERROR, function (data) {
			showError(
				data.message,
				F.translate('notifications.save-went-wrong')
			);
		});

		F.on(F.REQUEST_COMPLETE, function () {
			refreshIframe();
		});

		F.on(F.CONTENT_CHANGE, function (items) {
			var items = storage.getSaveItems();
			if (items.count()) {
				$discardButton.removeClass('btn-inactive');
				$saveButton.removeClass('btn-inactive');
				$itemCounter.html('(' + items.count() + ')');
			} else {
				$discardButton.addClass('btn-inactive');
				$saveButton.addClass('btn-inactive');
				$itemCounter.html('');
			}
		});
	}

	function save() {
		if (!storage.isEmpty()) {
			F.saveAll();
		} else {
			showWarning(
				F.translate('notifications.no-changes-description'),
				F.translate('notifications.no-changes-title')
			);
		}
	}

	function bindActions() {
		$saveButton.on('click', function (e) {
			save();
		});

		$('.t3-frontend-editing__discard').on('click', function () {
			if (!storage.isEmpty()) {
				F.confirm(F.translate('notifications.remove-all-changes'), {
					yes: function () {
						storage.clear();
						F.refreshIframe();
						F.trigger(F.CONTENT_CHANGE);
					}
				});
			}
		});

		var t = 0;
		var y = 0;
		var u = 1;

		// Add check for page tree navigation
		$('.site-root-button').click(function() {
			$('.t3-frontend-editing__page-site-root-wrapper').toggle();
		});
		$('.search-button').click(function() {
			$('.t3-frontend-editing__page-tree-filter-wrapper').toggle();
		});

		$('.t3-frontend-editing__full-view').on('click', function () {
			t = ++t % 2;
			y = ++y % 2;
			u = ++u % 2;

			$('.t3-frontend-editing__top-bar').stop().animate({top: u ? 0 : -160}, pushDuration, pushEasing);

			$('.t3-frontend-editing__iframe-wrapper').toggleClass('full-view');
			$('.t3-frontend-editing__full-view').toggleClass('full-view-active');
			$('.t3-frontend-editing__ckeditor-bar').toggleClass('full-view-active');
			$('.t3-frontend-editing__ckeditor-bar__wrapper').toggleClass('full-view-active');

			if ($('.t3-frontend-editing__right-bar').hasClass('open') && $('.t3-frontend-editing__left-bar').hasClass('open')) {

				$('.t3-frontend-editing__right-bar').stop().animate({right: t ? 0 : -325}, pushDuration, pushEasing);
				$('.t3-frontend-editing__left-bar').stop().animate({left: t ? 0 : -280}, pushDuration, pushEasing);

			} else if ($('.t3-frontend-editing__right-bar').hasClass('open') && !$('.t3-frontend-editing__left-bar').hasClass('open')) {
				$('.t3-frontend-editing__right-bar').stop().animate({right: t ? 0 : -325}, pushDuration, pushEasing);
				$('.t3-frontend-editing__left-bar').toggleClass('closed');
			} else if (!$('.t3-frontend-editing__right-bar').hasClass('open') && $('.t3-frontend-editing__left-bar').hasClass('open')) {
				$('.t3-frontend-editing__left-bar').stop().animate({left: y ? 0 : -280}, pushDuration, pushEasing);
				$('.t3-frontend-editing__right-bar').toggleClass('closed');
			}
			else {
				$('.t3-frontend-editing__right-bar').toggleClass('closed');
				$('.t3-frontend-editing__left-bar').toggleClass('closed');
			}
			F.getStorage().addItem('fullScreenState', {
				isActive: $('.t3-frontend-editing__full-view').hasClass('full-view-active')
			});
		});


		$('.t3-frontend-editing__page-tree li').click(function () {
			var linkUrl = $(this).data('url');
			F.showLoadingScreen();
			F.navigate(linkUrl);
		});

		$('.top-right-title').on('click', function () {
			$('.right-bar-button').toggleClass('icon-icons-tools-settings icon-icons-arrow-double');
			$('.t3-frontend-editing__top-bar-right').toggleClass('push-toleft');
			$('.t3-frontend-editing__iframe-wrapper').toggleClass('push-toleft-iframe');
			$('.t3-frontend-editing__right-bar').toggleClass('open');
			t = ++t % 2;
			$('.t3-frontend-editing__right-bar').stop().animate({right: t ? 0 : -325}, pushDuration, pushEasing);

			$('.t3-frontend-editing__ckeditor-bar').stop().animate({right: t ? 325 : 45}, pushDuration, pushEasing);

			updateRightPanelState();
		});

		$('.top-left-title').on('click', function () {
			$('.left-bar-button').toggleClass('icon-icons-site-tree icon-icons-arrow-double');

			// save state
			F.getStorage().addItem('leftPanelOpen', !$('.t3-frontend-editing__left-bar').hasClass('open'));

			$('.t3-frontend-editing__top-bar-left').toggleClass('push-toright');
			$('.t3-frontend-editing__left-bar').toggleClass('open');
			$('.t3-frontend-editing__top-bar').children('.cke').toggleClass('left-open');
			y = ++y % 2;

			$('.t3-frontend-editing__ckeditor-bar').stop().animate({left: y ? 280 : 45}, pushDuration, pushEasing);

			$('.t3-frontend-editing__left-bar').stop().animate(
				{
					left: y ? 0 : -280
				},
				{
					duration: pushDuration,
					easing: pushEasing,
					complete: function () {
						F.trigger(F.LEFT_PANEL_TOGGLE, $(this).hasClass('open'));
					}
				}
			);
		});
		$( document ).ready(function() {
			if (!$('.t3-frontend-editing__right-bar').hasClass('open') ||  !$('.t3-frontend-editing__left-bar').hasClass('open')
				|| $('.t3-frontend-editing__right-bar').hasClass('open') ||  $('.t3-frontend-editing__left-bar').hasClass('open')) {

				if (!$('.t3-frontend-editing__left-bar').hasClass('open')) {
					$('.t3-frontend-editing__ckeditor-bar').addClass('left-closed');
				}
				if (!$('.t3-frontend-editing__right-bar').hasClass('open')) {

					$('.t3-frontend-editing__ckeditor-bar').addClass('right-closed');
				}
			}
		});

		$('.t3-frontend-editing__page-edit, .t3-frontend-editing__page-new, .t3-frontend-editing__page-seo_module').click(function () {
			var url = $(this).data('url');
			F.loadInModal(url);
		});

		$('.t3-frontend-editing__show-hidden-items').click(function () {
			var url = $(this).data('url');
			loadPageIntoIframe(url, editorConfigurationUrl);
			// Change the state of visible/hidden GET parameter
			var changedUrlState = '';
			if (url.indexOf('show_hidden_items=1') >= 0) {
				changedUrlState = url.replace('show_hidden_items=1', 'show_hidden_items=0');
			} else if (url.indexOf('show_hidden_items=0') >= 0) {
				changedUrlState = url.replace('show_hidden_items=0', 'show_hidden_items=1');
			}
			$(this).data('url', changedUrlState);
			$('.t3-frontend-editing__show-hidden-items').toggleClass('active');
		});

		$('.media-devices span').on('click', function () {
			$('.media-devices').find('span').removeClass('active');
			$(this).addClass('active');
			$('.t3-frontend-editing__iframe-wrapper iframe').animate({
				'width': $(this).data('width')
			});
		});

		$('.accordion .trigger, .accordion .element-title').on('click', function () {
			$(this).toggleClass('active');
			$(this).closest('.accordion-container').find('.accordion-content').slideToggle(pushDuration, pushEasing);
			updateRightPanelState();
		});

		$('.accordion .grid').on('click', function () {
			$(this).closest('.accordion-container')
				.removeClass('accordion-list')
				.addClass('accordion-grid');
			updateRightPanelState();
		});

		$('.top-bar-items .dropdown-toggle').on('click', function () {
			$(this).toggleClass('active');
			$(this).next('.dropdown-menu').toggle();
		});


		$('.accordion .list-view').on('click', function () {
			$(this).closest('.accordion-container')
				.removeClass('accordion-grid')
				.addClass('accordion-list');
			updateRightPanelState();
		});

		$('.t3-frontend-editing__right-bar').stop().animate({right: t ? 0 : -325}, pushDuration, pushEasing);

		// Filter event
		$('input.search-page-tree').on('keyup', function (e) {
			if (D3IndentedTree.isSearchRunning()) {
				e.preventDefault();
			} else {
				var sword = $(this).val().trim();
				D3IndentedTree.treeFilter(sword);
			}
		});

		// Refresh page tree
		$('.t3-frontend-editing__page-tree-refresh').on('click', function (event) {
			event.preventDefault();
			D3IndentedTree.resetFilter();
		});

		// Allow external scripts to iframeUrl when main window url is updated by script (history.pushState)
		$iframe.on('update-url', function(e, url){
			if(url.indexOf('frontend_editing') === -1){
				url += (url.indexOf('?') === -1 ? '?' : '&') + 'frontend_editing=true';
			}
			iframeUrl = url;
		});
	}

	function initGuiStates() {
		var states = F.getStorage().getAllData();
		if (typeof states.leftPanelOpen !== 'undefined' && states.leftPanelOpen === true) {
			// Trigger open left panel
			$('.left-bar-button').trigger('click');
		}

		if (typeof states.rightPanelState !== 'undefined') {
			// Init right panel state
			if (states.rightPanelState.isVisible) {
				$('.right-bar-button').trigger('click');
			}

			for (var wizard in states.rightPanelState.wizards) {
				if (states.rightPanelState.wizards.hasOwnProperty(wizard)) {
					var $wizard = $('[data-wizard-type="' + wizard + '"]');
					if ($wizard.length === 1) {
						if (states.rightPanelState.wizards[wizard].isListView) {
							$wizard.find('.list-view').trigger('click');
						}
						if (states.rightPanelState.wizards[wizard].isExpanded) {
							$wizard.find('.trigger').trigger('click');
						}
					}
				}
			}
		}

		if (typeof states.fullScreenState !== 'undefined') {
			if(states.fullScreenState.isActive) {
				$('.t3-frontend-editing__full-view').trigger('click');
			}
		}
	}

	function updateRightPanelState() {
		var rightPanelState = {
			isVisible: $('.t3-frontend-editing__right-bar').hasClass('open'),
			wizards: {}
		};

		$('.accordion .trigger').each(function () {
			var accordionContainer = $(this).parents('.accordion-container'),
				isExpanded = $(this).hasClass('active'),
				isListView = accordionContainer.hasClass('accordion-list');

			if (isExpanded || isListView) {
				var containerType = accordionContainer.data('wizard-type');
				rightPanelState.wizards[containerType] = {
					isExpanded: isExpanded,
					isListView: isListView
				}
			}
		});

		F.getStorage().addItem('rightPanelState', rightPanelState);
	}

	function loadPageIntoIframe(url, editorConfigurationUrl) {
		showLoadingScreen();
		var deferred = $.Deferred();

		$iframe.attr({
			'src': url
		});

		$iframe.on('load', deferred.resolve);

		deferred.done(function () {
			Editor.init($iframe, editorConfigurationUrl, resourcePath);

			if (sessionStorage.scrollTop !== 'undefined') {
				$iframe.contents().scrollTop(sessionStorage.scrollTop);
				sessionStorage.removeItem('scrollTop');
			}

			hideLoadingScreen();
		});

		iframeUrl = url;
	}

	function refreshIframe() {
		sessionStorage.scrollTop = $iframe.contents().scrollTop();

		loadPageIntoIframe(iframeUrl, editorConfigurationUrl);
	}

	function showLoadingScreen() {
		$loadingScreen.fadeIn('fast', function () {
			$loadingScreen.removeClass(CLASS_HIDDEN);
		});
	}

	function hideLoadingScreen() {
		$loadingScreen.fadeOut('slow', function () {
			$loadingScreen.addClass(CLASS_HIDDEN);
		});
	}

	function getIframe() {
		return $iframe;
	}

	function flashMessage(type, message, title) {
		var toastrFunction;
		switch (type) {
			case messageTypes.OK:
				toastrFunction = 'success';
				break;
			case messageTypes.ERROR:
				toastrFunction = 'error';
				break;
			case messageTypes.WARNING:
				toastrFunction = 'warning';
				break;
			default:
				throw 'Invalid message type ' + type;
		}
		toastr[toastrFunction](message, title, toastrOptions);
	}

	function showSuccess(message, title) {
		flashMessage(messageTypes.OK, message, title);
	}

	function showError(message, title) {
		flashMessage(messageTypes.ERROR, message, title);
	}

	function showWarning(message, title) {
		flashMessage(messageTypes.WARNING, message, title);
	}

	function confirm(message, callbacks) {
		callbacks = callbacks || {};

		// Confirm dialog
		if (message === F.translate('notifications.unsaved-changes')) {
			TYPO3.Modal.confirm(
				'Navigate',
				message,
				Severity.warning,
				[
					{
						text: 'Cancel',
						trigger: function () {
							$(this).trigger('modal-dismiss');
							if (typeof callbacks.no === 'function') {
								callbacks.no();
							}
						},
						active: true,
						btnClass: 'btn-default'
					},
					{
						text: 'Save All',
						trigger: function () {
							$(this).trigger('modal-dismiss');
							if (typeof callbacks.yes === 'function') {
								save();
								callbacks.yes();
							}
						},
						active: false,
						btnClass: 'btn-warning'
					},
					{
						text: 'Discard and Navigate',
						trigger: function () {
							$(this).trigger('modal-dismiss');
							if (typeof callbacks.yes === 'function') {
								callbacks.yes();
							}
						},
						active: false,
						btnClass: 'btn-danger'
					}
				]
			);
		} else {
			TYPO3.Modal.confirm(
				message,
				message,
				Severity.warning,
				[
					{
						text: 'Cancel',
						trigger: function () {
							$(this).trigger('modal-dismiss');
							if (typeof callbacks.no === 'function') {
								callbacks.no();
							}
						},
						active: true,
						btnClass: 'btn-default'
					},
					{
						text: 'OK',
						trigger: function () {
							$(this).trigger('modal-dismiss');
							if (typeof callbacks.yes === 'function') {
								callbacks.yes();
							}
						},
						active: false,
						btnClass: 'btn-warning'
					}
				]
			);
		}
	}

	function windowOpen(url) {
		var vHWin = window.open(url, 'FEquickEditWindow', 'width=690,height=500,status=0,menubar=0,scrollbars=1,resizable=1');
		vHWin.focus();
		return false;
	}

	function siteRootChange(element) {
		var linkUrl = String($(element).val() + '?FEEDIT_BE_SESSION_KEY=' + F.getBESessionId()),
			message = storage.isEmpty() ? 'notifications.change_site_root' : 'notifications.unsaved-changes';

		if (linkUrl !== '0') {
			F.confirm(F.translate(message), {
				yes: function () {
					window.location.href = linkUrl;
				},
				no: function () {
					element.selectedIndex = 0;
				}
			});
		}

	}

	return FrontendEditing;
});
