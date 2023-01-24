/* global $ */ // jquery.js

var headers = {
	'Content-Type': 'application/json'
};

/**
 * New directory type
 *
 * @return  {void}
 */
function NewDirType() {
	var selected = document.getElementById("new_dir_type");
	selected = selected.options[selected.selectedIndex].value;

	var user_row = document.getElementById("new_dir_user_row");
	var user_select = document.getElementById("new_dir_user_select");
	var input = document.getElementById("new_dir_input");
	var unixgroup_select = document.getElementById("new_dir_unixgroup_select");
	var autouserunixgroup_row = document.getElementById("new_dir_autouserunixgroup_row");
	var unixgroup_select_decoy = document.getElementById("new_dir_unixgroup_select_decoy");
	var parent_unixgroup = document.getElementById("selected_dir_unixgroup");
	var x;

	if (selected == "user") {
		user_row.classList.remove('hidden');
		unixgroup_select.disabled = false;
		unixgroup_select_decoy.classList.add('hidden');
		unixgroup_select.classList.remove('hidden');
		autouserunixgroup_row.classList.add('hidden');

		if (!input.value) {
			input.value = "(Select User)";
		}

		for (x = 0; x < unixgroup_select.options.length; x++) {
			if (unixgroup_select.options[x].innerHTML == parent_unixgroup.value) {
				unixgroup_select.options[x].selected = true;
			}
		}
	}

	if (selected == "userwrite") {
		user_row.classList.remove('hidden');
		unixgroup_select.disabled = false;
		unixgroup_select_decoy.classList.add('hidden');
		unixgroup_select.classList.remove('hidden');
		autouserunixgroup_row.classList.add('hidden');

		if (!input.value) {
			input.value = "(Select User)";
		}

		for (x = 0; x < unixgroup_select.options.length; x++) {
			if (unixgroup_select.options[x].innerHTML == parent_unixgroup.value) {
				unixgroup_select.options[x].selected = true;
			}
		}
	} else if (selected == "userprivate") {
		user_row.classList.remove('hidden');
		user_select.options[0].selected = true;

		if (!input.value) {
			input.value = "(Select User)";
		}

		var opt = document.createElement("option");
		opt.innerHTML = parent_unixgroup.value;

		for (x = 0; x < unixgroup_select.options.length; x++) {
			if (unixgroup_select.options[x].innerHTML == parent_unixgroup.value) {
				opt.value = unixgroup_select.options[x].value;
			}
		}

		unixgroup_select_decoy.classList.remove('hidden');
		unixgroup_select.classList.add('hidden');
		autouserunixgroup_row.classList.add('hidden');

		$(unixgroup_select_decoy).empty();
		unixgroup_select_decoy.appendChild(opt);
		unixgroup_select_decoy.disabled = true;
	} else if (selected == "normal") {
		if (input.value == "(Select User)") {
			input.value = "";
		}

		user_row.classList.add('hidden');
		user_select.options[0].selected = true;
		unixgroup_select.disabled = false;
		unixgroup_select_decoy.classList.add('hidden');
		unixgroup_select.classList.remove('hidden');
		autouserunixgroup_row.classList.add('hidden');
	} else if (selected == "autouserprivate" || selected == "autouserread" || selected == "autouserreadwrite") {
		if (input.value == "(Select User)") {
			input.value = "";
		}

		user_row.classList.add('hidden');
		user_select.options[0].selected = true;
		unixgroup_select.disabled = false;
		unixgroup_select_decoy.classList.add('hidden');
		unixgroup_select.classList.remove('hidden');
		autouserunixgroup_row.classList.remove('hidden');
	}
}

/**
 * Guess the directory's unix group
 *
 * @return  {void}
 */
function GuessDirUnixGroup() {
	var input = document.getElementById("new_dir_input");
	var unixgroup = document.getElementById("new_dir_unixgroup_select");

	for (var x = 0; x < unixgroup.options.length; x++) {
		var opt = unixgroup.options[x];
		var bits = opt.innerHTML.split("-");
		if (bits.length > 1) {
			if (bits[1] == input.value) {
				unixgroup.selectedIndex = x;
				break;
			}
		}
	}
}

/**
 * Distribute quota
 *
 * @param   {string}  dir
 * @param   {string}  api
 * @return  {void}
 */
