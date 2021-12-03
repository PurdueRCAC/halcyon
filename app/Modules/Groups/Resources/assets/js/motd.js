/* global $ */ // jquery.js

var _DEBUG = true;

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

		$.ajax({
			url: message.getAttribute('data-api'),
			type: 'post',
			data: post,
			dataType: 'json',
			async: false,
			success: function () {
				window.location.reload();
			},
			error: function (xhr) {
				var msg = 'Failed to set notice.';

				if (xhr.responseJSON) {
					msg = xhr.responseJSON.message;
					if (typeof msg === 'object') {
						var lines = Object.values(msg);
						msg = lines.join('<br />');
					}
				}

				motd.setError(msg);
			}
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

		$.ajax({
			url: btn.getAttribute('data-api'),
			type: 'delete',
			async: false,
			success: function () {
				window.location.reload();
			},
			error: function (xhr) {
				var msg = 'Failed to set notice.';

				if (xhr.responseJSON) {
					msg = xhr.responseJSON.message;
					if (typeof msg === 'object') {
						var lines = Object.values(msg);
						msg = lines.join('<br />');
					}
				}

				motd.setError(msg);
			}
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
