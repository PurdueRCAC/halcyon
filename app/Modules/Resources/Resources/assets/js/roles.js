var Roles = {
	/**
	 * Populate roles
	 *
	 * @param   {object}  xml
	 * @return  {void}
	 */
	Populate: function(xml) {
		var main = document.getElementById('roles');

		if (!main) {
			return;
		}

		if (typeof(xml) == 'undefined') {
			WSGetURL(main.getAttribute('data-api'), Roles.Populate);
		} else {
			if (xml.status < 400) {
				var results = JSON.parse(xml.responseText);
				var userid = document.getElementById("userid").value;

				for (var count=0; count<results.data.length; count++) {
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
								console.log(cell.getAttribute('data-api') +  "/" + resource + "." + userid);
								WSGetURL(cell.getAttribute('data-api') +  "/" + resource + "." + userid, Roles.PopulateRole, results.data[count]['id']);
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
		if (xml.status == 200) {
			var results = JSON.parse(xml.responseText);
			var cell = document.getElementById('resource' + results.data['resource']['id']);

			if (results.data['status'] == '1') {
				cell.innerHTML = "No Role";
				cell.style.color = "#B20000";
			} else if (results.data['status'] == '2') {
				cell.innerHTML = "Role Pending";
				cell.style.color = "#A06020";
			} else if (results.data['status'] == '3') {
				cell.innerHTML = "Role Ready";
				cell.style.color = "#006600";
			} else if (results.data['status'] == '4') {
				cell.innerHTML = "Removal Pending";
				cell.style.color = "#A06020";
			} else {
				cell.innerHTML = "Error";
			}
		} else {
			var img = document.getElementById('IMG_' + id);
			if (img) {
				//img.src = "/include/images/error.png";
				img.className = 'fa fa-exclamation-circle';
				img.alt = "Error fetching roles. May be invalid Career Account.";
			}
			//var div = document.getElementById("role_errors");
			//div.innerHTML = "Error fetching roles. May be invalid Career Account.";
		}
	},

	/**
	 * Get user role status
	 *
	 * @param   {string}  userid
	 * @return  {void}
	 */
	GetUserStatus: function(userid) {
		var resource = document.getElementById("role");
		resource = resource[resource.selectedIndex].value;
		//userid = userid.split('/');
		//userid = userid[3];
		//resource = resource.split('/');
		//resource = resource[3];

		if (resource) {
			WSGetURL(ROOT_URL + "resources/members/" + resource + "." + userid, Roles.GotUserStatus);
		}
	},

	GotUserStatus: function(xml) {
				var stat = document.getElementById("role_status");
				if (xml.status == 200) {
					var results = JSON.parse(xml.responseText);
					var table = document.getElementById("role_table");
					var group = document.getElementById("role_group");
					var shell = document.getElementById("role_shell");
					var pi = document.getElementById("role_pi");
					var add = document.getElementById("role_add");
					var mod = document.getElementById("role_modify");
					var del = document.getElementById("role_delete");

					table.style.display = "table-row-group";
					if (results['status'] == 0) {
						stat.innerHTML = "Login Invalid";
						add.style.display = "none";
						mod.style.display = "none";
						del.style.display = "none";
					} else if (results['status'] == 1) {
						stat.innerHTML = "No Role Exists";
						add.style.display = "block";
						mod.style.display = "none";
						del.style.display = "none";
					} else if (results['status'] == 2) {
						stat.innerHTML = "Role Creation Pending";
						add.style.display = "none";
						mod.style.display = "block";
						del.style.display = "block";
					} else if (results['status'] == 3) {
						stat.innerHTML = "Role Exists";
						add.style.display = "none";
						mod.style.display = "block";
						del.style.display = "block";
					} else if (results['status'] == 4) {
						stat.innerHTML = "Role Removal Pending";
						add.style.display = "block";
						mod.style.display = "none";
						del.style.display = "none";
					} else {
						stat.innerHTML = "Unknown";
						add.style.display = "none";
						mod.style.display = "none";
						del.style.display = "none";
					}

					group.value = results['primarygroup'];
					shell.value = results['loginshell'];
					pi.value = results['pilogin'];
				} else {
					stat.innerHTML = "Unknown - Error";
				}
				document.getElementById("role_errors").innerHTML = "";
			},

	/**
	 * Add a role
	 *
	 * @param   {string}  userid
	 * @return  {void}
	 */
	AddRole: function(userid) {
		var resource = document.getElementById("role");
		resource = resource[resource.selectedIndex].value;
		var group = document.getElementById("role_group").value;
		var shell = document.getElementById("role_shell").value;
		var pi = document.getElementById("role_pi").value;

		var post = {
			'user' : userid,
			'resource' : resource,
			'primarygroup' : group,
			'loginshell' : shell,
			'pilogin' : pi
		};

		WSPostURL(ROOT_URL + "resources/members", JSON.stringify(post), function(xml) {
			if (xml.status == 200) {
				var results = JSON.parse(xml.responseText);
				var userid = results['user'].split('/');
				userid = userid[3];
				var resource = results['resource'].split('/');
				resource = resource[3];
				WSGetURL(ROOT_URL + "resourcemember/" + resource + "." + userid, Roles.GotUserRoleStatus);
			} else if (xml.status == 409) {
				document.getElementById("role_errors").innerHTML = "One of the arguments is not valid.";
			} else {
				document.getElementById("role_errors").innerHTML = "There was an error while processing the request.";
			}
		});
	},

	/**
	 * Delete a role
	 *
	 * @param   {object}  xml
	 * @return  {void}
	 */
	DeleteRole: function (userid) {
		var resource = document.getElementById("role");
		resource = resource[resource.selectedIndex].value;
		userid = userid.split('/');
		userid = userid[3];
		resource = resource.split('/');
		resource = resource[3];
		if (resource) {
			WSDeleteURL(ROOT_URL + "resources/members/" + resource + "." + userid, function(xml, target) {
				if (xml.status == 200) {
					WSGetURL(ROOT_URL + "resources/members/" + target, Roles.GotUserRoleStatus);
				} else if (xml.status == 202) {
					document.getElementById("role_errors").innerHTML = "Unable to delete role because of existing queue membership or group ownership.";
				} else if (xml.status == 409) {
					document.getElementById("role_errors").innerHTML = "One of the arguments is not valid.";
				} else {
					document.getElementById("role_errors").innerHTML = "There was an error while processing the request.";
				}
			}, resource + "." + userid);
		}
	}
}

$(document).ready(function() { 
	Roles.Populate();
});
