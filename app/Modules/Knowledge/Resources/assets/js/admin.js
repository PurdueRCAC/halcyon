/* global $ */ // jquery.js
/* global jQuery */ // jquery.js

jQuery(document).ready(function () {
	var alias = $('#field-alias');
	if (alias.length) {
		$('#field-title').on('keyup', function (){
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
		})
		.on('select2:select', function () {
			if ($(this).hasClass('filter-submit')) {
				$(this).closest('form').submit();
			}
		});

	$('body').on('click', '.delete-row', function (e) {
		e.preventDefault();

		console.log($(this).attr('href'));

		$($(this).attr('href')).remove();
	});

	$('.add-row').on('click', function (e) {
		e.preventDefault();

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

		tr.after(clone);
	});

	//----

	var dialog = $("#new-page").dialog({
		autoOpen: false,
		height: 250,
		width: 500,
		modal: true
	});

	$('#toolbar-plus').on('click', function (e) {
		e.preventDefault();

		dialog.dialog("open");
	});

	//----

	$('.snippet-checkbox').on('change', function () {
		if ($(this).is(':checked')) {
			$('tr[data-parent=' + $(this).data('id') + ']')
				.find('.snippet-checkbox')
				.each(function (i, el) {
					$(el).prop('checked', true).trigger('change');
				});
		} else {
			$('tr[data-parent=' + $(this).data('id') + ']')
				.find('.snippet-checkbox')
				.each(function (i, el) {
					$(el).prop('checked', false).trigger('change');
				});
		}
	});
	$('.toggle-tree').on('click', function (e) {
		e.preventDefault();

		$(this).closest('tr').toggleClass('open');

		$('tr[data-parent=' + $(this).data('id') + ']')
			.toggleClass('d-none');
	});
});
