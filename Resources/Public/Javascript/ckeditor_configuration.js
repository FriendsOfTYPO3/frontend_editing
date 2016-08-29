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
					dataType: 'JSONP',
					data: data
				}).fail(function(jqXHR, textStatus, errorThrown) {
					if (jqXHR.status === 200) {
						toastr.success('Content have been saved!', 'Content saved');
					} else {
						toastr.error(textStatus, 'Something went wrong');
					}
				});
			}
		});
	});

})(jQuery, window);