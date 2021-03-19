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
 * Module: TYPO3/CMS/FrontendEditing/GUI
 * FrontendEditing.GUI: Functionality related to the GUI and events listeners
 */
define([
    'jquery',
    './Crud',
    './D3IndentedTree',
    './Editor',
    './Notification',
    './Modal',
    './Utils/TranslatorLoader',
    './Utils/Logger'
], function createGuiModule (
    $,
    FrontendEditing,
    D3IndentedTree,
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
        confirmDiscardChanges: 'notifications.remove-all-changes',
        confirmChangeSiteRoot: 'notifications.change_site_root',
        confirmChangeSiteRootWithChange: 'notifications.unsaved-changes',
    };

    var translator = TranslatorLoader.useTranslator('gui', function reload (t) {
        translateKeys = $.extend(translateKeys, t.getKeys());
    }).translator;

    function translate () {
        return translator.translate.apply(translator, arguments);
    }


    // Extend FrontendEditing with additional events
    var events = {
        LEFT_PANEL_TOGGLE: 'LEFT_PANEL_TOGGLE'
    };

    // Add custom events to FrontendEditing
    for (var key in events) {
        if (!events.hasOwnProperty(key)) {
            continue;
        }

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
    FrontendEditing.prototype.initCustomLoadedContent = initCustomLoadedContent;

    var CLASS_HIDDEN = 'hidden';

    var pushDuration = 200;
    var pushEasing = 'linear';

    var topBarHeight = 160;
    var leftBarWidth = 280;
    var rightBarWidth = 325;
    var iconWidth = 45;

    var rightBarPosition = [-rightBarWidth, 0];
    var leftBarPosition = [-leftBarWidth, 0];
    var ckeditorBarPositionRight = [iconWidth, rightBarWidth];
    var ckeditorBarPositionLeft = [iconWidth, leftBarWidth];
    var topBarPosition = [-topBarHeight, 0];

    var storage;
    var editorConfigurationUrl;
    var resourcePath;

    var iframeUrl;

    // jquery elements
    var $iframeWrapper;
    var $iframe;
    var $loadingScreen;
    var loadingScreenLevel = 0;

    var $showHiddenItemsButton;

    var $itemCounter, $saveButton, $discardButton;
    var $fullViewButton;

    var $treeRefreshButton;
    var $searchTreeInput;

    var $leftBarOpenButton, $rightBarOpenButton;
    var $rightBar, $leftBar, $topBar, $ckeditorBar;

    var $topBarItems;

    var $mediaDevices;
    var $accordions;

    var $tree;
    var $treeFilterWrapper;
    var $searchButton;
    var $siteRootWrapper;
    var $siteRootButton;

    var $ckeditorBarWrapper;

    var $topRightTitle, $topRightBar;
    var $topLeftTitle, $topLeftBar;

    function init (options) {
        log.info('init', options);

        findElements();

        editorConfigurationUrl = options.editorConfigurationUrl;
        resourcePath = options.resourcePath;

        storage = F.getStorage();

        initListeners();
        bindActions();
        D3IndentedTree.init(options.pageTree);
        initGuiStates();
        loadPageIntoIframe(options.iframeUrl, editorConfigurationUrl);
    }

    function findElements () {
        $iframeWrapper = $('.t3-frontend-editing__iframe-wrapper');
        $iframe = $iframeWrapper.find('iframe');

        $loadingScreen = $('.t3-frontend-editing__loading-screen');

        $itemCounter = $('.top-bar-action-buttons .items-counter');
        $saveButton = $('.t3-frontend-editing__save');
        $discardButton = $('.t3-frontend-editing__discard');

        $rightBar = $('.t3-frontend-editing__right-bar');
        $leftBar = $('.t3-frontend-editing__left-bar');
        $topBar = $('.t3-frontend-editing__top-bar');
        $ckeditorBar = $('.t3-frontend-editing__ckeditor-bar');

        $fullViewButton = $('.t3-frontend-editing__full-view');

        $leftBarOpenButton = $('.left-bar-button');
        $rightBarOpenButton = $('.right-bar-button');

        $showHiddenItemsButton = $('.t3-frontend-editing__show-hidden-items');

        $treeRefreshButton = $('.t3-frontend-editing__page-tree-refresh');
        $searchTreeInput = $('input.search-page-tree');
        $mediaDevices = $('.media-devices');
        $accordions = $('.accordion');
        $topBarItems = $('.top-bar-items');

        $siteRootButton = $('.site-root-button');
        $siteRootWrapper = $('.t3-frontend-editing__page-site-root-wrapper');
        $searchButton = $('.search-button');
        $treeFilterWrapper = $(
            '.t3-frontend-editing__page-tree-filter-wrapper'
        );
        $ckeditorBarWrapper = $('.t3-frontend-editing__ckeditor-bar__wrapper');

        $tree = $('.t3-frontend-editing__page-tree');
        $topRightTitle = $('.top-right-title');
        $topRightBar = $('.t3-frontend-editing__top-bar-right');
        $topLeftTitle = $('.top-left-title');
        $topLeftBar = $('.t3-frontend-editing__top-bar-left');
    }

    function animate ($element, prop, completeCallback) {
        $element
            .stop()
            .animate(prop, pushDuration, pushEasing, completeCallback);
    }

    function initListeners () {
        F.on(F.UPDATE_CONTENT_COMPLETE, function showUpdateSuccess (response) {
            Notification.success(
                response.message,
                translate(translateKeys.updatedContentTitle)
            );
        });

        F.on(F.UPDATE_PAGES_COMPLETE, function showUpdateSuccess (response) {
            Notification.success(
                response.message,
                translate(translateKeys.updatedPageTitle)
            );
        });

        F.on(F.REQUEST_ERROR, function showRequestError (response) {
            Notification.error(
                response.message,
                translate(translateKeys.updateRequestErrorTitle)
            );
        });

        F.on(F.REQUEST_COMPLETE, refreshIframe);

        F.on(F.CONTENT_CHANGE, updateSaveItemsButtons);

        $iframe.on('load', function prepareIframe () {
            log.debug('iframe on load', $iframe[0].src);
            initEditorInIframe(editorConfigurationUrl);

            iframeUrl = $iframe[0].src;
        });
    }

    function updateSaveItemsButtons () {
        log.trace('updateSaveItemsButtons');

        if (storage.isEmpty()) {
            log.debug('disable save items buttons');

            $discardButton.prop('disabled', true);
            $saveButton.prop('disabled', true);
            $discardButton.addClass('btn-inactive');
            $saveButton.addClass('btn-inactive');
            $itemCounter.html('');
        } else {
            log.debug('enable save items buttons');

            $discardButton.prop('disabled', false);
            $saveButton.prop('disabled', false);
            $discardButton.removeClass('btn-inactive');
            $saveButton.removeClass('btn-inactive');
            $itemCounter.html('(' + storage.countSaveItems() + ')');
        }
    }

    function save () {
        log.info('save items');

        if (storage.isEmpty()) {
            log.warn('unable to save items without data in storage');

            Notification.warning(
                translate(translateKeys.saveWithoutChange),
                translate(translateKeys.saveWithoutChangeTitle)
            );
            // reset button states
            updateSaveItemsButtons();
            return;
        }

        F.saveAll();
    }

    function discardSaveItems () {
        log.debug('discardSaveItems');

        if (!storage.isEmpty()) {
            Modal.confirm(
                translate(translateKeys.confirmDiscardChanges), {
                    yes: function () {
                        log.info('discard save items');

                        storage.clear();
                        F.refreshIframe();
                        F.trigger(F.CONTENT_CHANGE);
                    }
                }
            );
        }
    }

    function bindActions () {
        // panel states t=left, y=right, u=top
        var t = 0;
        var y = 0;
        var u = 1;

        $saveButton.on('click', save);
        $discardButton.on('click', discardSaveItems);

        // Add check for page tree navigation
        $siteRootButton.click(function toggleTreeNavigation () {
            log.info('toggle tree navigation');

            $siteRootWrapper.toggle();
        });
        $searchButton.click(function toggleSearchFilter () {
            log.info('toggle search filter');

            $treeFilterWrapper.toggle();
        });

        $fullViewButton.on('click', function toggleFullscreen () {
            log.info('toggle fullscreen [t, y, u]', t, y, u);

            t = ++t & 1;
            y = ++y & 1;
            u = ++u & 1;

            animate($topBar, {
                top: topBarPosition[u]
            });

            $iframeWrapper.toggleClass('full-view');
            $fullViewButton.toggleClass('full-view-active');
            $ckeditorBar.toggleClass('full-view-active');
            $ckeditorBarWrapper.toggleClass('full-view-active');

            if ($rightBar.hasClass('open')) {
                animate($rightBar, {
                    right: rightBarPosition[t]
                });
            } else {
                $rightBar.toggleClass('closed');
            }

            if ($leftBar.hasClass('open')) {
                animate($leftBar, {
                    left: leftBarPosition[y]
                });
            } else {
                $leftBar.toggleClass('closed');
            }

            storage.addItem('fullScreenState', {
                isActive: $fullViewButton.hasClass('full-view-active')
            });
        });


        $tree.find('li')
            .click(function changeWebPage () {
                var linkUrl = $(this)
                    .data('url');

                log.info('change webpage', linkUrl);

                F.showLoadingScreen();
                F.navigate(linkUrl);
            });

        $topRightTitle.on('click', function toggleRightBar () {
            log.info('toggle right bar', t);

            $rightBarOpenButton.toggleClass(
                'icon-icons-tools-settings icon-icons-arrow-double'
            );
            $topRightBar.toggleClass('push-toleft');
            $iframeWrapper.toggleClass('push-toleft-iframe');
            $rightBar.toggleClass('open');

            t = ++t & 1;
            animate($rightBar, {
                right: rightBarPosition[t]
            });
            animate($ckeditorBar, {
                right: ckeditorBarPositionRight[t]
            });

            updateRightPanelState();
        });

        $topLeftTitle.on('click', function toggleLeftBar () {
            log.info('toggle left bar', t);

            $leftBarOpenButton.toggleClass(
                'icon-icons-site-tree icon-icons-arrow-double'
            );

            // save state
            storage.addItem('leftPanelOpen', !$leftBar.hasClass('open'));

            $topLeftBar.toggleClass('push-toright');
            $leftBar.toggleClass('open');
            $topBar.children('.cke')
                .toggleClass('left-open');
            y = ++y & 1;

            animate($ckeditorBar, {
                left: ckeditorBarPositionLeft[y]
            });

            animate($leftBar, {
                left: leftBarPosition[y]
            }, function triggerLeftPanelToggle () {
                var $this = $(this);
                F.trigger(F.LEFT_PANEL_TOGGLE, $this.hasClass('open'));
            });
        });

        $(document)
            .ready(function updateCloseLabel () {
                log.trace('document content loaded, update close label');

                // Doesn't do anything since it is always true
                /*
                if (!$rightBar.hasClass('open') || !$leftBar.hasClass('open') ||
                    $rightBar.hasClass('open') || $leftBar.hasClass('open')) {
*/

                if (!$leftBar.hasClass('open')) {
                    $ckeditorBar.addClass('left-closed');
                }
                if (!$rightBar.hasClass('open')) {
                    $ckeditorBar.addClass('right-closed');
                }
                /*
                }
*/
            });

        $('.t3-frontend-editing__page-edit, ' +
          '.t3-frontend-editing__page-new, ' +
          '.t3-frontend-editing__page-seo_module')
            .click(function loadEditOrCreatePageModal () {
                var url = $(this)
                    .data('url');

                log.info('loadEditOrCreatePageModal', url);

                F.loadInModal(url);
            });

        $showHiddenItemsButton.click(function toggleShowHiddenItems () {
            var $this = $(this);
            var url = $this.data('url');
            var parameter = 'show_hidden_items=';
            var parameterOn = parameter + '1';
            var parameterOff = parameter + '0';

            log.info('toggleShowHiddenItems', url);

            loadPageIntoIframe(url, editorConfigurationUrl);

            // Change the state of visible/hidden GET parameter
            var changedUrlState = '';
            if (url.indexOf(parameterOn) >= 0) {
                changedUrlState = url.replace(parameterOn, parameterOff);
            } else if (url.indexOf(parameterOff) >= 0) {
                changedUrlState = url.replace(parameterOff, parameterOn);
            } else {
                log.warn(
                    'unable to toggle url parameter to show hidden items',
                    parameter
                );

                //add parameter as try and error solution, nothing to lose
                changedUrlState = url +
                    (url.indexOf('?') >= 0 ? '&' : '?') +
                    parameterOn;
            }

            $this.data('url', changedUrlState);
            $this.toggleClass('active');
        });

        $mediaDevices.find('span')
            .on('click', function changeIFrameSize () {
                var $this = $(this);

                log.info('changeIFrameSize', this);

                $('.media-devices')
                    .find('span')
                    .removeClass('active');
                $this.addClass('active');

                $('.t3-frontend-editing__iframe-wrapper iframe')
                    .animate({
                        'width': $this.data('width')
                    });
            });

        $accordions.find('.trigger, .element-title')
            .on('click', function toggleAccordion () {
                var $this = $(this);

                log.info('toggleAccordion', this);

                $this.toggleClass('active');
                $this
                    .closest('.accordion-container')
                    .find('.accordion-content')
                    .slideToggle(pushDuration, pushEasing);

                updateRightPanelState();
            });

        $accordions.find('.grid')
            .on('click', function changeAccordionGridLayout () {
                log.info('changeAccordionGridLayout', this);

                $(this)
                    .closest('.accordion-container')
                    .removeClass('accordion-list')
                    .addClass('accordion-grid');
                updateRightPanelState();
            });

        $topBarItems.find('.dropdown-toggle')
            .on('click', function toggleDropDownMenu () {
                var $this = $(this);

                log.info('toggleDropDownMenu', this);

                $this.toggleClass('active');
                $this
                    .next('.dropdown-menu')
                    .toggle();
            });


        $accordions.find('.list-view')
            .on('click', function changeAccordionListLayout () {
                log.info('changeAccordionListLayout', this);

                $(this)
                    .closest('.accordion-container')
                    .removeClass('accordion-grid')
                    .addClass('accordion-list');
                updateRightPanelState();
            });


        //TODO: move to init state function since it is not an action binding
        animate($rightBar, {right: rightBarPosition[t]});

        // Filter event
        $searchTreeInput.on('keyup', function triggerTreeFilter (event) {
            log.trace('triggerTreeFilter', event);

            if (D3IndentedTree.isSearchRunning()) {
                event.preventDefault();
            } else {
                var sword = $(this)
                    .val()
                    .trim();

                log.info('filter tree', this, sword);

                D3IndentedTree.treeFilter(sword);
            }
        });

        // Refresh page tree
        $treeRefreshButton.on('click', function refreshTree (event) {
            log.info('refreshTree');

            event.preventDefault();
            D3IndentedTree.resetFilter();
        });

        // TODO find better explanation
        // Allow external scripts to use iframeUrl when main window url
        // is updated by script (history.pushState)
        $iframe.on('update-url', function updateIFrameUrl (event, url) {
            log.debug('updateIFrameUrl', event, url);

            if (url.indexOf('frontend_editing') === -1) {
                var delimiter = (url.indexOf('?') === -1 ? '?' : '&');
                url += delimiter + 'frontend_editing=true';
            }

            log.debug('set iframeurl to', url);

            iframeUrl = url;
        });
    }

    function initGuiStates () {
        var states = storage.getAllData();
        if (typeof states !== 'object') {
            return;
        }

        if (states.leftPanelOpen === true) {
            // Trigger open left panel
            $leftBarOpenButton.trigger('click');
        }

        if (states.rightPanelState) {
            // Init right panel state
            if (states.rightPanelState.isVisible) {
                $rightBarOpenButton.trigger('click');
            }

            for (var wizard in states.rightPanelState.wizards) {
                if (!states.rightPanelState.wizards.hasOwnProperty(wizard)) {
                    continue;
                }

                var $wizard = $('[data-wizard-type="' + wizard + '"]');
                if ($wizard.length === 0) {
                    log.warn('No wizard of following type found', wizard);

                    continue;
                }

                if (states.rightPanelState.wizards[wizard].isListView) {
                    $wizard
                        .find('.list-view')
                        .trigger('click');
                }
                if (states.rightPanelState.wizards[wizard].isExpanded) {
                    $wizard
                        .find('.trigger')
                        .trigger('click');
                }
            }
        }

        if (states.fullScreenState && states.fullScreenState.isActive) {
            $fullViewButton.trigger('click');
        }
    }

    function updateRightPanelState () {
        log.trace('updateRightPanelState', $rightBar);

        var rightPanelState = {
            isVisible: $rightBar.hasClass('open'),
            wizards: {}
        };

        $('.accordion .trigger')
            .each(function fetchRightPanelWizardStates () {
                log.trace('fetchRightPanelWizardStates', this);

                var $this = $(this);
                var accordionContainer = $this.parents('.accordion-container');
                var isExpanded = $this.hasClass('active');
                var isListView = accordionContainer.hasClass('accordion-list');

                if (isExpanded || isListView) {
                    var containerType = accordionContainer.data('wizard-type');
                    rightPanelState.wizards[containerType] = {
                        isExpanded: isExpanded,
                        isListView: isListView
                    };
                }
            });

        storage.addItem('rightPanelState', rightPanelState);
    }

    function loadPageIntoIframe (url) {
        log.debug('loadPageIntoIframe', url);

        $iframe.attr({
            'src': url
        });

        iframeUrl = url;
    }

    function initEditorInIframe (editorConfigurationUrl) {
        log.trace('initEditorInIframe', editorConfigurationUrl);

        // Avoid inception issue for example when link clicked redirects to a
        // new URL without "frontend_editing=true"
        var iframeDocumentLocation = $iframe[0].contentDocument.location;
        var url = iframeDocumentLocation.href;

        document.title = $iframe[0].contentDocument.title;

        if (!iframeDocumentLocation.search.includes('frontend_editing=true')) {
            log.debug(
                '"frontend_editing=true" parameter missing',
                iframeDocumentLocation.search
            );

            // history.replaceState(history.state, document.title, url);

            if (!url.includes('?')) {
                url = url + '?';
            } else if (url.slice(url.length - 1) !== '&') {
                url = url + '&';
            }

            loadPageIntoIframe(url + 'frontend_editing=true');
            hideLoadingScreen();
            return;
        }

        updateHistoryState(url);

        // check if LocalStorage contains any changes prior to iFrame reload
        var items = storage.getSaveItems();

        if (items.count()) {
            items.forEach(restoreInlineEditorContent);
        }

        initCustomLoadedContent($iframe);

        restoreScrollPosition();

        hideLoadingScreen();
    }

    function restoreInlineEditorContent (item) {
        log.trace('restoreInlineEditorContent', item);

        var content;

        if (item.inlineElement) {
            content = item.text;
        } else if (item.hasCkeditorConfiguration) {
            content = CKEDITOR.instances[item.editorInstance]
                .getData();
        } else {
            content = CKEDITOR.instances[item.editorInstance]
                .editable()
                .getText();
        }

        log.debug('try to restore content', content);

        $iframe.contents()
            .find('[contenteditable=\'true\']')
            .each(function checkAndRestoreEditableContent () {
                log.trace('checkAndRestoreEditableContent', this);

                var $this = $(this);
                var dataSet = $this.data();
                if (String(dataSet.uid) === String(item.uid) &&
                    dataSet.field === item.field &&
                    dataSet.table === item.table
                ) {
                    log.debug(
                        'restore editable content',
                        this,
                        content
                    );

                    $this.html(content);
                }
            });
    }

    function saveScrollPosition () {
        sessionStorage.scrollTop = $iframe.contents()
            .scrollTop();
    }

    function restoreScrollPosition () {
        if (sessionStorage.scrollTop !== 'undefined') {
            log.debug('restore scroll position', sessionStorage.scrollTop);

            $iframe.contents()
                .scrollTop(sessionStorage.scrollTop);
            sessionStorage.removeItem('scrollTop');
        }
    }

    function updateHistoryState (url) {
        log.trace('updateHistoryState', url);

        url = url
            .replace('&frontend_editing=true', '')
            .replace('frontend_editing=true', '');
        url = url
            .replace('&no_cache=1', '')
            .replace('no_cache=1', '');

        if (url.slice(url.length - 1) === '?') {
            url = url.slice(0, -1);
        }

        log.debug('replace state', document.title, url);
        history.replaceState(history.state, document.title, url);
    }

    function initCustomLoadedContent (customElement) {
        log.debug('initCustomLoadedContent', customElement);

        Editor.init(customElement, editorConfigurationUrl, resourcePath);
    }

    function refreshIframe () {
        log.debug('refreshIframe');

        saveScrollPosition();
        loadPageIntoIframe(iframeUrl, editorConfigurationUrl);
    }

    function showLoadingScreen () {
        if (loadingScreenLevel === 0) {
            $loadingScreen.fadeIn('fast', function showLoadingScreen () {
                $loadingScreen.removeClass(CLASS_HIDDEN);
            });
        }

        loadingScreenLevel++;
    }

    function hideLoadingScreen () {
        loadingScreenLevel--;

        if (loadingScreenLevel <= 0) {
            loadingScreenLevel = 0;
            $loadingScreen.fadeOut('slow', function hideLoadingScreen () {
                $loadingScreen.addClass(CLASS_HIDDEN);
            });
        }
    }

    function getIframe () {
        return $iframe;
    }

    /**
	 * Shows a success notification
     * @param message
     * @param title
	 * @deprecated use TYPO3/CMS/FrontendEditing/Notification instead
     */
    function showSuccess (message, title) {
        log.warn(
            'showSuccess: Deprecated function call.' +
            'Use TYPO3/CMS/FrontendEditing/Notification instead.'
        );
        Notification.success(message, title);
    }

    /**
     * Shows a error notification
     * @param message
     * @param title
     * @deprecated use TYPO3/CMS/FrontendEditing/Notification instead
     */
    function showError (message, title) {
        log.warn(
            'showError: Deprecated function call.' +
            'Use TYPO3/CMS/FrontendEditing/Notification instead.'
        );
        Notification.error(message, title);
    }

    /**
     * Shows a warning notification
     * @param message
     * @param title
     * @deprecated use TYPO3/CMS/FrontendEditing/Notification instead
     */
    function showWarning (message, title) {
        log.warn(
            'showWarning: Deprecated function call.' +
            'Use TYPO3/CMS/FrontendEditing/Notification instead.'
        );
        Notification.warning(message, title);
    }

    /**
	 * Shows a confirm modal. If message is 'notifications.unsaved-changes' a
	 * special "save all" button will be presented.
     * @param message
     * @param callbacks
	 * @deprecated
     */
    function confirm (message, callbacks) {
        log.warn(
            'confirm: Deprecated function call.' +
            'Use TYPO3/CMS/FrontendEditing/Modal instead.'
        );

        callbacks = callbacks || {};

        if (message === F.translate('notifications.unsaved-changes')) {
            Modal.confirmNavigate(message, function save () {
                if (typeof callbacks.yes === 'function') {
                    F.saveAll();
                    callbacks.yes();
                }
            }, callbacks);
        } else {
            Modal.confirm(message, callbacks);
        }
    }

    /**
     * Opens a predefined window "FEquickEditWindow" with body only in a
     * dimension of 690x500.
     * @param {string} url to open
     * @deprecated
     */
    function windowOpen (url) {
        log.warn(
            'confirm: Deprecated function call.' +
            'Use your own function instead.'
        );

        var vHWin = window.open(
            url,
            'FEquickEditWindow',
            'width=690,height=500,status=0,menubar=0,scrollbars=1,resizable=1'
        );

        vHWin.focus();

        return false;
    }

    function siteRootChange (element) {
        log.trace('siteRootChange', element);

        var linkUrl = $(element)
            .val();

        if (typeof linkUrl !== 'string' && linkUrl !== '') {
            return;
        }

        linkUrl += '?FEEDIT_BE_SESSION_KEY=' + F.getBESessionId();

        var callbacks = {
            yes: function () {
                log.debug('change site root to', linkUrl);
                window.location.href = linkUrl;
            },
            no: function () {
                element.selectedIndex = 0;
            }
        };

        if (storage.isEmpty()) {
            Module.confirm(
                translate(translateKeys.confirmChangeSiteRoot),
                callbacks
            );
        } else {
            Module.confirmNavigate(
                translate(translateKeys.confirmChangeSiteRootWithChange),
                function save () {
                    F.saveAll();
                    callbacks.yes();
                },
            	callbacks
            );
        }
    }

    return FrontendEditing;
});
