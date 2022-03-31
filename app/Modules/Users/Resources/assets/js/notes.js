/* global Halcyon */ // core.js

document.addEventListener('DOMContentLoaded', function () {

	document.querySelectorAll('.note-add').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var container = document.getElementById(btn.getAttribute('data-parent'));
			var comment = document.getElementById(container.getAttribute('id') + '_body');

			fetch(container.getAttribute('data-api'), {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
					body: JSON.stringify({
						'user_id': document.getElementById('userid').value,
						'body': comment.value
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

					var c = document.getElementById('note_list');
					var li = c.querySelector('li.d-none');

					if (typeof (li) !== 'undefined') {
						var template = li.cloneNode(true);
						template.classList.remove('d-none');
						template.setAttribute('id', template.getAttribute('id').replace(/\{id\}/g, data.id))
						template.setAttribute('data-api', data.api);

						template.querySelectorAll('a').forEach(function (el) {
							el.setAttribute('href', el.getAttribute('href').replace(/\{id\}/g, data.id));
						});
						template.querySelectorAll('textarea').forEach(function (el) {
							el.setAttribute('id', el.getAttribute('id').replace(/\{id\}/g, data.id));
						});
						template.querySelectorAll('label').forEach(function (el) {
							el.setAttribute('for', el.getAttribute('for').replace(/\{id\}/g, data.id));
						});
						template.querySelectorAll('button').forEach(function (el) {
							el.setAttribute('data-parent', el.getAttribute('data-parent').replace(/\{id\}/g, data.id));
						});

						template.querySelectorAll('.text-muted').forEach(function (el) {
							el.innerHTML = el.innerHTML
								.replace(/\{who\}/g, data.creator.username)
								.replace(/\{when\}/g, data.created_at);
						});

						template.querySelectorAll('div').forEach(function (el) {
							if (el.getAttribute('id')) {
								el.setAttribute('id', el.getAttribute('id').replace(/\{id\}/g, data.id));
								if (el.getAttribute('id').match(/_text$/)) {
									el.innerHTML = data.formattedbody;
								}
							}
						});

						var content = template
							.innerHTML
							.replace(/\{id\}/g, data.id);

						template.innerHTML = content;
						li.parentNode.insertBefore(template, li);

						var ta = document.getElementById('note_' + data.id + '_body');
						ta.value = data.body;
						ta.dispatchEvent(new Event('initEditor', { bubbles: true }));
					}

					comment.value = '';
					comment.dispatchEvent(new Event('refreshEditor', { bubbles: true }));
				})
				.catch(function (error) {
					console.log(error);
					Halcyon.message('danger', error);
				});
		});
	});

	document.getElementById('main').addEventListener('click', function (e) {
		if (e.target.parentNode.matches('.note-edit')
			|| e.target.matches('.note-cancel')) {
			e.preventDefault();
			e.target.closest('li').classList.toggle('is-editing');
		}

		if (e.target.parentNode.matches('.note-delete')) {
			if (confirm(e.target.parentNode.getAttribute('data-confirm'))) {
				var field = document.querySelector(e.target.parentNode.getAttribute('href'));

				fetch(field.getAttribute('data-api'), {
						method: 'DELETE',
						headers: {
							'Content-Type': 'application/json',
							'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
						}
					})
					.then(function (response) {
						if (response.ok) {
							Halcyon.message('success', 'Note removed');
							field.remove();
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
						Halcyon.message('danger', error);
					});
			}
		}

		if (e.target.matches('.note-save')) {
			e.preventDefault();

			var container = e.target.closest('li');
			var comment = document.getElementById(container.getAttribute('id') + '_body');

			fetch(container.getAttribute('data-api'), {
					method: 'PUT',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
					body: JSON.stringify({
						body: comment.value
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
					Halcyon.message('success', 'Note saved');
					container.classList.toggle('is-editing');
					document.getElementById('note_' + data.id + '_text').innerHTML = data.formattedbody;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
		}
	});
});
