/* global $ */ // jquery.js
/* global ROOT_URL */ // common.js
/* global WSGetURL */ // common.js
/* global WSPostURL */ // common.js
/* global WSDeleteURL */ // common.js
/* global ClearSearch */ // search.js
/* global ERRORS */ // common.js
/* global SetError */ // common.js
/* global search_path */ // crmsearch.js
/* global ChangeSearch */ // search.js
/* global HighlightMatches */ // text.js

// date.js - used for month names

var keywords_pending = 0;
var multi_group = false;
var groupsearch = false;
var usersearch = false;
var activetab = null;

/**
 * Toggle UI tabs
 *
 * @param   {string}  on
 * @param   {bool}    refresh
 * @return  {void}
 */
function CRMToggle(on, refresh) {
	if (typeof (refresh) == 'undefined') {
		refresh = true;
	}

	$(".tab-add").addClass('hide');
	$(".tab-edit").addClass('hide');
	$(".tab-follow").addClass('hide');
	$(".tab-search").addClass('hide');
	$(".tab-" + on).removeClass('hide');

	activetab = on;

	$(".tab").removeClass('activeTab').removeClass('active');

	document.getElementById("INPUT_clear").value = document.getElementById("INPUT_clear").getAttribute('data-txt-' + on);
	document.getElementById("INPUT_add").value = document.getElementById("INPUT_add").getAttribute('data-txt-' + on);
	document.getElementById("TAB_add").innerHTML = document.getElementById("TAB_add").getAttribute('data-txt-' + on);
	document.getElementById("SPAN_header").innerHTML = document.getElementById("SPAN_header").getAttribute('data-txt-' + on);

	if (on == 'search') {
		$("#TAB_" + on).addClass('activeTab').addClass('active');

		document.getElementById("datestartshort").disabled = false;

		multi_group = true;
	} else if (on == 'add') {
		$("#TAB_" + on).addClass('activeTab').addClass('active');

		document.getElementById("datestartshort").disabled = false;

		multi_group = false;
	} else if (on == 'edit') {
		$("#TAB_add").addClass('activeTab').addClass('active');

		multi_group = false;
	} else if (on == 'follow') {
		$("#TAB_" + on).addClass('activeTab').addClass('active');

		document.getElementById("datestartshort").disabled = false;

		// resets users and groups when in follow context
		CRMClearSearch();

		multi_group = true;

		document.getElementById("INPUT_add").disabled = true;
	}

	if (refresh) {
		CRMSearch();
	}

	CRMTabURL(on);
}

/**
 * Set URL and history by active tab
 *
 * @param   {string}  tab
 * @return  {void}
 */
function CRMTabURL(tab) {
	if (typeof (history.pushState) != 'undefined') {
		var url = window.location.href.match(/\?.*/);
		if (url != null) {
			url = url[0];
			if (url.match(/(search|add|edit|follow)/)) {
				url = url.replace(/edit/, tab);
				url = url.replace(/search/, tab);
				url = url.replace(/follow/, tab);
				url = url.replace(/add/, tab);
			} else if (url.match(/&/)) {
				url = url + "&" + tab;
			} else {
				url = url + tab;
			}
			history.pushState(null, null, encodeURI(url));
		} else {
			url = "?" + tab;
			history.pushState(null, null, encodeURI(url));
		}
	}
}

/**
 * Result handler function when selecting a group
 *
 * @param   {object}  xml
 * @param   {array}   flags
 * @return  {void}
 */
function CRMSearchGroup(xml, flags) {
	var pageload = false;
	//var disabled = false;

	if (typeof (flags) != 'undefined') {
		pageload = flags['pageload'];
		//disabled = flags['disabled'];
	}

	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);

		if (!pageload) {
			CRMSearch();
			if (document.getElementById("TAB_follow").className.match(/active/)) {
				document.getElementById("INPUT_add").disabled = false;
			}
		}

		// reset search box
		var group = $('#group');

		if ($('.tagsinput').length) {
			if (!group.tagExist(results['id'])) {
				group.addTag({
					'id': results['id'],
					'label': results['name']
				});
			}
		} else {
			group.val(group.val() + (group.val() ? ', ' : '') + results['name'] + ':' + results['id']);
		}
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
 * Result handler when adding a new person
 *
 * @param   {object}  xml
 * @param   {array}   flags
 * @return  {void}
 */
function CRMSearchUser(xml, flags) {
	var pageload = false;
	//var disabled = false;

	if (typeof (flags) != 'undefined') {
		pageload = flags['pageload'];
		//disabled = flags['disabled'];
	}

	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);

		if (!pageload) {
			CRMSearch();
			if (document.getElementById("TAB_follow").className.match(/active/)) {
				document.getElementById("INPUT_add").disabled = false;
			}
		}

		// reset search box
		var people = $('#people');

		if ($('.tagsinput').length) {
			if (!people.tagExist(results.id)) {
				people.addTag({
					'id': results.id,
					'label': results.name
				}, {
					focus: false,
					callback: false
				});
			}
		} else {
			people.val(people.val() + (people.val() ? ', ' : '') + results.name + ':' + results.id);
		}
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
 * Result handler function when selecting a group
 *
 * @param   {object}  xml
 * @param   {array}   flags
 * @return  {void}
 */
function CRMSearchResource(xml, flags) {
	var pageload = false;
	//var disabled = false;

	if (typeof (flags) != 'undefined') {
		pageload = flags['pageload'];
		//disabled = flags['disabled'];
	}

	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);

		if (!pageload) {
			CRMSearch();
			if (document.getElementById("TAB_follow").className.match(/active/)) {
				document.getElementById("INPUT_add").disabled = false;
			}
		}

		// reset search box
		var resource = $('#crmresource');

		if ($('.tagsinput').length) {
			if (!resource.tagExist(results.id)) {
				resource.addTag({
					'id': results.id,
					'label': results.name
				});
			}
		} else {
			resource.val(resource.val() + (resource.val() ? ', ' : '') + results.name + ':' + results.id);
		}
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
 * Remove a group
 *
 * @param   {string}  group
 * @param   {bool}    refresh
 * @return  {void}
 */
function CRMRemoveGroup(group, refresh) {
	var input = $('#group');

	if ($('.tagsinput').length) {
		input.removeTag(group);
	} else {
		var data = [];
		var items = input.val().split(',');
		var val;
		for (var x = 0; x < items.length; x++) {
			val = items[x];
			if (items[x].includes(':')) {
				val = items[x].split(':')[1];
			}

			if (val != group) {
				data.push(items[x]);
			}
		}
		input.val(data.join(', '));
	}

	if (document.getElementById("TAB_follow").className.match(/active/)) {
		document.getElementById("INPUT_add").disabled = false;
	}

	if (refresh) {
		CRMSearch();
	}
}

/**
 * Remove a user
 *
 * @param   {string}  user
 * @param   {bool}    refresh
 * @return  {void}
 */
function CRMRemoveUser(user, refresh) {
	var input = $('#people');

	if ($('.tagsinput').length) {
		input.removeTag(user);
	} else {
		var data = [];
		var items = input.val().split(',');
		var val;
		for (var x = 0; x < items.length; x++) {
			val = items[x];
			if (items[x].includes(':')) {
				val = items[x].split(':')[1];
			}

			if (val != user) {
				data.push(items[x]);
			}
		}
		input.val(data.join(', '));
	}

	if (document.getElementById("TAB_follow").className.match(/active/)) {
		document.getElementById("INPUT_add").disabled = false;
	}

	if (refresh) {
		CRMSearch();
	}
}

/**
 * Search by date
 *
 * @return  {void}
 */
function CRMDateSearch() {
	var start = document.getElementById("datestartshort").value;
	if (start.match(/^\d{4}-\d{2}-\d{2}$/) || start == "") {
		CRMSearch();
		return;
	}

	var stop = document.getElementById("datestopshort").value;
	if (stop.match(/^\d{4}-\d{2}-\d{2}$/) || stop == "") {
		CRMSearch();
		return;
	}
}

/**
 * Search by keyword
 *
 * @param   {number}  key
 * @return  {void}
 */
function CRMKeywordSearch(key) {
	// if someone hit enter
	if (key == 13) {
		CRMSearch();
		return;
	}

	// make sure all the keywords are long enough
	var search = true;
	var text = document.getElementById("keywords").value;
	var keywords = text.split(/ /);

	for (var x = 0; x < keywords.length; x++) {
		if (keywords[x].length < 3) {
			search = false;
		}
	}

	if (search || text == "") {
		keywords_pending++;
		setTimeout(function () {
			keywords_pending--;
			if (keywords_pending == 0) {
				CRMSearch();
			}
		}, 200);
	}
}

/**
 * Toggle search
 *
 * @param   {string}  on
 * @return  {void}
 */
