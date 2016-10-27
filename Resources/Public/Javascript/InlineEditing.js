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

	var localStorageKey = 'TYPO3:FrontendEditing';
	// Clear local storage on page load
	localStorage.removeItem(localStorageKey);

	var deferred = $.Deferred();

	var iframe = $('.t3-frontend-editing__iframe-wrapper iframe').attr({
		'src': FrontendEditing.iframeUrl
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
					href: '/typo3conf/ext/frontend_editing/Resources/Public/App/InlineEditing.css',
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
		});

		CKEDITOR.on('instanceReady', function(event) {

			// @TODO: This moves the dom instances of ckeditor into the top bar
			$('.t3-frontend-editing__iframe-wrapper iframe').contents().find('div[contenteditable=\'true\']').each(function() {
				var editorDomInstance = $(this).ckeditor().editor.id;
				$('.' + editorDomInstance).detach().appendTo('.t3-frontend-editing__top-bar');
			});

			var editor = event.editor;
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
