(function($, w){

	var editorConfig = {

	};

	for (var i in CKEDITOR.instances) {
		CKEDITOR.instances[i].on('change', function(event) {
			var editor = event.editor;
			console.log(editor.getData());
		});
	}

})(jQuery, window);