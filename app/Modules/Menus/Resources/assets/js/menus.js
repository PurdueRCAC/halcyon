
jQuery(document).ready(function ($) {
	var alias = $('#field-alias');
	if (alias.length && !alias.val()) {
		$('#field-title,#field-alias').on('keyup', function (e){
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}

	var alias = $('#field-menutype');
	if (alias.length && !alias.val()) {
		$('#field-title,#field-menutype').on('keyup', function (e) {
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}
});
