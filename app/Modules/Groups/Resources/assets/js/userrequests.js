/* global $ */ // jquery.js

var UserRequests = {
	/**
	 * Pending approved requests
	 *
	 * @var  {number}
	 */
	approvepending: 0,

	/**
	 * Pending rejected requests
	 *
	 * @var  {number}
	 */
	rejectpending: 0,

	/**
	 * Approve a user request
	 *
	 * @param   {array}  requests
	 * @return  {void}
	 */
	Approve: function (requests, groupid, membership) {
		for (var i = 0; i < requests.length; i++) {
			UserRequests.approvepending++;

			// Group ID isn't needed but is included for logging
			$.ajax({
				url: requests[i],
				type: 'put',
				data: {
					"groupid": groupid
				},
				dataType: 'json',
				async: false,
				success: function () {
					$.ajax({
						url: membership,
						type: 'put',
						data: {
							"membertype": 1
						},
						dataType: 'json',
						async: false,
						success: function () {
							UserRequests.approvepending--;

							if (UserRequests.approvepending == 0) {
								window.location.reload(true);
							}
						},
						error: function (xhr) {
							var msg = 'Failed to approve request.';

							if (xhr.responseJSON) {
								msg = xhr.responseJSON.message;
								if (typeof msg === 'object') {
									var lines = Object.values(msg);
									msg = lines.join('<br />');
								}
							}

							SetError(msg);
						}
					});

					/*UserRequests.approvepending--;

					if (UserRequests.approvepending == 0) {
						window.location.reload(true);
					}*/
				},
				error: function (xhr) {
					var msg = 'Failed to approve request.';

					if (xhr.responseJSON) {
						msg = xhr.responseJSON.message;
						if (typeof msg === 'object') {
							var lines = Object.values(msg);
							msg = lines.join('<br />');
						}
					}

					SetError(msg);
				}
			});
		}
	},

	/**
	 * Reject a user request
	 *
	 * @param   {array}  requests
	 * @return  {void}
	 */
	Reject: function (requests) {
		for (var i = 0; i < requests.length; i++) {
			UserRequests.rejectpending++;

			$.ajax({
				url: requests[i],
				type: 'delete',
				async: false,
				success: function () {
					UserRequests.rejectpending--;

					if (UserRequests.rejectpending == 0) {
						window.location.reload(true);
					}
				},
				error: function (xhr) {
					var msg = 'Failed to reject request.';

					if (xhr.responseJSON) {
						msg = xhr.responseJSON.message;
						if (typeof msg === 'object') {
							var lines = Object.values(msg);
							msg = lines.join('<br />');
						}
					}

					SetError(msg);
				}
			});
		}
	},

	/**
	 * toggle accept all radio buttons
	 *
	 * @param   {string}  btn
	 * @return  {void}
	 */
	ToggleAllRadio: function (btn) {
		if (btn == 0) {
			document.getElementById('denyAll').checked = false;
			document.querySelectorAll('.approve-value1').forEach(function (item) {
				item.checked = false;
			});
		}
		else if (btn == 1) {
			document.getElementById('acceptAll').checked = false;
			document.querySelectorAll('.approve-value0').forEach(function (item) {
				item.checked = false;
			});
		}

		document.querySelectorAll('.approve-value' + btn).forEach(function (item) {
			item.checked = true;
		});
	}
}

document.addEventListener('DOMContentLoaded', function () {
	var submitr = document.getElementById('submit-requests');

	if (!submitr) {
		return;
	}

	// Pending user requests
	document.querySelectorAll('.toggle-requests').forEach(function (item) {
		item.addEventListener('change', function (e) {
			UserRequests.ToggleAllRadio(parseInt(this.value));

			submitr.disabled = false;
		});
	});

	document.querySelectorAll('.approve-request').forEach(function (item) {
		item.addEventListener('change', function (e) {
			submitr.disabled = false;
		});
	});

	submitr.addEventListener('click', function (e) {
		e.preventDefault();

		var inputs = document.querySelectorAll('.approve-request:checked');

		if (!inputs) {
			alert("Must select an option for all users before continuing.");
			return;
		}

		this.classList.add('processing');

		UserRequests.approvepending = 0;

		// Loop through list and approve users. -2 so it doesnt hit the approve/deny all buttons
		for (var i = 0; i < inputs.length; i++) {
			//var user = inputs[i].value.split(",")[0];
			var approve = inputs[i].value.split(",")[1];

			if (inputs[i].checked == true) {
				if (approve == 0) {
					// Approve the user
					UserRequests.Approve(
						inputs[i].getAttribute('data-api').split(','),
						inputs[i].getAttribute('data-groupid'),
						inputs[i].getAttribute('data-membership')
					);
				}
				else if (approve == 1) {
					// Delete the request
					UserRequests.Reject(inputs[i].getAttribute('data-api').split(','));
				}
			}
		}
	});
});