function CRMToggleSearch(on) {
	// move search box for group
	if (on == 'group') {
		// reset box
		document.getElementById("newuser").value = "";
		ClearSearch();

		// flip the flags so it acts a group search box
		groupsearch = true;
		usersearch = false;
		search_path = "groupname";

		document.getElementById("newuser").focus();
	} else if (on == 'people') {
		// move search box for people
		document.getElementById("newuser").value = "";
		ClearSearch();

		// flip the flags so it acts a person search box
		groupsearch = false;
		usersearch = true;
		search_path = "name";

		document.getElementById("newuser").focus();
	} else if (on == 'none') {
		document.getElementById("newuser").blur();
		// hide the search box
		// onblur() event is a bit funky, need some safe guards to prevent unintended box hiding
		// make sure the box really no longer has focus, and provide slight delay in hiding it
		if (typeof (document.activeElement) != 'undefined') {
			setTimeout(function () {
				if (document.getElementById("newuser") != document.activeElement) {
					document.getElementById("DIV_peoplesearch").style.display = "none";
					document.getElementById("DIV_groupsearch").style.display = "none";
					document.getElementById("DIV_people").style.display = "block";
					// adjust group field
					var group_count = 0;
					var groupsdata = document.getElementById("TD_group").getElementsByTagName("div");
					for (var i = 0; i < groupsdata.length; i++) {
						if (groupsdata[i].id.search("GROUP_") == 0) {
							group_count++;
						}
					}

					if (group_count == 0 || multi_group) {
						document.getElementById("DIV_group").style.display = "block";
					}
				}
			}, 300);
		}
	}
}

/**
 * Post new entry to database
 *
 * @return  {void}
 */
function CRMAddEntry() {
	var groupsdata = new Array();
	var peopledata = new Array();
	var resourcedata = new Array();
	var notes;
	var groups = new Array();
	var people = new Array();
	var resources = new Array();
	var myuserid = document.getElementById("myuserid").value;
	var contactdate = document.getElementById("datestartshort").value;
	var type = document.getElementById("crmtype").value;
	var i = 0,
		x = 0,
		y = 0;

	// clear error boxes
	document.getElementById("TAB_search_action").innerHTML = "";
	document.getElementById("TAB_add_action").innerHTML = "";

	if (!document.getElementById("TAB_follow").className.match(/active/)) {
		if (!contactdate.match(/^\d{4}-\d{2}-\d{2}$/)) {
			SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		}
	}
	contactdate += " 00:00:00";

	if ($('.tagsinput').length) {
		groupsdata = document.getElementById("group").value.split(',');
		for (i = 0; i < groupsdata.length; i++) {
			if (groupsdata[i] != "") {
				/*if (groupsdata[i].indexOf('/') !== -1) {
					var res = groupsdata[i].split('/');
					groups.push(res[res.length-1]);
				} else {*/
				groups.push(groupsdata[i]);
				//}
			}
		}
		peopledata = document.getElementById("people").value.split(',');
		for (i = 0; i < peopledata.length; i++) {
			if (peopledata[i] != "") {
				/*if (usersdata[i].indexOf('/') !== -1) {
					var res = usersdata[i].split('/');
					people.push({
						'userid' : res[res.length-1]
					});
				} else {*/
				people.push(peopledata[i]);
				/*people.push({
					'userid': peopledata[i]
				});*/
				//}
			}
		}
		resourcedata = document.getElementById("crmresource").value.split(',');
		for (i = 0; i < resourcedata.length; i++) {
			if (resourcedata[i] != "") {
				if (resourcedata[i].indexOf('/') !== -1) {
					var res = resourcedata[i].split('/');
					resources.push(res[res.length - 1]);
				} else {
					resources.push(resourcedata[i]);
				}
			}
		}
	} /*else {
		groupsdata = document.getElementById("TD_group").getElementsByTagName("div");
		peopledata = document.getElementById("TD_people").getElementsByTagName("div");
		resourcedata = document.getElementById("TD_resource").getElementsByTagName("div");
		for (i = 0; i < groupsdata.length; i++) {
			if (groupsdata[i].id.search("GROUP_") == 0) {
				groups.push(groupsdata[i].id.substr(6));
			}
		}
		for (i = 0; i < peopledata.length; i++) {
			if (peopledata[i].id.search("USER_") == 0) {
				var name = peopledata[i].innerHTML.substr(peopledata[i].innerHTML.lastIndexOf(">") + 1);
				name.replace(/^ +/, "");
				name.replace(/ +$/, "");
				people.push({
					'userid': peopledata[i].id.substr(5),
					'name': name
				});
			}
		}
		for (i = 0; i < resourcedata.length; i++) {
			if (resourcedata[i].id.search("RESOURCE_") == 0) {
				resources.push(resourcedata[i].id.substr(6));
			}
		}
	}*/

	notes = document.getElementById("NotesText").value;

	var searchdata = $('#crm-search-data');
	var sdata = JSON.parse(searchdata.html());

	var removeusers = Array();
	var addusers = Array();
	var post = {};
	var remove = true,
		add = true;

	if (window.location.href.match(/edit/)) {
		var data = $('#crm-data');
		var original = {};
		var originalusers = [];
		var originalcontactusers = [];
		if (data.length) {
			var orig = JSON.parse(data.html());
			original = orig.original;
			//originalusers = orig.originalusers;
			//originalcontactusers = orig.originalcontactusers;
		}

		// update
		//if (contactdate != original['datetimecontact']) {
			post['datetimecontact'] = contactdate;
		//}
		if (groups.length > 0 && groups[0] != original['group']) {
			post['groupid'] = groups[0];
		}
		if (groups.length == 0 && original['group'] != '') {
			post['groupid'] = 0;
		}

		if (type != original['contactreporttypeid']) {
			post['contactreporttypeid'] = type;
		}

		post['resources'] = resources;
		post['users'] = people;

		// first determine if any users have been deleted
		/*for (x = 0; x < originalusers.length; x++) {
			remove = true;
			for (y = 0; y < people.length; y++) {
				if (originalusers[x] == people[y]['userid']) {
					remove = false;
					break;
				}
			}
			if (remove) {
				removeusers.push(originalcontactusers[x]);
			}
		}
		// then determine if any users have been added
		for (x = 0; x < people.length; x++) {
			add = true;
			for (y = 0; y < originalusers.length; y++) {
				if (originalusers[y] == people[x]['userid']) {
					add = false;
					break;
				}
			}
			if (add) {
				addusers.push(people[x]['userid']);
			}
		}

		for (x = 0; x < removeusers.length; x++) {
			WSDeleteURL(removeusers[x], CRMUpdatedReport);
		}

		for (x = 0; x < addusers.length; x++) {
			post = JSON.stringify({
				'contactreportid': original['id'],
				'userid': addusers[x]
			});
			WSPostURL(ROOT_URL + "contactreportuser", post, CRMUpdatedReport);
		}*/

		post['report'] = notes;
		post = JSON.stringify(post);
		if (post != "{}") {
			WSPutURL(original['api'], post, CRMUpdatedReport);
		}

		if (typeof (history.pushState) != 'undefined') {
			var querystring = "?id=" + original['id'];
			history.pushState(null, null, encodeURI(querystring));
		}

		var id = original['id'].substr(original['id'].lastIndexOf("/") + 1);

		setTimeout(function () {
			CRMToggle('search');
			CRMClearSearch();
			document.getElementById("id").value = id;
			CRMSearch();
		}, 250);

		return;
	} else if (document.getElementById("TAB_follow").className.match(/active/)) {
		// first determine if any users have been deleted
		for (x = 0; x < sdata.followerofusers.length; x++) {
			remove = true;
			for (y = 0; y < people.length; y++) {
				if (sdata.followerofusers[x]['id'] == people[y]['userid']) {
					remove = false;
					break;
				}
			}
			if (remove) {
				removeusers.push(sdata.followerofusers[x]['follow']);
			}
		}
		// then determine if any users have been added
		for (x = 0; x < people.length; x++) {
			add = true;
			for (y = 0; y < sdata.followerofusers.length; y++) {
				if (people[x]['userid'] == sdata.followerofusers[y]['id']) {
					add = false;
					break;
				}
			}
			if (add) {
				addusers.push(people[x]['userid']);
			}
		}

		var removegroups = Array();
		var addgroups = Array();
		// first determine if any groups have been deleted
		for (x = 0; x < sdata.followerofgroups.length; x++) {
			remove = true;
			for (y = 0; y < groups.length; y++) {
				if (sdata.followerofgroups[x]['id'] == groups[y]) {
					remove = false;
					break;
				}
			}
			if (remove) {
				removegroups.push(sdata.followerofgroups[x]['follow']);
			}
		}
		// then determine if any groups have been added
		for (x = 0; x < groups.length; x++) {
			add = true;
			for (y = 0; y < sdata.followerofgroups.length; y++) {
				if (groups[x] == sdata.followerofgroups[y]['id']) {
					add = false;
					break;
				}
			}
			if (add) {
				addgroups.push(groups[x]);
			}
		}

		for (x = 0; x < removeusers.length; x++) {
			WSDeleteURL(removeusers[x], CRMUpdatedReport);
			// delete from cache
			for (y = 0; y < sdata.followerofusers.length; y++) {
				if (removeusers[x] == sdata.followerofusers[y]['follow']) {
					sdata.followerofusers.splice(y, 1);
					break;
				}
			}
		}

		for (x = 0; x < addusers.length; x++) {
			post = JSON.stringify({ 'following': addusers[x] });
			WSPostURL(ROOT_URL + "contactreportfollowuser", post, CRMUpdatedFollowUser);
		}

		for (x = 0; x < removegroups.length; x++) {
			WSDeleteURL(removegroups[x], CRMUpdatedReport);
			// delete from cache
			for (y = 0; y < sdata.followerofgroups.length; y++) {
				if (removegroups[x] == sdata.followerofgroups[y]['follow']) {
					sdata.followerofgroups.splice(y, 1);
					break;
				}
			}
		}

		for (x = 0; x < addgroups.length; x++) {
			post = JSON.stringify({ 'following': addgroups[x] });
			WSPostURL(ROOT_URL + "contactreportfollowgroup", post, CRMUpdatedFollowGroup);
		}

		return;
	}

	if (people.length == 0) {
		if (groups.length != 0) {
			SetError('Required field missing', 'Please enter at least one person.');
			return;
		}
		else if (groups.length == 0) {
			SetError('Required field missing', 'Please enter at least one person and optionally a group.');
			return;
		}
	}
	else if (notes == "") {
		SetError('Required field missing', 'Please enter some note text.');
		return;
	}
	else {
		// new post
		post = {
			'report': notes,
			'datetimecontact': contactdate,
			'userid': myuserid,
			'contactreporttypeid': type
		};

		if (groups.length > 0) {
			post['groupid'] = groups[0];
		}

		if (resources.length > 0) {
			post['resources'] = resources;
		}

		post = JSON.stringify(post);
		document.getElementById("INPUT_add").disabled = true;

		WSPostURL(document.getElementById("reports").getAttribute('data-api'), post, CRMNewReport, people);
	}
}

