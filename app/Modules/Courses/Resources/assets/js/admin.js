/**
 * @package    halcyon
 * @copyright  Copyright 2019 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	var users = $(".form-users");
	if (users.length) {
		users.each(function(i, user){
			user = $(user);
			var cl = user.clone()
				.attr('type', 'hidden')
				.val(user.val().replace(/([^:]+):/, ''));
			user
				.attr('name', user.attr('id') + i)
				.attr('id', user.attr('id') + i)
				.val(user.val().replace(/(:\d+)$/, ''))
				.after(cl);
			user.autocomplete({
				minLength: 2,
				source: function( request, response ) {
					return $.getJSON(user.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
						response($.map(data.data, function (el) {
							return {
								label: el.name + ' (' + el.username + ')',
								name: el.name,
								id: el.id,
							};
						}));
					});
				},
				select: function (event, ui) {
					event.preventDefault();
					// Set selection
					user.val(ui.item.label); // display the selected text
					cl.val(ui.item.id); // save selected id to input

					if (user.hasClass('redirect')) {
						window.location.href = user.data('location').replace('%s', ui.item.id);
					}

					if (user.hasClass('submit')) {
						user.closest('form').submit();
					}

					return false;
				}
			});
		});
	}

	$('.type-dependant').hide();

	$('[name="type"]')
		.on('change', function () {
			$('.type-dependant').hide();
			$('.type-' + $(this).val()).show();
		})
		.each(function (i, el) {
			$('.type-' + $(el).val()).show();
		});

	$('#main').on('click', '.remove-member', function (e) {
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

	$('.add-member').on('click', function (e) {
		e.preventDefault();

		var select = $($(this).data('field'));
		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'userid': select.val(),
				'classaccountid': btn.data('account')
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				Halcyon.message('success', 'User added');

				var c = select.closest('table');
				var li = c.find('tr.d-none');

				if (typeof (li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('d-none');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.data.id))
						.data('id', response.data.id);

					template.find('a').each(function (i, el) {
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.data.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.data.id)
						.replace(/\{name\}/g, response.data.user.name)
						.replace(/\{userid\}/g, response.data.userid);

					template.html(content).insertBefore(li);
				}

				select.val();
			},
			error: function (xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});
});
