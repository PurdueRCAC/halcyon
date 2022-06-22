/* global $ */ // jquery.js
/* global Halcyon */ // core.js
/* global WSGetURL */ // common.js
/* global TomSelect */ // vendor/tom-select/js/tom-select.complete.min.js

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
		user_row.classList.remove('d-none');//.style.display = "table-row";
		unixgroup_select.disabled = false;
		unixgroup_select_decoy.classList.add('d-none');//.style.display = "none";
		unixgroup_select.classList.remove('d-none');//.style.display = "inline";
		autouserunixgroup_row.classList.add('d-none');//.style.display = "none";

		input.value = " (Select User) ";

		for (x = 0; x < unixgroup_select.options.length; x++) {
			if (unixgroup_select.options[x].innerHTML == parent_unixgroup.value) {
				unixgroup_select.options[x].selected = true;
				$.get(unixgroup_select.options[x].getAttribute('data-api'), NewDirUserPopulate);
			}
		}
	}

	if (selected == "userwrite") {
		user_row.classList.remove('d-none');//.style.display = "table-row";
		unixgroup_select.disabled = false;
		unixgroup_select_decoy.classList.add('d-none');//.style.display = "none";
		unixgroup_select.classList.remove('d-none');//.style.display = "inline";
		autouserunixgroup_row.classList.add('d-none');//.style.display = "none";

		input.value = " (Select User) ";

		for (x = 0; x < unixgroup_select.options.length; x++) {
			if (unixgroup_select.options[x].innerHTML == parent_unixgroup.value) {
				unixgroup_select.options[x].selected = true;
				WSGetURL(unixgroup_select.options[x].value, NewDirUserPopulate);
			}
		}
	} else if (selected == "userprivate") {
		user_row.classList.remove('d-none'); //.style.display = "table-row";
		user_select.options[0].selected = true;
		input.value = " (Select User) ";

		var opt = document.createElement("option");
		opt.innerHTML = parent_unixgroup.value;

		for (x = 0; x < unixgroup_select.options.length; x++) {
			if (unixgroup_select.options[x].innerHTML == parent_unixgroup.value) {
				opt.value = unixgroup_select.options[x].value;
				WSGetURL(unixgroup_select.options[x].value, NewDirUserPopulate);
			}
		}

		unixgroup_select_decoy.classList.remove('d-none');//.style.display = "inline";
		unixgroup_select.classList.add('d-none'); //.style.display = "none";
		autouserunixgroup_row.classList.add('d-none');//.style.display = "none";

		$(unixgroup_select_decoy).empty();
		unixgroup_select_decoy.appendChild(opt);
		unixgroup_select_decoy.disabled = true;
	} else if (selected == "normal") {
		input.value = "";

		user_row.classList.add('d-none');//.style.display = "none";
		user_select.options[0].selected = true;
		unixgroup_select.disabled = false;
		unixgroup_select_decoy.classList.add('d-none');//.style.display = "none";
		unixgroup_select.classList.remove('d-none');//.style.display = "inline";
		autouserunixgroup_row.classList.add('d-none');//.style.display = "none";
	} else if (selected == "autouserprivate" || selected == "autouserread" || selected == "autouserreadwrite") {
		input.value = "";

		user_row.classList.add('d-none');//.style.display = "none";
		user_select.options[0].selected = true;
		unixgroup_select.disabled = false;
		unixgroup_select_decoy.classList.add('d-none');//.style.display = "none";
		unixgroup_select.classList.remove('d-none');//.style.display = "inline";
		autouserunixgroup_row.classList.remove('d-none');//.style.display = "table-row";
	}
}

/**
 * Callback to populate new dir user
 *
 * @param   {object}  xml
 * @return  {void}
 */
