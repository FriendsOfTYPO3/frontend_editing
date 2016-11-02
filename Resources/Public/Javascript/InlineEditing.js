(function($, w){

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

	// Clear local storage on page load
	localStorage.removeItem(localStorageKey);

	var deferred = $.Deferred();

	var iframe = $('.t3-frontend-editing__iframe-wrapper iframe').attr({
		'src': iframeUrl
	});

	iframe.on('load', deferred.resolve);

	deferred.done(function() {

		// Include Inline editing styles after iframe has loaded
		var $head = $(iframe).contents().find('head');
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
		$('.t3-frontend-editing__iframe-wrapper iframe').contents().find('a').click(function(event) {
			event.preventDefault();
			var linkUrl = $(this).attr('href');
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

		// Add custom configuration to ckeditor
		$('.t3-frontend-editing__iframe-wrapper iframe').contents().find('div[contenteditable=\'true\']').each(function() {
			$(this).ckeditor(editorConfig);
			var that = $(this);
			// Inline editing -> delete action
			that.prev().find('.icon-actions-edit-delete').click(function() {
				/*var requestUrl = pageUrl + functionRoutes.crud.url + 'delete'
					+ functionRoutes.crud.prefix + '[table]=' + that.data('table')
					+ functionRoutes.crud.prefix + '[uid]=' + that.data('uid');
				$.ajax({
					type: 'GET',
					url: requestUrl
				}).done(function(data, textStatus, jqXHR) {
					toastr.success(
						contentSaveDescriptionLabel + data.message,
						contentSaveTitleLabel,
						toastrOptions
					);
					alert('STUFF DELETED!!!');
				}).fail(function(jqXHR, textStatus, errorThrown) {
					toastr.error(
						jqXHR.responseText,
						contentSaveWentWrongLabel,
						toastrOptions
					);
				});*/
				console.log('NICE!');
			});
		});

		$('.t3-frontend-editing__loading-screen').toggle('hidden');

		CKEDITOR.on('instanceReady', function(event) {

			// @TODO: This moves the dom instances of ckeditor into the top bar
			$('.t3-frontend-editing__iframe-wrapper iframe').contents().find('div[contenteditable=\'true\']').each(function() {
				var editorDomInstance = $(this).ckeditor().editor.id;
				$('.' + editorDomInstance).detach().appendTo('.t3-frontend-editing__top-bar');
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
					var saveItems = localStorage.getItem(localStorageKey);

					if (saveItems === null || saveItems === '') {
						saveItems = Immutable.Map({});
					} else {
						saveItems = Immutable.Map(JSON.parse(saveItems));
					}

					var data = {
						'action': 'save',
						'table': dataSet.table,
						'uid': dataSet.uid,
						'field': dataSet.field,
						'editorInstance': editor.name
					};

					var processedSaveItems = saveItems.set(data.uid, data);
					localStorage.setItem(localStorageKey, JSON.stringify(processedSaveItems));

					// Add counter to number of unsaved elements for save button
					$('.top-bar-action-buttons .items-counter').html('(' + processedSaveItems.count() + ')');
				}
			});
		});
	});

})(jQuery, window);
