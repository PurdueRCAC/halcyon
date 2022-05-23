/* global $ */ // jquery.js

/**
 * Convert title to URL segment
 *
 * @return void
 */
function setAlias() {
	document.getElementById('field-alias').value = this.value
		.trim()
		.toLowerCase()
		.replace(/\s+/g, '_')
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

	var sselects = $('.searchable-select');
	if (sselects.length) {
		$('.searchable-select').select2()
			.on('select2:select', function () {
				if (this.classList.contains('filter-submit')) {
					$(this).closest('form').submit();
				}

				this.dispatchEvent(new Event('change'));
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
		var dialog = $("#new-page").dialog({
			autoOpen: false,
			height: 250,
			width: 600,
			modal: true
		});

		document.getElementById('toolbar-plus').addEventListener('click', function (e) {
			e.preventDefault();

			dialog.dialog("open");
		});
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
