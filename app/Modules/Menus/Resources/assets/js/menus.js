/* global $ */ // jquery.js
/* global Halcyon */ // modules/core/js/core.js
/* global TomSelect */ // modules/core/vendor/tom-select/js/tom-select.complete.min.js

function setmenutype(type) {
	window.parent.Halcyon.submitbutton('items.setType', type);
	window.parent.$.dialog.close();
}

document.addEventListener('DOMContentLoaded', function () {
	var headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	var alias = document.getElementById('field-menutype');
	if (alias && !alias.value) {
		document.getElementById('field-title').addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
		alias.addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
	}

	document.querySelectorAll('.menutype-dependant').forEach(function (el) {
		el.classList.add('d-none');
	});

	document.querySelectorAll('[name="fields[type]"]').forEach(function (item) {
		item.addEventListener('change', function () {
			document.querySelectorAll('.menutype-dependant').forEach(function (el) {
				el.classList.add('d-none');
			});
			document.querySelectorAll('.menutype-' + this.value).forEach(function (el) {
				el.classList.remove('d-none');
			});
			if (item.value == 'separator') {
				var title = document.getElementById('fields_title');
				if (title) {
					title.value = item.value;
				}
			}
		});

		if (item.checked) {
			document.querySelectorAll('.menutype-' + item.value).forEach(function (el) {
				el.classList.remove('d-none');
			});
		}
	});

	var pageid = document.getElementById('fields_page_id');
	if (pageid) {
		if (typeof TomSelect !== 'undefined') {
			var sel = new TomSelect(pageid, {
				plugins: ['dropdown_input'],
				render: {
					option: function (data, escape) {
						return '<div>' +
							'<span class="indent d-inline-block">' + escape(data.indent) + '</span>' +
							'<span class="d-inline-block">' +
							'<span class="text">' + escape(data.text.replace(data.indent, '')) + '</span><br />' +
							'<span class="path text-muted">' + escape(data.path) + '</span>' +
							'</span>' +
							'</div>';
					},
					item: function (data, escape) {
						return '<div title="' + escape(data.title) + '">' + escape(data.path) + '</div>';
					}
				}
			});
			sel.on('change', function () {
				if (document.getElementById('fields_title').value == '') {
					document.getElementById('fields_title').value = this.input.selectedOptions[0].innerHTML.replace(/\|— /g, '');
				}
			});
		} else {
			pageid.addEventListener('change', function () {
				if (document.getElementById('fields_title').value == '') {
					document.getElementById('fields_title').value = this.selectedOptions[0].innerHTML.replace(/\|— /g, '');
				}
			});
		}
	}

	var data = document.getElementById('menutypes');
	if (data) {
		var menus = JSON.parse(data.innerHTML);

		document.getElementById(data.getAttribute('data-field')).addEventListener('change', function () {
			var val = this.value;

			if (typeof (menus[val]) !== 'undefined') {
				document.getElementById('fields_parent_id')
					.querySelectorAll('option').forEach(function (opt) {
						opt.remove();
					});

				for (var i = 0; i < menus[val].length; i++) {
					document.getElementById('fields_parent_id').append('<option value="' + menus[val][i].value + '">' + menus[val][i].text + '</option>');
				}
			}
		});
	}

	$('#sortable').sortable({
		items: 'li',
		listType: 'ul',
		handle: '.draghandle',
		placeholder: 'ui-state-highlight',
		update: function () {
			var p = document.getElementById('sortable');
			var l = new Array();
			p.querySelectorAll('li').forEach(function (item) {
				l.push(item.parentNode.getAttribute('data-parent') + ':' + item.getAttribute('data-id'));
			});

			var post = {
				ordering: l
			};

			fetch(p.getAttribute('data-api'), {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify(post),
			})
			.then(function (response) {
				if (response.ok) {
					Halcyon.message('success', 'Menu updated');
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
			.catch(function (err) {
				Halcyon.message('danger', err);
			});
		},
		forcePlaceholderSize: true,
		cursor: 'move'
	}).disableSelection();

	document.querySelectorAll('.toggle').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			el.classList.toggle('opened');
			document.getElementById(el.getAttribute('data-target')).classList.toggle('d-none');
		});
	});

	document.querySelectorAll('.choose_type').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			setmenutype(this.getAttribute('data-type'));
		});
	});
});
