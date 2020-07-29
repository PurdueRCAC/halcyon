/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
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
	$('#btn-batch-submit')
		.on('click', function (e){
			Halcyon.submitbutton('article.batch');
		});

	$('#btn-batch-clear')
		.on('click', function (e){
			e.preventDefault();
			$('#batch-category-id').val('');
			$('#batch-access').val('');
			$('#batch-language-id').val('');
		});

	var alias = $('#field-alias');
	if (alias.length && !alias.val()) {
		$('#field-title').on('keyup', function (e){
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}
});
