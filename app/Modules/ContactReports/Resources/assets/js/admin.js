/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js
/* global Halcyon */ // core.js
/* global Chart */ // vendor/chartjs/Chart.min.js

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	var headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	var users = document.querySelectorAll(".form-users");
	if (users.length) {
		users.forEach(function (user) {
			new TomSelect(user, {
				plugins: {
					remove_button: {
						title: 'Remove this user',
					}
				},
				valueField: 'id',
				labelField: 'name',
				searchField: ['name', 'username'],
				persist: false,
				create: true,
				load: function (query, callback) {
					var url = user.getAttribute('data-uri').replace('%s', encodeURIComponent(query));

					fetch(url, {
						method: 'GET',
						headers: headers
					})
						.then(response => response.json())
						.then(json => {
							for (var i = 0; i < json.data.length; i++) {
								if (!json.data[i].id) {
									json.data[i].id = json.data[i].username;
								}
							}
							callback(json.data);
						}).catch(() => {
							callback();
						});
				},
				render: {
					option: function (item, escape) {
						var name = item.name;
						var label = name || item.username;
						var caption = name ? item.username : null;
						return '<div>' +
							'<span class="label">' + escape(label) + '</span>' +
							(caption ? '&nbsp;<span class="caption text-muted">(' + escape(caption) + ')</span>' : '') +
							'</div>';
					},
					item: function (item) {
						var match = item.name.match(/([^:]+):(.+)/i);
						if (match) {
							item.name = match[1];
							item.id = match[2];
						}

						return `<div data-id="${escape(item.id)}">${item.name}</div>`;
					}
				}
			});
		});
	}

	var groups = document.querySelectorAll(".form-groups");
	if (groups.length) {
		groups.forEach(function (group) {
			new TomSelect(group, {
				plugins: {
					remove_button: {
						title: 'Remove this group',
					}
				},
				valueField: 'id',
				labelField: 'name',
				searchField: ['name'],
				persist: false,
				//create: true,
				load: function (query, callback) {
					var url = group.getAttribute('data-uri').replace('%s', encodeURIComponent(query));

					fetch(url, {
						method: 'GET',
						headers: headers
					})
						.then(response => response.json())
						.then(json => {
							callback(json.data);
						}).catch(() => {
							callback();
						});
				},
				render: {
					item: function (item) {
						var match = item.name.match(/([^:]+):(.+)/i);
						if (match) {
							item.name = match[1];
							item.id = match[2];
						}

						return `<div data-id="${escape(item.id)}">${escape(item.name)}</div>`;
					}
				}
			});
		});
	}

	var tags = document.querySelectorAll(".form-tags");
	if (tags.length) {
		tags.forEach(function (tag) {
			new TomSelect(tag, {
				plugins: {
					remove_button: {
						title: 'Remove this tag',
					}
				},
				valueField: 'slug',
				labelField: 'slug',
				searchField: ['name'],
				persist: false,
				create: true,
				load: function (query, callback) {
					var url = tag.getAttribute('data-uri').replace('%s', encodeURIComponent(query));

					fetch(url, {
						method: 'GET',
						headers: headers
					})
						.then(response => response.json())
						.then(json => {
							callback(json.data);
						}).catch(() => {
							callback();
						});
				},
				onDelete: function () { //values
					tag.closest('form').submit();
				}
			});
		});
	}

	var bselects = document.querySelectorAll('.basic-multiple');
	if (bselects.length) {
		bselects.forEach(function (select) {
			new TomSelect(select, { plugins: ['dropdown_input', 'remove_button'] });
		});
	}

	var sselects = document.querySelectorAll('.searchable-select');
	if (sselects.length) {
		sselects.forEach(function (select) {
			new TomSelect(select, { plugins: ['dropdown_input'] });
		});
	}

	var rselects = document.querySelectorAll('.searchable-select-multi');
	if (rselects.length) {
		rselects.forEach(function (select) {
			if (select.classList.contains('filter-submit')) {
				select.removeEventListener('change', Halcyon.filterSubmit);
			}
			if (typeof TomSelect !== 'undefined') {
				var sel = new TomSelect(select, { plugins: ['dropdown_input', 'remove_button'] });
				sel.on('change', function () {
					var frm = select.closest('form');
					if (!select.value) {
						frm.setAttribute('action', frm.getAttribute('action') + '?resource=');
					}
					frm.submit();
				});
			} else {
				select.addEventListener('change', function () {
					var frm = this.closest('form');
					if (!this.value) {
						frm.setAttribute('action', frm.getAttribute('action') + '?resource=');
					}
					frm.submit();
				});
			}
		});
	}

	document.querySelectorAll('.comment-add').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var btn = this;
			var container = document.querySelector(btn.getAttribute('data-parent'));
			var comment = document.getElementById(container.getAttribute('id') + '_comment');

			fetch(container.getAttribute('data-api'), {
				method: 'POST',
				headers: headers,
				body: JSON.stringify({
					'contactreportid': btn.getAttribute('data-id'),
					'comment': comment.value
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

					var c = container.parentNode;
					var li = c.querySelector('li.d-none');

					if (typeof (li) !== 'undefined') {
						var template = li.cloneNode(true);
						template.classList.remove('d-none');
						template.setAttribute('id', template.getAttribute('id').replace(/\{id\}/g, data.id))
						template.setAttribute('data-api', data.api);

						template.querySelectorAll('a').forEach(function (el) {
							if (el.getAttribute('href')) {
								el.setAttribute('href', el.getAttribute('href').replace(/\{id\}/g, data.id));
							}
						});
						template.querySelectorAll('textarea').forEach(function (el) {
							if (el.getAttribute('id')) {
								el.setAttribute('id', el.getAttribute('id').replace(/\{id\}/g, data.id));
							}
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
								.replace(/\{when\}/g, data.formatteddate);
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
		var el = e.target;

		if (e.target.matches('.comment-edit') || e.target.parentNode.matches('.comment-edit') || e.target.matches('.comment-cancel')) {
			e.preventDefault();

			if (e.target.parentNode.matches('.comment-edit')) {
				el = e.target.parentNode;
			}

			el.closest('li').classList.toggle('is-editing');
			return;
		}

		if (e.target.matches('.comment-delete') || e.target.parentNode.matches('.comment-delete')) {
			e.preventDefault();

			if (e.target.parentNode.matches('.comment-delete')) {
				el = e.target.parentNode;
			}

			var result = confirm(el.getAttribute('data-confirm'));

			if (result) {
				var field = document.querySelector(el.getAttribute('href'));

				fetch(field.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
				})
					.then(function (response) {
						if (response.ok) {
							Halcyon.message('success', 'Item removed');
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
			return;
		}

		if (e.target.matches('.comment-save')) {
			e.preventDefault();

			var container = e.target.closest('li');
			var comment = document.getElementById(container.getAttribute('id') + '_comment');

			fetch(container.getAttribute('data-api'), {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify({
					'comment': comment.value
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
					Halcyon.message('success', 'Item saved');
					container.classList.toggle('is-editing');
					document.getElementById('comment_' + data.id + '_text').innerHTML = data.formattedcomment;
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
		}
	});

	document.querySelectorAll('.comments-show').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			document.querySelector(this.getAttribute('href')).classList.toggle('d-none');
		});
	});

	var charts = new Array;
	document.querySelectorAll('.pie-chart').forEach(function (el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						data: JSON.parse(el.getAttribute('data-values')),
						backgroundColor: [
							'rgb(255, 99, 132)',
							'rgb(54, 162, 235)',
							'rgb(255, 205, 86)',
							'rgb(201, 203, 207)',
							'rgba(75, 192, 192)',
							'rgba(255, 159, 64)',
							'rgba(153, 102, 255)'
						]
					}
				]
			},
			options: {
				animation: {
					duration: 0
				}
			}
		});
		charts.push(chart);
	});
});