function NewDirUserPopulate(results) {
	var user_select = document.getElementById("new_dir_user_select");
	var opt = document.createElement("option");
	opt.innerHTML = "(Select User)";
	opt.value = "";

	$(user_select).empty();
	user_select.appendChild(opt);

	//if (xml.status == 200) {
	//var results = JSON.parse(xml.responseText);

	for (var x = 0; x < results['members'].length; x++) {
		opt = document.createElement("option");
		opt.value = results['members'][x]['userid'];
		opt.innerHTML = results['members'][x]['username'];

		user_select.appendChild(opt);
	}
	//}
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
 * Create a new directory
 *
 * @return  {void}
 */
function NewDir(btn) {
	var img = document.getElementById("new_dir_img");
	var input = document.getElementById("new_dir_input");
	var group = document.getElementById("groupid").value;
	var resource = document.getElementById("resourceid").value;
	var parentdir = document.getElementById("selected_dir").value;
	//var storageresource = document.getElementById("storageresource").value;

	var type = document.getElementById("new_dir_type");
	type = type.options[type.selectedIndex].value;

	//img.src = "/include/images/loading.gif";
	//img.style.width = "20px";
	//img.style.height = "20px";
	img.className = 'spinner-border';

	var share = document.getElementById("share_radio").checked;
	var deduct = document.getElementById("deduct_radio").checked;
	var unalloc = document.getElementById("unalloc_radio").checked;

	var unalloc_input = document.getElementById("new_dir_quota_unalloc").value;
	var deduct_input = document.getElementById("new_dir_quota_deduct").value;

	var unixgroup = document.getElementById("new_dir_unixgroup_select");
	unixgroup = unixgroup.options[unixgroup.selectedIndex].value;

	var error;

	if (type == "autouserread" || type == "autouserprivate" || type == "autouserreadwrite") {
		var autouserunixgroup = document.getElementById("new_dir_autouserunixgroup_select");
		autouserunixgroup = autouserunixgroup.options[autouserunixgroup.selectedIndex].value;

		if (autouserunixgroup == "") {
			error = document.getElementById("new_dir_error");
			error.innerHTML = "Please select a unix group.";
			//img.src = "/include/images/error.png";
			img.className = 'icon-warning';
			return;
		}
	}

	if (unixgroup == "" && type != "userprivate") {
		error = document.getElementById("new_dir_error");
		error.innerHTML = "Please select a unix group.";
		//img.src = "/include/images/error.png";
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
			error = document.getElementById("new_dir_error");
			error.innerHTML = "Please select a user.";
			//img.src = "/include/images/error.png";
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
		post['user'] = user;
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
		post['groupread'] = "1";
		post['groupwrite'] = "0";
		post['autouserunixgroupid'] = autouserunixgroup;
	}
	if (type == "autouserreadwrite") {
		post['autouser'] = "3";
		post['groupread'] = "1";
		post['groupwrite'] = "0";
		post['autouserunixgroupid'] = autouserunixgroup;
	}

	$.ajax({
		url: btn.getAttribute('data-api'),
		type: 'post',
		data: post,
		dataType: 'json',
		async: false,
		success: function () {
			Halcyon.message('success', 'Directory created!');
			window.location.reload(true);
		},
		error: function (xhr) { //xhr, reason, thrownError
			if (xhr.responseJSON) {
				Halcyon.message('danger', xhr.responseJSON.message);
			} else {
				Halcyon.message('danger', 'Failed to create directory.');
			}
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

		$.ajax({
			url: btn.getAttribute('data-api'),
			type: 'delete',
			dataType: 'json',
			async: false,
			success: function () {
				Halcyon.message('success', 'Directory removed!');
				window.location.reload(true);
			},
			error: function (xhr) {
				if (xhr.responseJSON) {
					Halcyon.message('danger', xhr.responseJSON.message);
				} else {
					Halcyon.message('danger', 'Failed to delete directory.');
				}
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

		$.ajax({
			url: btn.getAttribute('data-api'),
			type: 'put',
			data: post,
			dataType: 'json',
			async: false,
			success: function () {
				window.location.reload(true);
			},
			error: function (xhr) {
				if (xhr.responseJSON) {
					Halcyon.message('danger', xhr.responseJSON.message);
				} else {
					Halcyon.message('danger', 'Failed to reset permissions.');
				}
			}
		});
	}
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	var selects = document.querySelectorAll('.searchable-select');
	if (selects.length) {
		selects.forEach(function (el) {
			if (el.classList.contains('filter-submit')) {
				el.style.width = '15em';
			}
			var sel = new TomSelect(el, { plugins: ['dropdown_input'] });
			sel.on('item_select', function () {
				if (sel.classList.contains('filter-submit')) {
					sel.closest('form').submit();
				}
			});
		});
	}

	/*if ($('.searchable-select').length) {
		$('.searchable-select').select2()
			.on('select2:select', function () {
				if ($(this).hasClass('filter-submit')) {
					$(this).closest('form').submit();
				}
			});
	}
	var autocompleteUsers = function(url) {
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

	var groups = document.querySelectorAll(".form-groups");
	if (groups.length) {
		groups.forEach(function (group, i) {
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
								api: el.api
							};
						}));
					});
				},
				select: function (event, ui) {
					event.preventDefault();
					// Set selection
					group.val(ui.item.label); // display the selected text
					cl.val(ui.item.id); // save selected id to input

					if ($('#field-unixgroupid').length) {
						$.ajax({
							url: ui.item.api,
							type: 'get',
							dataType: 'json',
							async: false,
							success: function (data) {
								var i = 0;
								$('#field-unixgroupid')
									.empty()
									.append($('<option value="">(Select Unix Group)</option>'));
								$('#field-autouserunixgroupid')
									.empty()
									.append($('<option value="">(Select Unix Group)</option>'));
								for (i = 0; i < data.unixgroups.length; i++) {
									$('#field-unixgroupid')
										.append($('<option value="' + data.unixgroups[i].id + '">' + data.unixgroups[i].longname + '</option>'));

									$('#field-autouserunixgroupid')
										.append($('<option value="' + data.unixgroups[i].id + '">' + data.unixgroups[i].longname + '</option>'));
								}
							},
							error: function (xhr) {
								if (xhr.responseJSON) {
									Halcyon.message('danger', xhr.responseJSON.message);
								} else {
									Halcyon.message('danger', 'Failed to delete directory.');
								}
								console.log(xhr.responseText);
							}
						});
					}

					return false;
				}
			});
		});
	}

	var name = document.getElementById('field-name');
	if (name) {
		name.addEventListener('keyup', function () {
			this.value = this.value.toLowerCase()
				.replace(/\s+/g, '-')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
	}

	var autouser = document.getElementById('field-autouser');
	if (autouser) {
		autouser.addEventListener('change', function () {
			var opt = this.selectedOptions[0];

			var read = opt.getAttribute('data-read');
			var write = opt.getAttribute('data-write');

			if (this.value != '0') {
				document.querySelector(this.getAttribute('data-update')).classList.remove('hidden');
			} else {
				document.querySelector(this.getAttribute('data-update')).classList.add('hidden');
			}

			document.getElementById('field-ownerread').checked = true;
			document.getElementById('field-ownerwrite').checked = true;
			document.getElementById('field-publicread').checked = false;
			document.getElementById('field-publicwrite').checked = false;

			if (read == '1') {
				document.getElementById('field-groupread').checked = true;
			} else {
				document.getElementById('field-groupread').checked = false;
			}

			if (write == '1') {
				document.getElementById('field-groupwrite').checked = true;
			} else {
				document.getElementById('field-groupwrite').checked = false;
			}
		});
	}

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
						$("#tree").fancytree("getActiveNode").setActive(false);
					}
				});
				$("#" + did + "_dialog").dialog('open');
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
					doc: "far fa-plus-circle"//,
					//folder: "far fa-folder",
					//folderOpen: "far fa-folder-open"
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

	var newdirinput = document.getElementById('new_dir_input');
	if (newdirinput) {
		newdirinput.addEventListener('change', function () {
			GuessDirUnixGroup();
		});
	}
	var newdirtype = document.getElementById('new_dir_type');
	if (newdirtype) {
		newdirtype.addEventListener('change', function () {
			NewDirType();
		});
	}
	/*$('#new_dir_user_select').on('change', function (){
		NewDirUserSelected();
	});
	$('#new_dir_unixgroup_select').on('change', function (){
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

	var newdir = document.getElementById('new_dir');
	if (newdir) {
		newdir.addEventListener('click', function (e) {
			e.preventDefault();
			NewDir(this);
		});
	}

	var deduct = document.getElementById('deduct_radio');
	if (deduct) {
		deduct.addEventListener('click', function () {
			document.getElementById('new_dir_quota_deduct').focus();
		});
	}
	var newdirdeduct = document.getElementById('new_dir_quota_deduct');
	if (newdirdeduct) {
		newdirdeduct.addEventListener('focus', function () {
			document.getElementById('deduct_radio').checked = true;
		});
	}

	var unalloc = document.getElementById('unalloc_radio');
	if (unalloc) {
		unalloc.addEventListener('click', function () {
			document.getElementById('new_dir_quota_unalloc').focus();
		});
	}
	var newdirunalloc = document.getElementById('new_dir_quota_unalloc');
	if (newdirunalloc) {
		newdirunalloc.addEventListener('focus', function () {
			document.getElementById('unalloc_radio').checked = true;
		});
	}

	/*
	$('.unixgroup-edit').on('click', function(e) {
		e.preventDefault();
		EditUnixGroup($(this).data('dir'));
	});
	$('.unixgroup-create').on('click', function(e) {
		e.preventDefault();
		CreateDefaultUnixGroups($(this).data('unixgroup'), $(this).data('id'));
	});
	$('.unixgroup-basename-set').on('click', function(e) {
		e.preventDefault();
		SetBaseName($(this).data('id'));
	});*/
	document.querySelectorAll('.dir-delete').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			DeleteDir(this);
		})
	});
	/*$('.dir-create-default').on('click', function(e) {
		e.preventDefault();
		CreateDefaultDirs(
			$(this).data('id'),
			$(this).data('unixgroup'),
			$(this).data('resource'),
			$(this).data('quota'),
			$(this).data('base'),
			$(this).data('apps'),
			$(this).data('data')
		);
	});

	$('.quota_upa').on('click', function(e){
		e.preventDefault();
		DistributeQuota($(this).data('dir'));
	});*/

	document.querySelectorAll('.permissions-reset').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			ResetPermissions(this);
		})
	});

	$('body').on('change', '.form-control', function (el) {
		var dialog = $(el).closest('.dialog');
		if (dialog.length) {
			$('#' + $(dialog).data('id') + '_save_button').prop('disabled', false);
		}
		/*var id = $(el).data('id');
		console.log($(el).find('.form-control'));
		$(el).find('.form-control').on('change', function(e){
			console.log('this');
			$('#' + id + '_save_button').prop('disabled', false);
		});*/
	});

	var srpath = document.getElementById('storageresourceid_path');
	if (srpath) {
		document.getElementById('storageresourceid').addEventListener('change', function () {
			srpath.innerHTML = this.selectedOptions[0].getAttribute('data-path');
		});
	}
});
