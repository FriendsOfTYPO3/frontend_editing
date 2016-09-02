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
					'uid': dataSet.uid,
					'field': dataSet.field,
					'content': editor.getData()
				};

				$.ajax({
					type: 'POST',
					url: url,
					dataType: 'JSON',
					data: data
				}).done(function(data, textStatus, jqXHR) {
					toastr.success('Content (' + data.message +') have been saved!', 'Content saved');
				}).fail(function(jqXHR, textStatus, errorThrown) {
					toastr.error(errorThrown, 'Something went wrong');
				});
			}
		});
	});

})(jQuery, window);