/* global Halcyon */ // core.js

document.addEventListener('DOMContentLoaded', function () {

	document.querySelectorAll('.sluggable').forEach(function (el) {
		el.addEventListener('keyup', function () {
			if (this.getAttribute('data-rel')) {
				var alias = document.querySelector(this.getAttribute('data-rel'));

				alias.value = this.value.toLowerCase()
					.replace(/\s+/g, '_')
					.replace(/[^a-z0-9_]+/g, '');
			}
		});
	});

	document.querySelectorAll('.alias-add').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var name = document.querySelector(this.getAttribute('href'));
			var btn = this;
			var post = {
				'parent_id': btn.getAttribute('data-id'),
				'name': name.value
			};

			// create new relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify(post),
			})
				.then(function (response) {
					if (!response.ok) {
						return response.json().then(function (data) {
							var msg = data.message;
							if (typeof msg === 'object') {
								msg = Object.values(msg).join('<br />');
							}
							throw msg;
						});
					}

					return response.json();
				})
				.then(function (response) {
					Halcyon.message('success', btn.getAttribute('data-success'));

					var c = name.closest('table');
					var li = c.querySelector('tr.hidden');

					if (typeof (li) !== 'undefined') {
						var template = li.cloneNode(true);

						template.classList.remove('hidden');
						template.setAttribute('id', template.getAttribute('id').replace(/\{id\}/g, response.id));
						template.setAttribute('data-id', response.id);

						template.querySelectorAll('a').forEach(function (el) {
							el.setAttribute('data-api', el.getAttribute('data-api').replace(/\{id\}/g, response.id));
						});

						var content = template
							.innerHTML
							.replace(/\{id\}/g, response.id)
							.replace(/\{name\}/g, response.name)
							.replace(/\{slug\}/g, response.slug);

						template.innerHTML = content;
						li.parentNode.insertBefore(template, li);
					}

					name.value = '';
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
		});
	});

	document.getElementById('main').addEventListener('click', (e) => {
		if (!e.target.parentNode.matches('.remove-alias')) {
			return;
		}

		e.preventDefault();

		var btn = e.target.parentNode;
		var result = confirm(btn.getAttribute('data-confirm'));

		if (result) {
			var field = document.querySelector(btn.getAttribute('href'));

			// delete relationship
			fetch(btn.getAttribute('data-api'), {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				}
			})
				.then(function (response) {
					if (!response.ok) {
						return response.json().then(function (data) {
							var msg = data.message;
							if (typeof msg === 'object') {
								msg = Object.values(msg).join('<br />');
							}
							throw msg;
						});
					}

					Halcyon.message('success', btn.getAttribute('data-success'));
					field.remove();
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
		}
	});
});
