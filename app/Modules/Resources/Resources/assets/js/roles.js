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
					if (results.data[count]['rolename'] != '') {
						var resource = results.data[count]['id'];

						if (!results.data[count]['retired']) {
							var image = document.createElement("span");
							image.className = "spinner-border spinner-border-sm";
							image.role = "status";
							image.id = 'IMG_' + results.data[count]['id'];

							var cell = document.getElementById("resource" + resource);

							if (cell != null) {
								cell.innerHTML = "";
								cell.setAttribute('data-loading', true);
								cell.appendChild(image);

								WSGetURL(cell.getAttribute('data-api') + "/" + resource + "." + userid, Roles.PopulateRole, results.data[count]['id']);
							}
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
				cell.innerHTML = "No Role";
				cell.style.color = "#B20000";
			} else if (results['status'] == '2') {
				cell.innerHTML = "Role Pending";
				cell.style.color = "#A06020";
			} else if (results['status'] == '3') {
				cell.innerHTML = "Role Ready";
				cell.style.color = "#006600";
			} else if (results['status'] == '4') {
				cell.innerHTML = "Removal Pending";
				cell.style.color = "#A06020";
			} else {
				cell.innerHTML = "Error";
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
			var add = $("#role_add");
			var mod = $("#role_modify");
			var del = $("#role_delete");

			add.addClass('hide');
			mod.addClass('hide');
			del.addClass('hide');

			container.className = '';

			if (results['status'] == 0) {
				stat.value = "Login Invalid";
				//add.style.display = "none";
				//mod.style.display = "none";
				//del.style.display = "none";
			} else if (results['status'] == 1) {
				stat.value = "No Role Exists";
				add.removeClass('hide');
				//mod.style.display = "none";
				//del.style.display = "none";
			} else if (results['status'] == 2) {
				stat.value = "Role Creation Pending";
				//add.style.display = "none";
				mod.removeClass('hide');
				del.removeClass('hide');
			} else if (results['status'] == 3) {
				stat.value = "Role Exists";
				//add.style.display = "none";
				mod.removeClass('hide');
				del.removeClass('hide');
			} else if (results['status'] == 4) {
				stat.value = "Role Removal Pending";
				add.removeClass('hide');
				//mod.style.display = "none";
				//del.style.display = "none";
			} else {
				stat.value = "Unknown";
				//add.style.display = "none";
				//mod.style.display = "none";
				//del.style.display = "none";
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
		var resource = document.getElementById("role");
		resource = resource[resource.selectedIndex].value;

		if (resource) {
			WSDeleteURL(resource.getAttribute('data-api') + "/" + resource + "." + userid, function (xml, target) {
				if (xml.status < 400) {
					WSGetURL(resource.getAttribute('data-api') + "/" + target, Roles.GotUserStatus);
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

$(document).ready(function () {
	Roles.Populate();

	$('#role').on('change', function () {
		Roles.GetUserStatus($(this).data('id'));
	});
	$('.role-add').on('click', function (e) {
		e.preventDefault();
		Roles.Add($(this).data('id'));
	});
	$('.role-delete').on('click', function (e) {
		e.preventDefault();
		Roles.Delete($(this).data('id'));
	});

	$(".roles-dialog").dialog({
		autoOpen: false,
		height: 'auto',
		width: 500,
		modal: true
	});

	$('#manage_roles').on('click', function (e) {
		e.preventDefault();

		$($(this).attr('href')).dialog("open");
	});
});
