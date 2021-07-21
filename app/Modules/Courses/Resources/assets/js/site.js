/* global $ */ // jquery.js
/* global ROOT_URL */ // common.js
/* global WSGetURL */ // common.js
/* global WSPostURL */ // common.js
/* global WSDeleteURL */ // common.js
/* global ERRORS */ // common.js
/* global SetError */ // common.js

/**
 * Create a new class account
 *
 * @param   {string}  crn
 * @return  {void}
 */
function CreateNewClassAccount(btn) {
	var selected_class = $('#new_class_select option:selected');
	if (selected_class.data('crn') == undefined) {
		return;
	}

	// Fetch input
	var post = {
		crn: selected_class.data('crn'),
		classname: selected_class.data('classname'),
		classid: selected_class.data('classid'),
		userid: $('#userid').val(),
		semester: selected_class.data('semester'),
		datetimestart: selected_class.data('start'),
		datetimestop: selected_class.data('stop'),
		reference: selected_class.data('reference'),
		resourceid: $('#new_class_resource').val(),
	};

	var source = $('#new-course-message').html(),
		template = Handlebars.compile(source),
		context = {
			'class_name': $('#new_class_select').val() + " - " + $('#new_class_name').text(),
			'class_name_href': $('#new_class_name').data('href'),
			'estNum': $('#estNum').val(),
			'courseResources': $('#courseResources').val(),
			'dueDates': $('#dueDates').val(),
			'additional': $('#additional').val()
		};
		post['report'] = template(context);

	var users = [];
	$("li[id^=USER_]").each(function () {
		//var foo = this.id.split("_")[2];
		if (this.id.split("_")[2] == undefined) {
			users.push(this.id.split("_")[1]);
		}
	});

	post['users'] = users;

	WSPostURL(btn.data('api'), JSON.stringify(post), function (xml) {
		if (xml.status == 200) {
			document.location.reload(true);
		} else if (xml.status == 403) {
			alert("Your session may have expired. Click OK to reload page.");
			window.location.reload(true);
		} else if (xml.status == 415) {
			alert("Class already has accounts.");
		} else {
			alert("An error occurred. Reload the page and try again. If problem persists contact help.");
		}
	});
}

/**
 * Pending accounts to be added
 *
 * @var  {number}
 */
var pending = 0;

/**
 * Error count
 *
 * @var  {number}
 */
var errors = 0;

/**
 * List of users with errors
 *
 * @var  {array}
 */
var problem_users = Array();

/**
 * List of duplicate users
 *
 * @var  {array}
 */
var duplicate_users = Array();

/**
 * Create a new class account
 *
 * @param   {string}  crn
 * @param   {string}  classaccount
 * @return  {void}
 */
function BulkAddAccounts(crn, classaccount) {
	var accounts = $('#bulkadd_' + crn).val();
	accounts = accounts.split(/[\n,]/);

	errors = 0;
	problem_users = Array();
	duplicate_users = Array();
	pending = 0;
	var post_obj;

	for (var x = 0; x < accounts.length; x++) {
		var user = accounts[x].trim();
		var reg = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
		var email = reg.test(user);

		post_obj = {};
		post_obj['userid'] = user;
		post_obj['classaccountid'] = classaccount;


		if (email == true) {
			pending++;
			WSGetURL(ROOT_URL + 'users/?search=' + user, AddingManyUsersEmail, post_obj);
		} else {
			if (user != "") {
				pending++;
				WSGetURL(ROOT_URL + 'users/?search=' + user, AddingManyUsers, post_obj);
			}
		}
	}
}

/**
 * Adding multiple users by email
 *
 * @param   {object}  xml
 * @param   {object}  post_obj
 * @return  {void}
 */
