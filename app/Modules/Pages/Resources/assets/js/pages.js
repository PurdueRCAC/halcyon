/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

document.addEventListener('DOMContentLoaded', function () {
	var alias = document.getElementById('field-alias');
	if (alias && !alias.value) {
		document.getElementById('field-title').addEventListener('keyup', function () {
			alias.value = this.value//.toLowerCase()
				.replace(/\s+/g, '_')
				.replace(/[^a-zA-Z0-9\-_.]+/g, '');
		});
	}

	document.querySelector('body').addEventListener('click', function (e) {
		if (e.target.matches('.delete-row') || e.target.matches('.icon-trash')) {
			e.preventDefault();
			var el = e.target;
			if (e.target.matches('.icon-trash')) {
				el = e.target.parentNode;
			}
			document.querySelector(el.getAttribute('href')).remove();
		}
	});

	document.querySelectorAll('.add-row').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var tr = document.getElementById(this.getAttribute('data-container')).querySelector('.d-none');

			var clone = tr.cloneNode(true);
			clone.classList.remove('d-none');
			clone.querySelectorAll('.btn').forEach(function (b) {
				b.classList.remove('disabled');
			});

			var cindex = document.getElementById(this.getAttribute('data-container')).querySelectorAll('.input-group').length;
			var inputs = clone.querySelectorAll('input,select');

			clone.setAttribute('id', clone.getAttribute('id').replace(/-\d+/, '-' + cindex));

			inputs.forEach(function (el) {
				el.value = '';
				el.setAttribute('name', el.getAttribute('name').replace(/\[\d+\]/, '[' + cindex + ']'));
				el.setAttribute('id', el.getAttribute('id').replace(/-\d+/, '-' + cindex));
			});

			clone.querySelectorAll('a').forEach(function (el) {
				el.setAttribute('href', el.getAttribute('href').replace(/-\d+/, '-' + cindex));
			});

			tr.parentNode.insertBefore(clone, tr);
		});
	});

	var select = document.getElementById('field-parent_id');
	if (select) {
		if (typeof TomSelect !== 'undefined') {
			var sel = new TomSelect(select, { plugins: ['dropdown_input'] });
			sel.on('change', function () {
				document.getElementById('parent-path').innerHTML = this.input.selectedOptions[0].getAttribute('data-path');
			});
		} else {
			select.addEventListener('change', function () {
				document.getElementById('parent-path').innerHTML = this.selectedOptions[0].getAttribute('data-path');
			});
		}
	}
});
