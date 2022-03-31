
var _DEBUG = false;

/**
 * Message of the Day
 */
var motd = {
	/**
	 * Set the MOTD for a group
	 *
	 * @param   {string}  group
	 * @return  {void}
	 */
	set: function (group) {
		var message = document.getElementById("MotdText_" + group);

		motd.clearError();

		if (!group) {
			motd.setError('No group ID provided.');
			return false;
		}

		var post = {
			'groupid': group,
			'motd': message.value
		};

		_DEBUG ? console.log('post: ' + message.getAttribute('data-api'), post) : null;

		fetch(message.getAttribute('data-api'), {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
			},
			body: JSON.stringify(post)
		})
			.then(function (response) {
				if (response.ok) {
					window.location.reload();
				}
				return response.json();
			})
			.then(function (data) {
				var msg = data.message;
				if (typeof msg === 'object') {
					msg = Object.values(msg).join('<br />');
				}
				throw msg;
			})
			.catch(function (error) {
				motd.setError(error);
			});
	},

	/**
	 * Clear error messages
	 *
	 * @return  {void}
	 */
	clearError: function () {
		var err = document.getElementById("MotdText_error");
		if (err) {
			err.classList.add('hide');
			err.innerHTML = '';
		}
	},

	/**
	 * Set an error message
	 *
	 * @param   {string}  msg
	 * @return  {void}
	 */
	setError: function (msg) {
		var err = document.getElementById("MotdText_error");
		if (err) {
			err.classList.remove('hide');
			err.innerHTML = msg;
		}
		//Halcyon.message('danger', msg);
	},

	/**
	 * Delete the MOTD for a group
	 *
	 * @param   {string}  group
	 * @return  {void}
	 */
	delete: function (group) {
		motd.clearError();

		if (!group) {
			motd.setError('No group ID provided.');
			return false;
		}

		var btn = document.getElementById("MotdText_delete_" + group);

		_DEBUG ? console.log('delete: ' + btn.getAttribute('data-api')) : null;

		fetch(btn.getAttribute('data-api'), {
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
			}
		})
			.then(function (response) {
				if (response.ok) {
					window.location.reload();
				}
				return response.json();
			})
			.then(function (data) {
				var msg = data.message;
				if (typeof msg === 'object') {
					msg = Object.values(msg).join('<br />');
				}
				throw msg;
			})
			.catch(function (error) {
				motd.setError(error);
			});
	}
}

document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.motd-delete').forEach(function (item) {
		item.addEventListener('click', function (e) {
			e.preventDefault();
			motd.delete(this.getAttribute('data-group'));
		});
	});

	document.querySelectorAll('.motd-set').forEach(function (item) {
		item.addEventListener('click', function (e) {
			e.preventDefault();
			motd.set(this.getAttribute('data-group'));
		});
	});
});
