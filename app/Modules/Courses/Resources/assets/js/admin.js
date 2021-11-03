/* global $ */ // jquery.js
/* global Halcyon */ // core.js

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	var users = $(".form-users");
	if (users.length) {
		users.each(function (i, user) {
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
				source: function (request, response) {
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
			if ($(this).val() == 'workshop') {
				$('#field-semester').val('Workshop');
			}
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
				success: function () {
					Halcyon.message('success', $(this).data('success'));
					field.remove();
				},
				error: function (xhr) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});

	$('.add-member').on('click', function (e) {
		e.preventDefault();

		var select = $($(this).data('field'));
		var btn = $(this);
		var post = {
			'userid': select.val(),
			'classaccountid': btn.data('account'),
			'membertype': $($(this).data('type')).val()
		};

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: post,
			dataType: 'json',
			async: false,
			success: function () { //response
				Halcyon.message('success', btn.data('success'));
				window.location.reload(true);

				/*var c = select.closest('table');
				var li = c.find('tr.d-none');

				if (typeof (li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('d-none');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function (i, el) {
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{name\}/g, response.user.name)
						.replace(/\{userid\}/g, response.userid);

					template.html(content).insertBefore(li);
				}

				select.val();*/
			},
			error: function (xhr) {
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	//----

	var dialog = $("#new-account").dialog({
		autoOpen: false,
		height: 'auto',//200,
		width: 500,
		modal: true
	});

	$('#toolbar-plus').on('click', function (e) {
		e.preventDefault();

		dialog.dialog("open");
	});

	var searchusers = $('#filter_userid');
	if (searchusers.length) {
		searchusers.each(function (i, el) {
			$(el).select2({
				ajax: {
					url: $(el).data('api'),
					dataType: 'json',
					maximumSelectionLength: 1,
					data: function (params) {
						var query = {
							search: params.term,
							order: 'name',
							order_dir: 'asc'
						}

						return query;
					},
					processResults: function (data) {
						for (var i = 0; i < data.data.length; i++) {
							if (data.data[i].id) {
								data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
							} else {
								data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
								data.data[i].id = data.data[i].username;
							}
						}

						return {
							results: data.data
						};
					}
				},
				templateResult: function (state) {
					if (isNaN(state.id) && typeof state.name != 'undefined') {
						return $('<span>' + state.text + ' <span class="text-warning ml-1"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span> No local account</span></span>');
					}
					return state.text;
				}
			});
		});
		searchusers.on('select2:select', function (e) {
			var data = e.params.data;
			window.location = $(this).data('url') + "?userid=" + data.id;
		});
		searchusers.on('select2:unselect', function () {
			window.location = $(this).data('url') + "?userid=";
		});
	}
});
