/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

/**
 * Convert title to URL segment
 *
 * @return void
 */
function setAlias() {
	document.getElementById('field-alias').value = this.value
		.trim()
		.toLowerCase()
		.replace(/\s+/g, '-')
		.replace(/[^a-z0-9\-_]+/g, '');
}

document.addEventListener('DOMContentLoaded', function () {

	// Edit page

	var alias = document.getElementById('field-alias'),
		title = document.getElementById('field-title');
	if (alias && title) {
		title.addEventListener('focus', function () {
			if (!alias.value) {
				title.addEventListener('keyup', setAlias);
			}
		});
		title.addEventListener('blur', function () {
			title.removeEventListener('keyup', setAlias);
		});

		alias.addEventListener('keyup', setAlias);
	}

	var sselects = document.querySelectorAll('.searchable-select');
	if (sselects.length) {
		var sel, sels = new Array();
		sselects.forEach(function (el) {
			sel = new TomSelect(el, {
				plugins: ['dropdown_input'],
				searchField: ['text', 'path'],
				render: {
					option: function (data, escape) {
						return '<div>' +
							'<span class="d-inline-block indent">' + escape(data.indent) + '</span>' +
							'<span class="d-inline-block">' +
							'<span class="text">' + escape(data.text.replace(data.indent, '')) + '</span>' +
							(data.path ? '<br /><span class="path text-muted">' + escape(data.path) + '</span>' : '') +
							'</span>' +
							'</div>';
					},
					item: function (data, escape) {
						return '<div>' +
							'<span class="d-inline-block">' +
							'<span class="text">' + escape(data.text.replace(data.indent, '')) + '</span>' +
							(data.path ? '<br /><span class="path text-muted">' + escape(data.path) + '</span>' : '') +
							'</span>' +
							'</div>';
					}
				}
			});
			sels.push(sel);
		});
	}

	var taggables = document.querySelectorAll('.taggable');
	if (taggables.length) {
		taggables.forEach(function (el) {
			var sel = new TomSelect(el, {
				plugins: {
					remove_button: {
						title: 'Remove this tag',
					}
				},
				persist: false,
				createOnBlur: true,
				create: true,
				valueField: 'slug',
				labelField: 'name',
				searchField: 'name',
				load: function (query, callback) {
					var url = el.getAttribute('data-api') + '?search=' + encodeURIComponent(query);
					fetch(url)
						.then(response => response.json())
						.then(json => {
							callback(json.data);
						}).catch(() => {
							callback();
						});
				}
			});
		});
	}

	var parent = document.getElementById('field-parent_id');
	if (parent) {
		parent.addEventListener('change', function () {
			document.getElementById('parent-path').innerHTML = this.selectedOptions[0].getAttribute('data-path');
		});
	}

	document.querySelector('body').addEventListener('click', function (e) {
		if (e.target.matches('.delete-row')) {
			e.preventDefault();
			document.querySelector(e.target.getAttribute('href')).remove();
		}
	});

	document.querySelectorAll('.add-row').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var tr = document.getElementById(this.getAttribute('data-container')).querySelector('tbody tr:last-child');

			var clone = tr.cloneNode(true);
			clone.classList.remove('d-none');
			clone.querySelectorAll('.btn').forEach(function (item) {
				item.classList.remove('disabled');
			});

			var cindex = document.getElementById(this.getAttribute('data-container')).querySelectorAll('tbody tr').length;

			clone.setAttribute('id', clone.getAttribute('id').replace(/-\d+/, '-' + cindex));

			var inputs = clone.querySelectorAll('input,select');

			inputs.forEach(function (ele) {
				ele.value = '';
				ele.setAttribute('name', ele.getAttribute('name').replace(/\[\d+\]/, '[' + cindex + ']'));
				ele.setAttribute('id', ele.getAttribute('id').replace(/-\d+/, '-' + cindex));
			});

			clone.querySelectorAll('a').forEach(function (ele) {
				ele.setAttribute('href', ele.getAttribute('href').replace(/-\d+/, '-' + cindex));
			});

			tr.after(clone);
		});
	});

	// New page prompt

	var newbtn = document.getElementById('toolbar-plus');
	if (newbtn) {
		var a = newbtn.querySelector('a');
		if (a.href.indexOf('snippets') == -1) {
			a.setAttribute('data-toggle', 'modal');
		}
	}
	// Snippet tree

	document.querySelectorAll('.snippet-checkbox').forEach(function (el) {
		el.addEventListener('change', function () {
			const event = new Event('change');
			const checked = this.checked;

			document.querySelectorAll('tr[data-parent="' + this.getAttribute('data-id') + '"]').forEach(function (ele) {
				ele.querySelectorAll('.snippet-checkbox').forEach(function (item) {
					item.checked = checked;
					item.dispatchEvent(event);
				});
			});
		});
	});

	document.querySelectorAll('.toggle-tree').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			this.closest('tr').classList.toggle('open');

			document.querySelectorAll('tr[data-parent="' + this.getAttribute('data-id') + '"]').forEach(function (ele) {
				ele.classList.toggle('d-none');
			});
		});
	});
});
