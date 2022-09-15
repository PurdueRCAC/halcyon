
document.addEventListener('DOMContentLoaded', function () {

	var alias = document.getElementById('field-alias');
	if (alias) {
		document.getElementById('field-title').addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_.]+/g, '');
		});
	}

	var parent = document.getElementById('field-parent_id');
	if (parent) {
		parent.addEventListener('change', function () {
			document.getElementById('parent-path').innerHTML = this[this.selectedIndex].getAttribute('data-path');
		});
	}

	document.querySelectorAll('[data-confirm]').forEach(function (item) {
		item.addEventListener('click', function (e) {
			e.preventDefault();

			var res = confirm(this.getAttribute('data-confirm'));
			if (!res) {
				return;
			}

			fetch(this.getAttribute('data-api'), {
				method: 'DELETE',
				headers: new Headers({
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'),
					'Content-Type': 'application/json' // : 'application/x-www-form-urlencoded'
				})
			}).then(function (response) {
				if (response.ok) {
					window.location.href = document.querySelector('meta[name="base-url"]').getAttribute('content');
				}
				throw 'Failed to remove item';
			}).catch(function (error) {
				alert(error);
			});
		});
	});

	var pageform = document.getElementById('pageform');
	if (pageform) {
		pageform.addEventListener('submit', function (e) {
			e.preventDefault();

			var frm = this,
				invalid = false;

			var alert = frm.querySelector('.alert');
			alert.classList.add('d-none');

			var elms = frm.querySelectorAll('input[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});

			elms = frm.querySelectorAll('select[required]');
			elms.forEach(function (el) {
				if (!el.value || el.value <= 0) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});

			elms = frm.querySelectorAll('textarea[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});

			if (invalid) {
				return false;
			}

			var btn = document.getElementById('save-page');
			btn.classList.add('processing');

			var post = {},
				k,
				fields = new FormData(frm);

			for (var key of fields.keys()) {
				if (key.substring(0, 6) == 'params') {
					if (typeof (post['params']) === 'undefined') {
						post['params'] = {};
					}
					k = key.substring(7);

					post['params'][k.substring(0, k.length - 1)] = fields.get(key);
				} else {
					post[key] = fields.get(key);
				}
			}

			fetch(frm.getAttribute('data-api'), {
				method: (post['id'] ? 'PUT' : 'POST'),
				headers: new Headers({
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'),
					'Content-Type': 'application/json'
				}),
				body: JSON.stringify(post),
			}).then(function (response) {
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
			}).then(function (data) {
				if (data.url) {
					window.location.href = data.url;
				} else {
					window.location.reload();
				}
			}).catch(function (error) {
				btn.classList.remove('processing');

				alert.classList.remove('d-none');
				alert.innerHTML = error;
			});
		});
	}

});