/**
 * Callback after updating a report
 *
 * @param   {object}  xml
 * @return  {void}
 */
function CRMUpdatedReport(xml) {
	if (xml.status == 200) {
		document.getElementById("INPUT_add").disabled = true;
	}
}

/**
 * Callback after following a user
 *
 * @param   {object}  xml
 * @return  {void}
 */
function CRMUpdatedFollowUser(xml) {
	if (xml.status == 200) {
		// add to cache
		var results = JSON.parse(xml.responseText);

		var data = $('#crm-search-data');
		var sdata = JSON.parse(data.html());
		sdata.followerofusers.push(results);

		data.html(JSON.stringify(sdata));

		document.getElementById("INPUT_add").disabled = true;
	}
}

/**
 * Callback after following a group
 *
 * @param   {object}  xml
 * @return  {void}
 */
function CRMUpdatedFollowGroup(xml) {
	if (xml.status == 200) {
		// add to cache
		var results = JSON.parse(xml.responseText);

		var data = $('#crm-search-data');
		var sdata = JSON.parse(data.html());
		sdata.followerofgroups.push(results);

		data.html(JSON.stringify(sdata));

		document.getElementById("INPUT_add").disabled = true;
	}
}

/**
 * Callback for creating a new report
 *
 * @param   {object}  xml
 * @param   {array}   people
 * @return  {void}
 */
function CRMNewReport(xml, people) {
	document.getElementById("INPUT_add").disabled = false;

	if (xml.status < 400) {
		var results = JSON.parse(xml.responseText);

		/*for (var x = 0; x < people.length; x++) {
			// insert placeholders to put people when posted
			var post = JSON.stringify({
				'contactreport': results['id'],
				'user': people[x]['userid']
			});
			WSPostURL(ROOT_URL + "contactreportuser", post, CRMNewPeopleTag);
		}*/
		document.getElementById("NotesText").value = "";

		CRMClearSearch();

		document.getElementById("id").value = results.id;

		CRMToggle('search', true);
		/*setTimeout(function () {
			CRMSearch();
		}, 250);*/
	} else if (xml.status == 409 || xml.status == 415) {
		SetError('Invalid date.', 'Please pick the current date or a date in the past.');
	} else {
		SetError('Unable to create report.', 'Your session may have timed out. Copy your text and reload page.');
	}
}

/**
 * Callback for adding new people
 *
 * @param   {object}  xml
 * @return  {void}
 */
function CRMNewPeopleTag(xml) {
	if (xml.status != 200) {
		SetError('Unable to create report.', 'An error occurred during processing of new report.');
	}
}

/**
 * Search reports
 *
 * @return  {void}
 */
