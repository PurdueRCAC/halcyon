/* global $ */ // jquery.js

var headers = {
	'Content-Type': 'application/json'
};

var Roles = {
	/**
	 * Populate roles
	 *
	 * @return  {void}
	 */
	Populate: function () {
		var main = document.getElementById('roles');

		if (!main) {
			return;
		}

		fetch(main.getAttribute('data-api'), {
			method: 'GET',
			headers: headers
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
		.then(function (results) {
			var userid = document.getElementById("userid").value;

			for (var count = 0; count < results.data.length; count++) {
				if (results.data[count]['rolename'] == '') {
					continue;
				}

				var resource = results.data[count]['id'];

				if (!results.data[count]['retired']) {
					var indicator = document.createElement("span");
					indicator.className = "spinner-border spinner-border-sm";
					indicator.role = "status";
					indicator.id = 'IMG_' + results.data[count]['id'];

					var cell = document.getElementById("resource" + resource);

					if (cell != null) {
						cell.innerHTML = "";
						cell.setAttribute('data-loading', true);
						cell.appendChild(indicator);

						fetch(cell.getAttribute('data-api') + "/" + resource + "." + userid, {
							method: 'GET',
							headers: headers
						})
						.then(function (response) {
							if (response.ok) {
								return response.json();
							}

							var img = document.getElementById('IMG_' + results.data[count]['id']);
							if (img) {
								img.className = 'fa fa-exclamation-circle text-danger';
								img.alt = "Error fetching roles. May be invalid Career Account.";
							}
						})
						.then(function (data) {
							Roles.PopulateRole(data);
						});
					}
				}
			}
		})
		.catch(function (error) {
			alert(error);
		});
	},

	/**
	 * Populate a role
	 *
	 * @param   {object}  results
	 * @return  {void}
	 */
	PopulateRole: function (results) {
		var cell = document.getElementById('resource' + results['resource']['id']),
			status = 'danger';

		if (results['status'] == '1') {
			status = 'secondary';
		} else if (results['status'] == '2') {
			status = 'info';
		} else if (results['status'] == '3') {
			status = 'success';
		} else if (results['status'] == '4') {
			status = 'warning';
		} else {
			status = 'danger';
		}
		cell.innerHTML = '<span class="badge badge-' + status + '">' + results['status_text'] + '</span>';

		if (results.errors.length) {
			cell.innerHTML = cell.innerHTML + ' <span class="fa fa-exclamation-triangle text-warning tip ml-2" aria-hidden="true" title="' + results.errors.join('<br />') + '"></span><span class="sr-only"> ' + results.errors.join('<br />') + '</span>';
		}

		cell.setAttribute('data-api', results['api']);
		cell.removeAttribute('data-loading');

		if (typeof results['loginShell'] != 'undefined') {
			document.getElementById('resource' + results['resource']['id'] + '_shell').innerHTML = results['loginShell'];
		}
		if (typeof results['primarygroup'] != 'undefined') {
			document.getElementById('resource' + results['resource']['id'] + '_group').innerHTML = results['primarygroup'];
		}
		if (typeof results['pilogin'] != 'undefined') {
			document.getElementById('resource' + results['resource']['id'] + '_pi').innerHTML = results['pilogin'];
		}
	},

	/**
	 * Get user role status
	 *
	 * @return  {void}
	 */
	GetUserStatus: function () {
		var resource = document.getElementById("role");
		resource = resource[resource.selectedIndex];

		if (resource) {
			fetch(resource.getAttribute('data-api'), {
				method: 'GET',
				headers: headers
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
			.then(function (results) {
				Roles.GotUserStatus(results);
			})
			.catch(function (error) {
				alert(error);
			});
		}
	},

	/**
	 * Callback after getting user status
	 *
	 * @param   {object}  results
	 * @return  {void}
	 */
	GotUserStatus: function (results) {
		var stat = document.getElementById("role_status");

		// Inputs
		var container = document.getElementById("role_table");
		var group = document.getElementById("role_group");
		var shell = document.getElementById("role_shell");
		var pi = document.getElementById("role_pi");
		// Buttons
		var add = document.getElementById("role_add");
		var mod = document.getElementById("role_modify");
		var del = document.getElementById("role_delete");

		add.classList.add('hide');
		mod.classList.add('hide');
		del.classList.add('hide');

		container.className = '';

		if (results['status'] == 0) {
			stat.value = "Login Invalid";
		} else if (results['status'] == 1) {
			stat.value = results['status_text'];
			add.classList.remove('hide');
		} else if (results['status'] == 2) {
			stat.value = results['status_text'];
			mod.classList.remove('hide');
			del.classList.remove('hide');
		} else if (results['status'] == 3) {
			stat.value = results['status_text'];
			mod.classList.remove('hide');
			del.classList.remove('hide');
		} else if (results['status'] == 4) {
			stat.value = results['status_text'];
			add.classList.remove('hide');
		} else {
			stat.value = results['status_text'];
		}

		if (typeof results['primarygroup'] != 'undefined') {
			group.value = results['primarygroup'];
		}
		if (typeof results['loginShell'] != 'undefined') {
			shell.value = results['loginShell'];
		}
		if (typeof results['pilogin'] != 'undefined') {
			pi.value = results['pilogin'];
		}

		document.getElementById("role_errors").innerHTML = "";
		document.getElementById("role_errors").classList.add('hide');
	},

	/**
	 * Add a role
	 *
	 * @param   {string}  userid
	 * @return  {void}
	 */
	Add: function (userid) {
		var err = document.getElementById("role_errors");
		err.classList.add('hide');
		err.innerHTML = '';

		var resource = document.getElementById("role");

		var post = {
			'user': userid,
			'resource': resource[resource.selectedIndex].value,
			'primarygroup': document.getElementById("role_group").value,
			'loginshell': document.getElementById("role_shell").value,
			'pilogin': document.getElementById("role_pi").value
		};

		fetch(resource.getAttribute('data-api'), {
			method: 'POST',
			headers: headers,
			body: JSON.stringify(post)
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
		.then(function (results) {
			$('#manage_roles_dialog').modal('hide');
			Roles.GotUserStatus(results);
			Roles.PopulateRole(results);
		})
		.catch(function (error) {
			err.classList.remove('hide');
			err.innerHTML = error;
		});
	},

	/**
	 * Delete a role
	 *
	 * @param   {string}  userid
	 * @return  {void}
	 */
	Delete: function (userid) {
		var res = document.getElementById("role");
		var resource = res[res.selectedIndex].value;

		if (resource) {
			fetch(res.getAttribute('data-api') + "/" + resource + "." + userid, {
				method: 'DELETE',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					fetch(res.getAttribute('data-api') + "/" + resource + "." + userid, {
						method: 'GET',
						headers: headers
					})
					.then(function (resp) {
						if (resp.ok) {
							return resp.json();
						}
						return Promise.reject(resp);
					})
					.then(function (results) {
						$('#manage_roles_dialog').modal('hide');
						Roles.GotUserStatus(results);
						Roles.PopulateRole(results);
					});
				} else {
					return Promise.reject(response);
				}
			})
			.catch(function (error) {
				var err = document.getElementById("role_errors");
				err.classList.remove('hide');
				err.innerHTML = error;
			});
		}
	}
}

document.addEventListener('DOMContentLoaded', function () {
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	Roles.Populate();

	document.getElementById('role').addEventListener('change', function () {
		Roles.GetUserStatus(this.getAttribute('data-id'));
	});

	document.querySelectorAll('.role-add').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			Roles.Add(this.getAttribute('data-id'));
		});
	});
	document.querySelectorAll('.role-delete').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			Roles.Delete(this.getAttribute('data-id'));
		});
	});
});
