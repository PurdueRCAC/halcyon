/* global $ */ // jquery.js
/* global Halcyon */ // core.js

function token(length) {
	var result = '';
	var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	var charactersLength = characters.length;
	for (var i = 0; i < length; i++) {
		result += characters.charAt(Math.floor(Math.random() * charactersLength));
	}
	return result;
}

document.addEventListener('DOMContentLoaded', function () {
	const headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	var searchusers = document.getElementById('filter_search');
	if (searchusers) {
		//searchusers.each(function (i, el) {
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
							data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
							if (!data.data[i].id) {
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
		//});
		searchusers.addEventListener('select2:select', function (e) {
			var data = e.params.data;
			window.location = this.getAttribute('data-url') + "?search=" + data.id;
		});
		searchusers.addEventListener('select2:unselect', function () {
			window.location = this.getAttribute('data-url') + "?search=";
		});
	}

	// API token generation
	document.querySelectorAll('.btn-apitoken').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			if (confirm(this.getAttribute('data-confirm'))) {
				document.getElementById('field-api_token').value = token(60);
				document.getElementById('field-api_token').readonly = false;
			}
		});
	});

	const detailsbtn = document.getElementById('user_details_save');
	if (detailsbtn) {
		detailsbtn.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
				btn.disabled = true;

			fetch(btn.getAttribute('data-api'), {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify({
					'name': document.getElementById('field-name').value,
					'puid': document.getElementById('field-organization_id').value,
					'apitoken': document.getElementById('field-api_token').value,
					'username': document.getElementById('field_username').value,
					'email': document.getElementById('field_email').value
				})
			})
			.then(function (response) {
				if (response.ok) {
					window.location.reload(true);
					return;
				}
				return response.json().then(function (data) {
					var msg = data.message;
					msg = (typeof msg === 'object') ? Object.values(msg).join('<br />') : msg;
					throw msg;
				});
			})
			.catch(function (error) {
				Halcyon.message('danger', error);
			});
		});
	}

	const accessbtn = document.getElementById('user_access_save');
	if (accessbtn) {
		accessbtn.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var roles = [],
				r = btn.closest('form').querySelectorAll('input:checked');

			r.forEach(function (el) {
				roles.push(el.value);
			});

			fetch(btn.getAttribute('data-api'), {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify({
					'roles': roles
				})
			})
			.then(function (response) {
				if (response.ok) {
					window.location.reload(true);
					return;
				}
				return response.json().then(function (data) {
					var msg = data.message;
					msg = (typeof msg === 'object' ? Object.values(msg).join('<br />') : msg);
					throw msg;
				});
			})
			.catch(function (error) {
				Halcyon.message('danger', error);
			});
		});
	}

	// Roles
	$('#permissions-rules').accordion({
		heightStyle: 'content',
		collapsible: true,
		active: false
	});

	document.querySelectorAll('#permissions-rules .stop-propagation').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.stopPropagation();
		});
	});

	// User Facets
	document.querySelectorAll('.add-facet').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var key = document.getElementById(btn.getAttribute('href').replace('#', '') + '-key'),
				value = document.getElementById(btn.getAttribute('href').replace('#', '') + '-value'),
				access = document.getElementById(btn.getAttribute('href').replace('#', '') + '-access');

			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: headers,
				body: JSON.stringify({
					'user_id': btn.getAttribute('data-userid'),
					'key': key.value,
					'value': value.value,
					'access': access.value
				})
			})
			.then(function (response) {
				if (response.ok) {
					return response.json();
				}
				return response.json().then(function (data) {
					var msg = data.message;
					msg = (typeof msg === 'object' ? Object.values(msg).join('<br />') : msg);
					throw msg;
				});
			})
			.then(function (data) {
				Halcyon.message('success', btn.getAttribute('data-success'));

				var c = btn.closest('table');
				var template = document.getElementById('facet-template');

				if (template) {
					//var template = li;

					template.querySelectorAll('a').forEach(function (el) {
						el.setAttribute('data-api', el.getAttribute('data-api').replace(/\{id\}/g, data.id));
					});

					var content = template
						.innerHTML
						.replace(/\{i\}/g, c.querySelectorAll('tbody>tr').length + 2)
						.replace(/\{id\}/g, data.id)
						.replace(/\{key\}/g, data.key)
						.replace(/\{value\}/g, data.value)
						.replace(/\{access\}/g, data.access)
						.replace('option value="' + data.access + '"', 'option value="' + data.access + '" selected');

					var newRow = c.querySelector('tbody').insertRow(c.querySelector('tbody').rows.length);
					newRow.innerHTML = content;
					newRow.id = 'facet-' + data.id;
				}

				key.value = '';
				value.value = '';
				access.valule = 0;
			})
			.catch(function (error) {
				Halcyon.message('danger', error);
			});
		});
	});

	document.querySelectorAll('.update-facet').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var key = document.getElementById(btn.getAttribute('data-target').replace('#', '') + '-key'),
				value = document.getElementById(btn.getAttribute('data-target').replace('#', '') + '-value'),
				access = document.getElementById(btn.getAttribute('data-target').replace('#', '') + '-access');

			btn.parentNode.querySelectorAll('.btn').forEach(function (s) {
				s.disabled = true;
			});
			btn.classList.add('loading');
			btn.querySelectorAll('.fa').forEach(function (s) {
				s.classList.add('d-none');
			});
			btn.querySelectorAll('.spinner-border').forEach(function(s) {
				s.classList.remove('d-none');
			});

			fetch(btn.getAttribute('data-api'), {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify({
					'key': key.value,
					'value': value.value,
					'access': access.value
				})
			})
			.then(function (response) {
				btn.parentNode.querySelectorAll('.btn').forEach(function(s) {
					s.disabled = false;
				});
				btn.classList.remove('loading');
				btn.querySelectorAll('.spinner-border').forEach(function (s) {
					s.classList.add('d-none');
				});
				btn.querySelectorAll('.fs').forEach(function (s) {
					s.classList.remove('d-none');
				});

				if (response.ok) {
					Halcyon.message('success', btn.getAttribute('data-success'));
					return;
				}

				return response.json().then(function (data) {
					var msg = data.message;
					msg = (typeof msg === 'object' ? Object.values(msg).join('<br />') : msg);
					throw msg;
				});
			})
			.catch(function (error) {
				Halcyon.message('danger', error);
			});
		});
	});

	document.getElementById('main').addEventListener('click', function (e) {
		if (e.target.classList.contains('remove-facet')) {
			e.preventDefault();

			var btn = e.target;
			var result = confirm(btn.getAttribute('data-confirm'));

			if (result) {
				fetch(btn.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
				})
				.then(function (response) {
					if (response.ok) {
						Halcyon.message('success', btn.getAttribute('data-success'));

						var field = document.getElementById(btn.getAttribute('data-target').replace('#', ''));
						field.remove();
						return;
					}

					return response.json().then(function (data) {
						var msg = data.message;
						msg = (typeof msg === 'object' ? Object.values(msg).join('<br />') : msg);
						throw msg;
					});
				})
				.catch(function (error) {
					console.error(error);
					Halcyon.message('danger', error);
				});
			}
		}
	});
});