function AddingManyUsersEmail(xml, post_obj) {
	pending--;

	if (xml.status < 400) {
		var response = JSON.parse(xml.responseText);
		var post;

		if (response['data'].length == 0) {
			errors++;
			problem_users.push(post_obj['userid']);
			PrintErrors();
			return;
		}

		/*if (typeof (response['data'][0]['id']) == 'undefined' || !response['data'][0]['id']) {
			var username = response['data'][0]['username']; //[0]['name'];
			post = { 'username': username, 'name': response['data'][0]['name'] };
			post_obj['user'] = username;

			post = JSON.stringify(post);
			pending++;
			WSPostURL(ROOT_URL + "users", post, newUser, post_obj);
		}*/
		if (typeof (response['data'][0]['id']) == 'undefined' || !response['data'][0]['id']) {
			var user = response['data'][0]['username'];
		} else {
			var user = response['data'][0]['id'];
		}

		//var user = response['data'][0]['id'];
		post = {
			'userid': user,
			'classaccountid': post_obj['classaccountid'],
		};
console.log(post);
		post = JSON.stringify(post);
		pending++;

		WSPostURL(ROOT_URL + "courses/members", post, AddedManyUsers, post_obj);
	} else if (xml.status == 404) {
		errors++;
		problem_users.push(post_obj['userid']);
		PrintErrors();
	}
}

/**
 * Adding multiple users
 *
 * @param   {object}  xml
 * @param   {object}  post_obj
 * @return  {void}
 */
function AddingManyUsers(xml, post_obj) {
	pending--;

	if (xml.status < 400) {
		var response = JSON.parse(xml.responseText);
		var post;

		if (response['data'].length == 0) {
			errors++;
			problem_users.push(post_obj['userid']);
			PrintErrors();
			return;
		}

		/*if (typeof (response['data'][0]['id']) == 'undefined' || !response['data'][0]['id']) {
			var username = response['data'][0]['username']; //[0]['name'];
			post = { 'username': username, 'name': response['data'][0]['name'] };
			post = JSON.stringify(post);
			pending++;
			WSPostURL(ROOT_URL + "users", post, newUser, post_obj);
			return;
		}*/
		console.log(response['data']); return;
		if (typeof (response['data'][0]['id']) == 'undefined' || !response['data'][0]['id']) {
			var user = response['data'][0]['username'];
		} else {
			var user = response['data'][0]['id'];
		}

		//var user = response['data'][0]['id'];
		post = {
			'userid': user,
			'classaccountid': post_obj['classaccountid'],
		};
	console.log(post);
		post = JSON.stringify(post);
		pending++;

		WSPostURL(ROOT_URL + "courses/members", post, AddedManyUsers, post_obj);
	} else if (xml.status == 404) {
		errors++;
		problem_users.push(post_obj['userid']);
		PrintErrors();
	}
}

/**
 * Output error messages
 *
 * @return  {void}
 */
function PrintErrors() {
	if (pending == 0 && errors > 0) {
		var x;
		var problems;

		if (problem_users.length > 0) {
			problems = "";
			for (x = 0; x < problem_users.length; x++) {
				problems = problems + problem_users[x] + "\n";
			}
			alert("Could not add:\n" + problems);
		}

		if (duplicate_users.length > 0) {
			problems = "";
			for (x = 0; x < duplicate_users.length; x++) {
				problems = problems + duplicate_users[x] + "\n";
			}
			alert("Users are already added:\n" + problems);
		}
	}
	if (pending == 0) {
		window.location.reload(true);
	}
}

/**
 * Callback after adding a user
 *
 * @param   {object}  xml
 * @param   {object}  post_obj
 * @return  {void}
 */
function newUser(xml, post_obj) {
	pending--;
	if (xml.status == 200) {
		//var response = JSON.parse(xml.responseText);
		//var post = JSON.stringify(post_obj);
		pending++;
		WSGetURL(ROOT_URL + 'users/?search=' + post_obj['user'], AddingManyUsers, post_obj);
	} else {
		errors++;
		problem_users.push(post_obj['user']);
		PrintErrors();
	}
}

/**
 * Callback after adding multiple users
 *
 * @param   {object}  xml
 * @param   {object}  post_obj
 * @return  {void}
 */
function AddedManyUsers(xml, post_obj) {
	pending--;
	if (xml.status < 400) {
		if (pending == 0 && errors == 0) {
			window.location.reload(true);
			return;
		}
	} else if (xml.status == 414) {
		// Duplicate
		errors++;
		duplicate_users.push(post_obj['user']);
	} else {
		errors++;
		problem_users.push(post_obj['user']);
	}
	PrintErrors();
}

/**
 * Create a new workshop entry
 *
 * @return  {void}
 */
