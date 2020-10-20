/**
 * @package    halcyon
 * @copyright  Copyright 2019 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

Halcyon.submitbutton = function(task) {
	var frm = document.getElementById('adminForm');

	if (frm) {
		return Halcyon.submitform(task, frm);
	}

	$(document).trigger('editorSave');

	var frm = document.getElementById('item-form');

	if (frm) {
		if (task == 'cancel' || document.formvalidator.isValid(frm)) {
			Halcyon.submitform(task, frm);
		} else {
			alert(frm.getAttribute('data-invalid-msg'));
		}
	}
}

jQuery(document).ready(function ($) {

	$('#field-name').on('keyup', function (e){
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '_')
			.replace(/[^a-z0-9\-_]+/g, '');

		var rolename = $('#field-rolename');
		if (rolename.length) {// && !rolename.val()) {
			rolename.val(val);
		}
		var listname = $('#field-listname');
		if (listname.length) {// && !listname.val()) {
			listname.val(val);
		}
	});

	$('#field-rolename,#field-listname').on('keyup', function (e){
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '_')
			.replace(/[^a-z0-9\-_]+/g, '');

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
		var resource = $('#assoc-resourceid option:selected').text().replace(/(\- )+/, '');
		var cluster = $('#field-cluster').val();
		$('#field-name').val(resource + "-" + cluster);
	});

	$('#field-nodemem').on('keyup', function (e){
		var val = $(this).val();

		val = val.toUpperCase().replace(/[^0-9]{1,4}[^PTGMKB]/g, '');

		$(this).val(val);
	});
});
