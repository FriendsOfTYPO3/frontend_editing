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
 * Module: TYPO3/CMS/FrontendEditing/Editor
 * Used to initialize ckeditor and configure DOM interaction in iFrame
 */
define([
    'jquery',
    './Utils/TranslatorLoader',
    './Utils/Logger',
    './Modal'
], function createEditorControl (
    $,
    TranslatorLoader,
    Logger,
    Modal
) {
    'use strict';

    var log = Logger('FEditing:Editor');
    log.trace('--> createEditorControl');

    var translateKeys = {
        confirmOpenModalWithChange: 'notifications.unsaved-changes',
        confirmDeleteContentElement: 'notifications.delete-content-element',
        informRequestFailed: 'notifications.request.configuration.fail',
    };

    var translator = TranslatorLoader
        .useTranslator('editor', function reload (t) {
            translateKeys = $.extend(translateKeys, t.getKeys());
        }).translator;

    function translate () {
        return translator.translate.apply(translator, arguments);
    }

    var defaultEditorConfig = {
        'skin': 'moono',
        'entities_latin': false,
        'htmlEncodeOutput': false,
        'allowedContent': true,
        'customConfig': '',
        'stylesSet': [],
        'autoParagraph': false
    };

    /**
	 * Default simple toolbar for non CKEditor (RTE) field
	 *
	 * @type {{toolbarGroups: [*]}}
	 */
    var defaultSimpleEditorConfig = {
        toolbarGroups: [
            {
                name: 'clipboard',
                groups: ['clipboard', 'undo']
            }
        ]
    };

    var storage;

    var $iFrameContents;
    var $topBar;

    return {
        init: init
    };

    function init ($iframe, configurationUrl, resourcePath) {
        log.trace('init', $iframe, configurationUrl, resourcePath);

        // Only the content in the iframe will be used
        $iFrameContents = $iframe.contents();
        $topBar = $('.t3-frontend-editing__ckeditor-bar');

        loadStorage();

        appendInlineEditingStylesheet(resourcePath);

        suppressAnchorTagsToRedirect();

        prepareInlineActions();

        configureEditableContent(configurationUrl);
    }

    function appendInlineEditingStylesheet (resourcePath) {
        $iFrameContents.find('head')
            .append(
                $(
                    '<link/>',
                    {
                        rel: 'stylesheet',
                        href: resourcePath + 'Css/inline_editing.css',
                        type: 'text/css'
                    }
                )
            );
    }

    function suppressAnchorTagsToRedirect () {
        $iFrameContents.find('a')
            .click(function navigateWithAPI (event) {
                log.debug('iframe content anchor clicked', this);

                if (event.isDefaultPrevented()) {
                    log.trace('click event is prevented', event);

                    return;
                }

                var linkUrl = this.href;
                if (!linkUrl || linkUrl.indexOf('#') === 0) {
                    return;
                }

                event.preventDefault();
                F.navigate(linkUrl);
            });
    }

    function loadStorage () {
        // Storage for adding and checking if it's empty when navigating to
        // other pages
        // TODO: replace adding and checking by simple signals
        storage = F.getStorage();
    }

    function isStorageEmpty () {
        return storage.isEmpty();
    }

    function addSaveItem (id, saveItem) {
        log.trace('addSaveItem', id, saveItem);

        return storage.addSaveItem(id, saveItem);
    }

    function prepareInlineActions () {
        var $inlineActions = $iFrameContents
            .find('span.t3-frontend-editing__inline-actions');

        log.debug('prepare inline-actions', $inlineActions);

        $inlineActions.each(function defaultInitializeInlineActions (index) {
            log.trace('defaultInitializeInlineActions', index);

            var $inlineAction = $(this);
            var previous = index > 0 ? $inlineActions[index - 1] : null;
            var next = index < $inlineActions.length - 1
                ? $inlineActions[index + 1] : null;

            initializeInlineAction($inlineAction, previous, next);
            $inlineAction.data('t3-frontend-editing-initialized', true);
        });

        // Make sure that inline actions inserted after iFrame page
        // initialization are initialized.
        $iFrameContents.on('mouseover',
            'span.t3-frontend-editing__inline-actions',
            function postInitializeInlineAction () {
                //TODO: Find a clean solution to update move up/move down action
                var $inlineAction = $(this);
                if (!$inlineAction.data('t3-frontend-editing-initialized')) {
                    log.debug('prepare inline-actions', $inlineActions);

                    initializeInlineAction.call(this, [$inlineAction]);
                    $inlineAction.data('t3-frontend-editing-initialized', true);
                }
            });
    }

    function initializeInlineAction ($inlineAction, previous, next) {
        log.trace('initializeInlineAction', $inlineAction, previous, next);

        var uid = $inlineAction.data('uid');
        var table = $inlineAction.data('table');
        var editUrl = $inlineAction.data('edit-url');
        var newUrl = $inlineAction.data('new-url');

        var hidden = String($inlineAction.data('hidden'));
        var cid = String($inlineAction.data('cid'));

        $inlineAction.find('img')
            .on('dragstart', function disableDrag (event) {
                log.debug('prevent drag on image', this);

                event.preventDefault();
                return false;
            });

        $inlineAction.find('.icon-actions-open, .icon-actions-document-new')
            .on('click', function openEditOrNewDocAction () {
                var $this = $(this);
                var identifier = $this.data('identifier');

                log.info('open modal action', identifier);

                var url = editUrl;
                if (identifier === 'actions-document-new') {
                    url = newUrl;
                }

                if (isStorageEmpty()) {
                    openModal(url);
                    return;
                }

                Modal.confirmNavigate(
                    translate(translateKeys.confirmOpenModalWithChange),
                    function save () {
                        F.saveAll();
                        openModal(url);
                    }, {
                        yes: function () {
                            openModal(url);
                        },
                    }
                );

            });

        $inlineAction.find('.icon-actions-edit-delete')
            .on('click', function deleteAction () {
                log.info('delete action', uid, table);

                Modal.confirm(
                    translate(
                        translateKeys.confirmDeleteContentElement
                    ), {
                        yes: function () {
                            F.delete(uid, table);
                        },
                    }
                );
            });

        $inlineAction
            .find('.icon-actions-edit-hide, .icon-actions-edit-unhide')
            .on('click', function toggleHidden () {
                log.info('toggle hide action', uid, table, hidden);

                var hide = 1;
                if (String(hidden) === '1') {
                    hide = 0;
                }
                F.hideContent(uid, table, hide);
            });

        var $moveUpButton = $inlineAction.find('.icon-actions-move-up');
        if (previous && String(previous.dataset.cid) === cid) {
            $moveUpButton.on('click', function moveContentUp () {
                log.info('move content up action', uid, table);

                F.moveContent(previous.dataset.uid, table, uid);
            });
        } else {
            $moveUpButton.hide();
        }

        var $moveDownButton = $inlineAction.find('.icon-actions-move-down');
        if (next && String(next.dataset.cid) === cid) {
            $moveDownButton.on('click', function moveContentDown () {
                log.info('move content down action', uid, table);

                F.moveContent(uid, table, next.dataset.uid);
            });
        } else {
            $moveDownButton.hide();
        }
    }

    function openModal (url) {
        require([
            'jquery',
            'TYPO3/CMS/Backend/Modal',
            'TYPO3/CMS/Backend/Toolbar/ShortcutMenu' //used cause of side effect
        ], function createIFrameModal ($, Modal) {
            log.debug('open modal', url);

            Modal.advanced({
                type: Modal.types.iframe,
                title: '',
                content: url,
                size: Modal.sizes.large,
                // bad naming is cause of typo3 lib
                // eslint-disable-next-line id-denylist
                callback: function (currentModal) {
                    var modalIframe = currentModal.find(Modal.types.iframe);
                    modalIframe.attr('name', 'list_frame');

                    log.debug('modal ready', currentModal);

                    modalIframe.on('load', function propagateTypo3 () {
                        log.trace(
                            'propagate Typo3 environment',
                            window.TYPO3,
                            modalIframe[0].contentWindow.TYPO3
                        );

                        $.extend(
                            window.TYPO3,
                            modalIframe[0].contentWindow.TYPO3 || {}
                        );

                        // Simulate BE environment with correct CKEditor
                        // instance for RteLinkBrowser
                        top.TYPO3.Backend = top.TYPO3.Backend || {};
                        top.TYPO3.Backend.ContentContainer = {
                            get: function () {
                                return modalIframe[0].contentWindow;
                            }
                        };

                        log.debug(
                            'Typo3 environment propagated',
                            window.TYPO3,
                            top.TYPO3.Backend
                        );
                    });

                    currentModal.on('hidden.bs.modal',
                        function cleanupModalIFrame () {
                            log.trace('clean up modal iFrame');

                            delete top.TYPO3.Backend.ContentContainer;
                            F.refreshIframe();
                        });
                }
            });
        });
    }

    /***********************************/
    /*                                 */
    /*  CKEditor Configuration section */
    /*                                 */
    /***********************************/

    function configureEditableContent (configurationUrl) {
        log.trace('configureEditableContent', configurationUrl);

        // Add custom configuration to ckeditor
        var configurableEditableElements = [];
        var requestData = [];

        $iFrameContents.find('[contenteditable=\'true\']')
            .each(function initEditorAndConfigureInlineEditor () {
                var $editableContent = $(this);
                var $parent = $editableContent.parent();

                log.debug('init editor', $editableContent);

                // Prevent linked content element to be clickable in the
                // frontend editing mode
                if ($parent.is('a')) {
                    $parent.on('click', function preventOtherHandler (event) {
                        log.debug('preventParentClickHandler', this);

                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();
                    });
                }

                var uid = $editableContent.data('uid');
                var table = $editableContent.data('table');
                var field = $editableContent.data('field');

                var id = uid + '_' + field + '_' + table;

                var tagName = $editableContent.prop('tagName');
                // TODO check if div or in general a block element is needed
                if (tagName.toLowerCase() === 'div') {
                    // configure as CKeditor instance
                    configurableEditableElements.push(this);
                    requestData.push({
                        'table': table,
                        'uid': uid,
                        'field': field
                    });
                    return;
                }

                log.debug('configure non ckeditor', id);

                // inline elements are disallowed for CKeditor instance
                var saveItem = {
                    'action': 'save',
                    'table': table,
                    'uid': uid,
                    'field': field,
                    'hasCkeditorConfiguration': null,
                    'editorInstance': null,
                    'inlineElement': true,
                    'text': $editableContent.text()
                };

                $editableContent.on(
                    'blur keyup paste input',
                    function persistNonCkEditorChanges () {
                        saveItem.text = $editableContent.text();

                        log.debug('persist non ckEditor changes', id, saveItem);

                        addSaveItem(id, saveItem);
                        F.trigger(F.CONTENT_CHANGE);
                    });
            });

        if (requestData.length > 0) {
            F.showLoadingScreen();

            log.debug(
                'load ckeditor configuration',
                configurationUrl,
                requestData
            );

            $.ajax({
                url: configurationUrl,
                method: 'POST',
                dataType: 'json',
                // wording is part of jQuery API
                // eslint-disable-next-line id-denylist
                data: {
                    'elements': requestData
                }
            })
                .done(function handleConfigureResponse (response) {
                    log.debug('handleConfigureResponse', response);

                    var $editableElements = $(configurableEditableElements);
                    $editableElements.each(function initEditor () {
                        var $editableElement = $(this);

                        var uid = $editableElement.data('uid');
                        var table = $editableElement.data('table');
                        var field = $editableElement.data('field');
                        var elementIdentifier = uid + '_' + table + '_' + field;

                        var elementData = response.configurations[
                            response.elementToConfiguration[elementIdentifier]
                        ];
                        configureCkEditor($editableElement, elementData);
                    });
                })
                .fail(handleConfigurationRequestError)
                .always(F.hideLoadingScreen);
        }
    }

    function configureCkEditor ($editableElement, elementData) {
        log.trace('configureCkEditor', $editableElement, elementData);

        var uid = $editableElement.data('uid');
        var table = $editableElement.data('table');
        var field = $editableElement.data('field');
        var elementIdentifier = uid + '_' + table + '_' + field;

        // Ensure all plugins / buttons are loaded
        if (typeof elementData.externalPlugins !== 'undefined') {
            log.debug(
                'load external plugings by eval',
                elementData.externalPlugins
            );

            //TODO: check if eval could be replaced by better solution
            // eslint-disable-next-line no-eval
            eval(elementData.externalPlugins);
        }


        var config = $.extend(true, {},
            defaultEditorConfig,
            elementData.configuration
        );
        if (!elementData.hasCkeditorConfiguration) {
            $.extend(true, config, defaultSimpleEditorConfig);
        }

        log.debug('initialize ckEditor instance', elementIdentifier, config);

        // Initialize CKEditor now,
        // when finished remember any change
        var ckeditor = $editableElement.ckeditor(config);
        ckeditor.on('instanceReady.ckeditor',
            function bindCkEditorHandler (event, editor) {
                log.debug('ckEditor instance is ready', event, editor);

                // This moves the dom instances of ckeditor
                // into the top bar
                $('.' + editor.id)
                    .detach()
                    .appendTo($topBar);

                var saveItem = {
                    'action': 'save',
                    'table': table,
                    'uid': uid,
                    'field': field,
                    'hasCkeditorConfiguration':
                    elementData.hasCkeditorConfiguration,
                    'editorInstance': editor.name
                };

                editor.on('change', function persistEditorChangedIndicator () {
                    log.trace('persistEditorChangedIndicator');

                    if (typeof editor.element !== 'undefined') {
                        log.debug(
                            'persist saveItem',
                            elementIdentifier,
                            saveItem
                        );

                        addSaveItem(elementIdentifier, saveItem);
                        F.trigger(F.CONTENT_CHANGE);
                    }
                });
            });
    }

    function handleConfigurationRequestError (response) {
        log.error(
            'CKEditor configuration request failed',
            response
        );

        F.trigger(F.REQUEST_ERROR, {
            message: translate(
                translateKeys.informRequestFailed,
                response.status,
                response.statusText
            )
        });
    }
});
