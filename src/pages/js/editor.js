$( document ).ready( function() {
	var detectedmode = 'htmlmixed';

	var url		= $( location ).attr( 'href' );
	var url_JS	= url.search( '.js' );
	var url_CSS	= url.search( '.css' );

	if (url_CSS > 0) {
		if (url.substring( url_CSS + 4 ) == 0 || url.substring( url_CSS + 4 ).substring( 0, 1 ) == "&" || url.substring( url_CSS + 4 ).substring( 0, 1 ) == "#") {
			detectedmode = 'javascript';
		}
	}

	if (url_JS > 0) {
		if (url.substring( url_JS + 3 ) == 0 || url.substring( url_JS + 3 ).substring( 0, 1 ) == "&" || url.substring( url_JS + 3 ).substring( 0, 1 ) == "#") {
			detectedmode = 'javascript';
		}
	}

	require([
	  "node_modules/codemirror/lib/codemirror", "node_modules/codemirror/mode/htmlmixed/htmlmixed"
	], function(CodeMirror) {
	  CodeMirror.fromTextArea(document.getElementById("Editor"), {
		lineNumbers: true,
		indentUnit: 4,
		lineWrapping: true,
		mode: detectedmode
	  });
	});

});
