
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

	document.querySelectorAll('.btn-delete').forEack(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			if (confirm(el.getAttribute('data-confirm'))) {
				fetch(el.getAttribute('data-api'), {
					method: 'DELETE',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					}
				})
					.then(function (response) {
						if (response.ok) {
							window.location.reload(true);
							return;
						}

						return response.json().then(function (data) {
							var msg = data.message;
							if (typeof msg === 'object') {
								msg = Object.values(msg).join('<br />');
							}
							throw msg;
						});
					})
					.catch(function (error) {
						alert(error);
					});
			}
		});
	});
});
