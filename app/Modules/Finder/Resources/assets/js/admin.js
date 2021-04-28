/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	$('body').on('click', '.remove-choice', function (e) {
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

	$('.add-choice').on('click', function (e) {
		e.preventDefault();

		var source = $($(this).attr('data-template')).html();
		var container = $($(this).attr('data-container'));

		var template = Handlebars.compile(source),
			context = {
				"i": container.find('fieldset').length
			},
			html = template(context);

		container.append(html);
	});
});
