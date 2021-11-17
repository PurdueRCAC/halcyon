/* global $ */ // jquery.js

document.addEventListener('DOMContentLoaded', function () {

	/*document.querySelectorAll('[maxlength]').forEach(function (item) {
		var counter = document.createElement('span');
		counter.classList.add('char-counter');
		counter.classList.add('d-block');

		if (item.getAttribute('id') != '') {
			counter.setAttribute('id', item.getAttribute('id') + '-counter');
		}
		if (item.parentNode.classList.contains('input-group')) {
			item.parentNode.parentNode.insertBefore(counter, item.parentNode);
		} else {
			item.parentNode.insertBefore(counter, item);
		}
		counter.textContent = item.value.length + ' / ' + item.getAttribute('maxlength');

		item.addEventListener('keyup', function () {
			var chars = this.value.length;
			var counter = document.getElementById(this.getAttribute('id') + '-counter');

			if (counter) {
				counter.textContent = (chars + ' / ' + this.getAttribute('maxlength'));
			}
		});
	});*/

	var alias = document.getElementById('field-alias');
	if (alias) {
		document.getElementById('field-title').addEventListener('keyup', function () {
			var val = this.value;

			val = val.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');

			alias.value = val;
		});
	}

	document.getElementById('field-parent_id')
		.addEventListener('change', function () {
			document.getElementById('parent-path').innerHTML = this[this.selectedIndex].getAttribute('data-path');
		});

	document.querySelectorAll('[data-confirm]').forEach(function (item) {
		item.addEventListener('click', function (e) {
			e.preventDefault();

			var res = confirm(this.getAttribute('data-confirm'));
			if (!res) {
				return;
			}

			$.ajax({
				url: this.getAttribute('data-api'),
				type: 'delete',
				async: false,
				success: function () {
					window.location.href = document.querySelector('meta[name="base-url"]').getAttribute('content');
				},
				error: function (xhr) {
					alert(xhr.responseJSON.message);
				}
			});
		});
	});

	/*$('#content')
	.on('click', 'a.edit,a.cancel', function(e){
		e.preventDefault();

		var id = $(this).attr('data-id');

		$('#article-form' + id).toggleClass('d-none');
		$('#article-content' + id).toggleClass('d-none');
	});*/

	document.getElementById('pageform').addEventListener('submit', function (e) {
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

		/*fetch(frm.getAttribute('data-api'), {
			method: (post['id'] ? 'PUT' : 'POST'),
			headers: new Headers({
				'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'),
				'Content-Type': 'application/json' // : 'application/x-www-form-urlencoded'
			}),
			body: JSON.stringify(post), //new FormData(event.target),
		}).then(function (response) {
			if (response.ok) {
				return response.json();
			}
			return Promise.reject(response);
		}).then(function (data) {
			console.log(data);
			if (data.url) {
				window.location.href = data.url;
			} else {
				window.location.reload();
			}
		}).catch(function (error) {
			console.warn(error);
			btn.classList.remove('processing');

			alert.classList.remove('d-none');
			alert.innerHTML = error;
		});*/

		$.ajax({
			url: frm.getAttribute('data-api'),
			type: (post['id'] ? 'put' : 'post'),
			data: post,
			dataType: 'json',
			async: false,
			success: function (response) {
				if (response.url) {
					window.location.href = response.url;
				} else {
					window.location.reload();
				}
			},
			error: function (xhr) {
				btn.classList.remove('processing');

				alert.classList.remove('d-none');
				alert.innerHTML = xhr.responseJSON.message;
			}
		});
	});
});
