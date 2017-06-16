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
define(['jquery', 'TYPO3/CMS/FrontendEditing/Crud', 'TYPO3/CMS/FrontendEditing/Editor', 'toastr', 'alertify', 'TYPO3/CMS/Backend/Modal'], function ($, FrontendEditing, Editor, toastr, alertify, Modal) {
	'use strict';

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

	function bindActions() {
		$saveButton.on('click', function (e) {
			if (!storage.isEmpty()) {
				F.saveAll();
			} else {
				showWarning(
					F.translate('notifications.no-changes-description'),
					F.translate('notifications.no-changes-title')
				);
			}
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

		// Add check for page tree navigation
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
			updateRightPanelState();
		});

		$('.top-left-title').on('click', function () {
			$('.left-bar-button').toggleClass('icon-icons-site-tree icon-icons-arrow-double');
			if (!$('.t3-frontend-editing__left-bar').hasClass('open')) {
				F.getStorage().addItem('leftPanelOpen', true);
			}

			$('.t3-frontend-editing__top-bar-left').toggleClass('push-toright');
			$('.t3-frontend-editing__left-bar').toggleClass('open');
			$('.t3-frontend-editing__top-bar').children('.cke').toggleClass('left-open');
			y = ++y % 2;
			$('.t3-frontend-editing__left-bar').stop().animate({left: y ? 0 : -280}, pushDuration, pushEasing);
		});

		$('.t3-frontend-editing__page-edit, .t3-frontend-editing__page-new').click(function () {
			var url = $(this).data('url');
			F.loadInModal(url);
		});

		$('.page-seo-devices span').on('click', function () {
			$('.page-seo-devices').find('span').removeClass('active');
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
	}

	function initGuiStates() {
		var states = F.getStorage().getAllData();
		if (typeof states.leftPanelOpen !== 'undefined' && states.leftPanelOpen === true) {
			// Trigger open left panel
			$('.left-bar-button').trigger('click');
			F.getStorage().addItem('leftPanelOpen', false);
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
		$loadingScreen.fadeIn('fast', function() {
			$loadingScreen.removeClass(CLASS_HIDDEN);
		});
	}

	function hideLoadingScreen() {
		$loadingScreen.fadeOut('slow', function() {
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
		alertify.confirm(message, function () {
			// User clicked "ok"
			if (typeof callbacks.yes === 'function') {
				callbacks.yes();
			}
		}, function () {
			if (typeof callbacks.no === 'function') {
				callbacks.no();
			}
		});
	}

	function windowOpen(url) {
		var vHWin = window.open(url, 'FEquickEditWindow', 'width=690,height=500,status=0,menubar=0,scrollbars=1,resizable=1');
		vHWin.focus();
		return false;
	}

	return FrontendEditing;
});