function CRMSearch() {
	var groupsdata = new Array();
	var groups = new Array();
	var group = null;

	var peopledata = new Array();
	var people = new Array();
	var person = null;

	var resourcedata = new Array();
	var resources = new Array();
	var resource = null;

	var keywords = document.getElementById("keywords").value;
	//var myuserid = document.getElementById("myuserid").value;
	var start = document.getElementById("datestartshort").value;
	var stop = document.getElementById("datestopshort").value;
	var id = document.getElementById("id").value;
	var typeid = document.getElementById("crmtype").value;
	var i = 0,
		x = 0;

	if ($('.tagsinput').length) {
		groupsdata = document.getElementById("group").value.split(',');
		for (i = 0; i < groupsdata.length; i++) {
			if (groupsdata[i] != "") {
				if (groupsdata[i].indexOf('/') !== -1) {
					group = groupsdata[i].split('/');
					groups.push(group[group.length - 1]);
				} else {
					groups.push(groupsdata[i]);
				}
			}
		}

		peopledata = document.getElementById("people").value.split(',');
		for (i = 0; i < peopledata.length; i++) {
			if (peopledata[i] != "") {
				if (peopledata[i].indexOf('/') !== -1) {
					person = peopledata[i].split('/');
					people.push(person[person.length - 1]);
				} else {
					people.push(peopledata[i]);
				}
			}
		}

		// Fetch list of selected resources
		resourcedata = document.getElementById("crmresource").value.split(',');
		for (i = 0; i < resourcedata.length; i++) {
			if (resourcedata[i] != "") {
				if (resourcedata[i].indexOf('/') !== -1) {
					resource = resourcedata[i].split('/');
					resources.push(resource[resource.length - 1]);
				} else {
					resources.push(resourcedata[i]);
				}
			}
		}
	} else {
		groupsdata = document.getElementById("TD_group").getElementsByTagName("div");
		peopledata = document.getElementById("TD_people").getElementsByTagName("div");
		resourcedata = document.getElementById("TD_resource").getElementsByTagName("div");

		for (i = 0; i < groupsdata.length; i++) {
			if (groupsdata[i].id.search("GROUP_") == 0) {
				group = groupsdata[i].id.substr(6);
				group = group.split('/');
				groups.push(group[3]);
			}
		}

		for (i = 0; i < peopledata.length; i++) {
			if (peopledata[i].id.search("USER_") == 0) {
				person = peopledata[i].id.substr(5);
				person = person.split('/');
				people.push(person[3]);
			}
		}

		for (i = 0; i < resourcedata.length; i++) {
			if (resourcedata[i].id.search("RESOURCE_") == 0) {
				resource = resourcedata[i].id.substr(5);
				resource = resource.split('/');
				resources.push(resource[3]);
			}
		}
	}

	// sanity checks
	if (start != "") {
		if (!start.match(/^\d{4}-\d{2}-\d{2}$/)) {
			SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			// clear error boxes
			document.getElementById("TAB_search_action").innerHTML = "";
			document.getElementById("TAB_add_action").innerHTML = "";
		}
	}
	if (stop != "") {
		if (!stop.match(/^\d{4}-\d{2}-\d{2}$/)) {
			SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			// clear error boxes
			document.getElementById("TAB_search_action").innerHTML = "";
			document.getElementById("TAB_add_action").innerHTML = "";
		}
	}

	// start assembling string
	var searchstring = "";
	var querystring = "&";

	// if not add new
	if (!document.getElementById("TAB_add").className.match(/active/)) {
		if (start != "") {
			searchstring += " start:" + start;
			querystring += "&start=" + start;
		}
		if (stop != "") {
			searchstring += " stop:" + stop;
			querystring += "&stop=" + stop;
		}
	}
	if (groups.length > 0) {
		searchstring += " group:" + groups[0];
		querystring += "&group=" + groups[0];
		for (x = 1; x < groups.length; x++) {
			searchstring += "," + groups[x];
			querystring += "," + groups[x];
		}
	}
	if (people.length > 0) {
		searchstring += " people:" + people[0];
		querystring += "&people=" + people[0];
		for (x = 1; x < people.length; x++) {
			searchstring += "," + people[x];
			querystring += "," + people[x];
		}
	}
	// Construct resource query
	if (resources.length > 0) {
		searchstring += " resource:" + resources[0];
		querystring += "&resource=" + resources[0];
		for (x = 1; x < resources.length; x++) {
			searchstring += "," + resources[x];
			querystring += "," + resources[x];
		}
	}
	if (typeid != '-1') {
		searchstring += " type:" + typeid;
		querystring += "&type=" + typeid;
	}
	// if not add new
	if (!document.getElementById("TAB_add").className.match(/active/)) {
		if (keywords != "") {
			// format FP or CR ticket queries correctly
			keywords = keywords.replace(/(FP|CR)#(\d+)/g, '$1 $2');

			// filter out potentially dangerous garbage
			keywords = keywords.replace(/[^a-zA-Z0-9_ ]/g, '');
			searchstring += " " + keywords;
			querystring += "&search=" + keywords;
		}
	}
	// if not add new
	if (!document.getElementById("TAB_add").className.match(/active/)) {
		if (id.match(/(\d)+/)) {
			searchstring += " id:" + id;
			querystring += "&id=" + id;
		}
	}

	if (window.location.href.match(/edit/)) {
		document.getElementById("INPUT_add").disabled = false;
		return;
	} else {
		CRMToggleAddButton();
	}

	if (typeof (history.pushState) != 'undefined') {
		var tab = window.location.href.match(/[&?](\w+)$/);
		if (tab != null) {
			querystring = querystring + "&" + tab[1];
		}
		querystring = querystring.replace(/^&+/, '?');
		history.pushState(null, null, encodeURI(querystring));
	}

	//if (searchstring == "") {
	//	searchstring = "start:0000-00-00";
	//}

	console.log('Searching... ' + $("#reports").data('api') + encodeURI(querystring));
	$("#reports").data('query', querystring.replace('?', ''));

	WSGetURL($("#reports").data('api') + encodeURI(querystring), CRMSearched);
}

/**
 * Enable or disable Add button
 *
 * @return  {void}
 */
function CRMToggleAddButton() {
	// if add new
	if (document.getElementById("TAB_add").className.match(/active/)) {
		var start = document.getElementById("datestartshort").value;
		var people = document.getElementById("people").value.split(',');
		var notes = document.getElementById("NotesText").value;

		if (start != "" && people.length > 0 && notes != "") {
			document.getElementById("INPUT_add").disabled = false;
		} else {
			document.getElementById("INPUT_add").disabled = true;
		}
	}
}

/**
 * Callback after searching
 *
 * @param   {object}  xml
 * @return  {void}
 */
function CRMSearched(xml) {
	const DEFAULT_ENTRIES = 20;

	if (xml.status == 200) {
		var reports = $("#reports");
		var count = 0;

		const results = JSON.parse(xml.responseText);

		$("#matchingReports").html("Found " + results.data.length + " matching reports");

		if (results.data.length == 0) {
			reports.html('<p class="alert alert-warning">No matching reports found.</p>');
		} else {
			reports.html('');

			for (var x = 0; x < results.data.length; x++, count++) {
				CRMPrintRow(
					results.data[x],
					//results.people,
					//results.comments,
					//results.userid,
					'newEntries' //(x < DEFAULT_ENTRIES ? "newEntries" : "newEntriesHidden")
				);
			}

			// Re-initialize tooltips
			$('.tip').tooltip({
				position: {
					my: 'center bottom',
					at: 'center top'
				},
				hide: false
			});

			reports.find(".alert").hide();
			$(".newEntriesHidden").hide();

			/*var a = $("<a></a>")
				.attr({
					id: "showEntries",
					href: "#"
				})
				.html("Show All Reports")
				.on('click', function (e) {
					e.preventDefault();
					const flag = $(this).html().includes('Show All Reports');
					$(this).html(flag ? "Show Less Reports" : "Show All Reports");
					$(this).parent().text("Displaying all of CRM Reports...");
					flag ? $(".newEntriesHidden").show() : $(".newEntriesHidden").hide();
				});

			var td = $("<p></p>")
				.attr({
					id: "displayEntries",
				})
				.html("Displaying " + Math.min(count, DEFAULT_ENTRIES) + " of " +
					results.data.length + " CRM Reports...<br/>"
				)
				.append(a);

			reports.append(td);*/

			var q = reports.data('query');
			var query = q.replace(' ', '&').replace(':', '=');
			var lastpage = Math.ceil(results.meta.total > results.meta.per_page ? results.meta.total / results.meta.per_page : 1);

			// Pagination
			var ul = $('<ul class="pagination"></ul>');

			var li = $('<li class="page-item page-first">');
			var a = $('<a class="page-link" title="First page"><span aria-hidden="true">«</span></a>')
				.attr('href', '?' + query + '&page=1')
				.attr('data-page', 1);
			if (results.meta.total <= (results.meta.per_page * results.meta.current_page) || results.meta.current_page == 1) {
				li.addClass('disabled');
				a.attr('aria-disabled', 'true');
			}
			li.append(a);
			ul.append(li);

			li = $('<li class="page-item page-prev">');
			a = $('<a class="page-link" title="Previous page"><span aria-hidden="true">‹</span></a>')
				.attr('href', '?' + query + ' &page=' + (results.meta.current_page > 1 ? results.meta.current_page - 1 : 1))
				.attr('data-page', (results.meta.current_page > 1 ? results.meta.current_page - 1 : 1));
			if (results.meta.total <= (results.meta.per_page * results.meta.current_page) || results.meta.current_page == 1) {
				li.addClass('disabled');
				a.attr('aria-disabled', 'true');
			}
			li.append(a);
			ul.append(li);

			if (results.meta.total <= results.meta.per_page) {
				li = $('<li class="page-item">');
				a = $('<a class="page-link"></a>')
					.text('1')
					.attr('href', '?' + query + '&page=1')
					.attr('data-page', 1);
				if (results.meta.total <= (results.meta.per_page * results.meta.current_page)) {
					li.addClass('disabled');
					a.attr('aria-disabled', 'true');
				}
				li.append(a);
				ul.append(li);
			} else {
				var c = 0;
				for (var l = 1; l <= lastpage; l++) {
					if (c >= 10) {
						li = $('<li class="page-item">');
						a = $('<span class="page-link"></span>')
							.text('...')
							.attr('aria-disabled', 'true');
						li.append(a);
						ul.append(li);
						break;
					}
					li = $('<li class="page-item">');
					a = $('<a class="page-link"></a>')
						.text(l)
						.attr('href', '?' + query + '&page=' + l)
						.attr('data-page', l);
					if (results.meta.current_page == l) {
						li.addClass('active');
						//a.attr('aria-disabled', 'true');
					}
					li.append(a);
					ul.append(li);
					c++;
				}
			}

			li = $('<li class="page-item page-next">');
			a = $('<a class="page-link" title="Next page"><span aria-hidden="true">›</span></a>')
				.attr('href', '?' + query + '&page=' + (results.meta.current_page < lastpage ? lastpage - 1 : 1))
				.attr('data-page', (results.meta.current_page > 1 ? lastpage - 1 : 1))
				.attr('data-query', q.replace(/(page:\d+)/, 'page:' + (results.meta.current_page > 1 ? lastpage - 1 : 1)));
			if (results.meta.total <= (results.meta.per_page * results.meta.current_page)) {
				li.addClass('disabled');
				a.attr('aria-disabled', 'true');
			}
			li.append(a);
			ul.append(li);

			li = $('<li class="page-item page-last">');
			a = $('<a class="page-link" title="Last page"><span aria-hidden="true">»</span></a>')
				.attr('href', '?' + query + '&page=' + lastpage)
				.attr('data-page', lastpage)
				.attr('data-query', q.replace(/(page:\d+)/, 'page:' + lastpage));
			if (results.meta.total <= (results.meta.per_page * results.meta.current_page)) {
				li.addClass('disabled');
				a.attr('aria-disabled', 'true');
			}
			li.append(a);
			ul.append(li);

			reports.append(ul);

			$('.page-link').on('click', function (e) {
				e.preventDefault();
				$('#page').val($(this).data('page'));
				CRMSearch();
			});
		}
	}
}

/**
 * Print a report entry
 *
 * @param   {object}  report
 * @param   {array}   people
 * @param   {array}   comments
 * @param   {string}  userid
 * @param   {string}  cls
 * @return  {void}
 */
function CRMPrintRow(report, cls) { //people, comments, userid, cls) {
	var id = report['id'];//.split('/');
	//id = id[id.length - 1];

	// determine if this entry can be edited
	var edit = false;
	if (report['can']['edit']) {
		edit = true;
	}

	var tr, td, div, a, span, img, li, x;

	// create first row
	var container = document.getElementById('reports');

	var article = document.createElement("article");
	article.id = id;
	article.className = "crm-item " + cls;
	article.setAttribute('data-api', report['api']);

	var panel = document.createElement("div");
	panel.className = "card mb-3 panel panel-default";

	// -- Admin header
	tr = document.createElement("div");
	tr.className = 'card-header panel-heading news-admin';

	// ID
	td = document.createElement("span");
	td.className = "crmid";

	a = document.createElement("a");
	a.href = "contactreports?id=" + id;
	a.innerHTML = "#" + id;

	td.appendChild(a);

	if (edit) {
		// Delete button
		a = document.createElement("a");
		a.href = "/contactreports?id=" + id + "&delete";
		a.className = 'edit news-delete tip';
		a.onclick = function (e) {
			e.preventDefault();
			CRMDeleteReport(report['id']);
		};
		a.title = "Delete Contact Report.";

		img = document.createElement("i");
		img.className = "crmeditdelete fa fa-trash";
		img.setAttribute('aria-hidden', true);
		img.id = report['id'] + "_crmdeleteimg";

		a.appendChild(img);
		td.appendChild(a);
	}

	tr.appendChild(td);

	panel.appendChild(tr);

	// -- Header
	tr = document.createElement("div");
	tr.className = 'card-header panel-heading';

	td = document.createElement("h3");
	td.className = "card-title panel-title crmcontactdate";

	var bits = report['datetimecontact'].match(/\d+/g);
	var d = new Date(bits[0], bits[1] - 1, bits[2], bits[3], bits[4], bits[5], 0);

	td.innerHTML = d.getMonthName() + " " + d.getDate() + ", " + d.getFullYear();

	if (edit) {
		a = document.createElement("a");
		a.href = "contactreports/?id=" + report['id'] + "&edit";
		a.className = "news-action tip";
		a.onclick = function (e) {
			e.preventDefault();

			CRMClearSearch();

			if (!window.location.href.match(/crm/)) {
				window.location = this.href;
			} else {
				var url = window.location.href.split("?");
				url = url[0];
				window.location = url + "?id=" + report['id'] + "&edit";
			}
		};
		a.title = "Edit contact report date.";

		img = document.createElement("i");
		img.className = "crmedit fa fa-pencil";
		img.setAttribute('aria-hidden', true);

		a.appendChild(img);
		td.appendChild(a);
	}

	tr.appendChild(td);

	var ul = document.createElement("ul");
	ul.className = 'card-meta panel-meta news-meta';

	// Date
	li = document.createElement("li");
	li.className = 'news-date';

	bits = report['datetimecreated'].match(/\d+/g);
	d = new Date(bits[0], bits[1] - 1, bits[2], bits[3], bits[4], bits[5], 0);

	span = document.createElement("span");
	span.className = "crmpostdate";
	span.innerHTML = "Posted on " + d.getMonthName() + " " + d.getDate() + ", " + d.getFullYear();

	li.appendChild(span);
	ul.appendChild(li);

	// Creator
	li = document.createElement("li");
	li.className = 'news-author';

	span = document.createElement("span");
	span.className = "crmposter";
	span.innerHTML = "Posted by " + report['username'];

	li.appendChild(span);
	ul.appendChild(li);

	// Group
	if (report['groupname'] != null && report['groupid'] > 0) {
		li = document.createElement("li");
		li.className = 'news-group';

		a = document.createElement("a");
		a.href = "admin/groups/" + report['groupid'];
		a.innerHTML = report['groupname'];

		li.appendChild(a);
		ul.appendChild(li);
	}

	// People
	if (report.users.length > 0) {
		var p = Array();
		for (x = 0; x < report.users.length; x++) {
			//if (report.users[x]['id'] == report['id']) {
				a = document.createElement("a");
				a.href = "admin/users/" + report.users[x]['userid'];
				a.innerHTML = report.users[x]['name'];

				/*if (report.users[x]['can']['edit']) {
					a.className = 'crmadmin';
				}*/

				p.push(a.outerHTML);
			//}
		}

		if (p.length) {
			li = document.createElement("li");
			li.className = 'news-users';

			span = document.createElement("span");
			span.className = "crmpostpeople";
			span.innerHTML = p.join(', ');

			li.appendChild(span);

			if (edit) {
				a = document.createElement("a");
				a.href = "contactreports/?id=" + report['id'] + "&edit";
				a.className = "news-action tip";
				a.onclick = function (e) {
					e.preventDefault();

					CRMClearSearch();

					if (!window.location.href.match(/crm/)) {
						window.location = "contactreports/?id=" + report['id'] + "&edit";
					} else {
						var url = window.location.href.split("?");
						url = url[0];

						window.location = url + "?id=" + report['id'] + "&edit";
					}
				};
				a.title = "Add or remove users and groups.";

				img = document.createElement("i");
				img.className = "crmedit fa fa-pencil";
				img.setAttribute('aria-hidden', true);

				a.appendChild(img);
				li.appendChild(a);
			}

			ul.appendChild(li);
		}
	}

	// Resource list
	if (report.resources.length > 0) {
		li = document.createElement("li");
		li.className = 'news-tags';

		span = document.createElement("span");
		span.className = "crmpostresources";

		var r = Array();
		for (x = 0; x < report.resources.length; x++) {
			r.push(report.resources[x].name);
		}
		span.innerHTML = r.join(', ');

		li.appendChild(span);

		if (edit) {
			a = document.createElement("a");
			a.className = "news-action tip"
			a.href = "contactreports?id=" + id + '&edit';
			a.title = "Edit tagged resources.";

			img = document.createElement("i");
			img.className = "crmedit fa fa-pencil";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);

			span.appendChild(a);
			li.appendChild(span);
		}

		ul.appendChild(li);
	}

	// Type
	if (report['contactreporttypeid'] > 0) {
		li = document.createElement("li");
		li.className = 'news-type';

		span = document.createElement("span");
		span.className = "newstype";
		span.appendChild(document.createTextNode(report['type']['name']));

		li.appendChild(span);
		ul.appendChild(li);
	}

	//tr.appendChild(td);
	tr.appendChild(ul);
	panel.appendChild(tr);

	// --Body
	tr = document.createElement("div");
	tr.className = 'card-body panel-body';

	td = document.createElement("div");
	td.className = "newsposttext";

	if (edit) {
		var opt = document.createElement("div");
		opt.className = "card-options panel-options";

		// Edit button
		a = document.createElement("a");
		a.href = "contactreports?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			CRMEditReportTextOpen(report['id']);
		};
		a.className = "news-edit icn tip";
		a.title = "Edit report text.";
		a.id = report['id'] + "_textediticon";

		img = document.createElement("i");
		img.className = "crmedittext fa fa-pencil";
		img.setAttribute('aria-hidden', true);
		img.id = report['id'] + "_textediticonimg";

		a.appendChild(img);
		opt.appendChild(a);

		// Save button
		a = document.createElement("a");
		a.href = "contactreports?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			CRMSaveReportText(report['id'], report['api']);
		};
		a.className = "news-save icn tip";
		a.id = report['id'] + "_textsaveicon";
		a.title = "Save report text.";
		a.style.display = "none";

		img = document.createElement("i");
		img.className = "crmsavetext fa fa-save";
		img.setAttribute('aria-hidden', true);
		img.id = report['id'] + "_textsaveiconimg";

		a.appendChild(img);
		opt.appendChild(a);

		// Cancel button
		a = document.createElement("a");
		a.href = "contactreports?id=" + id;
		a.onclick = function (e) {
			e.preventDefault();
			CRMCancelReportText(id);
		};
		a.className = "news-cancel icn tip";
		a.title = "Cancel edits to text";
		a.id = id + "_textcancelicon";
		a.style.display = "none";

		img = document.createElement("i");
		img.className = "crmsavetext fa fa-ban";
		img.setAttribute('aria-hidden', true);
		img.id = id + "_textcanceliconimg";

		a.appendChild(img);
		opt.appendChild(a);

		td.appendChild(opt);
	}

	// format text
	var rawtext = report['report'];
	report['report'] = report['formattedreport'];

	// determine the directory we are operating in
	var page = document.location.href.split("/")[4];
	// if we are in crm, we are doing report searches, so we should highlight matches
	if (page == 'crm') {
		report['report'] = HighlightMatches(report['report']);
	}

	span = document.createElement("span");
	span.id = report['id'] + "_text";
	span.innerHTML = report['report'];

	td.appendChild(span);

	span = document.createElement("span");

	var textarea = document.createElement("textarea");
	textarea.id = report['id'] + "_textarea";
	textarea.innerHTML = rawtext;
	textarea.style.display = "none";
	textarea.rows = 7;
	textarea.cols = 45;
	textarea.className = "form-control crmreportedittextbox";

	span.appendChild(textarea);
	td.appendChild(span);

	tr.appendChild(td);

	panel.appendChild(tr);

	article.appendChild(panel);

	// -- New Comment
	tr = document.createElement("div");
	tr.className = 'crmnewcomment card mb-3 panel panel-default';

	// -- Footer
	var footer = document.createElement("div");
	footer.className = 'card-header panel-heading';

	// create subscribe row
	td = document.createElement("div");
	td.className = "crmcomment crmsubscribe";
	td.id = report['id'] + "_subscribed";

	if (!report['subscribed']) {
		a = document.createElement("a");
		a.href = "/contactreports?id=" + id + "&subscribe";
		a.className = 'btn btn-default btn-sm';
		a.onclick = function (e) {
			e.preventDefault();
			CRMSubscribeComment(report['id']);
		};
		a.innerHTML = "Subscribe";

		td.appendChild(a);
	} else if (report['subscribed'] == "1" || report['subscribed'] == '3') {
		td.appendChild(document.createTextNode("Subscribed"));
	} else if (report['subscribed'] == "2") {
		a = document.createElement("a");
		a.href = "/contactreports?id=" + id + "&unsubscribe";
		a.className = 'btn btn-default btn-sm';
		a.onclick = function (e) {
			e.preventDefault();
			CRMUnsubscribeComment(report['subscribedcommentid'], report['id']);
		};
		a.innerHTML = "Unsubscribe";

		td.appendChild(a);
	}

	footer.appendChild(td);
	tr.appendChild(footer);

	td = document.createElement("div");
	td.className = "card-body panel-body";

	div = document.createElement("div");
	div.id = report['id'] + "_newupdate";

	textarea = document.createElement("textarea");
	textarea.className = "form-control crmcommentbox";
	textarea.placeholder = "Write a comment...";
	textarea.id = report['id'] + "_newcommentbox";
	textarea.setAttribute('data-api', reports.getAttribute('data-comments'));
	textarea.rows = 1;
	textarea.cols = 45;
	textarea.onfocus = function () {
		CRMExpandNewComment(this.id);
	};
	textarea.onblur = function () {
		CRMCollapseNewComment(this.id);
	};

	div.appendChild(textarea);

	// Save button
	a = document.createElement("a");
	a.href = "/news/manage?update&id=" + id;
	a.onclick = function (e) {
		e.preventDefault();
		CRMPostComment(report['id']);
	};
	a.title = "Add a new comment.";

	img = document.createElement("i");
	img.className = "fa fa-save";
	img.setAttribute('aria-hidden', true);
	img.id = report['id'] + "_newcommentboxsave";
	img.style.display = "none";

	a.appendChild(img);
	div.appendChild(a);

	td.appendChild(div);
	tr.appendChild(td);

	article.appendChild(tr);

	// -- Comments
	ul = document.createElement("ul");
	ul.id = report['id'] + '_comments';
	ul.className = 'crm-comments';

	article.appendChild(ul);

	container.appendChild(article);

	for (x = 0; x < report['comments'].length; x++) {
		if (report['comments'][x]['comment'] != '') {
			CRMPrintComment(report['id'], report['comments'][x]);//, userid);
		}
	}
	
	/*var c = Array();
	for (x = 0; x < comments.length; x++) {
		if (comments[x]['contactreportid'] == report['id']) {
			c.push(comments[x]);
		}
	}
	for (x = 0; x < c.length; x++) {
		if (c[x]['comment'] != '') {
			CRMPrintComment(report['id'], c[x], userid);
		}
	}*/
}

