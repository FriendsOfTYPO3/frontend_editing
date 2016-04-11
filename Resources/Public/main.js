(function($, w){



	var editorConfig = {
        uiColor : '#FF0000'
	};

	$('p').attr('contenteditable', true).ckeditor(editorConfig, function(e){

	});

	for(var i in CKEDITOR.instances){
		CKEDITOR.instances[i].on('change', function(evt){
			var editor = evt.editor;
			console.log(editor.getData());
		});
	}
	


	/*
	CKEDITOR.inline( 'c164',
    {
        toolbar : 'links',
        uiColor : 'yellow'
    });
	*/	

})(jQuery, window);