/*function CreateNewWorkshop() {
	$('.create-form .alert').remove();
	$('.create-form input').removeClass('is-invalid');

	// Fetch input
	var post = { 'crn': $('#new_workshop_crn').val() };
	post['classid'] = $('#new_workshop_classid').val();
	post['user'] = $('#HIDDEN_property').val();
	post['semester'] = $('#new_workshop_semester').val();
	post['reference'] = $('#new_workshop_reference').val();
	post['classname'] = $('#new_workshop_name').val();
	post['department'] = "";
	post['coursenumber'] = "";
	post['datetimestart'] = $('#new_workshop_start').val();
	post['datetimestop'] = $('#new_workshop_end').val();
	post['resourceid'] = $('#new_workshop_resourceid').val();

	post['report'] = $('#new-workshop-message').html()
		.replace('{{ name }}', $('#new_workshop_name').val())
		.replace('{{ start }}', $('#new_workshop_start').val())
		.replace('{{ end }}', $('#new_workshop_end').val());

	if (!post['classname'] || !post['start'] || !post['stop']) {
		if (!post['classname']) {
			$('#new_workshop_name')
				.addClass('is-invalid');
				//.after('<p class="alert alert-error">Please provide a name.</p>');
		}
		if (!post['start']) {
			$('#new_workshop_start')
				.addClass('is-invalid');
				//.after('<p class="alert alert-error">Please provide a start time.</p>');
		}
		if (!post['stop']) {
			$('#new_workshop_end')
				.addClass('is-invalid');
				//.after('<p class="alert alert-error">Please provide a stop time.</p>');
		}
		return;
	}

	WSPostURL(ROOT_URL + "classaccount", JSON.stringify(post), function (xml) {
		if (xml.status == 200) {
			document.location.reload(true);
		} else if (xml.status == 403) {
			alert("Your session may have expired. Click OK to reload page.");
			window.location.reload(true);
		} else if (xml.status == 415) {
			alert("Class already has accounts.");
		} else {
			alert("An error occurred. Reload the page and try again. If problem persists contact help.");
		}
	});
}*/

/**
 * Event handler for new_class_select onChange event
 *
 * @return  {void}
 */
function NewClassSelect() {
	// Get selected class
	var selected_class = $('#new_class_select option:selected');

	// Clear old instructors
	document.getElementById("class_people").innerHTML = "";
	$('#new_class_name').text('(Select Class)');
	$('#new_class_count').text('-');

	if (selected_class.val() != 'first') {
		if (selected_class.data('students') && !selected_class.data('students')['students'].length) {
			var parent = $($('#new_class_count').parent());
			parent.addClass('processing');

			WSGetURL(selected_class.data('api'), function (xml) {
				parent.removeClass('processing');

				if (xml.status < 400) {
					var response = JSON.parse(xml.responseText);

					selected_class.data('count', response.enrollments.length);

					var emails = [];
					for (var i = 0; i < response.enrollments.length; i++) {
						emails.push(response.enrollments[i].email);
					}
					function onlyUnique(value, index, self) {
						return self.indexOf(value) === index;
					}
					emails = emails.filter(onlyUnique);

					selected_class.data('students')['students'] = emails;
					selected_class.data('count', emails.length);

					$('#new_class_count').text(selected_class.data('count'));
				}
			});
		} else {
			$('#new_class_count').text(selected_class.data('count'));
		}

		// Populate instructors
		var instructors = selected_class.data('instructors');

		// Set class name
		$('#new_class_name').text(selected_class.data('classname'));
		//$('#new_class_count').text(selected_class.data('count'));

		for (var x = 0; x < instructors.length; x++) {
			WSGetURL(ROOT_URL + "users/?search=" + instructors[x]['email'], AddUserClass);
		}
	}
}

/**
 * Event handler for user class search
 *
 * @param   {object}  event
 * @param   {object}  ui
 * @param   {string}  crn
 * @return  {void}
 */
function ClassUserSearchEventHandler(event, ui, crn) {
	if (typeof (crn) == 'undefined') {
		crn = '0';
	}
	var s_crn = "";
	if (crn != '0') {
		s_crn = "_" + crn;
	}
	document.getElementById("searchuser" + s_crn).value = "";

	var id = ui['item']['id'];
	var name = ui['item']['name'];
	var username = ui['item']['username'];

	if (typeof (id) == 'undefined') {
		var post = JSON.stringify({
			"name": name,
			"username": username
		});
		console.log(post);
		//WSPostURL(ROOT_URL + "users", post, AddUserClass, crn);
	} else {
		WSGetURL(ROOT_URL + "users/" + id, AddUserClass, crn);
	}
}

