(function($, w){

    var pageUrl = window.location.protocol + '//' + window.location.host;
    var functionRoutes = {
        'crud': '?type=1470741815'
    };
    var localStorageKey = 'TYPO3:FrontendEditing';
    var toastrOptions = {
        'positionClass': 'toast-top-left',
        'preventDuplicates': true
    };

    // Saving content
    $('.t3-frontend-editing__save').click(function() {
        var items = localStorage.getItem(localStorageKey);
        if (items !== null && items !== '') {
            items = JSON.parse(items);
            items = Immutable.Map(items);

            items.forEach(function(item) {
                var data = {
                    'action': item.action,
                    'table': item.table,
                    'uid': item.uid,
                    'field': item.field,
                    'content': CKEDITOR.instances[item.editorInstance].getData()
                };

                $.ajax({
                    type: 'POST',
                    url: pageUrl + functionRoutes.crud,
                    dataType: 'JSON',
                    data: data
                }).done(function(data, textStatus, jqXHR) {
                    toastr.success(
                        contentSaveDescriptionLabel + data.message,
                        contentSaveTitleLabel,
                        toastrOptions
                    );
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    toastr.error(
                        jqXHR.responseText,
                        contentSaveWentWrongLabel,
                        toastrOptions
                    );
                });
            });

            // Wait until all ajax requests are done
            $(document).ajaxStop(function () {
                // Remove counter for number of unsaved elements for save button
                $('.top-bar-action-buttons .items-counter').html('');
                // Clear local storage after save
                localStorage.removeItem(localStorageKey);
            });

        } else {
            toastr.info(
                contentNoChangesDescriptionLabel,
                contentNoChangesTitleLabel,
                toastrOptions
            );
        }
    });

    // Discard button
    $('.t3-frontend-editing__discard').click(function() {
        var numberOfUnsavedItems = localStorage.getItem(localStorageKey);
        if (numberOfUnsavedItems !== null && numberOfUnsavedItems !== '') {
            var confirmed = confirm(contentRemoveAllChangesLabel);
            if (confirmed) {
                // Remove counter for number of unsaved elements for save button
                $('.top-bar-action-buttons .items-counter').html('');
                localStorage.removeItem(localStorageKey);
            }
        }
    });

    // Add check for page tree navigation
    $('.t3-frontend-editing__page-tree li').click(function() {
        var linkUrl = $(this).data('url');
        if (linkUrl && linkUrl !== '#') {
            var numberOfItems = localStorage.getItem(localStorageKey);
            if (numberOfItems !== null && numberOfItems !== '') {
                var confirmed = confirm(contentUnsavedChangesLabel);
                if (confirmed) {
                    window.location.href = linkUrl;
                }
            } else {
                window.location.href = linkUrl;
            }
        }
    });

    var t = 0;
    var y = 0;


    $('#t3-frontend-editing').on('click', '.right-bar-button', function () {
        $('.t3-frontend-editing__top-bar-right').toggleClass('push-toleft');
        $('.t3-frontend-editing__iframe-wrapper').toggleClass('push-toleft-iframe');
        $('.t3-frontend-editing__right-bar').toggleClass('open');
        t = ++t % 2;
        $('.t3-frontend-editing__right-bar').stop().animate({ right: t ? 0 : -310 }, 200);
    });

    $('#t3-frontend-editing').on('click', '.left-bar-button', function () {
        $('.t3-frontend-editing__top-bar-left').toggleClass('push-toright');
        $('.t3-frontend-editing__left-bar').toggleClass('open');
        y = ++y % 2;
        $('.t3-frontend-editing__left-bar').stop().animate({ left: y ? 0 : -280 }, 200);
    });

    $('.page-seo-devices span').on('click', function() {
        $('.page-seo-devices').find('span').removeClass('active');
        $(this).addClass('active');
        $('.t3-frontend-editing__iframe-wrapper iframe').animate({
            'width': $(this).data('width'),
            'height': $(this).data('height')
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

})(jQuery, window);
