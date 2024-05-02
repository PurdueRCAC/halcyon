/* global Halcyon */ // core.js
/* global Handlebars */ // handlebars.js

document.addEventListener('DOMContentLoaded', function () {
	document.querySelector('body').addEventListener('click', function (e) {
		if (!e.target.matches('.remove-choice')) {
			return;
		}
		e.preventDefault();

		var result = confirm(this.getAttribute('data-confirm'));

		if (result) {
			var field = document.getElementById(this.getAttribute('href').replace('#', ''));

			// delete relationship
			if (this.getAttribute('data-api')) {
				fetch(this.getAttribute('data-api'), {
					method: 'DELETE',
					headers: {
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'),
						'Content-Type': 'application/json'
					}
				})
				.then(function (response) {
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
				})
				.then(function () {
					Halcyon.message('success', 'Item removed');
					field.remove();
				})
				.catch(function (error) {
					Halcyon.message('danger', error);
				});
			} else {
				field.remove();
			}
		}
	});

	document.querySelectorAll('.add-choice').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var source = document.getElementById(this.getAttribute('data-template').replace('#', '')).html();
			var container = document.getElementById(this.getAttribute('data-container').replace('#', ''));

			var template = Handlebars.compile(source),
				context = {
					"i": container.find('fieldset').length
				},
				html = template(context);

			container.append(html);
		});
	});
});