/**
 * Cancel updating report text
 *
 * @param   {string}  id
 * @return  {void}
 */
function CRMCancelReportText(id) {
	// hide text
	document.getElementById(id + "_text").style.display = "block";
	document.getElementById(id + "_textarea").style.display = "none";
	document.getElementById(id + "_textediticon").style.display = "inline";
	document.getElementById(id + "_textsaveicon").style.display = "none";
	document.getElementById(id + "_textcancelicon").style.display = "none";
}

/**
 * Print a report comment
 *
 * @param   {string}  reportid
 * @param   {array}   comments
 * @param   {string}  userid
 * @return  {void}
 */
function CRMPrintComment(reportid, comment) { //, userid) {
	var page = document.location.href.split("/")[4];
	if (page == 'crm') {
		comment['comment'] = HighlightMatches(comment['comment']);
	}
	// determine if we should edit comment
	var edit = false;
	if (comment['can']['edit']) {
		edit = true;
	}

	var container = document.getElementById(reportid + '_comments');

	var li = document.createElement("li");
		li.setAttribute('id', 'comment_' + comment['id']);
		li.setAttribute('data-api', comment['api']);

	var panel = document.createElement("div");
	panel.className = "card mb-3 panel panel-default";

	var div, span, a, img;

	if (edit) {
		var tr = document.createElement("div");
		tr.className = 'card-header panel-heading crm-admin';

		span = document.createElement("span");
		span.className = 'crmid';
		span.innerHTML = '#' + comment['id'];

		tr.appendChild(span);

		a = document.createElement("a");
		a.className = 'edit crm-comment-delete tip';
		a.href = "/account/crm?comment=" + comment['id'] + "&delete";
		a.onclick = function (e) {
			e.preventDefault();
			CRMDeleteComment(comment['id'], reportid);
		};
		a.id = comment['id'] + "_commenticon";
		a.title = "Delete comment.";

		img = document.createElement("i");
		img.className = "crmeditdeletecomment fa fa-trash";
		img.setAttribute('aria-hidden', true);
		img.id = comment['id'] + "_commentdeleteimg";

		a.appendChild(img);
		tr.appendChild(a);

		panel.appendChild(tr);
	}

	div = document.createElement("div");
	div.className = "card-body panel-body crmcomment crmcommenttext";

	span = document.createElement("span");
	span.id = comment['id'] + "_comment";
	span.innerHTML = comment['formattedcomment'];

	div.appendChild(span);

	if (edit) {
		// Edit button
		a = document.createElement("a");
		a.href = "/account/crm?id=" + reportid + "#" + comment['id'];
		a.onclick = function (e) {
			e.preventDefault();
			CRMEditCommentTextOpen(comment['id']);
		};
		a.id = comment['id'] + "_commenttextediticon";
		a.title = "Edit comment.";

		img = document.createElement("i");
		img.className = "crmedittextcomment fa fa-pencil";
		img.setAttribute('aria-hidden', true);
		img.id = comment['id'] + "_commenttextediticonimg";

		a.appendChild(img);
		div.appendChild(a);

		// Text box
		span = document.createElement("span");

		var textarea = document.createElement("textarea");
		textarea.id = comment['id'] + "_commenttextarea";
		textarea.innerHTML = comment['comment'];
		textarea.style.display = "none";
		textarea.className = "form-control crmcommentedittextbox";

		span.appendChild(textarea);
		div.appendChild(span);

		// Save button
		a = document.createElement("a");
		a.href = "/account/crm?id=" + reportid + "#" + comment['id'];
		a.onclick = function (e) {
			e.preventDefault();
			CRMSaveCommentText(comment['id']);
		};
		a.id = comment['id'] + "_commenttextsaveicon";
		a.style.display = "none";
		a.title = "Edit comment text.";

		img = document.createElement("i");
		img.className = "crmsavetext fa fa-save";
		img.setAttribute('aria-hidden', true);
		img.id = comment['id'] + "_commenttextsaveiconimg";

		a.appendChild(img);
		div.appendChild(a);
	}

	panel.appendChild(div);

	// Add comment footer
	div = document.createElement("div");
	div.className = "card-footer panel-footer";

	var bits = comment['datetimecreated'].match(/\d+/g);
	var d = new Date(bits[0], bits[1] - 1, bits[2], bits[3], bits[4], bits[5], 0);

	var div2 = document.createElement("div");
	div2.innerHTML += "Posted by " + comment['username'] + " on " + d.getMonthName() + " " + d.getDate() + ", " + d.getFullYear();
	div2.className = "crmcommentpostedby";

	div.appendChild(div2);
	panel.appendChild(div);

	li.appendChild(panel);

	// Attach comment to list
	container.appendChild(li);
}

