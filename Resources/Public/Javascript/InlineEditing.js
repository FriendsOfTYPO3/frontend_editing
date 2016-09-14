(function($, w){

	var editorConfig = {
		entities_latin: false,
		htmlEncodeOutput: false,
		allowedContent: true,
		toolbarGroups: [
			{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
			{ name: 'editing', groups: [ 'find', 'selection' ] },
			{ name: 'links' },
			{ name: 'insert' },
			{ name: 'tools' },
			{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
			{ name: 'others' },
			'/',
			{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
			{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
			{ name: 'styles' }
		]
	};

	var localStorageKey = 'TYPO3:FrontendEditing';
	// Clear local storage on page load
	localStorage.removeItem(localStorageKey);

	var deferred = $.Deferred();
	var iframe = $('.frontend-editing-iframe-wrapper iframe').attr({
		'src': iframeUrl
	});

	iframe.load(deferred.resolve);

	deferred.done(function() {

		// Add custom configuration to ckeditor
		$('.frontend-editing-iframe-wrapper iframe').contents().find('div[contenteditable=\'true\']').each(function() {
			$(this).ckeditor(editorConfig);
		});

		CKEDITOR.on('instanceReady', function(event) {

			// @TODO: This moves the dom instances of ckeditor into the top bar
			$('.frontend-editing-iframe-wrapper iframe').contents().find('div[contenteditable=\'true\']').each(function() {
				var editorDomInstance = $(this).ckeditor().editor.id;
				$('.' + editorDomInstance).detach().appendTo('.frontend-editing-top-bar');
			});

			var editor = event.editor;
			editor.on('change', function(changeEvent) {
				if (typeof editor.element !== 'undefined') {
					var dataSet = editor.element.$.dataset;
					var saveItems = localStorage.getItem(localStorageKey);

					if (saveItems === null || saveItems === '') {
						saveItems = [];
					} else {
						saveItems = JSON.parse(saveItems);
					}

					var data = {
						'action': 'save',
						'table': dataSet.table,
						'uid': dataSet.uid,
						'field': dataSet.field,
						'editorInstance': editor.name
					};

					saveItems.push(data);
					localStorage.setItem(localStorageKey, JSON.stringify(saveItems));
				}
			});
		});
	});

})(jQuery, window);