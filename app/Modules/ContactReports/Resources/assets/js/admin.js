/* global $ */ // jquery.js
/* global Halcyon */ // core.js
/* global Chart */ // vendor/chartjs/Chart.min.js

/**
 * Get and return array of objects
 *
 * @param   {string}  url
 * @return  {array}
 */
var autocompleteList = function (url) {
	return function (request, response) {
		return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
			response($.map(data.data, function (el) {
				if (typeof (el.id) == 'undefined' && typeof (el.username) != 'undefined') {
					el.id = el.username;
				}
				if (typeof (el.username) != 'undefined') {
					el.name += ' (' + el.username + ')';
				}
				if (typeof (el.slug) != 'undefined') {
					el.id = el.slug;
				}
				//var regEx = new RegExp("(" + request.term + ")(?!([^<]+)?>)", "gi");
				//el.name = el.name.replace(regEx, '<span class="highlight">$1</span>');
				return {
					label: el.name,
					name: el.name,
					id: el.id,
				};
			}));
		});
	};
};

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: '',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteList(newsuser.attr('data-uri')),
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
			},
			'onAddTag': function (input) {
				if (input.classList.contains('filter-submit')) {
					$(input).closest('form').submit();
				}
			},
			'onRemoveTag': function (input) {
				if (input.classList.contains('filter-submit')) {
					$(input).closest('form').submit();
				}
			}
		});
	}

	var group = $(".form-groups");
	if (group.length) {
		group.tagsInput({
			placeholder: '',
			importPattern: /([^:]+):(.+)/i,
			limit: 1,
			'autocomplete': {
				source: autocompleteList(group.attr('data-uri')),
				dataName: 'groups',
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
				//maxLength: 1
			},
			'onAddTag': function (input) {
				if (input.classList.contains('filter-submit')) {
					$(input).closest('form').submit();
				}
			},
			'onRemoveTag': function (input) {
				if (input.classList.contains('filter-submit')) {
					$(input).closest('form').submit();
				}
			}
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

					fetch(url)
						.then(response => response.json())
						.then(json => {
							callback(json.data);
						}).catch(() => {
							callback();
						});
				},
				onDelete: function () { //values
					tag.closest('form').submit();
					//return confirm(values.length > 1 ? 'Are you sure you want to remove these ' + values.length + ' items?' : 'Are you sure you want to remove "' + values[0] + '"?');
				}
			});
			/*$(tag).tagsInput({
				placeholder: tag.getAttribute('placeholder'),
				importPattern: /([^:]+):(.+)/i,
				'autocomplete': {
					source: autocompleteList(tag.getAttribute('data-uri')),
					dataName: 'tags',
					height: 150,
					delay: 100,
					minLength: 1
				},
				'onAddTag': function (input) {
					if (input.classList.contains('filter-submit')) {
						$(input).closest('form').submit();
					}
				},
				'onRemoveTag': function (input) {
					if (input.classList.contains('filter-submit')) {
						$(input).closest('form').submit();
					}
				}
			});*/
		});
	}

	var bselects = document.querySelectorAll('.basic-multiple');
	if (bselects.length) {
		bselects.forEach(function (select) {
			if (typeof TomSelect !== 'undefined') {
				new TomSelect(select, { plugins: ['dropdown_input', 'remove_button'] });
			} else if (typeof select2 !== 'undefined') {
				$(select).select2({
					placeholder: select.getAttribute('data-placeholder')
				});
			}
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
			} else if (typeof select2 !== 'undefined') {
				$(select).select2({
						multiple: true
					})
					.on('select2:select', function () {
						if (this.classList.contains('filter-submit')) {
							$(this).closest('form').submit();
						}
					})
					.on('select2:unselect', function () {
						if (this.classList.contains('filter-submit')) {
							var frm = $(this).closest('form');
							if (!$(this).val() || !$(this).val().length) {
								frm.attr('action', frm.attr('action') + '?resource=');
							}
							frm.submit();
						}
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
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
					body: JSON.stringify({
						'contactreportid': document.getElementById('field-id').value,
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
					Halcyon.message('danger', error);
				});
		});
	});

	document.getElementById('main').addEventListener('click', function (e) {
		if (e.target.matches('.comment-edit') || e.target.parentNode.matches('.comment-edit') || e.target.matches('.comment-cancel')) {
			e.preventDefault();

			var el = e.target;
			if (e.target.parentNode.matches('.comment-edit')) {
				el = e.target.parentNode;
			}

			el.closest('li').classList.toggle('is-editing');
			return;
		}

		if (e.target.matches('.comment-delete') || e.target.parentNode.matches('.comment-delete')) {
			e.preventDefault();

			var el = e.target;
			if (e.target.parentNode.matches('.comment-delete')) {
				el = e.target.parentNode;
			}

			var result = confirm(el.getAttribute('data-confirm'));

			if (result) {
				var field = document.querySelector(el.getAttribute('href'));

				fetch(field.getAttribute('data-api'), {
						method: 'DELETE',
						headers: {
							'Content-Type': 'application/json',
							'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
						}
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
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
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
