/**
 * @package  Tags
 */

jQuery(document).ready(function ($) {
	/*var alias = $('#field-slug');
	if (alias.length && !alias.val()) {
		$('#field-tag').on('keyup', function (e){
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.val(val);
		});
	}*/
	$('.sluggable').on('keyup', function (e) {
		if ($(this).attr('data-rel')) {
			var alias = $($(this).attr('data-rel'));

			//if (alias.length && !alias.val()) {
			var val = $(this).val();

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9_]+/g, '');

			alias.val(val);
			//}
		}
	});

	$('.alias-add').on('click', function (e) {
		e.preventDefault();

		var name = $($(this).attr('href'));
		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'parent_id': btn.data('id'),
				'name': name.val()
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				Halcyon.message('success', 'Item added');

				var c = name.closest('table');
				var li = c.find('tr.hidden');

				if (typeof (li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function (i, el) {
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{name\}/g, response.name)
						.replace(/\{slug\}/g, response.slug);

					template.html(content).insertBefore(li);
				}

				name.val('');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-alias', function (e) {
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			// delete relationship
			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function (data) {
					Halcyon.message('success', 'Item removed');
					field.remove();
				},
				error: function (xhr, ajaxOptions, thrownError) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});
});