function DistributeQuota(dir, api) {
	var post = { "bytes": "ALL" };

	fetch(api, {
		method: 'PUT',
		headers: headers,
		body: JSON.stringify(post)
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
	.catch(function () {
		var error = document.getElementById(dir + "_error");
		error.classList.remove('hide');
		error.innerHTML = "An error occurred while setting unix group.";
	});
}

/**
 * Create a new directory
 *
 * @param   {object}  btn
 * @return  {void}
 */
function NewDir(btn) {
	var img = document.getElementById("new_dir_img");
	var input = document.getElementById("new_dir_input");
	var group = document.getElementById("groupid").value;
	var resource = btn.getAttribute('data-resource');//document.getElementById("resourceid").value;
	var parentdir = document.getElementById("selected_dir").value;
	//var storageresource = document.getElementById("storageresource").value;

	var type = document.getElementById("new_dir_type");
	type = type.options[type.selectedIndex].value;

	img.className = 'spinner-border';

	var share = document.getElementById("share_radio").checked;
	var deduct = document.getElementById("deduct_radio").checked;
	var unalloc = document.getElementById("unalloc_radio").checked;

	var unalloc_input = document.getElementById("new_dir_quota_unalloc").value;
	var deduct_input = document.getElementById("new_dir_quota_deduct").value;

	var unixgroup = document.getElementById("new_dir_unixgroup_select");
	unixgroup = unixgroup.options[unixgroup.selectedIndex].value;

	var error = document.getElementById("new_dir_error");

	if (type == "autouserread" || type == "autouserprivate" || type == "autouserreadwrite") {
		var autouserunixgroup = document.getElementById("new_dir_autouserunixgroup_select");
		autouserunixgroup = autouserunixgroup.options[autouserunixgroup.selectedIndex].value;

		if (autouserunixgroup == "") {
			error.classList.remove('hide');
			error.innerHTML = "Please select a unix group.";
			//img.src = "/include/images/error.png";
			img.className = 'icon-warning';
			return;
		}
	}

	if (unixgroup == "" && type != "userprivate") {
		error.classList.remove('hide');
		error.innerHTML = "Please select a unix group.";

		img.className = 'icon-warning';
		return;
	}

	if (type == "userprivate") {
		unixgroup = document.getElementById("new_dir_unixgroup_select_decoy");
		unixgroup = unixgroup.options[unixgroup.selectedIndex].value;
	}

	var user = null;
	if (type == "user" || type == "userprivate" || type == "userwrite") {
		user = document.getElementById("new_dir_user_select");
		user = user.options[user.selectedIndex].value;

		if (user == "") {
			error.classList.remove('hide');
			error.innerHTML = "Please select a user.";

			img.className = 'icon-warning';
			return;
		}
	}

	var bytes;
	var bytesource = "";
	if (share) {
		bytes = "-";
	} else if (deduct) {
		bytes = deduct_input;
		bytesource = "p";
	} else if (unalloc) {
		bytes = unalloc_input;
	}

	var post = {
		"name": input.value,
		"parentstoragedirid": parentdir,
		"groupid": group,
		"resourceid": resource,
		"bytes": bytes,
		"bytesource": bytesource,
		"unixgroupid": unixgroup,
		//"storageresource" : storageresource,
	};

	if (user != null) {
		post['owneruserid'] = user;
	}
	if (type == "userprivate") {
		post['groupread'] = "0";
		post['groupwrite'] = "0";
		post['publicread'] = "0";
	}
	if (type == "user") {
		post['groupread'] = "1";
		post['groupwrite'] = "0";
		post['publicread'] = "0";
	}
	if (type == "userwrite") {
		post['groupread'] = "1";
		post['groupwrite'] = "1";
		post['publicread'] = "0";
	}
	if (type == "autouserread") {
		post['autouser'] = "1";
		post['groupread'] = "1";
		post['groupwrite'] = "0";
		post['autouserunixgroupid'] = autouserunixgroup;
	}
	if (type == "autouserprivate") {
		post['autouser'] = "2";
		post['groupread'] = "0";
		post['groupwrite'] = "0";
		post['autouserunixgroupid'] = autouserunixgroup;
	}
	if (type == "autouserreadwrite") {
		post['autouser'] = "3";
		post['groupread'] = "1";
		post['groupwrite'] = "1";
		post['autouserunixgroupid'] = autouserunixgroup;
	}

	fetch(btn.getAttribute('data-api'), {
		method: 'POST',
		headers: headers,
		body: JSON.stringify(post)
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
		error.classList.remove('hide');
		if (err) {
			error.innerHTML = err;
		} else {
			error.innerHTML = 'Failed to create directory.';
		}
	});
}

/**
 * Delete a directory
 *
 * @param   {string}  dir
 * @param   {string}  path
 * @return  {void}
 */
function DeleteDir(btn) {
	if (confirm(btn.getAttribute('data-confirm'))) {
		fetch(btn.getAttribute('data-api'), {
			method: 'DELETE',
			headers: headers
		})
		.then(function (response) {
			if (response.ok) {
				$('#' + btn.getAttribute('data-dir') + '_error')
					.removeClass('alert-danger')
					.addClass('alert-success')
					.removeClass('hide')
					.text('Directory removed!');
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
			var er = document.getElementById(btn.getAttribute('data-dir') + '_error');
			if (er) {
				er.classList.remove('hide');
				er.innerHTML = err;
			}
		});
	}
}

/**
 * Reset permissions
 *
 * @param   {string}  dir
 * @param   {string}  path
 * @return  {void}
 */
function ResetPermissions(btn) {
	if (confirm(btn.getAttribute('data-confirm'))) {
		var post = {
			"fixpermissions": "1"
		};

		fetch(btn.getAttribute('data-api'), {
			method: 'PUT',
			headers: headers,
			body: JSON.stringify(post)
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
			var er = document.getElementById(btn.getAttribute('data-dir') + '_error');
			if (er) {
				er.classList.remove('hide');
				er.innerHTML = err;
			}
		});
	}
}

/**
 * Edit a unix group
 *
 * @param   {string}  dir
 * @return  {void}
 */
function EditUnixGroup(dir, api) {
	//var button = document.getElementById(dir + "_edit_button");
	//var span = document.getElementById(dir + "_unixgroup_span");
	var input = document.getElementById(dir + "_unixgroup_select");
	//var auto_span = document.getElementById(dir + "_autouserunixgroup_span");
	var auto_input = document.getElementById(dir + "_autouserunixgroup_select");
	//var span_quota = document.getElementById(dir + "_quota_span");
	var input_quota = document.getElementById(dir + "_quota_input");
	//var span_other = document.getElementById(dir + "_other_read_span");
	var input_otheryes = document.getElementById(dir + "_other_read_box");
	//var input_otherno = document.getElementById(dir + "_other_read_box");

	//var span_type = document.getElementById(dir + "_dir_type");
	var input_type = document.getElementById(dir + "_dir_type_select");

	/*if (button.value.match(/^Edit/)) {
		// Turn on edit mode
		//img.src = "/include/images/save.png";
		button.value = "Save Changes";
		if (input != null) {
			span.style.display = "none";
			input.style.display = "inline";
			if (auto_span != null) {
				auto_span.style.display = "none";
				auto_input.style.display = "inline";
			}
		}
		if (input_other != null) {
			span_other.style.display = "none";
			input_other.style.display = "inline";
		}
		if (input_quota != null) {
			span_quota.style.display = "none";
			input_quota.style.display = "inline";
		}
		if (input_type != null) {
			span_type.style.display = "none";
			input_type.style.display = "inline";
		}
	} else {*/
	// Save quota
	//img.src = "/include/images/loading.gif";
	//img.style.width = "20px";
	//img.style.height = "20px";

	// Make WS call
	var post = {};
	if (input != null) {
		post['unixgroupid'] = input.options[input.selectedIndex].value;
	}

	if (auto_input != null) {
		post['autouserunixgroupid'] = auto_input.options[auto_input.selectedIndex].value;
	}

	if (input_otheryes != null) {
		if (input_otheryes.checked == true) {
			post['publicread'] = '1';
		} else {
			post['publicread'] = '0';
		}
	}

	if (input_quota != null && input_quota.value) {
		post['bytes'] = input_quota.value;
	}

	if (input_type != null) {
		var type = input_type.options[input_type.selectedIndex].value;
		if (type == "user") {
			post['ownerread'] = "1";
			post['ownerwrite'] = "1";
			post['groupread'] = "1";
			post['groupwrite'] = "0";
		} else if (type == "userwrite") {
			post['ownerread'] = "1";
			post['ownerwrite'] = "1";
			post['groupread'] = "1";
			post['groupwrite'] = "1";
		} else if (type == "userprivate") {
			post['ownerread'] = "1";
			post['ownerwrite'] = "1";
			post['groupread'] = "0";
			post['groupwrite'] = "0";
		} else if (type == "autouser") {
			post['autouser'] = "1";
			post['groupread'] = "1";
			post['groupwrite'] = "0";
		} else if (type == "autouserprivate") {
			post['autouser'] = "2";
			post['groupread'] = "0";
			post['groupwrite'] = "0";
		} else if (type == "autouserreadwrite") {
			post['autouser'] = "3";
			post['groupread'] = "1";
			post['groupwrite'] = "1";
		} else {
			post['groupread'] = "0";
		}
	}

	fetch(api, {
		method: 'PUT',
		headers: headers,
		body: JSON.stringify(post)
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
	.catch(function () {
		var error = document.getElementById(dir + "_error");
		error.classList.remove('hide');
		error.innerHTML = "An error occurred while setting unix group.";
	});
}

/**
 * Pedning directories
 *
 * @var  {number}
 */
var pending_dirs = 0;

/**
 * Create default directories
 *
 * @param   {string}  group
 * @param   {string}  name
 * @param   {string}  resource
 * @param   {number}  quota
 * @param   {string}  base
 * @param   {string}  apps
 * @param   {string}  data
 * @return  {void}
 */
function CreateDefaultDirs(api, group, name, resource, quota, base, apps, etc, data) {
	var top_dir = {
		'groupid': group,
		'name': name,
		'resourceid': resource,
		'bytes': 'ALL', //quota + " B",
		'parentstoragedirid': 0,
		'unixgroupid': base,
		'ownerread': 1,
		'ownerwrite': 1,
		'groupread': 1,
		'groupwrite': 0,
		'publicread': 0,
		'publicwrite': 0
	};

	var dirs_desired = {
		'dirs': [
			{
				'groupid': group,
				'name': "apps",
				'resourceid': resource,
				'bytes': '-',
				'unixgroupid': apps,
				'ownerread': 1,
				'ownerwrite': 1,
				'groupread': 1,
				'groupwrite': 1,
				'publicread': 1,
				'publicwrite': 0
			},
			{
				'groupid': group,
				'name': "data",
				'resourceid': resource,
				'bytes': '-',
				'unixgroupid': data,
				'ownerread': 1,
				'ownerwrite': 1,
				'groupread': 1,
				'groupwrite': 1,
				'publicread': 0,
				'publicwrite': 0
			},
			{
				'groupid': group,
				'name': "etc",
				'resourceid': resource,
				'bytes': '-',
				'unixgroupid': etc,
				'ownerread': 1,
				'ownerwrite': 1,
				'groupread': 1,
				'groupwrite': 1,
				'publicread': 1,
				'publicwrite': 0
			}
		]
	};

	// Create base dir
	fetch(api, {
		method: 'POST',
		headers: headers,
		body: JSON.stringify(top_dir)
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
		var parentdir = results['id'];
		var x;

		// set parent dir
		for (x = 0; x < dirs_desired.dirs.length; x++) {
			dirs_desired.dirs[x]['parentstoragedirid'] = parentdir;
		}

		// create sub dirs
		pending_dirs = dirs_desired.dirs.length;
		for (x = 0; x < dirs_desired.dirs.length; x++) {
			if (dirs_desired.dirs[x]['unixgroupid'] <= 0) {
				pending_dirs--;
				continue;
			}

			fetch(api, {
				method: 'POST',
				headers: headers,
				body: JSON.stringify(dirs_desired.dirs[x])
			})
			.then(function (response) {
				if (response.ok) {
					pending_dirs--;

					if (pending_dirs == 0) {
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
			.catch(function () {
				document.querySelectorAll('.dir-create-default').forEach(function (el) {
					el.classList.remove('processing');
				});

				var er = document.getElementById('error_new');
				if (er) {
					er.classList.remove('hide');
					er.innerHTML = "An error occurred while creating directory.";
				}
			});
		}
	})
	.catch(function () {
		document.querySelectorAll('.dir-create-default').forEach(function(el) {
			el.classList.remove('processing');
		});

		var er = document.getElementById('error_new');
		if (er) {
			er.classList.remove('hide');
			er.innerHTML = "An error occurred while creating directory.";
		}
	});
}

/**
 * Set base group name
 *
 * @param   {string}  api
 * @return  {void}
 */
function SetBaseName(api) {
	var name = document.getElementById("unixgroup").value;
	var post = JSON.stringify({ "unixgroup": name });

	if (name == "") {
		var er = document.getElementById('error_unixgroup');
		if (er) {
			er.classList.remove('hide');
			er.innerHTML = 'Unix group name is required.';
		}
	}

	fetch(api, {
		method: 'PUT',
		headers: headers,
		body: post
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
		var er = document.getElementById('error_unixgroup');
		if (er) {
			er.classList.remove('hide');
			er.innerHTML = err;
		}
	});
}

/**
 * Unix base groups
 *
 * @const
 * @type  {array}
 */
var BASEGROUPS = Array('', 'data', 'apps');

/**
 * Create UNIX group
 *
 * @param   {integer}  num    index for BASEGROUPS array
 * @param   {object}   btn
 * @param   {bool}     all
 * @return  {void}
 */
function CreateNewGroupVal(num, btn, all) {
	var group = btn.data('group');
	//var = base = btn.data('value');

	if (typeof (all) == 'undefined') {
		all = true;
	}

	fetch(btn.data('api'), {
		method: 'POST',
		headers: headers,
		body: JSON.stringify({
			'longname': BASEGROUPS[num],
			'groupid': group
		})
	})
	.then(function (response) {
		if (response.ok) {
			num++;
			if (all && num < BASEGROUPS.length) {
				setTimeout(function () {
					CreateNewGroupVal(num, btn, all);
				}, 5000);
			} else {
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
		btn.removeClass('processing');

		var er = document.getElementById('error_unixgroups');
		if (er) {
			er.classList.remove('hide');
			er.innerHTML = err;
		}
	});
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	/*var autocompleteUsers = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name + ' (' + el.username + ')',
						name: el.name,
						id: el.id,
					};
				}));
			});
		};
	};

	var autocompleteGroups = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name,
						name: el.name,
						id: el.id,
					};
				}));
			});
		};
	};

	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'data',
				height: 150,
				delay: 100,
				minLength: 1
			},
			limit: 1
		});
	}

	var newsuser = $(".form-groups");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: 'Select group...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteGroups(newsuser.attr('data-uri')),
				dataName: 'data',
				height: 150,
				delay: 100,
				minLength: 1
			},
			limit: 1
		});
	}*/

	var users = $(".form-users");
	if (users.length) {
		users.each(function (i, user) {
			user = $(user);
			var cl = user.clone()
				.attr('type', 'hidden')
				.val(user.val().replace(/([^:]+):/, ''));
			user
				.attr('name', user.attr('id') + i)
				.attr('id', user.attr('id') + i)
				.val(user.val().replace(/(:\d+)$/, ''))
				.after(cl);
			user.autocomplete({
				minLength: 2,
				source: function (request, response) {
					return $.getJSON(user.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
						response($.map(data.data, function (el) {
							return {
								label: el.name + ' (' + el.username + ')',
								name: el.name,
								id: el.id,
							};
						}));
					});
				},
				select: function (event, ui) {
					event.preventDefault();
					// Set selection
					user.val(ui.item.label); // display the selected text
					cl.val(ui.item.id); // save selected id to input
					return false;
				}
			});
		});
	}

	var groups = $(".form-groups");
	if (groups.length) {
		groups.each(function (i, group) {
			group = $(group);
			var cl = group.clone()
				.attr('type', 'hidden')
				.val(group.val().replace(/([^:]+):/, ''));
			group
				.attr('name', 'groupid' + i)
				.attr('id', group.attr('id') + i)
				.val(group.val().replace(/(:\d+)$/, ''))
				.after(cl);
			group.autocomplete({
				minLength: 2,
				source: function (request, response) {
					return $.getJSON(group.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
						response($.map(data.data, function (el) {
							return {
								label: el.name,
								name: el.name,
								id: el.id,
							};
						}));
					});
				},
				select: function (event, ui) {
					event.preventDefault();
					// Set selection
					group.val(ui.item.label); // display the selected text
					cl.val(ui.item.id); // save selected id to input
					return false;
				}
			});
		});
	}

	$('#field-name').on('keyup', function () {
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/[^a-z0-9\-_]+/g, '');

		$(this).val(val);
	});

	$(".tree").each(function (i, el) {
		var data = JSON.parse($('#' + $(el).attr('id') + '-data').html());

		$(el).fancytree({
			activate: function (event, data) {
				var node = data.node;
				var did = node.data.id;

				$("#" + did + "_dialog").dialog({
					modal: true,
					width: '550px',
					//position: { my: "left top", at: "left top", of: $( "#tree" ) },
					close: function () {
						var tree = $.ui.fancytree.getTree($(el));
						tree.getActiveNode().setActive(false);
					}
				});
				//$("#" + did + "_dialog").dialog('open');
				$('#selected_dir').attr('value', node.data.parentdir);
				$('#selected_dir_unixgroup').attr('value', node.data.parentunixgroup);
				$('#new_dir_path').html(node.data.path + "/");
				$('#new_dir_quota_available').html(node.data.parentquota);
				$('#new_dir_quota_available2').html(node.data.parentquota);
			},
			persist: true,
			extensions: ["table", "glyph"],
			table: {
				indentation: 20,      // indent 20px per node level
				nodeColumnIdx: 0,     // render the node title into the 2nd column
				checkboxColumnIdx: 0  // render the checkboxes into the 1st column
			},
			glyph: {
				preset: "awesome4",
				map: {
					doc: "far fa-plus-circle",
					folder: "far fa-folder",
					folderOpen: "far fa-folder-open"
				}
			},
			source: data,
			collapse: function (event, data) {
				if (typeof (history.pushState) != 'undefined') {
					var url = window.location.href.match(/\?.*/);
					if (url != null) {
						url = url[0].replace('?', '');

						var kvp = url.split('&');
						var i = 0;

						for (; i < kvp.length; i++) {
							if (kvp[i].startsWith('expanded=')) {
								var pair = kvp[i].split('=');
								var expanded = pair[1].split(','),
									vals = [];
								for (var j = 0; j < expanded.length; j++) {
									if (expanded[j] == data.node.data.id) {
										continue;
									}
									vals.push(expanded[j]);
								}
								pair[1] = vals.join(',');
								kvp[i] = pair.join('=');
								break;
							}
						}
						url = '?' + kvp.join('&');

						history.pushState(null, null, encodeURI(url));
					}
				}
			},
			expand: function (event, data) {
				if (typeof (history.pushState) != 'undefined') {
					var url = window.location.href.match(/\?.*/);
					if (url != null) {
						url = url[0].replace('?', '');

						var kvp = url.split('&');
						var i = 0;

						for (; i < kvp.length; i++) {
							if (kvp[i].startsWith('expanded=')) {
								var pair = kvp[i].split('=');
								var expanded = pair[1].split(','),
									vals = [];
								for (var j = 0; j < expanded.length; j++) {
									if (expanded[j] != data.node.data.id) {
										vals.push(expanded[j]);
									}
								}
								pair[1] = (vals.join(',') ? vals.join(',') + ',' : '') + data.node.data.id;
								kvp[i] = pair.join('=');
								break;
							}
						}

						if (i >= kvp.length) {
							kvp[kvp.length] = ['expanded', data.node.data.id].join('=');
						}
						url = '?' + kvp.join('&');
					} else {
						url = "?expanded=" + data.node.data.id;
					}

					history.pushState(null, null, encodeURI(url));
				}
			},
			renderColumns: function (event, data) {
				var node = data.node,
					$tdList = $(node.tr).find(">td");

				if (node.data.quota == '0 B') {
					$tdList.eq(1).text("-");
				} else {
					$tdList.eq(1).text(node.data.quota);
					$tdList.eq(1).attr("id", node.key + "_quota_td");
					if (node.data.quotaproblem == "1") {
						if (node.data.quota != "-") {
							$tdList.eq(1).addClass('quotaProblem');
							$tdList.eq(1).html(node.data.quota + '<span class="icon-error">Storage space is over-allocated. Quotas reduced until allocation balanced.</span>');
						}
					}
				}
				$tdList.eq(1).addClass('quota');

				if (typeof (node.data.futurequota) != 'undefined') {
					$tdList.eq(2).html(node.data.futurequota);
				}
				$tdList.eq(2).addClass('quota');
				// (index #2 is rendered by fancytree)
			}
		});
	});

	$('#new_dir_input').on('change', function () {
		GuessDirUnixGroup();
	});
	$('#new_dir_type').on('change', function () {
		NewDirType();
	});
	$('#new_dir_user_select').on('change', function () {
		//NewDirUserSelected();
		var input = document.getElementById("new_dir_input");
		var selected = document.getElementById("new_dir_user_select");
		selected = selected.options[selected.selectedIndex].innerHTML;
		input.value = selected;
	});
	/*$('#new_dir_unixgroup_select').on('change', function (){
		NewDirUser();
	});
	$('#new_dir_autouserunixgroup_select').on('change', function (){
		NewDirUser();
	});

	$('#group').on('change', function (){
		NewDirGroup($(this).data('resource'));
	});
	$('#new_top_dir_input').on('change', function (){
		GuessTopDirUnixGroup();
	});
	$('#new_top_dir').on('click', function(e) {
		e.preventDefault();
		NewTopDir();
	});*/

	$('#new_dir').on('click', function (e) {
		e.preventDefault();
		NewDir(this);
	});

	$('#deduct_radio').on('click', function () {
		document.getElementById('new_dir_quota_deduct').focus();
	});
	$('#new_dir_quota_deduct').on('focus', function () {
		document.getElementById('deduct_radio').checked = true;
	});

	$('#unalloc_radio').on('click', function () {
		document.getElementById('new_dir_quota_unalloc').focus();
	});
	$('#new_dir_quota_unalloc').on('focus', function () {
		document.getElementById('unalloc_radio').checked = true;
	});

	/*$('.permissions-reset').on('click', function(e) {
		e.preventDefault();
		ResetPermissions($(this).data('dir'), $(this).data('path'));
	});*/
	$('.unixgroup-edit').on('click', function (e) {
		e.preventDefault();
		EditUnixGroup($(this).data('dir'), $(this).data('api'));
	});
	$('.unixgroup-create').on('click', function(e) {
		e.preventDefault();

		$(this).addClass('processing');

		CreateNewGroupVal(0, $(this), true);
	});
	$('.unixgroup-basename-set').on('click', function(e) {
		e.preventDefault();
		SetBaseName($(this).data('api'));
	});
	$('.dir-delete').on('click', function (e) {
		e.preventDefault();
		DeleteDir(this);
	});
	$('.dir-create-default').on('click', function(e) {
		e.preventDefault();

		$(this).addClass('processing');

		CreateDefaultDirs(
			$(this).data('api'),
			$(this).data('id'),
			$('#new-name').val(), //$(this).data('unixgroup'),
			$('#new-resourceid').val(),
			$(this).data('quota'),
			$(this).data('base'),
			$('#new-apps').is(':checked') ? $('#new-apps').val() : 0,
			$('#new-etc').is(':checked') ? $('#new-etc').val() : 0,
			$('#new-data').is(':checked') ? $('#new-data').val() : 0
		);
	});

	$('.quota_upa').on('click', function(e){
		e.preventDefault();
		DistributeQuota($(this).data('dir'), $(this).data('api'));
	});

	$('.permissions-reset').on('click', function (e) {
		e.preventDefault();
		ResetPermissions(this);
	});

	$('body').on('change', '.form-control,.form-check-input', function () {
		var dialog = $(this).closest('.dialog');
		if (dialog.length) {
			$('#' + $(dialog).data('id') + '_save_button').prop('disabled', false);
		}
	});

	$('.dialog-btn').on('click', function (e) {
		e.preventDefault();

		$($(this).attr('href')).dialog({
			modal: true,
			width: '550px',
			open: function () {

				/*var groups = $(".form-group-storage");
				if (groups.length) {
					$(".form-group-storage").select2({
						//placeholder: $(this).data('placeholder')
					});
				}*/
				$(".form-group-storage").each(function (i, el) {
					$(el).select2({
						//placeholder: $(el).attr('placeholder'),
						ajax: {
							url: $(el).data('api'),// + '&api_token=' + $('meta[name="api-token"]').attr('content'),
							dataType: 'json',
							maximumSelectionLength: 1,
							data: function (params) {
								var query = {
									search: params.term,
									order: 'name',
									order_dir: 'asc'
								}

								return query;
							},
							processResults: function (data) {
								for (var i = 0; i < data.data.length; i++) {
									data.data[i].text = data.data[i].name;
								}

								var d = {};
								d.id = -1;
								d.text = el.options[1].innerHTML;
								data.data.unshift(d);

								return {
									results: data.data
								};
							}
						}
					})
					.on('select2:select', function (e) {
						e.preventDefault();

						var group = $(this);

						var seller = $('#' + group.attr('data-update'));
						//var dest_queue = document.getElementById("field-id").value;

						if (group.val() == 0) {
							seller.val(0);
							seller.parent().addClass('d-none');
							return;
						} else {
							seller.parent().removeClass('d-none');
						}
					});
				});
				/*var groups = $(".form-group-storage");
				if (groups.length) {
					groups.each(function (i, group) {
						group = $(group);
						var cl = group.clone()
							.attr('type', 'hidden')
							.val(group.val().replace(/([^:]+):/, ''));
						group
							.attr('name', 'groupid' + i)
							.attr('id', group.attr('id') + i)
							.val(group.val().replace(/(:\d+)$/, ''))
							.after(cl);
						group.autocomplete({
							minLength: 2,
							source: function (request, response) {
								return $.getJSON(group.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
									response($.map(data.data, function (el) {
										return {
											label: el.name,
											name: el.name,
											id: el.id,
										};
									}));
								});
							},
							select: function (event, ui) {
								event.preventDefault();
								// Set selection
								group.val(ui.item.label); // display the selected text
								cl.val(ui.item.id); // save selected id to input
								return false;
							}
						});
					});
				}*/
			}
		});
	});

	$('.dialog-submit').on('click', function (e) {
		e.preventDefault();

		var btn = $(this),
			frm = btn.closest('form'),
			invalid = false;

		if (frm.length) {
			var elms = frm[0].querySelectorAll('input[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			elms = frm[0].querySelectorAll('select[required]');
			elms.forEach(function (el) {
				if (!el.value || el.value <= 0) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			elms = frm[0].querySelectorAll('textarea[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});

			if (invalid) {
				return;
			}
		}

		$.ajax({
			url: frm.data('api'),
			type: btn.attr('data-id') ? 'put' : 'post',
			data: frm.serialize(),
			dataType: 'json',
			async: false,
			success: function () {
				window.location.reload(true);
			},
			error: function (xhr) {
				var msg = 'Failed to process item.';
				if (xhr.responseJSON) {
					msg = xhr.responseJSON.message;
					if (typeof msg === 'object') {
						var lines = Object.values(msg);
						msg = lines.join('<br />');
					}
				}
				$('#error_' + btn.attr('data-type') + btn.attr('data-id'))
					.removeClass('hide')
					.text(msg);
			}
		});
	});

	$('.storage-delete').on('click', function (e) {
		e.preventDefault();

		var btn = this;

		if (confirm(btn.getAttribute('data-confirm'))) {
			$.ajax({
				url: btn.getAttribute('data-api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
					window.location.reload(true);
				},
				error: function (xhr) {
					var msg = 'Failed to delete item.';
					if (xhr.responseJSON) {
						msg = xhr.responseJSON.message;
						if (typeof msg === 'object') {
							var lines = Object.values(msg);
							msg = lines.join('<br />');
						}
					}
					$('#' + btn.attr('data-dir') + '_error')
						.removeClass('hide')
						.text(msg);
				}
			});
		}
	});

	$('.storage-edit').on('click', function (e) {
		e.preventDefault();

		$($(this).attr('href')).dialog({
			modal: true,
			width: '550px'
		});
	});

	$('input.datetime').datetimepicker({
		duration: '',
		//showTime: true,
		constrainInput: false,
		//stepMinutes: 1,
		//stepHours: 1,
		//altTimeField: '',
		//time24h: true,
		dateFormat: 'yy-mm-dd',
		controlType: 'select',
		oneLine: true,
		timeFormat: 'HH:mm:00'
	});
});
