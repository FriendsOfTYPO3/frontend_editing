(function($){

    'use strict';

    // Extend FrontendEditing with the following functions
    FrontendEditing.prototype.initGUI = init;
    FrontendEditing.prototype.showLoadingScreen = showLoadingScreen;
    FrontendEditing.prototype.hideLoadingScreen = hideLoadingScreen;
    FrontendEditing.prototype.refreshIframe = refreshIframe;
    FrontendEditing.prototype.showSuccess = showSuccess;
    FrontendEditing.prototype.showError = showError;
    FrontendEditing.prototype.showWarning = showWarning;

    var CLASS_HIDDEN = 'hidden';

    var messageTypes = {
        OK: 'OK',
        ERROR: 'ERROR',
        WARNING: 'WARNING',
    };

    var toastrOptions = {
        'positionClass': 'toast-top-left',
        'preventDuplicates': true
    };

    var $itemCounter;
    var $iframe;
    var $loadingScreen;
    var iframeUrl;
    var iframeLoadedCallback;
    var storage;

    function init(options) {
        $itemCounter = $('.top-bar-action-buttons .items-counter');
        $iframe = $('.t3-frontend-editing__iframe-wrapper iframe');
        $loadingScreen = $('.t3-frontend-editing__loading-screen');

        initListeners();
        bindActions();
        loadPageIntoIframe(options.iframeUrl, options.iframeLoadedCallback);
        iframeLoadedCallback = options.iframeLoadedCallback;
        storage = F.getStorage();
    }

    function initListeners() {
        F.on(F.REQUEST_START, function() {
            showLoadingScreen();
        });

        F.on(F.UPDATE_CONTENT_COMPLETE, function(data) {
            showSuccess(
                contentSaveTitleLabel,
                data.message
            );
        });

        F.on(F.REQUEST_ERROR, function(data) {
            showError(
                data.message,
                contentSaveWentWrongLabel
            );
        });

        F.on(F.REQUEST_COMPLETE, function() {
            refreshIframe();
        });

        F.on(F.CONTENT_CHANGE, function(items) {
            var items = storage.getSaveItems();
            if (items.count()) {
                $itemCounter.html('(' + items.count() + ')');
            } else {
                $itemCounter.html('');
            }
        });
    }

    function bindActions() {
        $('.t3-frontend-editing__save').on('click', function(e) {
            if (!storage.isEmpty()) {
                F.saveAll();
            } else {
                showWarning(
                    contentNoChangesTitleLabel, 
                    contentNoChangesDescriptionLabel
                );
            }
        });

        $('.t3-frontend-editing__discard').on('click', function() {
            if (!storage.isEmpty() && F.confirm(contentRemoveAllChangesLabel)){
                storage.clear();
                F.refreshIframe();
                F.trigger(F.CONTENT_CHANGE);
            }
        });

        var t = 0;
        var y = 0;

        // Add check for page tree navigation
        $('.t3-frontend-editing__page-tree li').click(function() {
            var linkUrl = $(this).data('url');
            F.navigate(linkUrl);
        });

        $('.right-bar-button').on('click', function () {
            $('.t3-frontend-editing__top-bar-right').toggleClass('push-toleft');
            $('.t3-frontend-editing__iframe-wrapper').toggleClass('push-toleft-iframe');
            $('.t3-frontend-editing__right-bar').toggleClass('open');
            t = ++t % 2;
            $('.t3-frontend-editing__right-bar').stop().animate({ right: t ? 0 : -310 }, 200);
        });

        $('.left-bar-button').on('click', function() {
            $('.t3-frontend-editing__top-bar-left').toggleClass('push-toright');
            $('.t3-frontend-editing__left-bar').toggleClass('open');
            y = ++y % 2;
            $('.t3-frontend-editing__left-bar').stop().animate({ left: y ? 0 : -280 }, 200);
        });

        $('.page-seo-devices span').on('click', function() {
            $('.page-seo-devices').find('span').removeClass('active');
            $(this).addClass('active');
            $('.t3-frontend-editing__iframe-wrapper iframe').animate({
                'width': $(this).data('width')
            });
        });

        $('.accordion .trigger').on('click', function(){
            $(this).toggleClass('active');
            $(this).closest('.accordion-container').find('.accordion-content').slideToggle(200);
        });

        $('.accordion .grid').on('click', function(){
            $(this).closest('.accordion-container')
                .removeClass('accordion-list')
                .addClass('accordion-grid');
        });

        $('.list-view').on('click', function(){
            $(this).closest('.accordion-container')
                .removeClass('accordion-grid')
                .addClass('accordion-list');
        });
    }

    function loadPageIntoIframe(url, callback) {
        showLoadingScreen();
        var deferred = $.Deferred();

        $iframe.attr({
            'src': url
        });

        $iframe.on('load', deferred.resolve);

        deferred.done(function() {
            callback($iframe);
            hideLoadingScreen();
        });

        iframeUrl = url;
    }

    function refreshIframe() {
        loadPageIntoIframe(iframeUrl, iframeLoadedCallback);
    };

    function showLoadingScreen() {
        $loadingScreen.removeClass(CLASS_HIDDEN);
    }

    function hideLoadingScreen() {
        $loadingScreen.addClass(CLASS_HIDDEN);
    }

    function flashMessage(type, title, message) {
        var toastrFunction;
        switch(type) {
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

    function showSuccess(title, message) {
        flashMessage(messageTypes.OK, title, message);
    }
    function showError (title, message) {
        flashMessage(messageTypes.ERROR, title, message);
    }
    function showWarning (title, message) {
        flashMessage(messageTypes.WARNING, title, message);
    }

}(jQuery));