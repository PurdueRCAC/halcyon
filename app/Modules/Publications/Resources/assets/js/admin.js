
document.addEventListener('DOMContentLoaded', function () {
	var alias = document.getElementById('field-alias');
	if (alias && !alias.value) {
		document.getElementById('field-name').addEventListener('keyup', function () {
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

	document.querySelectorAll('.type-dependent').forEach(function (el) {
		el.classList.add('d-none');
	});

	document.querySelectorAll('[name="type_id"]').forEach(function (el) {
		el.addEventListener('change', function () {
			document.querySelectorAll('.type-dependent').forEach(function (dep) {
				dep.classList.add('d-none');
				if (dep.classList.contains('type-' + el.selectedOptions[0].getAttribute('data-alias'))) {
					dep.classList.remove('d-none');
				}
			});
		})

		document.querySelectorAll('.type-' + el.selectedOptions[0].getAttribute('data-alias')).forEach(function (dep) {
			dep.classList.remove('d-none');
		});
	});
});
