(function($, w){

	var editorConfig = {

	};

	CKEDITOR.on('instanceReady', function(event) {
		var editor = event.editor;
		editor.on('change', function(event) {
			console.log(editor.getData());
		});
	});

})(jQuery, window);