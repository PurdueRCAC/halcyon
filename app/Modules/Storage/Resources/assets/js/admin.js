/* global Halcyon */ // core.js
/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

var headers = {
	'Content-Type': 'application/json'
};

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	var selects = document.querySelectorAll('.searchable-select');
	if (selects.length) {
		selects.forEach(function (el) {
			if (el.classList.contains('filter-submit')) {
				el.style.width = '15em';
			}
			var sel = new TomSelect(el, { plugins: ['dropdown_input'] });
			sel.on('item_select', function () {
				if (sel.classList.contains('filter-submit')) {
					sel.closest('form').submit();
				}
			});
		});
	}

	var seluser = null;
	document.querySelectorAll('.form-users').forEach(function (el) {
		seluser = new TomSelect(el, {
			maxItems: 1,
			valueField: 'id',
			labelField: 'name',
			searchField: ['name', 'username'],
			plugins: ['clear_button'],
			persist: false,
			// Fetch remote data
			load: function (query, callback) {
				var url = el.getAttribute('data-api') + '&search=' + encodeURIComponent(query);

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
			// Custom rendering functions for options and items
			render: {
				// Option list when searching
				option: function (item, escape) {
					if (item.name.match(/\([a-z0-9]+\)$/)) {
						item.username = item.name.replace(/([^(]+\()/, '').replace(/\)$/, '');
						item.name = item.name.replace(/\s(\([a-z0-9]+\))$/, '');
					}
					return `<div data-id="${escape(item.id)}">${escape(item.name)}&nbsp;<span class="text-muted">(${escape(item.username)})</span></div>`;
				},
				// Selected items
				item: function (item, escape) {
					if (item.name.match(/\([a-z0-9]+\)$/)) {
						if (isNaN(item.id)) {
							item.id = item.username;
						}
						item.username = item.name.replace(/([^(]+\()/, '').replace(/\)$/, '');
						item.name = item.name.replace(/\s(\([a-z0-9]+\))$/, '');
					}
					return `<div data-id="${escape(item.id)}">${escape(item.name)}&nbsp;<span class="text-muted">(${escape(item.username)})</span></div>`;
				}
			}
		});
	});

	document.querySelectorAll('.form-groups').forEach(function (el) {
		var sel = new TomSelect(el, {
			maxItems: 1,
			valueField: 'id',
			labelField: 'name',
			searchField: ['name'],
			plugins: ['clear_button'],
			persist: false,
			// Fetch remote data
			load: function (query, callback) {
				var url = el.getAttribute('data-api') + '&search=' + encodeURIComponent(query);

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
			// Custom rendering functions for options and items
			render: {
				// Selected items
				item: function (item, escape) {
					return `<div data-id="${escape(item.id)}" data-api="${escape(item.api)}">${escape(item.name)}</div>`;
				}
			}
		});
		sel.on('item_add', function (item, data) {
			var unix = document.getElementById('field-unixgroupid');
			if (unix) {
				fetch(data.getAttribute('data-api'), {
					method: 'GET',
					headers: headers
				})
					.then(response => response.json())
					.then(data => {
						var i = 0;

						unix.innerHTML = '<option value="">(Select Unix Group)</option>';

						var aunix = document.getElementById('field-autouserunixgroupid');
						aunix.innerHTML = '<option value="">(Select Unix Group)</option>';

						for (i = 0; i < data.unixgroups.length; i++) {
							unix.innerHTML = unix.innerHTML + '<option value="' + data.unixgroups[i].id + '">' + data.unixgroups[i].longname + '</option>';
							aunix.innerHTML = aunix.innerHTML + '<option value="' + data.unixgroups[i].id + '">' + data.unixgroups[i].longname + '</option>';
						}
					})
					.catch(function (err) {
						Halcyon.message('danger', err);
					});
			}
		});
		sel.on('item_remove', function () {
			var unix = document.getElementById('field-unixgroupid');
			if (unix) {
				unix.innerHTML = '<option value="">(Select Unix Group)</option>';
			}
			var aunix = document.getElementById('field-autouserunixgroupid');
			if (aunix) {
				aunix.innerHTML = '<option value="">(Select Unix Group)</option>';
			}
		});
	});

	var name = document.getElementById('field-name');
	if (name) {
		name.addEventListener('keyup', function () {
			this.value = this.value.toLowerCase()
				.replace(/\s+/g, '-')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
	}

	var autouser = document.getElementById('field-autouser');
	if (autouser) {
		autouser.addEventListener('change', function () {
			var opt = this.selectedOptions[0];

			var read = opt.getAttribute('data-read');
			var write = opt.getAttribute('data-write');
			var owner = opt.getAttribute('data-owner');

			if (this.value != '0') {
				document.querySelector(this.getAttribute('data-update')).classList.remove('hidden');
			} else {
				document.querySelector(this.getAttribute('data-update')).classList.add('hidden');
			}

			document.getElementById('field-ownerread').checked = true;
			document.getElementById('field-ownerwrite').checked = true;
			document.getElementById('field-publicread').checked = false;
			document.getElementById('field-publicwrite').checked = false;

			if (read == '1') {
				document.getElementById('field-groupread').checked = true;
			} else {
				document.getElementById('field-groupread').checked = false;
			}

			if (write == '1') {
				document.getElementById('field-groupwrite').checked = true;
			} else {
				document.getElementById('field-groupwrite').checked = false;
			}

			if (this.value != '1' && this.value != '2' && this.value != '3') {
				document.getElementById('field-autouserunixgroupid').value = '';
			}

			if (owner == '0') {
				document.getElementById('field-owneruserid').value = '0';
				if (seluser) {
					seluser.clear();
				}
			}
		});
	}
});
