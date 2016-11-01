var Editor = (function($, FE){

    "use strict";

    var editorConfig = {
        entities_latin: false,
        htmlEncodeOutput: false,
        allowedContent: true,
        toolbarGroups: [
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'links' },
            { name: 'insert' },
            { name: 'tools' },
            { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'others' },
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
        ]
    };

    function init($iframe) {
        // Storage for adding and checking if it's empty when navigating to other pages
        var storage = F.getStorage();

        // The content in the iframe will be used many times
        var $iframeContents = $iframe.contents();

        // Include Inline editing styles after iframe has loaded
        var $head = $iframeContents.find('head');

        $head.append(
            $(
                '<link/>',
                {
                    rel: 'stylesheet',
                    href: '/typo3conf/ext/frontend_editing/Resources/Public/Styles/InlineEditing.css',
                    type: 'text/css'
                }
            )
        );

        // Suppress a tags (links) to redirect the normal way
        $iframeContents.find('a').click(function(event) {
            event.preventDefault();
            var linkUrl = $(this).attr('href');
            F.navigate(linkUrl);
        });

        // Add custom configuration to ckeditor
        var $contenteditable = $iframeContents.find('div[contenteditable=\'true\']');
        $contenteditable.each(function() {
            $(this).ckeditor(editorConfig);
        });

        var $topBar = $('.t3-frontend-editing__top-bar')
        CKEDITOR.on('instanceReady', function(event) {

            // @TODO: This moves the dom instances of ckeditor into the top bar
            $contenteditable.each(function() {
                var editorDomInstance = $(this).ckeditor().editor.id;
                $('.' + editorDomInstance).detach().appendTo($topBar);
            });

            var editor = event.editor;
            editor.on('change', function(changeEvent) {
                if (typeof editor.element !== 'undefined') {
                    var dataSet = editor.element.$.dataset;
                    storage.addSaveItem(dataSet.uid, {
                        'action': 'save',
                        'table': dataSet.table,
                        'uid': dataSet.uid,
                        'field': dataSet.field,
                        'editorInstance': editor.name
                    });
                    F.trigger(F.CONTENT_CHANGE);
                }
            });
        });
    }

    return {
        init: init,
    }

})(jQuery, FrontendEditing);