/**
 * Add a user to a class
 *
 * @param   {object}  xml
 * @param   {string}  crn
 * @return  {void}
 */
function AddUserClass(xml, crn) {
	if (xml.status < 400) {
		if (typeof (crn) == 'undefined') {
			crn = '0';
		}
		var s_crn = "";
		if (crn != '0') {
			s_crn = "_" + crn;
		}
		var response = JSON.parse(xml.responseText),
			results = response; //.data;
		var list = document.getElementById("class_people" + s_crn);

		// if this was a name search
		/*if (typeof (results['users']) != "undefined") {
			if (results['users'].length > 1) {
				// Something's weird, bail out
				return;
			}

			results = results['users'][0];
		}*/

		// skip this person if they already exist
		if (document.getElementById("USER_" + results['id'] + s_crn) != null) {
			document.getElementById("searchuser" + s_crn).value = "";
			return;
		}

		var span = document.createElement("li");
			span.id = "USER_" + results['id'] + s_crn;

		// make red X button image
		var img = document.createElement("i");
			img.setAttribute('aria-hidden', true);
			img.className = "fa fa-trash text-danger crmdeleteuser";

		// create link for button
		var a = document.createElement("a");
			a.href = "#USER_" + results['id'] + s_crn;
			a.setAttribute('data-api', results['api']);
			a.onclick = function (e) {
				e.preventDefault();
				RemoveUser(this);//results['id'], crn);
			};

		// create hidden thing
		var hidden = document.createElement("input");
			hidden.type = "hidden";
			hidden.id = "HIDDEN_" + results['id'] + s_crn;
			hidden.value = "";

		// assemble the objects
		a.appendChild(img);
		span.appendChild(a);
		/*if (results['fullname'] != undefined) {
			span.appendChild(document.createTextNode(results['fullname']));
		} else {*/
		span.appendChild(document.createTextNode(' ' + results['name'] + ' (' + results['username'] + ')'));
		//}
		list.appendChild(hidden);

		// put the person at the top of the list
		list.appendChild(span);

		// If an existing class, then make a call
		if (crn != '0') {
			var post = {
				'classaccountid': $("#HIDDEN_" + crn).val(),
				'userid': results['id']
			};
			WSPostURL(
				ROOT_URL + "courses/members",
				JSON.stringify(post),
				function (xml, data) {
					if (xml.status < 400) {
						var results = JSON.parse(xml.responseText);
						document.getElementById("HIDDEN_" + data['user'] + "_" + data['crn']).value = results['id'];
						$('#searchuser_alert_' + data['crn'])
							.removeClass('hide')
							.removeClass('alert-danger')
							.addClass('alert-success')
							.text('Successfully added person.');
					} else {
						$('#searchuser_alert_' + data['crn'])
							.removeClass('hide')
							.removeClass('alert-success')
							.addClass('alert-danger')
							.text('An error occurred. Reload the page and try again. If problem persists contact help.');
					}
				},
				{ 'user': results['id'], 'crn': crn }
			);
		}

		// reset search box
		document.getElementById("searchuser" + s_crn).value = "";
	} else {
		// error handling
		switch (xml.status) {
			case 401:
			case 403:
				SetError(ERRORS['403_generic'], null);
				break;
			case 500:
				SetError(ERRORS['500'], null);
				break;
			default:
				SetError(ERRORS['generic'], ERRORS['unknown']);
				break;
		}
	}
}

/**
 * Remove a user from a class
 *
 * @param   {string}  user
 * @param   {string}  crn
 * @return  {void}
 */
function RemoveUser(btn) {//user, crn) {
	/*var s_crn = "";
	if (crn != '0') {
		s_crn = "_" + crn;
	}
	var list = document.getElementById("class_people" + s_crn);
	// find and remove person div
	var user_div = document.getElementById("USER_" + user + s_crn);
	list.removeChild(user_div);

	var user_div_id = document.getElementById("HIDDEN_" + user + s_crn).value;*/

	WSDeleteURL(btn.getAttribute('data-api'), function (xml) {
		if (xml.status < 400) {
			alert("An error occurred. Reload the page and try again. If problem persists contact help.");
		} else {
			$(btn.getAttribute('href')).remove();
		}
	});
}

/**
 * Delete a class account
 *
 * @param   {string}  id
 * @return  {void}
 */
