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
 * Editor: used in iframe for DOM interaction
 */
var Editor = (function($){

    'use strict';

    var editorConfig = {
        skin: 'moono',
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
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] }
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

        // Find all t3-frontend-editing__inline-actions
        var $inlineActions = $iframeContents.find('span.t3-frontend-editing__inline-actions');
        $inlineActions.each(function(index) {
            var that = $(this);

            // Disable dragging icons by mistake
            that.find('img').on('dragstart', function() {
                return false;
            });

            // Open/edit action
            that.find('.icon-actions-open').on('click', function() {
                F.windowOpen(that.data('edit-url'));
            });

            // Delete action
            that.find('.icon-actions-edit-delete').on('click', function() {
                F.confirm(F.translate('notifications.delete-content-element'), {
                    yes: function() {
                        F.delete(that.data('uid'), that.data('table'));
                    }
                });
            });

            // Hide/Unhide action
            that.find('.icon-actions-edit-hide, .icon-actions-edit-unhide').on('click', function() {
                var hide = 1;
                if (that.data('hidden') == 1) {
                    hide = 0;
                }
                F.hideContent(that.data('uid'), that.data('table'), hide);
            });

            if (typeof $inlineActions[index - 1] !== 'undefined' &&
                $inlineActions[index - 1].dataset.cid == that.data('cid')
            ) {
                // Move up action
                that.find('.icon-actions-move-up').on('click', function() {
                    // Find the previous editor instance element
                    var previousDomElementUid = $inlineActions[index - 1].dataset.uid;
                    var currentDomElementUid = that.data('uid');
                    var currentDomElementTable = that.data('table');
                    F.moveContent(previousDomElementUid, currentDomElementTable, currentDomElementUid)
                });
            } else {
                that.find('.icon-actions-move-up').hide();
            }

            if (typeof $inlineActions[index + 1] !== 'undefined' &&
                $inlineActions[index + 1].dataset.cid == that.data('cid')
            ) {
                // Move down action
                that.find('.icon-actions-move-down').on('click', function() {
                    // Find the next editor instance element
                    var nextDomElementUid = $inlineActions[index + 1].dataset.uid;
                    var currentDomElementUid = that.data('uid');
                    var currentDomElementTable = that.data('table');
                    F.moveContent(currentDomElementUid, currentDomElementTable, nextDomElementUid)
                });
            } else {
                that.find('.icon-actions-move-down').hide();
            }
        });

        // Add custom configuration to ckeditor
        var $contenteditable = $iframeContents.find('div[contenteditable=\'true\']');
        $contenteditable.each(function(index) {
            var that = $(this);
            that.ckeditor(editorConfig);
        });

        var $topBar = $('.t3-frontend-editing__top-bar');
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
                    storage.addSaveItem(dataSet.uid + '_' + dataSet.field, {
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
