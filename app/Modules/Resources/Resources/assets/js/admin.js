/* global $ */ // jquery.js
/* global jQuery */ // jquery.js

jQuery(document).ready(function () {

	$('#field-name').on('keyup', function (){
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '_')
			.replace(/[^a-z0-9-_]+/g, '');

		var rolename = $('#field-rolename');
		if (rolename.length) {
			rolename.val(val);
		}
		var listname = $('#field-listname');
		if (listname.length) {
			listname.val(val);
		}
	});

	$('#field-rolename,#field-listname').on('keyup', function (){
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '_')
			.replace(/[^a-z0-9-_]+/g, '');

		$(this).val(val);
	});

	// Subresources

	/*$('#assoc-resourceid').on('change', function() {
		var resource = $('#' + $(this).attr('id') + ' option:selected').text().replace(/(\- )+/, '');
		var cluster = $('#field-cluster').val();
		$('#field-name').val(resource + "-" + cluster);
	});*/

	// Autocomplete the fields related to resource name
	$('#assoc-resourceid,#field-cluster').on('change', function() {
		var resource = $('#assoc-resourceid option:selected').text().replace(/(- )+/, '');
		var cluster = $('#field-cluster').val();
		$('#field-name').val(resource + "-" + cluster);
	});

	$('#field-nodemem').on('keyup', function (){
		var val = $(this).val();

		val = val.toUpperCase().replace(/[^0-9]{1,4}[^PTGMKB]/g, '');

		$(this).val(val);
	});
});
