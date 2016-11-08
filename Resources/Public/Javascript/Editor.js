var Editor = (function($){

    'use strict';

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
                    href: '/typo3conf/ext/frontend_editing/Resources/Public/Css/InlineEditing.css',
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

            var that = $(this);
            // Inline editing -> delete action
            that.prev().find('.icon-actions-edit-delete').on('click', function() {
                F.confirm(F.translate('notifications.delete-content-element'), {
                    yes: function() {
                        F.delete(that.data('uid'), that.data('table'));
                    }
                });
            });

            // Inline editing -> move up action
            that.prev().find('.icon-actions-move-up').on('click', function() {
                console.log('YES');
            });
        });

        var $topBar = $('.t3-frontend-editing__top-bar');
        CKEDITOR.on('instanceReady', function(event) {

            // @TODO: This moves the dom instances of ckeditor into the top bar
            $contenteditable.each(function() {
                var editorDomInstance = $(this).ckeditor().editor.id;
                $('.' + editorDomInstance).detach().appendTo($topBar);
            });

            var editor = event.editor;

            editor.editable().on('click', function(event) {
                // Move inline action icons into the content editable div
                $.when($(event.sender.$).prepend($(event.sender.$).prev(inlineActionButtonsClass))).done(function() {
                    // Then set the icons to be visible
                    $(event.sender.$).find(inlineActionButtonsClass).css('visibility', 'visible');
                });
            });

            editor.editable().on('blur', function(event) {
                // Once again move the inline action icons back to outside the content editable div
                $(event.sender.$).before($(event.sender.$).find(inlineActionButtonsClass));
                // The hide the icons once again
                $(event.sender.$).prev().css('visibility', 'hidden');
            });

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
        init: init
    }

})(jQuery);