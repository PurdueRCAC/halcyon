/* global $ */ // jquery.js
/* global Halcyon */ // core.js

/**
 * Unix base groups
 *
 * @const
 * @type  {array}
 */
var BASEGROUPS = Array('', 'data', 'apps');

var headers = {
	'Content-Type': 'application/json'
};

/**
 * Create UNIX group
 *
 * @param   {integer}  num    index for BASEGROUPS array
 * @param   {string}   group
 * @return  {void}
 */
function CreateNewGroupVal(num, btn, all) {
	var group = btn.getAttribute('data-group');
	//var base = btn.getAttribute('data-value');

	if (typeof (all) == 'undefined') {
		all = true;
	}

	fetch(btn.getAttribute('data-api'), {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
		},
		body: JSON.stringify({
			'longname': BASEGROUPS[num],
			'groupid': group
		})
	})
	.then(function (response) {
		if (response.ok) {
			num++;
			if (all && num < BASEGROUPS.length) {
				setTimeout(function () {
					CreateNewGroupVal(num, btn, all);
				}, 5000);
			} else {
				Halcyon.message('success', 'Item added');
				window.location.reload(true);
			}
			return;
		}
		return response.json().then(function (data) {
			var msg = data.message;
			if (typeof msg === 'object') {
				msg = Object.values(msg).join('<br />');
			}
			throw msg;
		});
	})
	.catch(function (error) {
		btn.querySelector('.spinner-border').classList.add('d-none');
		Halcyon.message('danger', error);
	});
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	if ($.fn.select2) {
		$('.searchable-select').select2();
	}

	document.querySelectorAll('.reveal').forEach(function (item) {
		item.addEventListener('click', function () {
			document.querySelector(this.getAttribute('data-toggle')).classList.toggle('hide');

			var text = this.getAttribute('data-text');
			this.setAttribute('data-text', this.innerHTML);
			this.innerHTML = text;
		});
	});

	document.querySelectorAll('.input-unixgroup').forEach(function (el) {
		el.addEventListener('keyup', function () {
			var val = this.value;

			val = val.toLowerCase()
				.replace(/\s+/g, '-')
				.replace(/[^a-z0-9-]+/g, '');

			this.value = val;
		});
	});

	document.querySelectorAll('.create-default-unix-groups').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			this.querySelector('.spinner-border').classList.remove('d-none');

			CreateNewGroupVal(0, this, parseInt(this.getAttribute('data-all-groups')));
		});
	});

	document.querySelectorAll('.add-category').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var select = document.getElementById(this.getAttribute('href'));
			var btn = this;

			// create new relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: headers,
				body: JSON.stringify({
					'groupid': btn.getAttribute('data-group'),
					[select.getAttribute('data-category')]: select.value
				})
			})
			.then(function (response) {
				if (response.ok) {
					Halcyon.message('success', 'Item added');

					var c = select.closest('table');
					var li = c.querySelector('tr.hidden');

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
							.replace(/\{name\}/g, select.find('option:selected').text());

						template.html(content).insertBefore(li);
					}

					select.value = 0;
					return;
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.catch(function (error) {
				btn.querySelector('.spinner-border').classList.add('d-none');
				Halcyon.message('danger', error);
			});
		});
	});

	document.querySelectorAll('.add-unixgroup').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var name = document.getElementById(this.getAttribute('href').replace('#', ''));
			var btn = this;

			// create new relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: headers,
				body: JSON.stringify({
					'groupid': btn.getAttribute('data-group'),
					'longname': name.value
				})
			})
			.then(function (response) {
				if (response.ok) {
					return response.json();
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.then(function (data) {
				Halcyon.message('success', 'Item added');

				var c = name.closest('table');
				var li = c.querySelector('tr.hidden');

				if (typeof (li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, data.id))
						.data('id', data.id);

					template.find('a').each(function (i, el) {
						el.setAttribute('data-api', el.getAttribute('data-api').replace(/\{id\}/g, data.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, data.id)
						.replace(/\{longname\}/g, data.longname)
						.replace(/\{shortname\}/g, data.shortname);

					template.html(content).insertBefore(li);
				}

				name.value = '';
			})
			.catch(function (error) {
				Halcyon.message('danger', error);
			});
		});
	});

	var main = document.getElementById('main');
	if (main) {
		// Remove a field of science or department
		main.addEventListener('click', function (e) {
			if (e.target.matches('.remove-category')
			|| e.target.parentNode.matches('.remove-category')) {
				e.preventDefault();

				var tar = e.target;
				if (e.target.parentNode.matches('.remove-category')) {
					tar = e.target.parentNode;
				}

				var result = confirm(tar.getAttribute('data-confirm'));

				if (result) {
					var field = document.getElementById(tar.getAttribute('href').replace('#', ''));

					// delete relationship
					fetch(tar.getAttribute('data-api'), {
						method: 'DELETE',
						headers: headers
					})
					.then(function (response) {
						if (response.ok) {
							field.remove();
							Halcyon.message('success', 'Item removed');
							return;
						}
						return response.json();
					})
					.then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					})
					.catch(function (error) {
						Halcyon.message('danger', error);
					});
				}
			}
		});

		main.addEventListener('click', function (e) {
			// Remove unix group
			if (e.target.matches('.remove-unixgroup')
			|| e.target.parentNode.matches('.remove-unixgroup')) {
				e.preventDefault();

				var tar = e.target;
				if (e.target.parentNode.matches('.remove-unixgroup')) {
					tar = e.target.parentNode;
				}

				if (confirm(tar.getAttribute('data-confirm'))) {
					var field = document.getElementById(tar.getAttribute('href').replace('#', ''));

					// delete relationship
					fetch(tar.getAttribute('data-api'), {
						method: 'DELETE',
						headers: headers
					})
					.then(function (response) {
						if (response.ok) {
							field.remove();
							Halcyon.message('success', 'Item removed');
							return;
						}
						return response.json();
					})
					.then(function (data) {
						var msg = data.message;
						if (typeof msg === 'object') {
							msg = Object.values(msg).join('<br />');
						}
						throw msg;
					})
					.catch(function (error) {
						Halcyon.message('danger', error);
					});
				}
			}
		});
	}

	$('.list-group').on('click', '.delete-row', function (e) {
		e.preventDefault();

		var result = confirm('Are you sure you want to remove this?');

		if (result) {
			var container = $(this).closest('li');

			//$.post($(this).data('api'), data, function(e){
			container.remove();
			//});
		}
	});

	document.querySelectorAll('.btn-edit').forEach(function (el){
		el.addEventListener('click', function (e){
			e.preventDefault();

			var frm = el.closest('form');
			frm.querySelectorAll('.edit-show').forEach(function (es){
				es.classList.toggle('d-none');
			});
			frm.querySelectorAll('.edit-hide').forEach(function (es) {
				es.classList.toggle('d-none');
			});
		});
	});

	document.querySelectorAll('.btn-edit-cancel').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var frm = el.closest('form');
			frm.querySelectorAll('.edit-show').forEach(function (es) {
				es.classList.toggle('d-none');
			});
			frm.querySelectorAll('.edit-hide').forEach(function (es) {
				es.classList.toggle('d-none');
			});
		});
	});

	document.querySelectorAll('.btn-edit-save').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			el.parentNode.querySelector('.spinner-border').classList.add('processing');

			var frm = el.closest('form');
			var post = {},
				fields = new FormData(frm);

			for (var key of fields.keys()) {
				if (key.substring(0, 6) != 'fields') {
					continue;
				}
				post[key.replace('fields[', '').replace(']', '')] = fields.get(key);
			}
			if (typeof (post['cascademanagers']) == 'undefined') {
				post['cascademanagers'] = 0;
			}
			if (typeof (post['prefix_unixgroup']) == 'undefined') {
				post['prefix_unixgroup'] = 0;
			}

			fetch(frm.getAttribute('data-api'), {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify(post)
			})
			.then(function (response) {
				if (response.ok) {
					window.location.reload(true);
					return;
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.catch(function (error) {
				el.parentNode.querySelector('.spinner-border').classList.remove('processing');
				Halcyon.message('danger', error);
			});
		});
	});
});
