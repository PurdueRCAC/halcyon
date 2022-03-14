/* global $ */ // jquery.js
/* global WSGetURL */ // common.js
/* global WSPostURL */ // common.js
/* global WSDeleteURL */ // common.js

var Roles = {
	/**
	 * Populate roles
	 *
	 * @param   {object}  xml
	 * @return  {void}
	 */
	Populate: function (xml) {
		var main = document.getElementById('roles');

		if (!main) {
			return;
		}

		if (typeof (xml) == 'undefined') {
			WSGetURL(main.getAttribute('data-api'), Roles.Populate);
		} else {
			if (xml.status < 400) {
				var results = JSON.parse(xml.responseText);
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

							WSGetURL(cell.getAttribute('data-api') + "/" + resource + "." + userid, Roles.PopulateRole, results.data[count]['id']);
						}
					}
				}
			}
		}
	},

	/**
	 * Populate a role
	 *
	 * @param   {object}  xml
	 * @param   {string}  id
	 * @return  {void}
	 */
	PopulateRole: function (xml, id) {
		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);
			var cell = document.getElementById('resource' + results['resource']['id']);

			if (results['status'] == '1') {
				cell.innerHTML = '<span class="badge badge-secondary">No Role</span>';
			} else if (results['status'] == '2') {
				cell.innerHTML = '<span class="badge badge-info">Role Pending</span>';
			} else if (results['status'] == '3') {
				cell.innerHTML = '<span class="badge badge-success">Role Ready</span>';
			} else if (results['status'] == '4') {
				cell.innerHTML = '<span class="badge badge-warning">Removal Pending</span>';
			} else {
				cell.innerHTML = '<span class="badge badge-danger">Error</span>';
			}

			if (results.errors.length) {
				cell.innerHTML = cell.innerHTML + ' <span class="fa fa-exclamation-triangle text-warning tip ml-2" aria-hidden="true" title="' + results.errors.join('<br />') + '"></span><span class="sr-only"> ' + results.errors.join('<br />') + '</span>';
			}

			cell.setAttribute('data-api', results['api']);
			cell.removeAttribute('data-loading');

			if (typeof results['loginshell'] != 'undefined') {
				document.getElementById('resource' + results['resource']['id'] + '_shell').innerHTML = results['loginshell'];
			}
			if (typeof results['primarygroup'] != 'undefined') {
				document.getElementById('resource' + results['resource']['id'] + '_group').innerHTML = results['primarygroup'];
			}
			if (typeof results['pilogin'] != 'undefined') {
				document.getElementById('resource' + results['resource']['id'] + '_pi').innerHTML = results['pilogin'];
			}
		} else {
			var img = document.getElementById('IMG_' + id);
			if (img) {
				img.className = 'fa fa-exclamation-circle text-danger';
				img.alt = "Error fetching roles. May be invalid Career Account.";
			}
		}
	},

	/**
	 * Get user role status
	 *
	 * @param   {string}  userid
	 * @return  {void}
	 */
	GetUserStatus: function () {
		var resource = document.getElementById("role");
		resource = resource[resource.selectedIndex];

		if (resource) {
			WSGetURL(resource.getAttribute('data-api'), Roles.GotUserStatus);
		}
	},

	/**
	 * Callback after getting user status
	 *
	 * @param   {object}  xml
	 * @return  {void}
	 */
	GotUserStatus: function (xml) {
		var stat = document.getElementById("role_status");

		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);
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
				stat.value = "No Role Exists";
				add.classList.remove('hide');
			} else if (results['status'] == 2) {
				stat.value = "Role Creation Pending";
				mod.classList.remove('hide');
				del.classList.remove('hide');
			} else if (results['status'] == 3) {
				stat.value = "Role Exists";
				mod.classList.remove('hide');
				del.classList.remove('hide');
			} else if (results['status'] == 4) {
				stat.value = "Role Removal Pending";
				add.classList.remove('hide');
			} else {
				stat.value = "Unknown";
			}

			if (typeof results['primarygroup'] != 'undefined') {
				group.value = results['primarygroup'];
			}
			if (typeof results['loginshell'] != 'undefined') {
				shell.value = results['loginshell'];
			}
			if (typeof results['pilogin'] != 'undefined') {
				pi.value = results['pilogin'];
			}
		} else {
			//stat.classList.remove('hide');
			stat.value = "Unknown - Error";
		}
		document.getElementById("role_errors").innerHTML = ""
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

		WSPostURL(resource.getAttribute('data-api'), JSON.stringify(post), function (xml) {
			if (xml.status < 400) {
				$(".roles-dialog").dialog('close');
				Roles.GotUserStatus(xml);
			} else {
				err.classList.remove('hide');

				var msg = "There was an error while processing the request.";
				if (xml.status == 409) {
					msg = "One of the arguments is not valid.";
				}

				if (xml.responseText) {
					var j = JSON.parse(xml.responseText);
					msg = j.message;
					if (typeof msg === 'object') {
						var lines = Object.values(msg);
						msg = lines.join('<br />');
					}
				}

				err.innerHTML = msg;
			}
		});
	},

	/**
	 * Delete a role
	 *
	 * @param   {object}  xml
	 * @return  {void}
	 */
	Delete: function (userid) {
		var res = document.getElementById("role");
		var resource = res[res.selectedIndex].value;

		/*var headers = new Headers({
			'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'),
			'Content-Type': 'application/json' // : 'application/x-www-form-urlencoded'
		});*/

		if (resource) {
			/*fetch(resource.getAttribute('data-api') + "/" + resource + "." + userid, {
				method: 'DELETE',
				headers: headers
			}).then(function (response) {
				if (response.ok) {
					fetch(resource.getAttribute('data-api') + "/" + resource + "." + userid, {
						method: 'GET',
						headers: headers
					})
					.then(function (response) {
						if (response.ok) {
							Roles.GotUserStatus();
						}
						return Promise.reject(response);
					})
				}
				return Promise.reject(response);
			}).catch(function (error) {
				var err = document.getElementById("role_errors");
				err.classList.remove('hide');
				err.innerHTML = error;
			});*/

			WSDeleteURL(res.getAttribute('data-api') + "/" + resource + "." + userid, function (xml, target) {
				if (xml.status < 400) {
					WSGetURL(res.getAttribute('data-api') + "/" + target, Roles.GotUserStatus);
				} else {
					var err = document.getElementById("role_errors");
					err.classList.remove('hide');

					if (xml.status == 202) {
						err.innerHTML = "Unable to delete role because of existing queue membership or group ownership.";
					} else if (xml.status == 409) {
						err.innerHTML = "One of the arguments is not valid.";
					} else {
						err.innerHTML = "There was an error while processing the request.";
					}
				}
			}, resource + "." + userid);
		}
	}
}

document.addEventListener('DOMContentLoaded', function () {
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

	$(".roles-dialog").dialog({
		autoOpen: false,
		height: 'auto',
		width: 500,
		modal: true
	});

	document.getElementById('manage_roles').addEventListener('click', function (e) {
		e.preventDefault();

		$(this.getAttribute('href')).dialog("open");
	});
});
