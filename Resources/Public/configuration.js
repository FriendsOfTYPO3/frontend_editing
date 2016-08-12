(function($, w){

	var editorConfig = {

	};


	var url = 'http://localhost:8000/?type=1470741815';

	CKEDITOR.on('instanceReady', function(event) {
		var editor = event.editor;
		editor.on('change', function(event) {

			/*
			*         $split = explode('--', $request['identifier']);

			 if (count($split) != 3) {
			 throw new \Exception(Helper::ll('error.request.identifier'));
			 } elseif (empty($split[0])) {
			 throw new \Exception(Helper::ll('error.request.table'));
			 } elseif (empty($split[1])) {
			 throw new \Exception(Helper::ll('error.request.field'));
			 } elseif (!ctype_digit($split[2])) {
			 throw new \Exception(Helper::ll('error.request.uid'));*/

			var data = {
				'action': 'save',
				'table': 'tt_content',
				'identifier': '1',
				'content': editor.getData()
			};

			/*var result = $.post(url, data, function(event) {
					alert(event);
				})
				.done(function(event) {
					alert(event);
				})
				.fail(function() {
					alert(event);
				});*/

			$.ajax({
				type: 'POST',
				url: url,
				data: data,
				success: function(event) {
					console.log(event);
				},
				error: function(event) {
					console.error(event);
				}
			});
		});
	});

})(jQuery, window);