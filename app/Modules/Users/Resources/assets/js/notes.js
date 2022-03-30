/* global $ */ // jquery.js

document.addEventListener('DOMContentLoaded', function () {

	document.querySelectorAll('.note-add').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var container = document.getElementById(btn.getAttribute('data-parent'));
			var comment = document.getElementById(container.getAttribute('id') + '_body');

			// create new relationship
			$.ajax({
				url: container.getAttribute('data-api'),
				type: 'post',
				data: {
					'user_id': document.getElementById('userid').value,
					'body': comment.value
				},
				dataType: 'json',
				async: false,
				success: function (response) {
					Halcyon.message('success', 'Item added');

					var c = $('#note_list');
					var li = c.find('li.d-none');

					if (typeof (li) !== 'undefined') {
						var template = $(li)
							.clone()
							.removeClass('d-none');
						console.log(template);
						template
							.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
							.data('api', response.api);

						template.find('a').each(function (i, el) {
							$(el).attr('href', $(el).attr('href').replace(/\{id\}/g, response.id));
						});
						template.find('textarea').each(function (i, el) {
							$(el).attr('id', $(el).attr('id').replace(/\{id\}/g, response.id));
							//$(el).val(response.comment);
						});
						template.find('label').each(function (i, el) {
							$(el).attr('for', $(el).attr('for').replace(/\{id\}/g, response.id));
						});
						template.find('button').each(function (i, el) {
							$(el).attr('data-parent', $(el).attr('data-parent').replace(/\{id\}/g, response.id));
						});

						template.find('.text-muted').each(function (i, el) {
							$(el).html(
								$(el).html()
									.replace(/\{who\}/g, response.creator.username)
									.replace(/\{when\}/g, response.created_at)
							);
						});

						template.find('div').each(function (i, el) {
							if ($(el).attr('id')) {
								$(el).attr('id', $(el).attr('id').replace(/\{id\}/g, response.id));
								if ($(el).attr('id').match(/_text$/)) {
									$(el).html(response.formattedbody);
								}
							}
						});

						var content = template
							.html()
							.replace(/\{id\}/g, response.id);

						template.html(content).insertBefore(li);

						var ta = document.getElementById('note_' + response.id + '_body');
						ta.value = response.body;
						ta.dispatchEvent(new Event('initEditor', { bubbles: true }));
					}

					comment.value = '';
					comment.dispatchEvent(new Event('refreshEditor', { bubbles: true }));
				},
				error: function (xhr) { //xhr, ajaxOptions, thrownError
					//console.log(xhr);
					Halcyon.message('danger', xhr.responseJSON.message);
				}
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
