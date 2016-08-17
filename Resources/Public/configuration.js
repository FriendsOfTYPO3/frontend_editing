(function($, w){

	var editorConfig = {

	};

	var url = 'http://localhost:8000/?type=1470741815';

	CKEDITOR.on('instanceReady', function(event) {
		var editor = event.editor;
		editor.on('change', function(changeEvent) {
			if (typeof editor.element !== 'undefined') {
				var dataSet = editor.element.$.dataset;
				var data = {
					'action': 'save',
					'table': dataSet.table,
					'identifier': dataSet.identifier,
					'field': 'bodytext',
					'content': editor.getData()
				};

				$.ajax({
					type: 'POST',
					url: url,
					dataType: 'JSONP',
					data: data,
					success: function(event) {
						console.log(event);
					},
					error: function(event) {
						console.error(event);
					}
				});
			}
		});
	});

})(jQuery, window);