/*function DeleteClassAccount(id) {
	if (confirm("Are you sure you wish to delete this class account?")) {
		WSDeleteURL(id, function (xml) {
			if (xml.status == 200) {
				document.location.reload(true);
			} else if (xml.status == 403) {
				alert("Your session may have expired. Click OK to reload page.");
				window.location.reload(true);
			} else {
				alert("An error occurred. Reload the page and try again. If problem persists contact help.");
			}
		});
	}
}*/

/**
 * Show students for a class
 *
 * @param   {string}  crn
 * @return  {void}
 */
/*function ShowStudents(crn) {
	var selected_class = '';
	if (crn == 'new') {
		selected_class = $('#new_class_select option:selected');
	} else {
		selected_class = $('#new_class_select option[id="option_class_' + crn + '"]');
	}

	var students = '';
	if (selected_class.val() != 'first') {
		students = selected_class.data('students')['students'].join('<br/>');
	} else {
		students = "(Select class to see student list)";
	}

	$('<p>These students will automatically have an account created. Do not explicitly add each student.<br /><br />' + decodeURIComponent(students.replace(/\+/g, ' ')) + '</p>').dialog();
}*/

/**
 * Redirect to the selected semester
 *
 * @return  {void}
 */
/*function SemesterSelect() {
	var selected_semester = $('#semester_select option:selected').val();

	window.location = "/account/class/?semester=" + selected_semester;
}*/

var autocompleteName = function (url) {
	return function (request, response) {
		return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
			response($.map(data.data, function (el) {
				return {
					label: el.name,
					name: el.name,
					id: el.id,
					usernames: [{'name':el.username}]//,
					//priorusernames: el.priorusernames
				};
			}));
		});
	};
};

