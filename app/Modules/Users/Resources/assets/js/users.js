/* global $ */ // jquery.js
/* global jQuery */ // jquery.js
/* global Halcyon */ // core.js

jQuery(document).ready(function(){
	var searchusers = $('#filter_search');
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
			window.location = $(this).data('url') + "?search=" + data.id;
		});
		searchusers.on('select2:unselect', function () {
			window.location = $(this).data('url') + "?search=";
		});
	}

	// API token generation
	$('.btn-apitoken').on('click', function(e){
		e.preventDefault();

		if (confirm('Are you sure you want to regenerate the API token for this user?')) {
			$('#field-api_token').val(token(60)).prop('readonly', false);
		}
	});

	function token(length) {
		var result = '';
		var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
		for (var i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}

	$('#user_details_save').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'put',
			data: {
				'name': $('#field-name').val(),
				'puid': $('#field-organization_id').val(),
				'api_token': $('#field-api_token').val(),
				'username': $('#field_username').val(),
				'email': $('#field_email').val()
			},
			dataType: 'json',
			async: false,
			success: function () { //response
				Halcyon.message('success', 'Account updated');

				window.location.reload(true);
			},
			error: function (xhr) { //, ajaxOptions, thrownError
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#user_access_save').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);
		var roles = [],
			r = $(btn.closest('form')).find('input:checked');

		r.each(function (i, el) {
			roles.push(el.value);
		});

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'put',
			data: {
				'roles': roles
			},
			dataType: 'json',
			async: false,
			success: function () { //response
				Halcyon.message('success', 'Roles updated');

				window.location.reload(true);
			},
			error: function (xhr) { //, ajaxOptions, thrownError
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	// Roles
	$('#permissions-rules').accordion({
		heightStyle: 'content',
		collapsible: true,
		active: false
	});
	$('#permissions-rules .stop-propagation').on('click', function(e) {
		e.stopPropagation();
	});

	// User Facets
	$('.add-facet').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);
		var key = $(btn.attr('href') + '-key'),
			value = $(btn.attr('href') + '-value'),
			access = $(btn.attr('href') + '-access');

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'user_id': btn.data('userid'),
				'key': key.val(),
				'value': value.val(),
				'access': access.val()
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				Halcyon.message('success', 'Item added');

				var c = btn.closest('table');
				var li = '#facet-template';//c.find('tr.hidden');

				if (typeof (li) !== 'undefined') {
					var template = $(li);
					//.clone()
					//.removeClass('hidden');

					//template
					//	.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
					//	.data('id', response.id);

					template.find('a').each(function (i, el) {
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{i\}/g, c.find('tbody>tr').length + 2)
						.replace(/\{id\}/g, response.id)
						.replace(/\{key\}/g, response.key)
						.replace(/\{value\}/g, response.value)
						.replace(/\{access\}/g, response.access);

					content = $(content);
					content.find('select').val(response.access);

					//template.html(content).insertBefore(li);
					//template.html(content);
					$(c.find('tbody')[0]).append(content);
				}

				key.val(''),
					value.val(''),
					access.val(0);
			},
			error: function (xhr) { // xhr, ajaxOptions, thrownError
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-facet', function (e) {
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
					Halcyon.message('success', 'Item removed');
					field.remove();
				},
				error: function (xhr) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});
});
