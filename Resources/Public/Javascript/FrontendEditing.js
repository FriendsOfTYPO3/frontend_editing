(function($, w){

    var pageUrl = window.location.protocol + '//' + window.location.host;
    var functionRoutes = {
        'crud': '?type=1470741815'
    };
    var localStorageKey = 'TYPO3:FrontendEditing';
    var toastrOptions = {'positionClass': 'toast-top-left'};

    // Saving content
    $('.t3-frontend-editing__save').click(function() {
        var items = localStorage.getItem(localStorageKey);
        if (items !== null && items !== '') {
            items = JSON.parse(items);

            $('.t3-frontend-editing__loading-screen').toggle('hidden');

            $.each(items, function(index) {
                var item = items[index];
                if (item !== null) {
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
                            'Content (uid: "' + data.message +'") have been saved!',
                            'Content saved',
                            toastrOptions
                        );
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        toastr.error(
                            jqXHR.responseText,
                            'Something went wrong',
                            toastrOptions
                        );
                    });
                }
            });

            // Wait until all ajax requests are done
            $(document).ajaxStop(function () {
                $('.t3-frontend-editing__loading-screen').toggle('hidden');
                // Clear local storage after save
                localStorage.removeItem(localStorageKey);
            });

        } else {
            toastr.info(
                'There are currently no changes made to the content on the page!',
                'No changes made',
                toastrOptions
            );
        }
    });

    // Discard button
    $('.t3-frontend-editing__discard').click(function() {
        var numberOfUnsavedItems = localStorage.getItem(localStorageKey);
        if (numberOfUnsavedItems !== null && numberOfUnsavedItems !== '') {
            var confirmed = confirm('Are you sure you want to remove all unsaved changes?');
            if (confirmed) {
                localStorage.removeItem(localStorageKey);
            }
        }
    });

    var t = 0;
    var y = 0;

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
        $('span').removeClass('active');
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
