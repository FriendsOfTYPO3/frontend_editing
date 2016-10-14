(function($, w){

    var pageUrl = window.location.protocol + '//' + window.location.host;
    var functionRoutes = {
        'crud': '?type=1470741815'
    };
    var localStorageKey = 'TYPO3:FrontendEditing';

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
                        toastr.success('Content (uid: "' + data.message +'") have been saved!', 'Content saved');
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        toastr.error(errorThrown, 'Something went wrong');
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
            toastr.info('There are currently no changes made to the content on the page!', 'No changes made');
        }
    });

})(jQuery, window);
