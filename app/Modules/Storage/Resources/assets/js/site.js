/* global $ */ // jquery.js

var headers = {
	'Content-Type': 'application/json'
};

/**
 * New directory type
 *
 * @param   {object} selected
 * @return  {void}
 */
function NewDirType(sel) {
	//var selected = document.getElementById("new_dir_type");
	selected = sel.options[sel.selectedIndex].value;
	var id = sel.getAttribute('data-id');

	var user_row = document.getElementById(id + "_dir_user_row");
	var user_select = document.getElementById(id + "_dir_user_select");
	var input = document.getElementById(id + "_dir_input");
	var unixgroup_select = document.getElementById(id + "_dir_unixgroup_select");
	var autouserunixgroup_row = document.getElementById(id + "_dir_autouserunixgroup_row");
	var unixgroup_select_decoy = document.getElementById(id + "_dir_unixgroup_select_decoy");
	var parent_unixgroup = document.getElementById(id + "_selected_dir_unixgroup");
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

		unixgroup_select_decoy.innerHTML = '';
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
	var parentdir = document.getElementById("new_selected_dir").value;
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
				var er = document.getElementById(btn.getAttribute('data-dir') + '_error');
				if (er) {
					er.classList.remove('alert-danger');
					er.classList.add('alert-success');
					er.classList.remove('hide');
					er.innerHTML = 'Directory removed!';
				}
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
 * @param   {string}  api
 * @return  {void}
 */
function EditUnixGroup(dir, api) {
	var input = document.getElementById(dir + "_unixgroup_select");
	var auto_input = document.getElementById(dir + "_autouserunixgroup_select");
	var input_quota = document.getElementById(dir + "_quota_input");
	var input_otheryes = document.getElementById(dir + "_other_read_box");
	var input_type = document.getElementById(dir + "_dir_type");

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
			//post['groupread'] = "0";
			post['groupread'] = "1";
			post['groupwrite'] = "1";
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
	.catch(function (err) {
		var er = document.getElementById(dir + "_error");
		if (er) {
			er.classList.remove('hide');
			er.innerHTML = err;
		}
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
 * @param   {string}  api
 * @param   {string}  group
 * @param   {string}  name
 * @param   {string}  resource
 * @param   {number}  quota
 * @param   {string}  base
 * @param   {string}  apps
 * @param   {string}  etc
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
	var group = btn.getAttribute('data-group');
	//var = base = btn.getAttribute('data-value');

	if (typeof (all) == 'undefined') {
		all = true;
	}

	fetch(btn.getAttribute('data-api'), {
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

	var fieldname = document.getElementById('field-name');
	if (fieldname) {
		fieldname.addEventListener('keyup', function () {
			var val = this.value;

			val = val.toLowerCase()
				.replace(/\s+/g, '-')
				.replace(/[^a-z0-9\-_]+/g, '');

			this.value = val;
		});
	}

	document.querySelectorAll('summary').forEach(function (el) {
		el.addEventListener('click', function () {
			if (typeof (history.pushState) != 'undefined') {
				var url = window.location.href.match(/\?.*/);
				if (url != null) {
					url = url[0].replace('?', '');

					var kvp = url.split('&');
					var i = 0;

					if (this.parentNode.open) {
						for (; i < kvp.length; i++) {
							if (kvp[i].startsWith('expanded=')) {
								var pair = kvp[i].split('=');
								var expanded = pair[1].split(','),
									vals = [];
								for (var j = 0; j < expanded.length; j++) {
									if (expanded[j] == this.getAttribute('data-id')) {
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
					} else {
						for (; i < kvp.length; i++) {
							if (kvp[i].startsWith('expanded=')) {
								var pair = kvp[i].split('=');
								var expanded = pair[1].split(','),
									vals = [];
								for (var j = 0; j < expanded.length; j++) {
									if (expanded[j] != this.getAttribute('data-id')) {
										vals.push(expanded[j]);
									}
								}
								pair[1] = (vals.join(',') ? vals.join(',') + ',' : '') + this.getAttribute('data-id');
								kvp[i] = pair.join('=');
								break;
							}
						}

						if (i >= kvp.length) {
							kvp[kvp.length] = ['expanded', this.getAttribute('data-id')].join('=');
						}
						url = '?' + kvp.join('&');
						history.pushState(null, null, encodeURI(url));
					}
				} else if (!this.parentNode.open) {
					url = "?expanded=" + this.getAttribute('data-id');
					history.pushState(null, null, encodeURI(url));
				}
			}
		});
	});

	var url = window.location.href.match(/\?.*/);
	if (url != null) {
		url = url[0].replace('?', '');

		var kvp = url.split('&');
		var i = 0, dir;

		for (; i < kvp.length; i++) {
			if (kvp[i].startsWith('expanded=')) {
				var pair = kvp[i].split('=');
				var expanded = pair[1].split(',');
				for (var j = 0; j < expanded.length; j++) {
					dir = document.getElementById('directory-' + expanded[j]);
					if (dir) {
						dir.open = true;
					}
				}
				break;
			}
		}
	}

	document.querySelectorAll('.dir-modal').forEach(function (el) {
		el.addEventListener('click', function () {
			$(this.getAttribute('href')).modal('show');
		});
	});

	document.querySelectorAll('.btn-newdir').forEach(function (el) {
		el.addEventListener('click', function () {
			document.getElementById('new_selected_dir').value = this.getAttribute('data-parent');
			document.getElementById('new_selected_dir_unixgroup').value = this.getAttribute('data-parentunixgroup');
			document.getElementById('new_dir_path').innerHTML = this.getAttribute('data-path') + "/";
			document.getElementById('new_dir_quota_available').innerHTML = this.getAttribute('data-parentquota');
			document.getElementById('new_dir_quota_available2').innerHTML = this.getAttribute('data-parentquota');
		});
	});

	var newdiri = document.getElementById('new_dir_input');
	if (newdiri) {
		newdiri.addEventListener('change', function () {
			GuessDirUnixGroup();
		});
	}
	/*var newdirt = document.getElementById('new_dir_type');
	if (newdirt) {
		newdirt.addEventListener('change', function () {
			NewDirType(this);
		});
	}*/
	document.querySelectorAll('.dir_type').forEach(function (el) {
		el.addEventListener('change', function () {
			NewDirType(this);
		});
	});

	var newdiru = document.getElementById('new_dir_user_select');
	if (newdiru) {
		newdiru.addEventListener('change', function () {
			//NewDirUserSelected();
			var input = document.getElementById("new_dir_input");
			var selected = document.getElementById("new_dir_user_select");
			selected = selected.options[selected.selectedIndex].innerHTML;
			input.value = selected;
		});
	}

	var newdir = document.getElementById('new_dir');
	if (newdir) {
		newdir.addEventListener('click', function (e) {
			e.preventDefault();
			NewDir(this);
		});
	}

	var deductradio = document.getElementById('deduct_radio');
	if (deductradio) {
		deductradio.addEventListener('click', function () {
			document.getElementById('new_dir_quota_deduct').focus();
		});
	}
	var newdirdeduct = document.getElementById('new_dir_quota_deduct');
	if (newdirdeduct) {
		newdirdeduct.addEventListener('focus', function () {
			document.getElementById('deduct_radio').checked = true;
		});
	}

	var unallocradio = document.getElementById('unalloc_radio');
	if (unallocradio) {
		unallocradio.addEventListener('click', function () {
			document.getElementById('new_dir_quota_unalloc').focus();
		});
	}
	var newdirunallocradio = document.getElementById('new_dir_quota_unalloc');
	if (newdirunallocradio) {
		newdirunallocradio.addEventListener('focus', function () {
			document.getElementById('unalloc_radio').checked = true;
		});
	}

	/*document.querySelectorAll('.permissions-reset').forEach(function (el) {
		el.addEventListener('click', function(e) {
			e.preventDefault();
			ResetPermissions(this.getAttribute('data-dir'), this.getAttribute('data-path'));
		});
	}*/
	document.querySelectorAll('.unixgroup-edit').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			EditUnixGroup(this.getAttribute('data-dir'), this.getAttribute('data-api'));
		});
	});
	document.querySelectorAll('.unixgroup-create').forEach(function (el) {
		el.addEventListener('click', function(e) {
			e.preventDefault();

			this.classList.add('processing');

			CreateNewGroupVal(0, this, true);
		});
	});
	document.querySelectorAll('.unixgroup-basename-set').forEach(function (el) {
		el.addEventListener('click', function(e) {
			e.preventDefault();
			SetBaseName(this.getAttribute('data-api'));
		});
	});
	document.querySelectorAll('.dir-delete').forEach(function(el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			DeleteDir(this);
		});
	});
	document.querySelectorAll('.dir-create-default').forEach(function (el) {
		el.addEventListener('click', function(e) {
			e.preventDefault();

			this.classList.add('processing');

			CreateDefaultDirs(
				this.getAttribute('data-api'),
				this.getAttribute('data-id'),
				document.getElementById('new-name').value,
				document.getElementById('new-resourceid').value,
				this.getAttribute('data-quota'),
				this.getAttribute('data-base'),
				document.getElementById('new-apps').checked ? document.getElementById('new-apps').value : 0,
				document.getElementById('new-etc').checked ? document.getElementById('new-etc').value : 0,
				document.getElementById('new-data').checked ? document.getElementById('new-data').value : 0
			);
		});
	});

	document.querySelectorAll('.quota_upa').forEach(function (el) {
		el.addEventListener('click', function(e){
			e.preventDefault();
			DistributeQuota(this.getAttribute('data-dir'), this.getAttribute('data-api'));
		});
	});

	document.querySelectorAll('.permissions-reset').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			ResetPermissions(this);
		});
	});

	/*$('body').on('change', '.form-control,.form-check-input', function () {
		var dialog = $(this).closest('.dialog');
		if (dialog.length) {
			$('#' + $(dialog).getAttribute('data-id') + '_save_button').prop('disabled', false);
		}
	});*/

	document.querySelectorAll('.form-group-storage').forEach(function (el) {
		var sel = new TomSelect(el, {
			maxItems: 1,
			valueField: 'id',
			labelField: 'name',
			searchField: ['name'],
			plugins: ['clear_button'],
			persist: false,
			// Fetch remote data
			load: function (query, callback) {
				var url = el.getAttribute('data-api') + '&api_token=' + document.querySelector('meta[name="api-token"]').getAttribute('content') + '&order=name&order_by=asc&search=' + encodeURIComponent(query);

				fetch(url)
					.then(response => response.json())
					.then(json => {
						callback(json.data);
					}).catch(() => {
						callback();
					});
			}
		});
		/*sel.on('item_add', function (item, data) {
			var seller = document.getElementById(el.getAttribute('data-update'));
			//var dest_queue = document.getElementById("field-id").value;
			if (item == 0) {
				seller.value = 0;
				seller.parentNode.classList.add('d-none');
				return;
			} else {
				seller.parentNode.classList.remove('d-none');
			}
		});*/
	});

	document.querySelectorAll('.dialog-submit').forEach(function(btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();

			var frm = btn.closest('form'),
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

			var post = {},
				fields = new FormData(frm);

			for (var i of fields.keys()) {
				post[i] = fields.get(i);
			}

			fetch(frm.getAttribute('data-api'), {
				method: btn.getAttribute('data-id') ? 'PUT' : 'POST',
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
				var er = document.getElementById('error_' + btn.getAttribute('data-type') + btn.getAttribute('data-id'));
				if (er) {
					er.classList.remove('hide');
					er.innerHTML = err;
				}
			});
		});
	});

	document.querySelectorAll('.storage-delete').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();

			if (confirm(btn.getAttribute('data-confirm'))) {
				fetch(btn.getAttribute('data-api'), {
					method: 'DELETE',
					headers: headers
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
