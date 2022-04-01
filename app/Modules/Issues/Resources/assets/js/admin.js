/* global $ */ // jquery.js
/* global Halcyon */ // core.js

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.issue-todo').forEach(function (el) {
		el.addEventListener('change', function () {
			var that = this;

			if (that.checked) {
				fetch(that.getAttribute('data-api'), {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
						},
						body: JSON.stringify({
							'report': that.getAttribute('data-name'),
							'issuetodoid': that.getAttribute('data-id')
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
						var li = that.closest('li');

						var fadeEffect = setInterval(function () {
							if (!li.style.opacity) {
								li.style.opacity = 1;
							}
							if (li.style.opacity > 0) {
								li.style.opacity -= 0.1;
							} else {
								clearInterval(fadeEffect);

								li.classList.add('hide');
								li.style = '';
								li.classList.add('complete');
								li.classList.remove('incomplete');
								that.setAttribute('data-issue', data.id);

								document.getElementById('checklist_status').dispatchEvent(new Event('change', { bubbles: true }));
							}
						}, 200);
					})
					.catch(function (error) {
						var img = that.closest('li').querySelector('.fa');
						img.className = "fa fa-exclamation-triangle";
						img.parentNode.title = "Unable to save changes, reload the page and try again.";

						Halcyon.message('danger', error);
					});
			} else if (that.getAttribute('data-issue')) {
				fetch(that.getAttribute('data-api') + '/' + that.getAttribute('data-issue'), {
						method: 'DELETE',
						headers: {
							'Content-Type': 'application/json',
							'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
						}
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
					.then(function () {
						var li = that.closest('li');

						var fadeEffect = setInterval(function () {
							if (!li.style.opacity) {
								li.style.opacity = 1;
							}
							if (li.style.opacity > 0) {
								li.style.opacity -= 0.1;
							} else {
								clearInterval(fadeEffect);

								li.classList.add('hide');
								li.style = '';
								li.classList.add('incomplete');
								li.classList.remove('complete');
								that.setAttribute('data-issue', '');

								document.getElementById('checklist_status').dispatchEvent(new Event('change', { bubbles: true }));
							}
						}, 200);
					})
					.catch(function (error) {
						var img = that.closest('li').querySelector('.fa');
						img.className = "fa fa-exclamation-triangle";
						img.parentNode.title = "Unable to save changes, reload the page and try again.";

						Halcyon.message('danger', error);
					});
			}
		});
	});

	var status = document.getElementById('checklist_status');
	if (status) {
		status.addEventListener('change', function () {
			var val = this.value;
			if (val == 'all') {
				document.querySelectorAll('.checklist>li').forEach(function (el) {
					el.classList.remove('hide');
				});
			} else {
				document.querySelectorAll('.checklist>li').forEach(function (el) {
					el.classList.add('hide');
				});
				document.querySelectorAll('.' + val).forEach(function (el) {
					el.classList.remove('hide');
				});
			}
		});
	}

	$('.basic-multiple').select2({
		placeholder: $(this).data('placeholder')
	});

	$('.searchable-select').select2();

	document.querySelectorAll('.comments-show').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			document.getElementById(this.getAttribute('href')).classList.toggle('hide');
		});
	});

	document.querySelectorAll('.comment-add').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var container = document.querySelector(btn.getAttribute('data-parent'));
			var comment = document.getElementById(container.getAttribute('id') + '_comment');
			var resolution = document.getElementById(container.getAttribute('id') + '_resolution');
			var post = {
				'issueid': document.getElementById('field-id').value,
				'comment': comment.value,
				'resolution': resolution.checked ? 1 : 0
			};

			fetch(container.getAttribute('data-api'), {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
					body: JSON.stringify(post)
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

					var c = container.parentNode;
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
								.replace(/\{who\}/g, data.username)
								.replace(/\{when\}/g, data.datetimecreated);
						});

						template.querySelectorAll('div').forEach(function (el) {
							if (el.getAttribute('id')) {
								el.setAttribute('id', el.getAttribute('id').replace(/\{id\}/g, data.id));
								if (el.getAttribute('id').match(/_text$/)) {
									el.innerHTML = data.formattedcomment;
								}
							}
						});

						template.setAttribute('id', template.getAttribute('id').replace(/\{id\}/g, data.id))
						template.setAttribute('data-api', data.api);

						var content = template
							.innerHTML
							.replace(/\{id\}/g, data.id);

						template.innerHTML = content;
						li.parentNode.insertBefore(template, li);

						var ta = document.getElementById('comment_' + data.id + '_comment');
						ta.value = data.comment;
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
		if (e.target.parentNode.matches('.comment-edit')
			|| e.target.matches('.comment-cancel')) {
			e.preventDefault();
			e.target.closest('li').classList.toggle('is-editing');
		}

		if (e.target.parentNode.matches('.comment-delete')) {
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
							Halcyon.message('success', 'Comment removed');
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

		if (e.target.matches('.comment-save')) {
			e.preventDefault();

			var container = e.target.closest('li');
			var comment = document.getElementById(container.getAttribute('id') + '_comment');
			var resolution = document.getElementById(container.getAttribute('id') + '_resolution');

			fetch(container.getAttribute('data-api'), {
					method: 'PUT',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
					body: JSON.stringify({
						'comment': comment.value,
						'resolution': resolution.checked ? 1 : 0
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
					Halcyon.message('success', 'Comment saved');

					container.classList.toggle('is-editing');
					if (data.resolution == 1) {
						document.getElementById('comment_' + data.id).querySelectorAll('.badge-success').forEach(function (el) {
							el.classList.remove('hide');
						});
					} else {
						document.getElementById('comment_' + data.id).querySelectorAll('.badge-success').forEach(function (el) {
							el.classList.remove('hide');
						});
					}
					document.getElementById('comment_' + data.id + '_text').innerHTML = data.formattedcomment;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
		}
	});
});
