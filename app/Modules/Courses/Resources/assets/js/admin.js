/* global $ */ // jquery.js
/* global Halcyon */ // core.js

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {

	document.querySelectorAll('.form-user').forEach(function (user, i) {
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

	var autocompleteUsers = function (url) {
		return function (request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name + ' (' + el.username + ')',
						name: el.name,
						id: (el.id ? el.id : el.username),
					};
				}));
			});
		};
	};

	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: '',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1,
				open: function () { //e, ui
					var acData = $(this).data('ui-autocomplete');

					acData
						.menu
						.element
						.find('.ui-menu-item-wrapper')
						.each(function () {
							var me = $(this);
							var regex = new RegExp('(' + acData.term + ')', "gi");
							me.html(me.text().replace(regex, '<b>$1</b>'));
						});
				}
			}
		});
	}

	document.querySelectorAll('.type-dependant').forEach(function (el) {
		el.classList.add('d-none');
	});

	document.querySelectorAll('[name="type"]').forEach(function (el) {
		el.addEventListener('change', function () {
			document.querySelectorAll('.type-dependant').forEach(function (dep) {
				dep.classList.add('d-none');
				if (dep.classList.contains('type-' + el.value)) {
					dep.classList.remove('d-none');
				}
			});

			if (el.value == 'workshop') {
				document.getElementById('field-semester').value = 'Workshop';
			}
		})

		document.querySelectorAll('.type-' + el.value).forEach(function (dep) {
			dep.classList.remove('d-none');
		});
	});

	document.querySelector('#main').addEventListener('click', function (e) {
		if (e.target.closest('#main .remove-member')) {
			e.preventDefault();

			var result = confirm(this.getAttribute('data-confirm'));

			if (result) {
				var btn = this,
					field = document.getElementById(this.getAttribute('href').replace('#', ''));

				// delete relationship
				$.ajax({
					url: btn.getAttribute('data-api'),
					type: 'delete',
					dataType: 'json',
					async: false,
					success: function () {
						Halcyon.message('success', btn.getAttribute('data-success'));
						field.parentNode.removeChild(field);
					},
					error: function (xhr) {
						Halcyon.message('danger', xhr.responseJSON.message);
					}
				});
			}
		}
	});

	document.querySelectorAll('.add-member').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var select = document.querySelector(this.getAttribute('data-field'));
			var btn = this;
			var post = {
				'userid': select.value,
				'classaccountid': btn.getAttribute('data-account'),
				'membertype': document.querySelector(this.getAttribute('data-type')).value
			};

			// create new relationship
			$.ajax({
				url: btn.getAttribute('data-api'),
				type: 'post',
				data: post,
				dataType: 'json',
				async: false,
				success: function () { //response
					Halcyon.message('success', btn.getAttribute('data-success'));
					window.location.reload(true);
				},
				error: function (xhr) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		});
	});

	//----

	var plus = document.getElementById('toolbar-plus');
	if (plus) {
		var dialog = $("#new-account").dialog({
			autoOpen: false,
			height: 'auto',//200,
			width: 500,
			modal: true
		});

		plus.addEventListener('click', function (e) {
			e.preventDefault();

			dialog.dialog("open");
		});
	}

	var searchusers = document.getElementById('filter_userid');
	if (searchusers) {
		$(searchusers).select2({
			ajax: {
				url: searchusers.getAttribute('data-api'),
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
		$(searchusers).on('select2:select', function (e) {
			var data = e.params.data;
			window.location = this.getAttribute('data-url') + "?userid=" + data.id;
		});
		$(searchusers).on('select2:unselect', function () {
			window.location = this.getAttribute('data-url') + "?userid=";
		});
	}

	var refresh = document.getElementById('toolbar-refresh');
	if (refresh) {
		var sdialog = $("#sync").dialog({
			autoOpen: false,
			height: 400,
			width: 500,
			modal: true
		});

		refresh.addEventListener('click', function (e) {
			e.preventDefault();

			sdialog.dialog("open");
		});
	}

	document.querySelectorAll('.btn-sync').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();

			var spinners = btn.querySelectorAll('.spinner-border');
			spinners.forEach(function (sp) {
				sp.classList.remove('d-none');
			});

			// create new relationship
			$.ajax({
				url: btn.getAttribute('data-api'),
				type: 'get',
				dataType: 'json',
				async: false,
				success: function (response) { //response
					spinners.forEach(function (sp) {
						sp.classList.add('d-none');
					});
					document.getElementById('sync-output').innerHTML = response.responseText;
					//Halcyon.message('success', btn.data('success'));
					//window.location.reload(true);
				},
				error: function (xhr) {
					spinners.forEach(function (sp) {
						sp.classList.add('d-none');
					});
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		});
	});
});