$(document).ready(function () {
	// Help dialogs
	$('.dialog').dialog({
		autoOpen: false,
		modal: true,
		width: '450px'
	});

	var user = $('.search-user');
	if (user.length) {
		user.autocomplete({
			source: autocompleteName(user.attr('data-api')),
			dataName: 'users',
			height: 150,
			delay: 100,
			minLength: 2,
			filter: /^[a-z0-9\-_ .,@+]+$/i,
			open: function () {
				$('#searchuser_alert_' + $(this).data('id')).addClass('hide');
				/*var dlg = $('#class_dialog_' + $(this).data('id'));
				if (dlg.length) {
					$(this).autocomplete("widget").zIndex(dlg.zIndex() + 1);
				}*/
			},
			select: function (event, ui) {
				ClassUserSearchEventHandler(event, ui, $(this).data('id'));
			},
			create: function () {
				$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
					var thing = item.label;

					if (typeof (item.usernames) != 'undefined') {
						thing = thing + " (" + item.usernames[0]['name'] + ")";
					} else if (typeof (item.priorusernames) != 'undefined') {
						thing = thing + " (" + item.priorusernames[0]['name'] + ")";
					}

					return $("<li>")
						.append($("<div>").text(thing))
						.appendTo(ul);
				};
			}
		});
	}

	$("#searchuser").autocomplete({
		source: autocompleteName($("#searchuser").data('api')),
		dataName: 'users',
		height: 150,
		delay: 100,
		minLength: 2,
		filter: /^[a-z0-9\-_ .,@+]+$/i,
		open: function () {
			//$(this).autocomplete("widget").zIndex($('#new_class_dialog').zIndex() + 1);
		},
		select: function (event, ui) {
			ClassUserSearchEventHandler(event, ui);
		}
	});

	$('.type-dependant').hide();

	$('[name="type"]')
		.on('change', function () {
			$('.type-dependant').hide();
			$('.type-' + $(this).val()).show();
		})
		.each(function (i, el) {
			$('.type-' + $(el).val()).show();
		});

	/*$('#semester_select').on('change', function (e) {
		SemesterSelect();
	});

	$('.create-form').on('submit', function (e) {
		e.preventDefault();
		$(this).find('input[type=button]').trigger('click');
	});*/

	$('.btn-create-workshop').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);

		//CreateNewWorkshop();
		//$('.create-form .alert').remove();
		$('.create-form input').removeClass('is-invalid');

		// Fetch input
		var post = {
			'crn': $('#new_workshop_crn').val(),
			'classid': $('#new_workshop_classid').val(),
			'userid': $('#userid').val(),
			'semester': $('#new_workshop_semester').val(),
			'reference': $('#new_workshop_reference').val(),
			'classname': $('#new_workshop_name').val(),
			'department': '',
			'coursenumber': '',
			'datetimestart': $('#new_workshop_start').val(),
			'datetimestop': $('#new_workshop_end').val(),
			'resourceid': $('#new_workshop_resource').val(),
			'report': ''
		};

		post['report'] = $('#new-workshop-message').html()
			.replace('{{ name }}', post['classname'])
			.replace('{{ start }}', post['datetimestart'])
			.replace('{{ end }}', post['datetimestop']);

		if (!post['classname'] || !post['start'] || !post['stop']) {
			if (!post['classname']) {
				$('#new_workshop_name')
					.addClass('is-invalid');
			}
			if (!post['start']) {
				$('#new_workshop_start')
					.addClass('is-invalid');
			}
			if (!post['stop']) {
				$('#new_workshop_end')
					.addClass('is-invalid');
			}
			return;
		}

		WSPostURL(btn.data('api'), JSON.stringify(post), function (xml) {
			if (xml.status < 400) {
				document.location.reload(true);
			} else if (xml.status == 403) {
				alert("Your session may have expired. Click OK to reload page.");
				window.location.reload(true);
			} else if (xml.status == 415) {
				alert("Class already has accounts.");
			} else {
				alert("An error occurred. Reload the page and try again. If problem persists contact help.");
			}
		});
	});

	$('#new_class_select').on('change', function (e) {
		NewClassSelect();
	});

	/*
	Experimental lazy load student list
	$('#new_class_select option').each(function(i, el){
		var selected_class = $(el);

		if (!selected_class.data('students')) {
			return;
		}

		if (!selected_class.data('students')['students'].length) {
			WSGetURL(selected_class.data('api'), function (xml) {
				if (xml.status < 400) {
					var response = JSON.parse(xml.responseText);

					selected_class.data('count', response.enrollments.length);

					var emails = [];
					for (var i = 0; i < response.enrollments.length; i++) {
						emails.push(response.enrollments[i].email);
					}
					selected_class.data('students')['students'] = emails;
				}
			});
		}
	});
	*/

	// Account
	$('.account-delete').on('click', function (e) {
		e.preventDefault();
		//DeleteClassAccount($(this).data('id'));
		if (confirm($(this).data('confirm'))) {
			WSDeleteURL($(this).data('api'), function (xml) {
				if (xml.status == 200) {
					document.location.reload(true);
				} else if (xml.status == 403) {
					alert("Your session may have expired. Click OK to reload page.");
					window.location.reload(true);
				} else {
					alert("An error occurred. Reload the page and try again. If problem persists contact help.");
				}
			});
		}
	});
	$('.account-add').on('click', function (e) {
		e.preventDefault();
		BulkAddAccounts($(this).data('crn'), $(this).data('id'));
	});
	$('.account-create').on('click', function (e) {
		e.preventDefault();
		CreateNewClassAccount($(this));
	});
	$('.add-account').on('click', function (e) {
		e.preventDefault();
		$($(this).attr('href')).toggleClass('hide');
		$($(this).data('hide')).toggleClass('hide');
		var txt = $(this).html();
		$(this).html($(this).data('text'));
		$(this).data('text', txt);
		//$(this).prop('disabled', true);
	});

	// Account users
	$('.user-delete').on('click', function (e) {
		e.preventDefault();
		RemoveUser(this); //.data('user'), $(this).data('crn'));
	});

	$('.show-students').on('click', function (e) {
		e.preventDefault();
		//ShowStudents($(this).data('crn'));
		var crn = $(this).data('crn');

		var target = $(this).attr('href');

		if ($(target).length) {
			$(target).toggleClass('hide');
			return;
		}

		var selected_class = '';
		if (crn == 'new') {
			selected_class = $('#new_class_select option:selected');
		} else {
			selected_class = $('#new_class_select option[id="option_class_' + crn + '"]');
		}

		var students = '';
		if (selected_class.val() != 'first') {
			students = selected_class.data('students')['students'].join('<br/>');
		} else {
			students = "(Select class to see student list)";
		}

		$('<p>These students will automatically have an account created. Do not explicitly add each student.<br /><br />' + decodeURIComponent(students.replace(/\+/g, ' ')) + '</p>').dialog();
	});
});
