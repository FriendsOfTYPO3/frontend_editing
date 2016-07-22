(function($, w){

	var editorConfig = {

	};

	$('h2').attr('contenteditable', true).ckeditor(editorConfig, function(e){

	});

	for (var i in CKEDITOR.instances){
		CKEDITOR.instances[i].on('change', function(evt){
			var editor = evt.editor;
			console.log(editor.getData());
		});
	}

})(jQuery, window);