/**
 * Delete a report comment
 *
 * @param   {string}  commentid
 * @param   {string}  reportid
 * @return  {void}
 */
function CRMDeleteComment(commentid, reportid) {
	if (confirm("Are you sure you want to delete this comment?")) {
		WSDeleteURL(document.getElementById('comment_' + commentid).getAttribute('data-api'), CRMDeletedComment, { 'commentid': commentid, 'reportid': reportid });
	}
}

/**
 * Callback after deleting a report comment
 *
 * @param   {object}  xml
 * @param   {array}   arg
 * @return  {void}
 */
function CRMDeletedComment(xml, arg) {
	if (xml.status < 400) {
		document.getElementById('comment_' + arg['commentid']).style.display = "none";

		WSGetURL(document.getElementById(arg['reportid']).getAttribute('data-api'), function (xml) {
			if (xml.status < 400) {
				var results = JSON.parse(xml.responseText);
				var div, a;

				if (results['subscribed'] == '0') {
					div = document.getElementById(arg['reportid'] + "_subscribed");

					a = document.createElement("a");
					a.href = "?id=" + arg['reportid'] + "&subscribe";
					a.className = 'btn btn-default btn-sm';
					a.onclick = function (e) {
						e.preventDefault();
						CRMSubscribeComment(arg['reportid']);
					};
					a.innerHTML = "Subscribe";

					div.appendChild(a);
				} else if (results['subscribed'] == '2') {
					div = document.getElementById(arg['reportid'] + "_subscribed");

					a = document.createElement("a");
					a.href = "?id=" + arg['reportid'] + "&unsubscribe";
					a.className = 'btn btn-default btn-sm';
					a.onclick = function (e) {
						e.preventDefault();
						CRMUnsubscribeComment(results['subscribedcommentid'], arg['reportid']);
					};
					a.innerHTML = "Unsubscribe";

					div.appendChild(a);
				}
			}
		});
	} else if (xml.status == 403) {
		document.getElementById(arg['reportid'] + "_commentdeleteimg").className = "fa fa-exclamation-circle";
		document.getElementById(arg['reportid'] + "_commentdeleteimg").parentNode.title = "Unable to save changes, grace editing window has passed.";
	} else {
		document.getElementById(arg['commentid'] + "_commentdeleteimg").className = "fa fa-exclamation-circle";
		document.getElementById(arg['commentid'] + "_commentdeleteimg").parentNode.title = "An error occurred while deleting comment.";
	}
}

/**
 * Delete a report
 *
 * @param   {string}  reportid
 * @return  {void}
 */
