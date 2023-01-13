
var headers = {
	'Content-Type': 'application/json'
};

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
		// Filter empty values
		// This can happen if the user request access to the
		// group but not a specific resource/queue
		requests = requests.filter(function (el) {
			return (el != null && el != '');
		});

		// If no specific resources, at least approve access
		// to the group.
		if (requests.length <= 0) {
			fetch(membership, {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify({
					"membertype": 1
				})
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
			.catch(function (err) {
				alert(err);
			});
			return;
		}

		for (var i = 0; i < requests.length; i++) {
			// Ensure we have a URL to work with
			if (!requests[i]) {
				continue;
			}

			UserRequests.approvepending++;

			// Group ID isn't needed but is included for logging
			fetch(requests[i], {
				method: 'PUT',
				headers: headers,
				body: JSON.stringify({
					"groupid": groupid
				})
			})
			.then(function (response) {
				if (response.ok) {
					fetch(membership, {
						method: 'PUT',
						headers: headers,
						body: JSON.stringify({
							"membertype": 1
						})
					})
					.then(function (response) {
						if (response.ok) {
							UserRequests.approvepending--;

							if (UserRequests.approvepending == 0) {
								window.location.reload(true);
							}
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
					.catch(function (err) {
						alert(err);
					});
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
			.catch(function (err) {
				alert(err);
			});
		}
	},

	/**
	 * Reject a user request
	 *
	 * @param   {array}  requests
	 * @return  {void}
	 */
	Reject: function (requests, membership) {
		// Filter empty values
		// This can happen if the user request access to the
		// group but not a specific resource/queue
		requests = requests.filter(function (el) {
			return (el != null && el != '');
		});

		// If no specific resources, at least deny access
		// to the group.
		if (requests.length <= 0) {
			fetch(membership, {
				method: 'DELETE',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					UserRequests.rejectpending--;

					if (UserRequests.rejectpending == 0) {
						window.location.reload(true);
					}
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
			.catch(function (err) {
				alert(err);
			});
			return;
		}

		for (var i = 0; i < requests.length; i++) {
			// Ensure we have a URL to work with
			if (!requests[i]) {
				continue;
			}

			UserRequests.rejectpending++;

			fetch(requests[i], {
				method: 'DELETE',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					fetch(membership, {
						method: 'DELETE',
						headers: headers
					})
					.then(function (response) {
						if (response.ok) {
							UserRequests.rejectpending--;

							if (UserRequests.rejectpending == 0) {
								window.location.reload(true);
							}
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
					.catch(function (err) {
						alert(err);
					});
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
			.catch(function (err) {
				alert(err);
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

	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	// Pending user requests
	document.querySelectorAll('.toggle-requests').forEach(function (item) {
		item.addEventListener('change', function () {
			UserRequests.ToggleAllRadio(parseInt(this.value));

			submitr.disabled = false;
		});
	});

	document.querySelectorAll('.approve-request').forEach(function (item) {
		item.addEventListener('change', function () {
			submitr.disabled = false;
		});
	});

	submitr.addEventListener('click', function (e) {
		e.preventDefault();

		var inputs = document.querySelectorAll('.approve-request:checked');

		if (!inputs) {
			alert("Please select an option for all users before continuing.");
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
					UserRequests.Reject(
						inputs[i].getAttribute('data-api').split(','),
						inputs[i].getAttribute('data-membership')
					);
				}
			}
		}
	});
});
