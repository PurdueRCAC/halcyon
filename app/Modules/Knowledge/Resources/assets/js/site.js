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
	var headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

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
				headers: headers,
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

	var sselects = document.querySelectorAll('.searchable-select');
	if (sselects.length) {
		sselects.forEach(function (el) {
			var sel, sels = new Array();
			sel = new TomSelect(el, {
				plugins: ['dropdown_input'],
				render: {
					option: function (data, escape) {
						return '<div>' +
							'<span class="d-inline-block indent">' + escape(data.indent) + '</span>' +
							'<span class="d-inline-block">' +
								'<span class="text">' + escape(data.text.replace(data.indent, '')) + '</span><br />' +
								'<span class="path text-muted">' + escape(data.path) + '</span>' +
							'</span>' +
						'</div>';
					},
					item: function (data, escape) {
						return '<div>' +
							'<span class="d-inline-block">' +
								'<span class="text">' + escape(data.text.replace(data.indent, '')) + '</span><br />' +
								'<span class="path text-muted">' + escape(data.path) + '</span>' +
							'</span>' +
						'</div>';
					}
				}
			});
			sels.push(sel);
		});
	}

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

			post.content = post.content.replace(/<mark class="kbif if">/g, '');
			post.content = post.content.replace(/<mark class="kbif elseif">/g, '');
			post.content = post.content.replace(/<mark class="kbif else">/g, '');
			post.content = post.content.replace(/<mark class="kbif endif">/g, '');
			post.content = post.content.replace(/<\/mark>/g, '');

			fetch(frm.getAttribute('data-api'), {
				method: (post['id'] ? 'PUT' : 'POST'),
				headers: headers,
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

	//----

	var pageids = document.querySelectorAll('.page-revision-id');
	if (pageids.length) {
		pageids.forEach(function (radio) {
			radio.addEventListener('change', function(){

				if (!radio.checked) {
					return;
				}

				var start, stop, i;
				for (i = 0; i < pageids.length; i++) {
					if (pageids[i].classList.contains('page-revision-oldid') && pageids[i].checked) {
						stop = pageids[i].closest('tr').id;
						pageids[i].classList.remove('d-none');
						break;
					}
				}

				for (i = 0; i < pageids.length; i++) {
					if (pageids[i].classList.contains('page-revision-newid') && pageids[i].checked) {
						start = pageids[i].closest('tr').id;
						pageids[i].classList.remove('d-none');
						break;
					}
				}

				var hide = false, hideo = true;
				for (i = 0; i < pageids.length; i++) {
					if (pageids[i].closest('tr').id == stop) {
						hide = true;
					}
					if (pageids[i].classList.contains('page-revision-newid')) {
						if (hide) {
							pageids[i].classList.add('d-none');
						} else {
							pageids[i].classList.remove('d-none');
						}
					} else {
						if (hideo) {
							pageids[i].classList.add('d-none');
						} else {
							pageids[i].classList.remove('d-none');
						}
					}
					if (pageids[i].closest('tr').id == start) {
						hideo = false;
					}
				}
			});
		});
	}

	document.querySelectorAll('.btn-diff').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var oldid = 0,
				newid = 0;

			document.querySelectorAll('input[type=radio]').forEach(function(radio){
				if (oldid && newid) {
					return;
				}
				if (radio.name == 'oldid' && radio.checked) {
					oldid = radio.value;
				}
				if (radio.name == 'newid' && radio.checked) {
					newid = radio.value;
				}
			});

			if (!oldid || !newid) {
				return;
			}

			fetch(el.getAttribute('data-api') + '?oldid=' + oldid + '&newid=' + newid, {
				method: 'GET',
				headers: headers
			})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (data.diff) {
					document.getElementById('page-diff').innerHTML = data.diff;
				} else {
					document.getElementById('page-diff').innerHTML = '<div class="alert alert-warning">' + el.getAttribute('data-emptydiff') + '</div>';
				}
			})
			.catch(function (error) {
				//btn.classList.remove('processing');
				//frm.prepend('<div class="alert alert-danger">' + error + '</div>');
				alert(error);
			});
		});
	});
});