function CRMDeleteReport(reportid) {
	if (confirm("Are you sure you want to delete this report?")) {
		WSDeleteURL(document.getElementById(reportid).getAttribute('data-api'), function(xml, reportid) {
			if (xml.status < 400) {
				document.getElementById(reportid).style.display = "none";
			} else if (xml.status == 403) {
				document.getElementById(reportid + "_crmdeleteimg").className = "fa fa-exclamation-circle";
				document.getElementById(reportid + "_crmdeleteimg").parentNode.title = "Unable to save changes, grace editing window has passed.";
			} else {
				document.getElementById(reportid + "_crmdeleteimg").className = "fa fa-exclamation-circle";
				document.getElementById(reportid + "_crmdeleteimg").parentNode.title = "An error occurred while deleting report.";
			}
		}, reportid);
	}
}

/**
 * Post a report comment
 *
 * @param   {string}  reportid
 * @return  {void}
 */
function CRMPostComment(reportid) {
	var comment = document.getElementById(reportid + "_newcommentbox");

	var post = JSON.stringify({
		'contactreportid': reportid,
		'comment': comment.value
	});

	WSPostURL(comment.getAttribute('data-api'), post, function(xml, reportid) {
		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);

			CRMPrintComment(reportid, results);
			document.getElementById(reportid + "_newcommentbox").value = "";
			CRMCollapseNewComment(reportid + "_newcommentbox");

			var div = document.getElementById(reportid + "_subscribed");
			div.innerHTML = "Subscribed";
		} else {
			document.getElementById(reportid + "_newcommentboxsave").className = "fa fa-exclamation-circle";
			document.getElementById(reportid + "_newcommentboxsave").parentNode.title = "An error occured while posting comment.";
		}
	}, reportid);
}

/**
 * Subscribe to report comments
 *
 * @param   {string}  reportid
 * @return  {void}
 */
function CRMSubscribeComment(reportid) {
	var post = JSON.stringify({
		'contactreportid': reportid,
		'comment': ''
	});

	WSPostURL(document.getElementById('reports').getAttribute('data-comments'), post, function(xml, reportid) {
		if (xml.status < 400) {
			var div = document.getElementById(reportid + "_subscribed");
			var results = JSON.parse(xml.responseText);
			var a = div.getElementsByTagName("a")[0];
			a.onclick = function (e) {
				e.preventDefault();
				CRMUnsubscribeComment(results.id, reportid);
			};
			a.innerHTML = "Unsubscribe";
		} else {
			var results = JSON.parse(xml.responseText);
			alert(results.message);
		}
	}, reportid);
}

/**
 * Unsubscribe to report comments
 *
 * @param   {string}  commentid
 * @param   {string}  reportid
 * @return  {void}
 */
function CRMUnsubscribeComment(commentid, reportid) {
	WSDeleteURL(document.getElementById('reports').getAttribute('data-comments') + "/" + commentid, function(xml, reportid) {
		if (xml.status < 400) {
			var div = document.getElementById(reportid + "_subscribed");
			var a = div.getElementsByTagName("a")[0];
			a.onclick = function (e) {
				e.preventDefault();
				CRMSubscribeComment(reportid);
			};
			a.innerHTML = "Subscribe";
		}
	}, reportid);
}

/**
 * Toggle controls open for editing report text
 *
 * @param   {string}  report
 * @return  {void}
 */
function CRMEditReportTextOpen(report) {
	// hide text
	var text = document.getElementById(report + "_text");
	document.getElementById(report + "_textarea").style.height = (25 + text.parentNode.offsetHeight) + "px";
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(report + "_textarea");
	box.style.display = "block";

	// hide edit icon
	var eicon = document.getElementById(report + "_textediticon");
	eicon.style.display = "none";

	// show save icon
	var sicon = document.getElementById(report + "_textsaveicon");
	sicon.style.display = "inline";

	var cicon = document.getElementById(report + "_textcancelicon");
	cicon.style.display = "inline";
}

/**
 * Toggle controls open for editing comment text
 *
 * @param   {string}  comment
 * @return  {void}
 */
function CRMEditCommentTextOpen(comment) {
	// hide text
	var text = document.getElementById(comment + "_comment");
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(comment + "_commenttextarea");
	box.style.display = "block";

	// hide edit icon
	var eicon = document.getElementById(comment + "_commenttextediticon");
	eicon.style.display = "none";

	// show save icon
	var sicon = document.getElementById(comment + "_commenttextsaveicon");
	sicon.style.display = "block";

	var img = document.getElementById(comment + "_commenttextsaveiconimg");
	img.className = "fa fa-save";
	img.parentNode.title = "Click to save changes.";
}

/**
 * Save edited report text
 *
 * @param   {string}  report
 * @return  {void}
 */
function CRMSaveReportText(report, api) {
	// get text
	var text = document.getElementById(report + "_textarea").value;

	// change save icon
	var icon = document.getElementById(report + "_textsaveicon");
	icon.onclick = function () { };
	var img = document.getElementById(report + "_textsaveiconimg");
	img.className = "fa fa-spinner fa-spin";
	img.parentNode.title = "Saving changes...";

	var post = { 'report': text };
	post = JSON.stringify(post);

	WSPutURL(api, post, function(xml, report) {
		var img = document.getElementById(report + "_textsaveiconimg");

		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);

			var icon = document.getElementById(report + "_textsaveicon");
			icon.onclick = function () {
				CRMSaveReportText(report, api);
			};
			icon.style.display = "none";

			var text = document.getElementById(report + "_text");
			text.style.display = "block";
			text.innerHTML = results.formattedreport;

			document.getElementById(report + "_textarea").style.display = "none";
			document.getElementById(report + "_textediticon").style.display = "block";
			document.getElementById(report + "_textcancelicon").style.display = "none";
		} else if (xml.status == 403) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, grace editing window has passed.";
		} else {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, reload the page and try again.";
		}
	}, report);
}

/**
 * Save edited comment text
 *
 * @param   {string}  comment
 * @return  {void}
 */
function CRMSaveCommentText(comment) {
	// get text
	var text = document.getElementById(comment + "_commenttextarea").value;

	// change save icon
	var icon = document.getElementById(comment + "_commenttextsaveicon");
	icon.onclick = function () { };
	var img = document.getElementById(comment + "_commenttextsaveiconimg");
	img.className = "fa fa-spinner fa-spin";
	img.parentNode.title = "Saving changes...";

	var post = { 'comment': text };
	post = JSON.stringify(post);

	WSPutURL(document.getElementById('comment_' + comment).getAttribute('data-api'), post, function(xml, comment) {
		var img = document.getElementById(comment + "_commenttextsaveiconimg");

		if (xml.status == 200) {
			var results = JSON.parse(xml.responseText);

			var icon = document.getElementById(comment + "_commenttextsaveicon");
			icon.style.display = "none";
			icon.onclick = function () {
				CRMSaveCommentText(comment);
			};

			var text = document.getElementById(comment + "_comment");
			text.style.display = "block";
			text.innerHTML = results.formattedcomment;

			var box = document.getElementById(comment + "_commenttextarea");
			box.style.display = "none";

			var editicon = document.getElementById(comment + "_commenttextediticon");
			editicon.style.display = "block";
		} else if (xml.status == 403) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, grace editing window has passed.";
		} else {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, reload the page and try again.";
		}
	}, comment);
}

/**
 * Expand comment box
 *
 * @param   {string}  comment
 * @return  {void}
 */
function CRMExpandNewComment(comment) {
	var textarea = document.getElementById(comment);
	textarea.className = "form-control crmcommentboxexpand";
	var img = document.getElementById(comment + "save");
	img.className = "crmnewcommentsave fa fa-save";
	img.style.display = "block";
	img.parentNode.title = "Add new comment.";
}

/**
 * Collapse comment box
 *
 * @param   {string}  comment
 * @return  {void}
 */
function CRMCollapseNewComment(comment) {
	var textarea = document.getElementById(comment);
	if (textarea.value == "") {
		textarea.className = "form-control crmcommentbox";
		var img = document.getElementById(comment + "save");
		img.style.display = "none";
	}
}

/**
 * Clear search values
 *
 * @return  {void}
 */
