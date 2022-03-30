/* global $ */ // jquery.js

document.addEventListener('DOMContentLoaded', function () {
	// Feedback
	document.querySelectorAll('.btn-feedback').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			document.getElementById('feedback-state').classList.remove('hide');
			var lbl = document.getElementById('feedback-label'),
				val = this.getAttribute('data-feedback-text');

			document.getElementById('feedback-type').value = this.getAttribute('data-feedback-type');

			lbl.innerHTML = document.getElementById('feedback-text').getAttribute('data-' + val + '-label');
			document.getElementById('feedback-response').innerHTML = document.getElementById('feedback-response').getAttribute('data-' + val + '-label');

			document.getElementById('question-state').classList.add('hide');
		});
	});

	var feedback = document.getElementById('submit-feedback');
	if (feedback) {
		feedback.addEventListener('click', function (e) {
			e.preventDefault();

			// Honeypot was filled
			if (document.getElementById('feedback-hpt').value) {
				return;
			}

			document.getElementById('feedback-state').classList.add('hide');

			var frm = this.closest('form');
			var post = Object.fromEntries(new FormData(frm).entries());

			fetch(frm.getAttribute('data-api'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify(post),
			})
				.then(function () {
					document.getElementById('rating-done').classList.remove('hide');
				})
				.catch(function () {
					document.getElementById('rating-error').classList.remove('hide');
				});
		});
	}

	//----

	var alias = document.getElementById('field-alias');
	if (alias) {
		document.getElementById('field-title').addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
	}

	var parent = document.getElementById('field-parent_id');
	if (parent) {
		parent.addEventListener('change', function () {
			document.getElementById('parent-path').innerHTML = this.selectedOptions[0].getAttribute('data-path');
		});
	}

	// Page editing
	document.getElementById('content')
		// Add confirm dialog to delete links
		.addEventListener('click', function (e) {
			if (e.target.matches('a.delete')) {
				var res = confirm(e.target.getAttribute('data-confirm'));
				if (!res) {
					e.preventDefault();
				}
				return res;
			}

			if (e.target.matches('a.edit')
				|| e.target.matches('a.cancel')) {
				e.preventDefault();

				var id = e.target.getAttribute('data-id');

				document.getElementById('page-form' + id).classList.toggle('hide');
				document.getElementById('page-content' + id).classList.toggle('hide');
			}
		});

	var pageform = document.getElementById('pageform');
	if (pageform) {
		pageform.addEventListener('submit', function (e) {
			e.preventDefault();

			var frm = this,
				invalid = false;

			frm.querySelectorAll('input[required]').forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			frm.querySelectorAll('select[required]').forEach(function (el) {
				if (!el.value || el.value <= 0) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			frm.querySelectorAll('textarea[required]').forEach(function (el) {
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

			for (var i of fields.keys()) {
				if (i.substring(0, 6) == 'params') {
					if (typeof (post['params']) === 'undefined') {
						post['params'] = {};
					}
					k = i.substring(7);

					post['params'][k.substring(0, k.length - 1)] = fields.get(i);
				} else {
					post[i] = fields.get(i);
				}
			}

			fetch(frm.getAttribute('data-api'), {
				method: (post['id'] ? 'PUT' : 'POST'),
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify(post),
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (data) {
					if (data.url) {
						window.location.href = data.url;
					} else {
						window.location.reload(true);
					}
				})
				.catch(function (error) {
					btn.classList.remove('processing');
					frm.prepend('<div class="alert alert-danger">' + error + '</div>');
				});
		});
	}

	//----

	var dialog = $("#new-page").dialog({
		autoOpen: false,
		height: 250,
		width: 500,
		modal: true
	});

	var addpage = document.getElementById('add-page');
	if (addpage) {
		addpage.addEventListener('click', function (e) {
			e.preventDefault();

			dialog.dialog("open");
		});
	}

	//----

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
