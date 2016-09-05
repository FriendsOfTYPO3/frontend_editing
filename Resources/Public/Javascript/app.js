(function($, w){

	var editorConfig = {
		entities_latin: false,
		htmlEncodeOutput: false
	};

	var pageUrl = window.location.protocol + '//' + window.location.host;
	var functionRoutes = {
		'crud': '?type=1470741815'
	};

	// Add additional configuration to all 'contenteditable' instances
	$('body').find('div[contenteditable=\'true\']').each(function() {
		$(this).ckeditor(editorConfig);
	});

	CKEDITOR.on('instanceReady', function(event) {
		var editor = event.editor;
		editor.on('change', function(changeEvent) {
			if (typeof editor.element !== 'undefined') {
				var dataSet = editor.element.$.dataset;
				var data = {
					'action': 'save',
					'table': dataSet.table,
					'uid': dataSet.uid,
					'field': dataSet.field,
					'content': editor.getData()
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
	});

})(jQuery, window);