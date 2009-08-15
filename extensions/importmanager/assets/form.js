$(document).ready(function() {
	var createExample = function() {
		var importer = $('#importmanager-importer').val();
		var source = $('#importmanager-source').val();
		var url = '', base = location.href.split('?')[0];
		
		if (!importer || !source) {
			$('#importmanager-example').hide();
		} else {
			$('#importmanager-example').show();
		}
		
		url = '?' + importer + '=' + source;
		
		$('#importmanager-example').html('Automatic import URL: <a href="' + base + url + '">' + url + '</a>.');
	}
	
	$('#importmanager-source')
		.parents('label')
		.after('<p id="importmanager-example" class="help"></p>');
	
	$('#importmanager-importer').change(createExample);
	$('#importmanager-source').change(createExample).keyup(createExample);
	
	createExample();
});