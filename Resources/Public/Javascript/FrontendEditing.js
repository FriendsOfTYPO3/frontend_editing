(function($, w){

    var pageUrl = window.location.protocol + '//' + window.location.host;
    var functionRoutes = {
        'crud': '?type=1470741815'
    };

    // Saving content
    $('#frontend-editing-save').click(function() {
        var items = localStorage.getItem('TYPO3:FrontendEditing');
        if (items !== null && items !== '') {
            items = JSON.parse(items);

            $.each(items, function(index) {
                var itemDomElement = $('.' + this.editorInstance);
                var data = {
                    'action': this.action,
                    'table': this.table,
                    'uid': this.uid,
                    'field': this.field,
                    'content': CKEDITOR.instances[this.editorInstance].getData()
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
            });
        }
    });

})(jQuery, window);