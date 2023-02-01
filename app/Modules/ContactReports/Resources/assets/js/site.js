/* global $ */ // jquery.js

var root = document.querySelector('meta[name="base-url"]').getAttribute('content') + "/api/";
var keywords_pending = 0;
var multi_group = false;
var activetab = null;
var headers = {
	'Content-Type': 'application/json'
};

/**
 * Used to highlight stemmed keyword matches in news text
 *
 * @param   {string}  text
 * @return  {string}
 */
function HighlightMatches(text) {
	var search = document.getElementById("keywords").value;
	if (search.replace(' ', '') == "") {
		return text;
	}

	// Filter out any bad characters
	search = search.replace(/[^a-zA-Z0-9_ ]/g, '');
	var keywords = search.split(/ /);

	for (var i = 0; i < keywords.length; i++) {
		keywords[i] = stemmer(keywords[i]).toLowerCase();
	}

	// amethyst, sky, green, honeydew, jade, lime, mallow, orpiment, 
	// pink, red, blue, turquoise, uranium, wine, yellow
	var colors = [
		'rgb(240,163,255)', 'rgb(94,241,242)', 'rgb(43,206,72)', 'rgb(255,204,153)',
		'rgb(148,255,181)', 'rgb(157,204,0)', 'rgb(194,0,136)', 'rgb(255,164,5)', 'rgb(255,168,187)',
		'rgb(255,0,16)', 'rgb(0,117,220)', 'rgb(0,153,143)', 'rgb(224,255,102)', 'rgb(153,0,0)', 'rgb(255,225,0)'
	];

	var regx = new RegExp(/(<[^>]+>)|((^|\b)([^<]+?)(\b|$))/i);
	var m;
	var txt = "";
	var temp = "";
	var keyid = 0;
	var color = "";
	// iterate through matches
	while ((m = regx.exec(text))) {
		txt = m[0];
		keyid = keywords.indexOf(stemmer(txt).toLowerCase());
		if (keyid != -1) {
			// if number of keywords exceeds color array, loop back around
			color = colors[keyid % colors.length];
			// include everything that was skipped and the match
			temp += text.substr(0, m.index) + "<span style='background-color:" + color + "'>" + txt + "</span>";
		} else {
			temp += text.substr(0, m.index) + txt
		}
		text = text.substr(m.index + m[0].length);
	}
	temp += text;

	return temp;
}

// Porter stemmer in Javascript. Few comments, but it's easy to follow against the rules in the original
// paper, in
//
//  Porter, 1980, An algorithm for suffix stripping, Program, Vol. 14,
//  no. 3, pp 130-137,
//
// see also http://www.tartarus.org/~martin/PorterStemmer

// Release 1 be 'andargor', Jul 2004
// Release 2 (substantially revised) by Christopher McKenzie, Aug 2009

var stemmer = (function () {
	var step2list = {
		"ational": "ate",
		"tional": "tion",
		"enci": "ence",
		"anci": "ance",
		"izer": "ize",
		"bli": "ble",
		"alli": "al",
		"entli": "ent",
		"eli": "e",
		"ousli": "ous",
		"ization": "ize",
		"ation": "ate",
		"ator": "ate",
		"alism": "al",
		"iveness": "ive",
		"fulness": "ful",
		"ousness": "ous",
		"aliti": "al",
		"iviti": "ive",
		"biliti": "ble",
		"logi": "log"
	},

		step3list = {
			"icate": "ic",
			"ative": "",
			"alize": "al",
			"iciti": "ic",
			"ical": "ic",
			"ful": "",
			"ness": ""
		},

		c = "[^aeiou]",          // consonant
		v = "[aeiouy]",          // vowel
		C = c + "[^aeiouy]*",    // consonant sequence
		V = v + "[aeiou]*",      // vowel sequence

		mgr0 = "^(" + C + ")?" + V + C,               // [C]VC... is m>0
		meq1 = "^(" + C + ")?" + V + C + "(" + V + ")?$",  // [C]VC[V] is m=1
		mgr1 = "^(" + C + ")?" + V + C + V + C,       // [C]VCVC... is m>1
		s_v = "^(" + C + ")?" + v;                   // vowel in stem

	return function (w) {
		var stem,
			suffix,
			firstch,
			re,
			re2,
			re3,
			re4,
			fp;

		if (w.length < 3) {
			return w;
		}

		w = w.toLowerCase();

		firstch = w.substr(0, 1);
		if (firstch == "y") {
			w = firstch.toUpperCase() + w.substr(1);
		}

		// Step 1a
		re = /^(.+?)(ss|i)es$/;
		re2 = /^(.+?)([^s])s$/;

		if (re.test(w)) {
			w = w.replace(re, "$1$2");
		}
		else if (re2.test(w)) {
			w = w.replace(re2, "$1$2");
		}

		// Step 1b
		re = /^(.+?)eed$/;
		re2 = /^(.+?)(ed|ing)$/;
		if (re.test(w)) {
			fp = re.exec(w);
			re = new RegExp(mgr0);
			if (re.test(fp[1])) {
				re = /.$/;
				w = w.replace(re, "");
			}
		} else if (re2.test(w)) {
			fp = re2.exec(w);
			stem = fp[1];
			re2 = new RegExp(s_v);
			if (re2.test(stem)) {
				w = stem;
				re2 = /(at|bl|iz)$/;
				re3 = new RegExp("([^aeiouylsz])\\1$");
				re4 = new RegExp("^" + C + v + "[^aeiouwxy]$");
				if (re2.test(w)) {
					w = w + "e";
				}
				else if (re3.test(w)) {
					re = /.$/;
					w = w.replace(re, "");
				}
				else if (re4.test(w)) {
					w = w + "e";
				}
			}
		}

		// Step 1c
		re = /^(.+?)y$/;
		if (re.test(w)) {
			fp = re.exec(w);
			stem = fp[1];
			re = new RegExp(s_v);
			if (re.test(stem)) {
				w = stem + "i";
			}
		}

		// Step 2
		re = /^(.+?)(ational|tional|enci|anci|izer|bli|alli|entli|eli|ousli|ization|ation|ator|alism|iveness|fulness|ousness|aliti|iviti|biliti|logi)$/;
		if (re.test(w)) {
			fp = re.exec(w);
			stem = fp[1];
			suffix = fp[2];
			re = new RegExp(mgr0);
			if (re.test(stem)) {
				w = stem + step2list[suffix];
			}
		}

		// Step 3
		re = /^(.+?)(icate|ative|alize|iciti|ical|ful|ness)$/;
		if (re.test(w)) {
			fp = re.exec(w);
			stem = fp[1];
			suffix = fp[2];
			re = new RegExp(mgr0);
			if (re.test(stem)) {
				w = stem + step3list[suffix];
			}
		}

		// Step 4
		re = /^(.+?)(al|ance|ence|er|ic|able|ible|ant|ement|ment|ent|ou|ism|ate|iti|ous|ive|ize)$/;
		re2 = /^(.+?)(s|t)(ion)$/;
		if (re.test(w)) {
			fp = re.exec(w);
			stem = fp[1];
			re = new RegExp(mgr1);
			if (re.test(stem)) {
				w = stem;
			}
		} else if (re2.test(w)) {
			fp = re2.exec(w);
			stem = fp[1] + fp[2];
			re2 = new RegExp(mgr1);
			if (re2.test(stem)) {
				w = stem;
			}
		}

		// Step 5
		re = /^(.+?)e$/;
		if (re.test(w)) {
			fp = re.exec(w);
			stem = fp[1];
			re = new RegExp(mgr1);
			re2 = new RegExp(meq1);
			re3 = new RegExp("^" + C + v + "[^aeiouwxy]$");
			if (re.test(stem) || (re2.test(stem) && !(re3.test(stem)))) {
				w = stem;
			}
		}

		re = /ll$/;
		re2 = new RegExp(mgr1);
		if (re.test(w) && re2.test(w)) {
			re = /.$/;
			w = w.replace(re, "");
		}

		// and turn initial Y back to y

		if (firstch == "y") {
			w = firstch.toLowerCase() + w.substr(1);
		}

		return w;
	}
})();

