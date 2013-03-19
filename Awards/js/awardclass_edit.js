jQuery(document).ready(function(){
	// Load the syntax highlighter for the CSS textarea
	var ClassCSSEditor = CodeMirror.fromTextArea(document.getElementById('Form_AwardClassCSS'),
																							 {
																									lineNumbers: true
																							 });
	$(".CodeMirror").resizable({
      resize: function() {
				ClassCSSEditor.setSize($(this).width(), $(this).height());
      }
	});
});
