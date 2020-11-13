/**
 * @package    halcyon
 * @copyright  Copyright 2019 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

/*Halcyon.submitbutton = function(task) {
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
}*/

jQuery(document).ready(function ($) {
	var alias = $('#field-alias');
	if (alias.length) {
		$('#field-title').on('keyup', function (e){
			//if (alias.val() == '') {
				var val = $(this).val();

				val = val.toLowerCase()
					.replace(/\s+/g, '_')
					.replace(/[^a-z0-9\-_]+/g, '');

				alias.val(val);
			//}
		});
	}

	$('#field-parent_id')
		.on('change', function (){
			$('#parent-path').html($(this).children("option:selected").data('path'));
		});

	$('.searchable-select').select2({
		//placeholder: $(this).data('placeholder')
	});

	$('body').on('click', '.delete-row', function (e) {
		e.preventDefault();

		console.log($(this).attr('href'));

		$($(this).attr('href')).remove();
	});

	$('.add-row').on('click', function (e) {
		e.preventDefault();

		/*var row   = $('#' + $(this).data('container')).find('.param-item:last');

		var clone = row.clone(true);
		var cindex = $('#' + $(this).data('container')).find('.param-item').length;
		var inputs = clone.find('input,select');

		inputs.val('');
		inputs.each(function(i, el){
			$(el).attr('name', $(el).attr('name').replace(/\[\d+\]/, '[' + cindex + ']'));
			$(el).attr('id', $(el).attr('id').replace(/\[\d+\]/, '[' + cindex + ']'));
		});
		console.log(row);
		console.log(clone);
		row.after(clone);*/
		var tr = $('#' + $(this).data('container')).find('tbody tr:last');

		var clone = tr.clone(true);
		clone.removeClass('d-none');
		clone.find('.btn').removeClass('disabled');

		var cindex = $('#' + $(this).data('container')).find('tbody tr').length;
		var inputs = clone.find('input,select');

		clone.attr('id', clone.attr('id').replace(/\-\d+/, '-' + cindex));

		inputs.val('');
		inputs.each(function (i, el) {
			$(el).attr('name', $(el).attr('name').replace(/\[\d+\]/, '[' + cindex + ']'));
			$(el).attr('id', $(el).attr('id').replace(/\-\d+/, '-' + cindex));
		});

		clone.find('a').each(function (i, el) {
			$(el).attr('href', $(el).attr('href').replace(/\-\d+/, '-' + cindex));
		});

		//clone.find('.btn').removeClass('disabled');

		tr.after(clone);
	});
});