/**
 * Put an error into the action bar
 *
 * @param   {string}  title
 * @param   {string}  message
 */
function DisplayError(title, message) {
	var span = document.getElementById("crm_action");
	if (span) {
		span.classList.remove('d-none');
		span.innerHTML = '<strong>' + title + '</strong><br />' + message;
	}
}

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

	document.querySelectorAll('.tab-add').forEach(function (el) {
		el.classList.add('d-none');
	});
	document.querySelectorAll('.tab-edit').forEach(function (el) {
		el.classList.add('d-none');
	});
	document.querySelectorAll('.tab-follow').forEach(function (el) {
		el.classList.add('d-none');
	});
	document.querySelectorAll('.tab-search').forEach(function (el) {
		el.classList.add('d-none');
	});
	document.querySelectorAll('.tab-' + on).forEach(function (el) {
		el.classList.remove('d-none');
	});

	activetab = on;

	document.querySelectorAll('.tab').forEach(function (el) {
		el.classList.remove('activeTab');
		el.classList.remove('active');
	});

	document.getElementById("INPUT_clear").value = document.getElementById("INPUT_clear").getAttribute('data-txt-' + on);
	document.getElementById("INPUT_add").value = document.getElementById("INPUT_add").getAttribute('data-txt-' + on);
	document.getElementById("TAB_add").innerHTML = document.getElementById("TAB_add").getAttribute('data-txt-' + on);
	document.getElementById("SPAN_header").innerHTML = document.getElementById("SPAN_header").getAttribute('data-txt-' + on);

	var tab = document.getElementById("TAB_" + (on == 'edit' ? 'add' : on));
	tab.classList.add('activeTab');
	tab.classList.add('active');

	if (on == 'search') {
		document.getElementById("datestartshort").disabled = false;

		multi_group = true;
	} else if (on == 'add') {
		document.getElementById("datestartshort").disabled = false;

		multi_group = false;
	} else if (on == 'edit') {
		multi_group = false;
	} else if (on == 'follow') {
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
 * @param   {object}  results
 * @param   {array}   flags
 * @return  {void}
 */
function CRMSearchGroup(results, flags) {
	var pageload = false;
	//var disabled = false;

	if (typeof (flags) != 'undefined') {
		pageload = flags['pageload'];
		//disabled = flags['disabled'];
	}

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
}

/**
 * Result handler when adding a new person
 *
 * @param   {object}  results
 * @param   {array}   flags
 * @return  {void}
 */
function CRMSearchUser(results, flags) {
	var pageload = false;
	//var disabled = false;

	if (typeof (flags) != 'undefined') {
		pageload = flags['pageload'];
		//disabled = flags['disabled'];
	}

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
}

/**
 * Result handler function when selecting a resource
 *
 * @param   {object}  results
 * @param   {array}   flags
 * @return  {void}
 */
function CRMSearchResource(results, flags) {
	var pageload = false;
	//var disabled = false;

	if (typeof (flags) != 'undefined') {
		pageload = flags['pageload'];
		//disabled = flags['disabled'];
	}

	if (!pageload) {
		CRMSearch();
		if (document.getElementById("TAB_follow").className.match(/active/)) {
			document.getElementById("INPUT_add").disabled = false;
		}
	}

	// reset search box
	var resource = $('#crmresource');

	resource.val(results.id);
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
		CRMClearSearch();

		// flip the flags so it acts a group search box
		//groupsearch = true;
		//usersearch = false;
		//search_path = "groupname";

		document.getElementById("newuser").focus();
	} else if (on == 'people') {
		// move search box for people
		document.getElementById("newuser").value = "";
		CRMClearSearch();

		// flip the flags so it acts a person search box
		//groupsearch = false;
		//usersearch = true;
		//search_path = "name";

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
			DisplayError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
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
		/*resourcedata = document.getElementById("crmresource").value.split(',');
		for (i = 0; i < resourcedata.length; i++) {
			if (resourcedata[i] != "") {
				if (resourcedata[i].indexOf('/') !== -1) {
					var res = resourcedata[i].split('/');
					resources.push(res[res.length - 1]);
				} else {
					resources.push(resourcedata[i]);
				}
			}
		}*/
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
	resourcedata = Array.prototype.slice.call(document.querySelectorAll('#crmresource option:checked'), 0).map(function (v) {
		return v.value;
	});
	for (i = 0; i < resourcedata.length; i++) {
		if (resourcedata[i] != "") {
			if (resourcedata[i].indexOf('/') !== -1) {
				var resource = resourcedata[i].split('/');
				resources.push(resource[resource.length - 1]);
			} else {
				resources.push(resourcedata[i]);
			}
		}
	}

	notes = document.getElementById("NotesText").value;

	var searchdata = document.getElementById('crm-search-data');
	var sdata = JSON.parse(searchdata.innerHTML);

	var removeusers = Array();
	var addusers = Array();
	var post = {};
	var remove = true,
		add = true;

	if (window.location.href.match(/edit/)) {
		var data = document.getElementById('crm-data');
		var original = {};

		if (data) {
			var orig = JSON.parse(data.innerHTML);
			original = orig.original;
		}

		// update
		post['datetimecontact'] = contactdate;

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
		post['report'] = notes;
		post = JSON.stringify(post);

		if (post != "{}") {
			fetch(original['api'], {
				method: 'PUT',
				headers: headers,
				body: post
			})
				.then(function (response) {
					if (response.ok) {
						CRMUpdatedReport();
						return; //response.json();
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
			fetch(removeusers[x], {
				method: 'DELETE',
				headers: headers
			})
				.then(function (response) {
					if (response.ok) {
						CRMUpdatedReport();
						return; // response.json();
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
			fetch(root + "contactreportfollowuser", {
				method: 'POST',
				headers: headers,
				body: post
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
					CRMUpdatedFollowUser(results);
				})
				.catch(function (err) {
					alert(err);
				});
		}

		for (x = 0; x < removegroups.length; x++) {
			fetch(removegroups[x], {
				method: 'DELETE',
				headers: headers
			})
				.then(function (response) {
					if (response.ok) {
						CRMUpdatedReport();
						return; // response.json();
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
			fetch(root + "contactreportfollowgroup", {
				method: 'POST',
				headers: headers,
				body: post
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
					CRMUpdatedFollowGroup(results);
				})
				.catch(function (err) {
					alert(err);
				});
		}

		return;
	}

	if (people.length == 0) {
		if (groups.length != 0) {
			DisplayError('Required field missing', 'Please enter at least one person.');
			return;
		}
		else if (groups.length == 0) {
			DisplayError('Required field missing', 'Please enter at least one person and optionally a group.');
			return;
		}
	}
	else if (notes == "") {
		DisplayError('Required field missing', 'Please enter some note text.');
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

		if (people.length > 0) {
			post['users'] = people;
		}

		post = JSON.stringify(post);
		document.getElementById("INPUT_add").disabled = true;

		fetch(document.getElementById("reports").getAttribute('data-api'), {
			method: 'POST',
			headers: headers,
			body: post
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
				CRMNewReport(results); //, people);
			})
			.catch(function (err) {
				alert(err);
			});
	}
}

/**
 * Callback after updating a report
 *
 * @return  {void}
 */
function CRMUpdatedReport() {
	document.getElementById("INPUT_add").disabled = true;
}

/**
 * Callback after following a user
 *
 * @param   {object}  results
 * @return  {void}
 */
function CRMUpdatedFollowUser(results) {
	var data = document.getElementById('crm-search-data');
	var sdata = JSON.parse(data.innerHTML);
	sdata.followerofusers.push(results);

	data.innerHTML = JSON.stringify(sdata);

	document.getElementById("INPUT_add").disabled = true;
}

/**
 * Callback after following a group
 *
 * @param   {object}  results
 * @return  {void}
 */
function CRMUpdatedFollowGroup(results) {
	var data = document.getElementById('crm-search-data');
	var sdata = JSON.parse(data.innerHTML);
	sdata.followerofgroups.push(results);

	data.innerHTML = JSON.stringify(sdata);

	document.getElementById("INPUT_add").disabled = true;
}

/**
 * Callback for creating a new report
 *
 * @param   {object}  results
 * @return  {void}
 */
function CRMNewReport(results) {
	document.getElementById("INPUT_add").disabled = false;

	document.getElementById("NotesText").value = "";

	CRMClearSearch();

	document.getElementById("id").value = results.id;

	CRMToggle('search', true);
}

/**
 * Callback for JS MarkDown parsing
 *
 * @param   {string}  text
 * @return  {string}
 */
/* exported customMarkdownParser */
//    The above won't work due to the current .eslintrc.js
//    see: https://stackoverflow.com/questions/37470918/eslint-exported-functionname-not-working-in-browser-env
// eslint-disable-next-line no-unused-vars
function customMarkdownParser(text) {
	text = text.replaceAll(/(^|[^a-z0-9_])#([a-z0-9\-_]+)/ig, '$1<span class="badge badge-secondary">$2</span>');
	text = text.replaceAll(/(contact|CRM?)(\s+report)?\s*#?(\d+)/g, '<a href="?id=$3">Contact Report #$3</a>');

	return text;
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

	//var resourcedata = new Array();
	var resources = new Array();
	var resource = null;

	var tags = new Array();
	var tagdata = new Array();

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

		tagdata = document.getElementById("tag").value.split(',');
		for (i = 0; i < tagdata.length; i++) {
			if (tagdata[i] != "") {
				tags.push(tagdata[i]);
			}
		}

		// Fetch list of selected resources
		/*resourcedata = document.getElementById("crmresource").value.split(',');
		for (i = 0; i < resourcedata.length; i++) {
			if (resourcedata[i] != "") {
				if (resourcedata[i].indexOf('/') !== -1) {
					resource = resourcedata[i].split('/');
					resources.push(resource[resource.length - 1]);
				} else {
					resources.push(resourcedata[i]);
				}
			}
		}*/
	} else {
		groupsdata = document.getElementById("TD_group").getElementsByTagName("div");
		peopledata = document.getElementById("TD_people").getElementsByTagName("div");
		//resourcedata = document.getElementById("TD_resource").getElementsByTagName("div");

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

		/*for (i = 0; i < resourcedata.length; i++) {
			if (resourcedata[i].id.search("RESOURCE_") == 0) {
				resource = resourcedata[i].id.substr(5);
				resource = resource.split('/');
				resources.push(resource[3]);
			}
		}*/
	}

	var resourcedata = Array.prototype.slice.call(document.querySelectorAll('#crmresource option:checked'), 0).map(function (v) {
		return v.value;
	});
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

	// sanity checks
	if (start != "") {
		if (!start.match(/^\d{4}-\d{2}-\d{2}$/)) {
			DisplayError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			// clear error boxes
			document.getElementById("TAB_search_action").innerHTML = "";
			document.getElementById("TAB_add_action").innerHTML = "";
		}
	}
	if (stop != "") {
		if (!stop.match(/^\d{4}-\d{2}-\d{2}$/)) {
			DisplayError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			// clear error boxes
			document.getElementById("TAB_search_action").innerHTML = "";
			document.getElementById("TAB_add_action").innerHTML = "";
		}
	}

	// start assembling string
	var querystring = new Array();

	// if not add new
	if (!document.getElementById("TAB_add").className.match(/active/)) {
		if (start != "") {
			querystring.push("start=" + start);
		}
		if (stop != "") {
			querystring.push("stop=" + stop);
		}
	}
	if (groups.length > 0) {
		var qg = groups[0];
		for (x = 1; x < groups.length; x++) {
			qg += "," + groups[x];
		}
		querystring.push("group=" + qg);
	}
	if (people.length > 0) {
		var qp = people[0];
		for (x = 1; x < people.length; x++) {
			qp += "," + people[x];
		}
		querystring.push("people=" + qp);
	}
	if (tags.length > 0) {
		var qt = tags[0];
		for (x = 1; x < tags.length; x++) {
			qt += "," + tags[x];
		}
		querystring.push("tag=" + qt);
	}
	// Construct resource query
	if (resources.length > 0) {
		var qr = resources[0];
		for (x = 1; x < resources.length; x++) {
			qr += "," + resources[x];
		}
		querystring.push("resource=" + qr);
	}
	if (typeid != '-1') {
		querystring.push("type=" + typeid);
	}
	// if not add new
	if (!document.getElementById("TAB_add").className.match(/active/)) {
		if (keywords != "") {
			// format FP or CR ticket queries correctly
			keywords = keywords.replace(/(FP|CR)#(\d+)/g, '$1 $2');

			// filter out potentially dangerous garbage
			keywords = keywords.replace(/[^a-zA-Z0-9_ ]/g, '');

			querystring.push("search=" + keywords);
		}
	}
	// if not add new
	if (!document.getElementById("TAB_add").className.match(/active/)) {
		if (id.match(/(\d)+/)) {
			querystring.push("id=" + id);
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
		if (tab != null && tab[1] != 'search') {
			querystring.push(tab[1]);
		}

		history.pushState(null, null, encodeURI('?' + querystring.join('&')));
	}

	//if (searchstring == "") {
	//	searchstring = "start:0000-00-00";
	//}

	//console.log('Searching... ' + $("#reports").data('api') + encodeURI(querystring));

	document.getElementById("reports").setAttribute('data-query', querystring.join('&'));//querystring.replace('?', ''));

	querystring.push("page=" + document.getElementById("page").value);

	fetch(document.getElementById("reports").getAttribute('data-api') + encodeURI('?' + querystring.join('&')), {
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
			CRMSearched(results);
		})
		.catch(function (err) {
			alert(err);
		});
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

		if (start != "" && people.length > 0) {
			document.getElementById("INPUT_add").disabled = false;
		} else {
			document.getElementById("INPUT_add").disabled = true;
		}
	}
}

/**
 * Callback after searching
 *
 * @param   {object}  results
 * @return  {void}
 */
function CRMSearched(results) {
	//const DEFAULT_ENTRIES = 20;

	//if (xml.status == 200) {
	var reports = $("#reports");
	var count = 0;

	//const results = JSON.parse(xml.responseText);

	$("#matchingReports").html("Found " + results.data.length + " matching reports");

	if (results.data.length == 0) {
		reports.html('<p class="alert alert-warning">No matching reports found.</p>');
	} else {
		reports.html('');

		var keywords = document.getElementById("keywords").value;
		for (var x = 0; x < results.data.length; x++, count++) {
			if (keywords) {
				var regex = new RegExp('(' + keywords.split(' ').join('|') + ')', "gi");
				results.data[x].formattedreport = results.data[x].formattedreport.replace(regex, '<strong class="highlight">$1</strong>');
			}
			CRMPrintRow(
				results.data[x],
				//results.people,
				//results.comments,
				//results.userid,
				'newEntries' //(x < DEFAULT_ENTRIES ? "newEntries" : "newEntriesHidden")
			);
		}
		//document.dispatchEvent(new Event('initEditor', { bubbles: true }));

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
				id: "displayEntries"
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
			.attr('href', '?page=1&' + query)
			.attr('data-page', 1);
		if (results.meta.total <= (results.meta.per_page * results.meta.current_page) || results.meta.current_page == 1) {
			li.addClass('disabled');
			a.attr('aria-disabled', 'true');
		}
		li.append(a);
		ul.append(li);

		li = $('<li class="page-item page-prev">');
		a = $('<a class="page-link" title="Previous page"><span aria-hidden="true">‹</span></a>')
			.attr('href', '?page=' + (results.meta.current_page > 1 ? results.meta.current_page - 1 : 1) + '&' + query)
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
				.attr('href', '?page=1&' + query)
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
					.attr('href', '?page=' + l + '&' + query)
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
			.attr('href', '?page=' + (results.meta.current_page < lastpage ? lastpage - 1 : 1) + '&' + query)
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
			.attr('href', '?page=' + lastpage + '&' + query)
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
	//}
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
	var edit = false, del = false;
	if (report['can']['edit']) {
		edit = true;
	}
	if (report['can']['delete']) {
		del = true;
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
	a.href = "?id=" + id;
	a.innerHTML = "#" + id;

	td.appendChild(a);

	if (del) {
		// Delete button
		a = document.createElement("a");
		a.href = "?id=" + id + "&delete";
		a.className = 'edit news-delete tip';
		a.onclick = function (e) {
			e.preventDefault();
			CRMDeleteReport(report['id']);
		};
		a.title = "Delete Contact Report.";

		img = document.createElement("span");
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

	var d = new Date(report['datetimecontact']);

	td.innerHTML = d.getMonthName() + " " + d.getDate() + ", " + d.getFullYear();

	if (edit) {
		a = document.createElement("a");
		a.href = "?id=" + report['id'] + "&edit";
		a.className = "news-action tip";
		a.onclick = function (e) {
			e.preventDefault();

			CRMClearSearch();

			if (!window.location.href.match(/contactreports/)) {
				window.location = this.href;
			} else {
				var url = window.location.href.split("?");
				url = url[0];
				window.location = url + "?id=" + report['id'] + "&edit";
			}
		};
		a.title = "Edit contact report date.";

		img = document.createElement("span");
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

	d = new Date(report['datetimecreated']);

	span = document.createElement("span");
	span.className = "crmpostdate";
	span.innerHTML = "Posted on " + d.getMonthName() + " " + d.getDate() + ", " + d.getFullYear();

	li.appendChild(span);
	ul.appendChild(li);

	// Creator
	/*li = document.createElement("li");
	li.className = 'news-author';

	span = document.createElement("span");
	span.className = "crmposter";
	span.innerHTML = "Posted by " + report['username'];

	li.appendChild(span);
	ul.appendChild(li);*/

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
			a.classList.add('badge');
			a.classList.add('badge-' + (report.users[x]['highlight'] ? 'danger' : 'secondary'));

			if (report.users[x]['datetimelastnotify']) {
				a.innerHTML = a.innerHTML + ' <span class="fa fa-envelope" aria-hidden="true" title="Follow up email sent ' + report.users[x]['datetimelastnotify'] + '"></span>';
			}

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

			/*if (edit) {
				a = document.createElement("a");
				a.href = "contactreports/?id=" + report['id'] + "&edit";
				a.className = "news-action tip";
				a.onclick = function (e) {
					e.preventDefault();

					CRMClearSearch();

					if (!window.location.href.match(/contactreports/)) {
						window.location = "contactreports/?id=" + report['id'] + "&edit";
					} else {
						var url = window.location.href.split("?");
						url = url[0];

						window.location = url + "?id=" + report['id'] + "&edit";
					}
				};
				a.title = "Add or remove users and groups.";

				img = document.createElement("span");
				img.className = "crmedit fa fa-pencil";
				img.setAttribute('aria-hidden', true);

				a.appendChild(img);
				li.appendChild(a);
			}*/

			ul.appendChild(li);
		}
	}

	// Resource list
	if (report.resources.length > 0) {
		li = document.createElement("li");
		li.className = 'news-resources';

		/*var icon = document.createElement("span");
		icon.className = "fa fa-server";
		icon.setAttribute('aria-hidden', true);
		li.appendChild(icon);*/

		span = document.createElement("span");
		span.className = "crmpostresources";

		var r = Array();
		for (x = 0; x < report.resources.length; x++) {
			r.push(report.resources[x].name);
		}
		span.innerHTML = r.join(', ');

		li.appendChild(span);

		/*if (edit) {
			a = document.createElement("a");
			a.className = "news-action tip"
			a.href = "contactreports?id=" + id + '&edit';
			a.title = "Edit tagged resources.";

			img = document.createElement("span");
			img.className = "crmedit fa fa-pencil";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);

			span.appendChild(a);
			li.appendChild(span);
		}*/

		ul.appendChild(li);
	}

	// Tags list
	if (report.tags.length > 0) {
		li = document.createElement("li");
		li.className = 'news-tags';

		/*icon = document.createElement("span");
		icon.className = "fa fa-fw fa-tags";
		icon.setAttribute('aria-hidden', true);
		li.appendChild(icon);*/

		span = document.createElement("span");
		span.className = "crmposttags";

		r = Array();
		for (x = 0; x < report.tags.length; x++) {
			r.push('<a href="contactreports?search&tag=' + report.tags[x].slug + '">' + report.tags[x].name + '</a>');
		}
		span.innerHTML = r.join(', ');

		li.appendChild(span);

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
		a.href = "?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			CRMEditReportTextOpen(report['id']);
		};
		a.className = "news-edit icn tip";
		a.title = "Edit report text.";
		a.id = report['id'] + "_textediticon";

		img = document.createElement("span");
		img.className = "crmedittext fa fa-pencil";
		img.setAttribute('aria-hidden', true);
		img.id = report['id'] + "_textediticonimg";

		a.appendChild(img);
		opt.appendChild(a);

		// Save button
		a = document.createElement("a");
		a.href = "?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			CRMSaveReportText(report['id'], report['api']);
		};
		a.className = "news-save icn tip";
		a.id = report['id'] + "_textsaveicon";
		a.title = "Save report text.";
		a.style.display = "none";

		img = document.createElement("span");
		img.className = "crmsavetext fa fa-save";
		img.setAttribute('aria-hidden', true);
		img.id = report['id'] + "_textsaveiconimg";

		a.appendChild(img);
		opt.appendChild(a);

		// Cancel button
		a = document.createElement("a");
		a.href = "?id=" + id;
		a.onclick = function (e) {
			e.preventDefault();
			CRMCancelReportText(id);
		};
		a.className = "news-cancel icn tip";
		a.title = "Cancel edits to text";
		a.id = id + "_textcancelicon";
		a.style.display = "none";

		img = document.createElement("span");
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
	//var page = document.location.href.split("/")[4];
	// if we are in crm, we are doing report searches, so we should highlight matches
	//if (page == 'crm') {
	report['report'] = HighlightMatches(report['report']);
	//}

	span = document.createElement("span");
	span.id = report['id'] + "_text";
	span.innerHTML = report['report'];

	td.appendChild(span);

	span = document.createElement("span");

	var textarea = document.createElement("textarea");
	textarea.id = report['id'] + "_textarea";
	textarea.innerHTML = rawtext;
	//textarea.style.display = "none";
	textarea.rows = 7;
	textarea.cols = 45;
	textarea.className = "form-control md crmreportedittextbox";

	span.appendChild(textarea);
	span.style.display = "none";
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
		a.href = "?id=" + id + "&subscribe";
		a.className = 'btn btn-secondary btn-sm';
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
		a.href = "?id=" + id + "&unsubscribe";
		a.className = 'btn btn-secondary btn-sm';
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
	textarea.className = "form-control md crmcommentbox";
	textarea.placeholder = "Write a comment...";
	textarea.id = report['id'] + "_newcommentbox";
	textarea.setAttribute('data-api', document.getElementById('reports').getAttribute('data-comments'));
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
	a.href = "?comment&id=" + id;
	a.onclick = function (e) {
		e.preventDefault();
		CRMPostComment(report['id']);
	};
	a.title = "Add a new comment.";

	img = document.createElement("span");
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
	document.getElementById(id + "_textarea").parentNode.style.display = "none";
	document.getElementById(id + "_textediticon").style.display = "inline";
	document.getElementById(id + "_textsaveicon").style.display = "none";
	document.getElementById(id + "_textcancelicon").style.display = "none";
}

/**
 * Print a report comment
 *
 * @param   {string}  reportid
 * @param   {array}   comments
 * @return  {void}
 */
function CRMPrintComment(reportid, comment) {
	comment['comment'] = HighlightMatches(comment['comment']);

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
		a.className = 'edit news-delete tip';
		a.href = "?comment=" + comment['id'] + "&delete";
		a.onclick = function (e) {
			e.preventDefault();
			CRMDeleteComment(comment['id'], reportid);
		};
		a.id = comment['id'] + "_commenticon";
		a.title = "Delete comment.";

		img = document.createElement("span");
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
		a.href = "?id=" + reportid + "#" + comment['id'];
		a.onclick = function (e) {
			e.preventDefault();
			CRMEditCommentTextOpen(comment['id']);
		};
		a.id = comment['id'] + "_commenttextediticon";
		a.title = "Edit comment.";

		img = document.createElement("span");
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
		//textarea.style.display = "none";
		textarea.className = "form-control md simplemde crmcommentedittextbox";

		span.appendChild(textarea);
		span.style.display = "none";
		div.appendChild(span);

		// Save button
		a = document.createElement("a");
		a.href = "?id=" + reportid + "#" + comment['id'];
		a.onclick = function (e) {
			e.preventDefault();
			CRMSaveCommentText(comment['id']);
		};
		a.id = comment['id'] + "_commenttextsaveicon";
		a.style.display = "none";
		a.title = "Edit comment text.";

		img = document.createElement("span");
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

	//var bits = comment['datetimecreated'].match(/\d+/g);
	var d = new Date(comment['datetimecreated']);

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
		fetch(document.getElementById('comment_' + commentid).getAttribute('data-api'), {
			method: 'DELETE',
			headers: headers
		})
			.then(function (response) {
				if (response.ok) {
					CRMDeletedComment({ 'commentid': commentid, 'reportid': reportid });
					return;// response.json();
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
				document.getElementById(commentid + "_commentdeleteimg").className = "fa fa-exclamation-circle";
				document.getElementById(commentid + "_commentdeleteimg").parentNode.title = err; //"An error occurred while deleting comment.";
			});
	}
}

/**
 * Callback after deleting a report comment
 *
 * @param   {array}   arg
 * @return  {void}
 */
function CRMDeletedComment(arg) {
	document.getElementById('comment_' + arg['commentid']).style.display = "none";

	fetch(document.getElementById(arg['reportid']).getAttribute('data-api'), {
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
		})
		.catch(function (err) {
			document.getElementById(arg['commentid'] + "_commentdeleteimg").className = "fa fa-exclamation-circle";
			document.getElementById(arg['commentid'] + "_commentdeleteimg").parentNode.title = err; //"An error occurred while deleting comment.";
		});
}

/**
 * Delete a report
 *
 * @param   {string}  reportid
 * @return  {void}
 */
function CRMDeleteReport(reportid) {
	if (confirm("Are you sure you want to delete this report?")) {
		fetch(document.getElementById(reportid).getAttribute('data-api'), {
			method: 'DELETE',
			headers: headers
		})
			.then(function (response) {
				if (response.ok) {
					document.getElementById(reportid).style.display = "none";
					return;// response.json();
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
				document.getElementById(reportid + "_crmdeleteimg").className = "fa fa-exclamation-circle";
				document.getElementById(reportid + "_crmdeleteimg").parentNode.title = err; //"An error occurred while deleting report.";
			});
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

	fetch(comment.getAttribute('data-api'), {
		method: 'POST',
		headers: headers,
		body: post
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
			CRMPrintComment(reportid, results);
			document.getElementById(reportid + "_newcommentbox").value = "";
			CRMCollapseNewComment(reportid + "_newcommentbox");

			var div = document.getElementById(reportid + "_subscribed");
			div.innerHTML = "Subscribed";
		})
		.catch(function (err) {
			document.getElementById(reportid + "_newcommentboxsave").className = "fa fa-exclamation-circle";
			document.getElementById(reportid + "_newcommentboxsave").parentNode.title = err; //"An error occured while posting comment.";
		});
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

	fetch(document.getElementById('reports').getAttribute('data-comments'), {
		method: 'POST',
		headers: headers,
		body: post
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
			var div = document.getElementById(reportid + "_subscribed");
			var a = div.getElementsByTagName("a")[0];
			a.onclick = function (e) {
				e.preventDefault();
				CRMUnsubscribeComment(results.id, reportid);
			};
			a.innerHTML = "Unsubscribe";
		})
		.catch(function (err) {
			alert(err);
		});
}

/**
 * Unsubscribe to report comments
 *
 * @param   {string}  commentid
 * @param   {string}  reportid
 * @return  {void}
 */
function CRMUnsubscribeComment(commentid, reportid) {
	fetch(document.getElementById('reports').getAttribute('data-comments') + "/" + commentid, {
		method: 'DELETE',
		headers: headers
	})
		.then(function (response) {
			if (response.ok) {
				var div = document.getElementById(reportid + "_subscribed");
				var a = div.getElementsByTagName("a")[0];
				a.onclick = function (e) {
					e.preventDefault();
					CRMSubscribeComment(reportid);
				};
				a.innerHTML = "Subscribe";
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

/**
 * Toggle controls open for editing report text
 *
 * @param   {string}  report
 * @return  {void}
 */
function CRMEditReportTextOpen(report) {
	// hide text
	var text = document.getElementById(report + "_text");
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(report + "_textarea");
	box.style.height = (25 + text.parentNode.offsetHeight) + "px";
	box.dispatchEvent(new Event('initEditor', { bubbles: true }));
	box.parentNode.style.display = "block";

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
	box.dispatchEvent(new Event('initEditor', { bubbles: true }));
	box.parentNode.style.display = "block";

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
 * @param   {string}  api
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

	fetch(api, {
		method: 'PUT',
		headers: headers,
		body: post
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
			var icon = document.getElementById(report + "_textsaveicon");
			icon.onclick = function () {
				CRMSaveReportText(report, api);
			};
			icon.style.display = "none";

			img.className = "fa fa-save";
			img.parentNode.title = "Save";

			var text = document.getElementById(report + "_text");
			text.style.display = "block";
			text.innerHTML = results.formattedreport;

			document.getElementById(report + "_textarea").parentNode.style.display = "none";
			document.getElementById(report + "_textediticon").style.display = "block";
			document.getElementById(report + "_textcancelicon").style.display = "none";
		})
		.catch(function (err) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = err; //"Unable to save changes, reload the page and try again.";
		});
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

	fetch(document.getElementById('comment_' + comment).getAttribute('data-api'), {
		method: 'PUT',
		headers: headers,
		body: post
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
			var icon = document.getElementById(comment + "_commenttextsaveicon");
			icon.style.display = "none";
			icon.onclick = function () {
				CRMSaveCommentText(comment);
			};

			var text = document.getElementById(comment + "_comment");
			text.style.display = "block";
			text.innerHTML = results.formattedcomment;

			var box = document.getElementById(comment + "_commenttextarea");
			box.parentNode.style.display = "none";

			var editicon = document.getElementById(comment + "_commenttextediticon");
			editicon.style.display = "block";
		})
		.catch(function (err) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = err; //"Unable to save changes, reload the page and try again.";
		});
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
	document.getElementById("NotesText").dispatchEvent(new Event('refreshEditor', { bubbles: true }));

	var data = document.getElementById('crm-search-data');
	var sdata = JSON.parse(data.innerHTML);
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
		resources.dispatchEvent(new Event('change'));
	}

	var type = document.getElementById("crmtype");
	if (type) {
		type.value = '-1';
	}

	if (window.location.href.match(/edit/)) {
		window.location = window.location.href.replace(/&edit/, ""); //.replace(/id=\d+/, "");
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
				xml.responseText = JSON.stringify(sdata.followerofgroups[x]); //.{data: sdata.followerofgroups[x]});
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
				xml.responseText = JSON.stringify(sdata.followerofusers[x]); //{data: sdata.followerofusers[x]});
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
var autocompleteList = function (url) {
	return function (request, response) {
		return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
			response($.map(data.data, function (el) {
				if (typeof (el.id) == 'undefined' && typeof (el.username) != 'undefined') {
					el.id = el.username;
				}
				if (typeof (el.username) != 'undefined') {
					el.name += ' (' + el.username + ')';
				}
				if (typeof (el.slug) != 'undefined') {
					el.id = el.slug;
				}
				//var regEx = new RegExp("(" + request.term + ")(?!([^<]+)?>)", "gi");
				//el.name = el.name.replace(regEx, '<span class="highlight">$1</span>');
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
/*var autocompleteResource = function (url) {
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
};*/

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	/*if (typeof (smdeConfig) !== 'undefined') {
		smdeConfig.previewRender = function(plainText, preview) {
			preview.innerHTML = plainText.replace('#', '???');
			return preview;
		};
	}*/
	var frm = document.getElementById('DIV_crm');
	if (frm) {
		//usersearch = false;
		//groupsearch = true;
		multi_group = true;

		document.querySelectorAll('.date-pick').forEach(function (el) {
			el.addEventListener('change', function () {
				CRMDateSearch();
			});
		});
		var keywords = document.getElementById('keywords');
		if (keywords) {
			keywords.addEventListener('keyup', function (event) {
				CRMKeywordSearch(event.keyCode);
			});
		}
		var id = document.getElementById('id');
		if (id) {
			id.addEventListener('keyup', function (event) {
				CRMKeywordSearch(event.keyCode);
			});
		}
		var notes = document.getElementById('NotesText');
		if (notes) {
			notes.addEventListener('keyup', function () {
				CRMToggleAddButton();
			});
		}

		document.querySelectorAll('.clickto').forEach(function (el) {
			el.addEventListener('click', function (event) {
				event.preventDefault();
				CRMToggleSearch(this.getAttribute('data-subject'));
			});
		});

		var btnsearch = document.getElementById('btn-search');
		if (btnsearch) {
			btnsearch.addEventListener('click', function (event) {
				event.preventDefault();
				CRMSearch();
			});
		}
		document.querySelectorAll('.btn-clear').forEach(function (el) {
			el.addEventListener('click', function (event) {
				event.preventDefault();
				CRMClearSearch();
			});
		});

		var inputadd = document.getElementById('INPUT_add');
		if (inputadd) {
			inputadd.addEventListener('click', function (event) {
				event.preventDefault();
				CRMAddEntry();
			});
		}
		var crmtype = document.getElementById('crmtype');
		if (crmtype) {
			crmtype.addEventListener('click', function (event) {
				event.preventDefault();
				CRMSearch();
			});
		}

		var tag = $("#tag");
		if (tag.length) {
			tag.tagsInput({
				placeholder: '',
				importPattern: /([^:]+):(.+)/i,
				'autocomplete': {
					source: autocompleteList(tag.attr('data-uri') + '&api_token=' + document.querySelector('meta[name="api-token"]').getAttribute('content'), 'tags'),
					dataName: 'tags',
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

		var group = $("#group");
		if (group.length) {
			group.tagsInput({
				placeholder: '',
				importPattern: /([^:]+):(.+)/i,
				limit: 1,
				'autocomplete': {
					source: autocompleteList(group.attr('data-uri') + '&api_token=' + document.querySelector('meta[name="api-token"]').getAttribute('content'), 'groups'),
					dataName: 'groups',
					height: 150,
					delay: 100,
					minLength: 1,
					open: function () { //e, ui
						var acData = $(this).data('ui-autocomplete');

						acData
							.menu
							.element
							.find('.ui-menu-item-wrapper')
							.each(function () {
								var me = $(this);
								var regex = new RegExp('(' + acData.term + ')', "gi");
								me.html(me.text().replace(regex, '<b>$1</b>'));
							});
					}
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
					minLength: 1,
					open: function () { //e, ui
						var acData = $(this).data('ui-autocomplete');

						acData
							.menu
							.element
							.find('.ui-menu-item-wrapper')
							.each(function () {
								var me = $(this);
								var regex = new RegExp('(' + acData.term + ')', "gi");
								me.html(me.text().replace(regex, '<b>$1</b>'));
							});
					}
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
		/*if (crmresource.length) {
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
		}*/
		var rselects = $(".searchable-select-multi");
		if (rselects.length) {
			$(".searchable-select-multi").select2({
				multiple: true,
				closeOnSelect: false,
				templateResult: function (item) {
					if (typeof item.children != 'undefined') {
						//var s = $(item.element).find('option').length - $(item.element).find('option:selected').length;
						var el = $('<button class="btn btn-sm btn_select2_optgroup" data-group="' + item.text + '">Select All</span>');

						// Click event
						el.on('click', function (e) {
							e.preventDefault();
							// Select all optgroup child if there aren't, else deselect all
							rselects.find('optgroup[label="' + $(this).data('group') + '"] option').prop(
								'selected',
								$(item.element).find('option').length - $(item.element).find('option:selected').length
							);

							// Trigger change event + close dropdown
							rselects.trigger('change.select2');
							rselects.select2('close');
							CRMSearch();
						});

						var elp = $('<span class="my_select2_optgroup">' + item.text + '</span>');
						elp.append(el);

						return elp;
					}
					return item.text;
				}
			})
				.on('select2:select', function () {
					CRMSearch();
				})
				.on('select2:unselect', function () {
					CRMSearch();
				});
		}

		var data = document.getElementById('crm-search-data');
		var sdata = JSON.parse(data.innerHTML);
		var x;

		if (sdata.length) {
			if (sdata.groups.length) {
				for (x = 0; x < sdata.groups.length; x++) {
					fetch(group.data('api') + '/' + sdata.groups[x], {
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
							CRMSearchGroup(results, { 'pageload': true, 'disabled': false });
						})
						.catch(function (err) {
							alert(err);
						});
				}
			}
			if (sdata.people.length) {
				//CRMToggleSearch('people');
				//CRMToggleSearch('none');

				for (x = 0; x < sdata.people.length; x++) {
					fetch(people.data('api') + '/' + sdata.people[x], {
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
							CRMSearchUser(results, { 'pageload': true, 'disabled': false });
						})
						.catch(function (err) {
							alert(err);
						});
				}
			}
			if (sdata.resources.length) {
				for (x = 0; x < sdata.resources.length; x++) {
					fetch(crmresource.data('api') + '/' + sdata.resources[x], {
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
							CRMSearchResource(results, { 'pageload': true, 'disabled': false });
						})
						.catch(function (err) {
							alert(err);
						});
				}
			}
		}

		data = document.getElementById('crm-data');
		if (data) {
			//var edit = true;
			var orig = JSON.parse(data.innerHTML);
			var original = orig.original;

			document.getElementById('datestartshort').value = original.datetimecontact;//.substring(0, 10);
			document.getElementById('NotesText').value = original.note;

			document.getElementById('crmtype').value = original['contactreporttypeid'];
			/*$("#crmtype > option").each(function () {
				if (this.value == original['contactreporttypeid']) {
					$('#crmtype > option:selected', 'select[name="options"]').removeAttr('selected');
					$(this).attr('selected', true);
				}
			});*/

			//CRMToggleSearch('people');
			//CRMToggleSearch('none');

			for (x = 0; x < original.users.length; x++) {
				fetch(people.data('api') + '/' + original.users[x]['userid'], {
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
						CRMSearchUser(results, { 'pageload': true, 'disabled': false });
					})
					.catch(function (err) {
						alert(err);
					});
			}

			if (original.groupid > 0) {
				multi_group = false;

				fetch(group.data('api') + '/' + original.groupid, {
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
						CRMSearchGroup(results, { 'pageload': true, 'disabled': false });
					})
					.catch(function (err) {
						alert(err);
					});
			}

			var vals = [];
			for (x = 0; x < original.resources.length; x++) {
				vals.push(original.resources[x]['resourceid']);
			}
			crmresource
				.val(vals)
				.trigger('change');

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

		document.querySelectorAll('.date-pick').forEach(function (el) {
			el.addEventListener('change', function () {
				CRMDateSearch();
			});
		});
	}

	var container = document.getElementById('reports');
	if (container && !window.location.href.match(/[&?](\w+)$/)) {
		CRMToggle('search');
	}
});
