/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

document.addEventListener('DOMContentLoaded', function () {
	var alias = document.getElementById('field-alias');
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

	var bselects = document.querySelectorAll('.resources-select');
	if (bselects.length) {
		bselects.forEach(function (select) {
			new TomSelect(select, { plugins: ['dropdown_input', 'remove_button'] });
		});
	}

	document.querySelectorAll('.add-version').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var li = document.getElementById(this.getAttribute('data-target'));

			if (typeof (li) !== 'undefined') {
				var container = li.parentNode;
				var i = parseInt(this.getAttribute('data-length')) + 1;
				this.setAttribute('data-length', i);
				var template = li.cloneNode(true);
				template.classList.remove('d-none');
				template.classList.add('version');

				template.setAttribute('id', template.getAttribute('id').replace(/\{\{id\}\}/g, i));
				template.innerHTML = template.innerHTML.replace(/\{\{id\}\}/g, i);

				var sel = template.querySelector('select');
				sel.classList.add('resources-select');
				new TomSelect(sel, { plugins: ['dropdown_input', 'remove_button'] });

				container.insertBefore(template, li);
			}
		});
	});

	document.querySelector('body').addEventListener('click', function (e) {
		var target;

		if (e.target.matches('.remove-version')) {
			target = e.target;
		} else if (e.target.parentNode.matches('.remove-version')) {
			target = e.target.parentNode;
		}

		if (target) {
			e.preventDefault();

			target.closest('.row').remove();
		}
	});

	document.querySelectorAll('.remove-application').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			if (confirm(this.getAttribute('data-confirm'))) {
				return true;
			}

			return false;
		});
	});
});
