
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.property-edit').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			document.querySelectorAll('.edit-hide').forEach(function (item) {
				item.classList.add('hide');
			});
			document.querySelectorAll('.edit-show').forEach(function (item) {
				item.classList.remove('hide');
			});
		});
	});

	document.querySelectorAll('.property-cancel').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			document.querySelectorAll('.edit-show').forEach(function (item) {
				item.classList.add('hide');
			});
			document.querySelectorAll('.edit-hide').forEach(function (item) {
				item.classList.remove('hide');
			});
		});
	});

	document.querySelectorAll('.property-save').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var data = {
				loginShell: document.getElementById('INPUT_loginshell').value
			};

			fetch(el.getAttribute('data-api'), {
					method: 'PUT',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					},
					body: JSON.stringify(data),
				})
				.then(function () {
					window.location.reload(true);
				})
				.catch(function (error) {
					var err = document.getElementById('loginshell_error');
					err.classList.remove('hide');
					if (error) {
						err.innerHTML = error;
					} else {
						err.innerHTML = 'Failed to update login shell.';
					}
				});
		});
	});
});
