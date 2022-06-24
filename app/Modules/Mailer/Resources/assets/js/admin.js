/* global Halcyon */ // core.js
/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

/**
 * Email regex
 */
var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
	'(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

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
						title: 'Remove this email',
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
							callback(json.data);
						}).catch(() => {
							callback();
						});
				},
				createFilter: function (input) {
					var regexpA = new RegExp('^' + REGEX_EMAIL + '$', 'i');
					var regexpB = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
					return regexpA.test(input) || regexpB.test(input);
				},
				render: {
					option: function (item, escape) {
						var name = item.name;
						var label = name || item.username;
						var caption = name ? item.username : null;
						return '<div>' +
							'<span class="label">' + escape(label) + '</span>' +
							(caption ? '<span class="caption text-muted">(' + escape(caption) + ')</span>' : '') +
							'</div>';
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
				create: true,
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
				}
			});
		});
	}

	var templates = document.getElementById('field-template');
	if (templates) {
		templates.addEventListener('change', function () {
			if (this.options[this.selectedIndex].value) {
				var selected = document.getElementById(this.options[this.selectedIndex].value);

				document.getElementById('field-subject').value = this.options[this.selectedIndex].innerHTML;

				document.getElementById('field-body').value = selected.value;
				document.getElementById('field-body').dispatchEvent(new Event('refreshEditor', { bubbles: true }));
			}
		});
	}

	var confirmed = 0;
	var parent = document.getElementById('field-roles');
	if (parent) {
		parent.querySelectorAll('input').forEach(function (el) {
			el.addEventListener('change', function () {
				if (this.checked) {
					if (!confirmed) {
						parent.querySelectorAll('.alert').forEach(function (al) {
							al.classList.remove('d-none');
						});
					}

					confirmed++;
				} else if (!this.checked) {
					confirmed--;
					if (confirmed == 0) {
						parent.querySelectorAll('.alert').forEach(function (al) {
							al.classList.add('d-none');
						});
					}
				}
			});
		});
	}

	var me = document.getElementById('field-fromme');
	if (me) {
		me.addEventListener('change', function () {
			if (this.checked) {
				document.getElementById('field-fromemail').setAttribute('data-value', document.getElementById('field-fromemail').value);
				document.getElementById('field-fromname').setAttribute('data-value', document.getElementById('field-fromname').value);

				document.getElementById('field-fromemail').value = this.value;
				document.getElementById('field-fromname').value = this.getAttribute('data-name');
			} else {
				document.getElementById('field-fromemail').value = document.getElementById('field-fromemail').getAttribute('data-value');
				document.getElementById('field-fromname').value = document.getElementById('field-fromname').getAttribute('data-value');
			}
		});
	}
});