function CRMClearSearch() {
	var x = 0,
		y = 0;

	document.getElementById("keywords").value = "";
	document.getElementById("datestartshort").value = "";
	document.getElementById("datestopshort").value = "";
	document.getElementById("id").value = "";
	document.getElementById("NotesText").value = "";

	var data = $('#crm-search-data');
	var sdata = JSON.parse(data.html());
	var skip = false;

	var groupsdata = document.getElementById("group").value.split(',');
	for (x = 0; x < groupsdata.length; x++) {
		if (groupsdata[x] != "") {
			skip = false;

			if (document.getElementById("TAB_follow").className.match(/active/)) {
				for (y = 0; y < sdata.followerofgroups.length; y++) {
					if (groupsdata[x] == sdata.followerofgroups[y]['id']) {
						skip = true;
					}
				}
			}

			if (!skip) {
				CRMRemoveGroup(groupsdata[x], false);
			}
		}
	}

	var people_divs = document.getElementById("people").value.split(',');
	for (x = people_divs.length - 1; x >= 0; x--) {
		if (people_divs[x] != "") {
			skip = false;

			if (document.getElementById("TAB_follow").className.match(/active/)) {
				for (y = 0; y < sdata.followerofusers.length; y++) {
					if (people_divs[x] == sdata.followerofusers[y]['id']) {
						skip = true;
					}
				}
			}

			if (!skip) {
				CRMRemoveUser(people_divs[x], false);
			}
		}
	}

	var resources = document.getElementById("crmresource");
	if (resources) {
		resources.value = '';
		if ($('.tagsinput').length) {
			$(resources).clearTags();
		}
	}

	var type = document.getElementById("crmtype");
	if (type) {
		type.value = '-1';
	}

	if (window.location.href.match(/edit/)) {
		window.location = window.location.href.replace(/&edit/, "&search");
	} else if (document.getElementById("TAB_follow").className.match(/active/)) {
		var xml = null;

		for (x = 0; x < sdata.followerofgroups.length; x++) {
			skip = false;

			for (y = 0; y < groupsdata.length; y++) {
				if (groupsdata[y] == sdata.followerofgroups[x]['id']) {
					skip = true;
				}
			}

			if (!skip) {
				// fake WS call
				xml = new Object();
				xml.responseText = JSON.stringify({data: sdata.followerofgroups[x]});
				xml.status = 200;
				CRMSearchGroup(xml, { 'pageload': true, 'disabled': false });
			}
		}

		for (x = 0; x < sdata.followerofusers.length; x++) {
			skip = false;

			for (y = 0; y < people_divs.length; y++) {
				if (people_divs[y] != "") {
					if (people_divs[y] == sdata.followerofusers[x]['id']) {
						skip = true;
					}
				}
			}

			if (!skip) {
				// fake WS call
				xml = new Object();
				xml.responseText = JSON.stringify({data: sdata.followerofusers[x]});
				xml.status = 200;
				CRMSearchUser(xml, { 'pageload': true, 'disabled': false });
			}
		}

		document.getElementById("INPUT_add").disabled = true;
	}

	setTimeout(function () {
		CRMSearch();
	}, 200);
}

/**
 * Get and return array of objects
 *
 * @return  {array}
 */
var autocompleteList = function (url, key) {
	return function (request, response) {
		return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
			response($.map(data.data, function (el) {
				if (typeof (el.id) == 'undefined' && typeof (el.usernames) != 'undefined') {
					el.id = el.usernames[0]['name'];
				}
				return {
					label: el.name,
					name: el.name,
					id: el.id,
				};
			}));
		});
	};
};

/**
 * Get and return array of resource objects
 *
 * @return  {array}
 */
var autocompleteResource = function (url) {
	return function (request, response) {
		return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
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

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	var frm = document.getElementById('DIV_crm');
	if (frm) {
		usersearch = false;
		groupsearch = true;
		multi_group = true;

		$('.date-pick').on('change', function () {
			CRMDateSearch();
		});
		$('#keywords,#id').on('keyup', function (event) {
			CRMKeywordSearch(event.keyCode);
		});
		$('#NotesText').on('keyup', function () {
			CRMToggleAddButton();
		});

		$('.clickto').on('click', function (event) {
			event.preventDefault();
			CRMToggleSearch($(this).data('subject'));
		});

		$('#btn-search').on('click', function (event) {
			event.preventDefault();
			CRMSearch();
		});
		$('.btn-clear').on('click', function (event) {
			event.preventDefault();
			CRMClearSearch();
		});

		$('#INPUT_add').on('click', function (event) {
			event.preventDefault();
			CRMAddEntry();
		});
		$('#crmtype').on('change', function (event) {
			event.preventDefault();
			CRMSearch();
		});

		var group = $("#group");
		if (group.length) {
			group.tagsInput({
				placeholder: 'Select group...',
				importPattern: /([^:]+):(.+)/i,
				limit: 1,
				'autocomplete': {
					source: autocompleteList(group.attr('data-uri') + '&api_token=' + document.querySelector('meta[name="api-token"]').getAttribute('content'), 'groups'),
					dataName: 'groups',
					height: 150,
					delay: 100,
					minLength: 1,
					//maxLength: 1
				},
				'onAddTag': function () { //input, value
					CRMSearch();
				},
				'onRemoveTag': function () { //input, value
					CRMSearch();
				}
			});
		}

		var people = $("#people");
		if (people.length) {
			people.tagsInput({
				placeholder: '',
				importPattern: /([^:]+):(.+)/i,
				'autocomplete': {
					source: autocompleteList(people.attr('data-uri') + '&api_token=' + document.querySelector('meta[name="api-token"]').getAttribute('content'), 'users'),
					dataName: 'users',
					height: 150,
					delay: 100,
					minLength: 1
				},
				'onAddTag': function () { //input, value
					CRMSearch();
				},
				'onRemoveTag': function () { //input, value
					CRMSearch();
				}
			});
		}

		var crmresource = $("#crmresource");
		if (crmresource.length) {
			crmresource.tagsInput({
				placeholder: '',
				importPattern: /([^:]+):(.+)/i,
				'autocomplete': {
					source: autocompleteResource(crmresource.attr('data-uri') + '&api_token=' + document.querySelector('meta[name="api-token"]').getAttribute('content')),
					dataName: 'resources',
					height: 150,
					delay: 100,
					minLength: 1
				},
				'onAddTag': function () {
					CRMSearch();
				},
				'onRemoveTag': function () {
					CRMSearch();
				}
			});
		}

		var data = $('#crm-search-data');
		var sdata = JSON.parse(data.html());
		var x;

		if (sdata.length) {
			if (sdata.groups.length) {
				for (x = 0; x < sdata.groups.length; x++) {
					WSGetURL(group.data('api') + '/' + sdata.groups[x], CRMSearchGroup, { 'pageload': true, 'disabled': false });
				}
			}
			if (sdata.people.length) {
				//CRMToggleSearch('people');
				//CRMToggleSearch('none');

				for (x = 0; x < sdata.people.length; x++) {
					WSGetURL(people.data('api') + '/' + sdata.people[x], CRMSearchUser, { 'pageload': true, 'disabled': false });
				}
			}
			if (sdata.resources.length) {
				for (x = 0; x < sdata.resources.length; x++) {
					WSGetURL(crmresource.data('api') + '/' + sdata.resources[x], CRMSearchResource, { 'pageload': true, 'disabled': false });
				}
			}
		}

		data = $('#crm-data');
		if (data.length) {
			//var edit = true;
			var orig = JSON.parse(data.html());
			var original = orig.original;

			document.getElementById('datestartshort').value = original.datetimecontact;//.substring(0, 10);
			document.getElementById('NotesText').value = original.note;

			$("#crmtype > option").each(function () {
				if (this.value == original['contactreporttypeid']) {
					$('#crmtype > option:selected', 'select[name="options"]').removeAttr('selected');
					$(this).attr('selected', true);
				}
			});

			//CRMToggleSearch('people');
			//CRMToggleSearch('none');

			for (x = 0; x < original.users.length; x++) {
				/*if (original.users[x]['age'] > 86400) {
					WSGetURL(original.users[x]['user'], CRMSearchUser, { 'pageload': true, 'disabled': true });
				}
				else {*/
				WSGetURL(people.data('api') + '/' + original.users[x]['userid'], CRMSearchUser, { 'pageload': true, 'disabled': false });
				//}
			}

			if (original.groupid > 0) {
				multi_group = false;

				/*if (original.groupage > 86400) {
					WSGetURL(original.group, CRMSearchGroup, { 'pageload': true, 'disabled': true });
				}
				else {*/
				WSGetURL(group.data('api') + '/' + original.groupid, CRMSearchGroup, { 'pageload': true, 'disabled': false });
				//}
			}

			for (x = 0; x < original.resources.length; x++) {
				WSGetURL(crmresource.data('api') + '/' + original.resources[x]['resourceid'], CRMSearchResource, { 'pageload': true, 'disabled': false });
			}

			if (original.age > 86400) {
				document.getElementById('datestartshort').disabled = true;
			}

			CRMToggle('edit', false);
		}
	}

	var tabs = document.querySelectorAll('.crm-tabs a');

	if (tabs.length) {
		for (var i = 0; i < tabs.length; i++) {
			tabs[i].addEventListener('click', function (event) {
				event.preventDefault();

				CRMToggle(this.getAttribute('href').replace('#', ''));
			});
		}

		var url = window.location.href.match(/[&?](\w+)$/);
		if (url != null && activetab != url[1]) {
			CRMToggle(url[1]);
			setTimeout(function () {
				CRMSearch();
			}, 300);
		}

		$('.date-pick').on('change', function () {
			CRMDateSearch();
		});

		$('#tabMain')
			.on('paste', '.crmsearch', function () {
				ChangeSearch(0);
			})
			.on('keyup', '.crmsearch', function (event) {
				ChangeSearch(event.keyCode);
			})
			.on('focus', '.crmsearch', function () {
				ChangeSearch(0);
			})
			.on('blur', '.crmsearch', function () {
				CRMToggleSearch('none');
			});
	}

	var container = $('#reports');
	if (container.length && !window.location.href.match(/[&?](\w+)$/)) {
		/*var q = '';
		if (container.data('query')) {
			q = '?' + encodeURI($('#reports').data('query'));
		}

		WSGetURL(container.data('api') + q, CRMSearched);*/
		CRMToggle('search');
	}
});
