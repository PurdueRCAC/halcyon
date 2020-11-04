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

	var frm = document.getElementById('item-form'),
		invalid = false;

	if (frm) {
		var elms = frm.querySelectorAll('input[required]');
		elms.forEach(function (el) {
			if (!el.value || !el.validity.valid) {
				el.classList.add('is-invalid');
				invalid = true;
				return;
			} else {
				el.classList.remove('is-invalid');
			}
		});

		if (task == 'cancel' || task.match(/cancel$/) || !invalid) {
			Halcyon.submitform(task, frm);
		} /*else {
			alert('Invalid data');
		}*/
	}
}

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
