/* global $ */ // jquery.js

var headers = {
	'Content-Type': 'application/json'
};
var keywords_pending = 0;
var path = window.location.href; //base_url + '/issues';
var root = document.querySelector('meta[name="base-url"]').getAttribute('content') + "/api/issues";

/**
 * Put an error into the action bar
 *
 * @param   {string}  message
 */
function DisplayError(title, message) {
	var span = document.getElementById("issues_action");
	if (span) {
		span.classList.remove('d-none');
		span.innerHTML = '<strong>' + title + '</strong><br />' + message;
	}
}

/**
 * Hide any displayed errors
 */
function ClearError() {
	var span = document.getElementById("issues_action");
	if (span) {
		span.classList.add('d-none');
		span.innerHTML = '';
	}
}

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
	//var prev = -1;
	var txt = "";
	var temp = "";
	var keyid = 0;
	//var lastMatch = 0;
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
 * Toggle UI tabs
 *
 * @param   {string}  on
 * @param   {bool}    refresh
 * @return  {void}
 */
function IssuesToggle(on, refresh) {
	if (typeof (refresh) == 'undefined') {
		refresh = true;
	}

	$(".tab-add").addClass('hide');
	$(".tab-edit").addClass('hide');
	$(".tab-search").addClass('hide');
	$(".tab-" + on).removeClass('hide');

	$(".tab").removeClass('activeTab');

	if (on == 'search') {
		document.getElementById("TAB_" + on).classList.add('activeTab');

		document.getElementById("SPAN_header").innerHTML = "Search Reports";
		document.getElementById("TAB_add").innerHTML = "Add New";
		document.getElementById("INPUT_clear").value = "Clear";
		document.getElementById("INPUT_add").value = "Add Report";

		document.getElementById("datestartshort").value = document.getElementById("datestartshort").getAttribute('data-value');
		document.getElementById("timestartshort").value = document.getElementById("timestartshort").getAttribute('data-value');
	} else if (on == 'add') {
		document.getElementById("TAB_" + on).classList.add('activeTab');

		document.getElementById("SPAN_header").innerHTML = "Add New Report";
		document.getElementById("TAB_add").innerHTML = "Add New";
		document.getElementById("INPUT_clear").value = "Clear";
		document.getElementById("INPUT_add").value = "Add Report";

		var dt = document.getElementById("datestartshort");
		dt.setAttribute('data-value', dt.value);

		var d = new Date();
		dt.value = d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);

		dt = document.getElementById("timestartshort");
		dt.setAttribute('data-value', dt.value);

		var hr = ('0' + d.getHours()).slice(-2);
		var min = ('0' + d.getMinutes()).slice(-2);
		var ampm = "AM";
		if (hr >= 12) {
			if (hr > 12) {
				hr -= 12;
			}
			ampm = "PM";
		}

		dt.value = hr + ':' + min + ' ' + ampm;
	} else if (on == 'edit') {
		document.getElementById("TAB_add").classList.add('activeTab');

		document.getElementById("TAB_add").innerHTML = "Edit Report";
		document.getElementById("SPAN_header").innerHTML = "Edit Report";
		document.getElementById("INPUT_clear").value = "Cancel edit";
		document.getElementById("INPUT_add").value = "Save Changes";
	}

	if (refresh) {
		IssuesSearch();
	}

	IssuesTabURL(on);
}

/**
 * Set URL and history by active tab
 *
 * @param   {string}  tab
 * @return  {void}
 */
function IssuesTabURL(tab) {
	if (typeof (history.pushState) != 'undefined') {
		var url = window.location.href.match(/\?.*/);
		if (url != null) {
			url = url[0];
			if (url.match(/(search|add|edit|follow)/)) {
				url = url.replace(/edit/, tab);
				url = url.replace(/search/, tab);
				url = url.replace(/follow/, tab);
				url = url.replace(/add/, tab);
				//} else if (url.match(/&/)) {
				//	url = url + "&" + tab;
			} else {
				url = url + "&" + tab;
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
/*function IssuesSearchResource(xml, flags) {
	var pageload = false;
	//var disabled = false;

	if (typeof (flags) != 'undefined') {
		pageload = flags['pageload'];
		//disabled = flags['disabled'];
	}

	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);

		if (!pageload) {
			IssuesSearch();
			if (document.getElementById("TAB_follow").className.match(/active/)) {
				document.getElementById("INPUT_add").disabled = false;
			}
		}

		// reset search box
		var resource = $('#resource');

		if ($('.tagsinput').length) {
			if (!resource.tagExist(results['id'])) {
				resource.addTag({
					'id': results['id'],
					'label': results['name']
				});
			}
		} else {
			resource.val(resource.val() + (resource.val() ? ', ' : '') + results['name'] + ':' + results['id']);
		}
	} else {
		// error handling
		switch (xml.status) {
			case 401:
			case 403:
				DisplayError(ERRORS['403_generic'], null);
				break;
			case 500:
				DisplayError(ERRORS['500'], null);
				break;
			default:
				DisplayError(ERRORS['generic'], ERRORS['unknown']);
				break;
		}
	}
}*/

/**
 * Search by date
 *
 * @return  {void}
 */
function IssuesDateSearch() {
	var start = document.getElementById("datestartshort").value;
	if (start.match(/^\d{4}-\d{2}-\d{2}$/) || start == "") {
		IssuesSearch();
		return;
	}

	var stop = document.getElementById("datestopshort").value;
	if (stop.match(/^\d{4}-\d{2}-\d{2}$/) || stop == "") {
		IssuesSearch();
		return;
	}
}

/**
 * Search by keyword
 *
 * @param   {number}  key
 * @return  {void}
 */
function IssuesKeywordSearch(key) {
	// if someone hit enter
	if (key == 13) {
		IssuesSearch();
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
				IssuesSearch();
			}
		}, 200);
	}
}

/**
 * Post new entry to database
 *
 * @return  {void}
 */
function IssuesAddEntry() {
	var resourcedata = new Array();
	var notes;
	var resources = new Array();
	var myuserid = document.getElementById("myuserid").value;
	var createddate = document.getElementById("datestartshort").value;
	var createdtime = document.getElementById("timestartshort").value;
	var i = 0;

	// clear error boxes
	ClearError();

	if (!createddate.match(/^\d{4}-\d{2}-\d{2}$/)) {
		DisplayError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
		return;
	}
	//createddate += " 00:00:00";

	var match = createdtime.match(/^(\d{1,2}):(\d{2}) ?(AM|PM)$/);
	if (match) {
		var hour = parseInt(match[1]);
		if (hour != 12 && match[3] == "PM") {
			hour = hour + 12;
		}
		if (hour == 12 && match[3] == "AM") {
			hour = 0;
		}
		if (hour < 10) {
			hour = "0" + hour.toString(); // Pad with leading 0
		}
		createddate += " " + hour + ":" + match[2] + ":00";
	} else {
		createddate += " 00:00:00";
	}

	if ($('.tagsinput').length) {
		resourcedata = document.getElementById("resource").value.split(',');

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
	} else {
		resourcedata = document.getElementById("TD_resource").getElementsByTagName("div");

		for (i = 0; i < resourcedata.length; i++) {
			if (resourcedata[i].id.search("RESOURCE_") == 0) {
				resources.push(resourcedata[i].id.substr(6));
			}
		}
	}

	notes = document.getElementById("NotesText").value;

	var post = {};

	if (window.location.href.match(/edit/)) {
		var data = $('#report-data');
		var original = {};

		if (data.length) {
			var orig = JSON.parse(data.html());
			original = orig.original;
		}

		post['report'] = notes;

		// update
		if (createddate != original['createddate']) {
			post['datetimecreated'] = createddate;
		}

		post['resources'] = resources;

		post = JSON.stringify(post);

		if (post != "{}") {
			fetch(original['api'], {
				method: 'PUT',
				headers: headers,
				body: post
			})
			.then(function (response) {
				if (response.ok) {
					document.getElementById("INPUT_add").disabled = true;
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
				alert(err);
			});
		}

		if (typeof (history.pushState) != 'undefined') {
			var querystring = "?id=" + original['id'];
			history.pushState(null, null, encodeURI(querystring));
		}

		var id = original['id'].substr(original['id'].lastIndexOf("/") + 1);

		setTimeout(function () {
			IssuesToggle('search');
			IssuesClearSearch();
			document.getElementById("id").value = id;
			IssuesSearch();
		}, 250);

		return;
	}

	if (notes == "") {
		DisplayError('Required field missing', 'Please enter some note text.');
		return;
	}
	else {
		// new post
		post = {
			'report': notes,
			'datetimecreated': createddate,
			'userid': myuserid
		};

		if (resources.length > 0) {
			post['resources'] = resources;
		}

		post = JSON.stringify(post);
		document.getElementById("INPUT_add").disabled = true;

		fetch(root, {
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
			IssuesNewReport(results);
		})
		.catch(function (err) {
			alert(err);
		});
	}
}

/**
 * Callback after updating a report
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function IssuesUpdatedReport(xml) {
	if (xml.status == 200) {
		document.getElementById("INPUT_add").disabled = true;
	}
}*/

/**
 * Callback for creating a new report
 *
 * @param   {object}  results
 * @return  {void}
 */
function IssuesNewReport(results) {
	document.getElementById("INPUT_add").disabled = false;

	document.getElementById("NotesText").value = "";

	IssuesClearSearch();

	document.getElementById("id").value = results['id'];

	IssuesToggle('search', true);
}

/**
 * Search reports
 *
 * @return  {void}
 */
function IssuesSearch() {
	var resourcedata = new Array();
	var resources = new Array();
	var resource = null;

	var keywords = document.getElementById("keywords").value;
	//var myuserid = document.getElementById("myuserid").value;
	var start = document.getElementById("datestartshort").value;
	var stop = document.getElementById("datestopshort").value;
	var id = document.getElementById("id").value;
	var page = document.getElementById("page").value;
	var resolved = document.getElementById("resolved").value;
	var i = 0,
		x = 0;

	if ($('.tagsinput').length) {
		// Fetch list of selected resources
		resourcedata = document.getElementById("resource").value.split(',');
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
	}

	// sanity checks
	if (start != "") {
		if (!start.match(/^\d{4}-\d{2}-\d{2}$/)) {
			DisplayError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			// clear error boxes
			ClearError();
		}
	}
	if (stop != "") {
		if (!stop.match(/^\d{4}-\d{2}-\d{2}$/)) {
			DisplayError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			// clear error boxes
			ClearError();
		}
	}

	// start assembling string
	var searchstring = "page:" + page;
	var querystring = "&page=" + page;

	if (resolved) {
		searchstring += " resolved:" + resolved;
		querystring += "&resolved=" + resolved;
	}
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
	// Construct resource query
	if (resources.length > 0) {
		searchstring += " resource:" + resources[0];
		querystring += "&resource=" + resources[0];
		for (x = 1; x < resources.length; x++) {
			searchstring += "," + resources[x];
			querystring += "," + resources[x];
		}
	}
	// if not add new
	if (!document.getElementById("TAB_add").className.match(/active/)) {
		if (keywords != "") {
			// format FP or CR ticket queries correctly
			keywords = keywords.replace(/(FP|CR)#(\d+)/g, '$1 $2');

			// filter out potentially dangerous garbage
			keywords = keywords.replace(/[^a-zA-Z0-9_ ]/g, '');
			searchstring += " " + keywords;
			querystring += "&keywords=" + keywords;
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
		IssuesToggleAddButton();
	}

	if (typeof (history.pushState) != 'undefined') {
		var tab = window.location.href.match(/[&?](\w+)$/);
		if (tab != null) {
			querystring = querystring + "&" + tab[1];
		}
		querystring = querystring.replace(/^&+/, '?');
		history.pushState(null, null, encodeURI(querystring));
	}

	if (searchstring == "") {
		searchstring = "page=1"; //"start:0000-00-00";
	}

	//console.log('Searching... ' + encodeURI(searchstring));
	document.getElementById("reports").setAttribute('data-query', searchstring);

	fetch(root + "?" + encodeURI(querystring.replace('?', '')), {
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
		IssuesSearched(results);
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
function IssuesToggleAddButton() {
	// if add new
	if (document.getElementById("TAB_add").className.match(/active/)) {
		var start = document.getElementById("datestartshort").value;
		var notes = document.getElementById("NotesText").value;

		if (start != "" && notes != "") {
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
function IssuesSearched(results) {
	var reports = $("#reports");
	var count = 0;

	$("#matchingReports").html("Found " + results.data.length + " matching reports");

	if (results.data.length == 0) {
		reports.html('<p class="alert alert-warning">No matching reports found.</p>');
	} else {
		reports.html('');

		for (var x = 0; x < results.data.length; x++, count++) {
			IssuesPrintRow(
				results.data[x],
				results.userid,
				"newEntries" //(x < DEFAULT_ENTRIES ? "newEntries" : "newEntriesHidden")
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

		var q = reports.data('query');
		var query = q.replace(' ', '&').replace(':', '=');
		var lastpage = Math.ceil(results.total > results.limit ? results.total / results.limit : 1);

		// Pagination
		var ul = $('<ul class="pagination"></ul>');

		var li = $('<li class="page-item page-first">');
		var a = $('<a class="page-link" title="First page"><span aria-hidden="true">«</span></a>')
			.attr('href', path + '?' + query.replace(/(page=\d+)/, 'page=1'))
			.attr('data-page', 1);
		if (results.total <= (results.limit * results.page) || results.page == 1) {
			li.addClass('disabled');
			a.attr('aria-disabled', 'true');
		}
		li.append(a);
		ul.append(li);

		li = $('<li class="page-item page-prev">');
		a = $('<a class="page-link" title="Previous page"><span aria-hidden="true">‹</span></a>')
			.attr('href', path + '?' + query.replace(/(page=\d+)/, 'page=' + (results.page > 1 ? results.page - 1 : 1)))
			.attr('data-page', (results.page > 1 ? results.page - 1 : 1));
		if (results.total <= (results.limit * results.page) || results.page == 1) {
			li.addClass('disabled');
			a.attr('aria-disabled', 'true');
		}
		li.append(a);
		ul.append(li);

		if (results.total <= results.limit) {
			li = $('<li class="page-item">');
			a = $('<a class="page-link"></a>')
				.text('1')
				.attr('href', path + '?' + query.replace(/(page=\d+)/, 'page=1'))
				.attr('data-page', 1);
			if (results.total <= (results.limit * results.page)) {
				li.addClass('disabled');
				a.attr('aria-disabled', 'true');
			}
			li.append(a);
			ul.append(li);
		} else {
			for (var l = 1; l <= lastpage; l++) {
				li = $('<li class="page-item">');
				a = $('<a class="page-link"></a>')
					.text(l)
					.attr('href', path + '?' + query.replace(/(page=\d+)/, 'page=' + l))
					.attr('data-page', l);
				if (results.page == l) {
					li.addClass('active');
					//a.attr('aria-disabled', 'true');
				}
				li.append(a);
				ul.append(li);
			}
		}

		li = $('<li class="page-item page-next">');
		a = $('<a class="page-link" title="Next page"><span aria-hidden="true">›</span></a>')
			.attr('href', path + '?' + query.replace(/(page=\d+)/, 'page=' + (results.page > 1 ? lastpage - 1 : 1)))
			.attr('data-page', (results.page > 1 ? lastpage - 1 : 1))
			.attr('data-query', q.replace(/(page:\d+)/, 'page:' + (results.page > 1 ? lastpage - 1 : 1)));
		if (results.total <= (results.limit * results.page)) {
			li.addClass('disabled');
			a.attr('aria-disabled', 'true');
		}
		li.append(a);
		ul.append(li);

		li = $('<li class="page-item page-last">');
		a = $('<a class="page-link" title="Last page"><span aria-hidden="true">»</span></a>')
			.attr('href', path + '?' + query.replace(/(page=\d+)/, 'page=' + lastpage))
			.attr('data-page', lastpage)
			.attr('data-query', q.replace(/(page:\d+)/, 'page:' + lastpage));
		if (results.total <= (results.limit * results.page)) {
			li.addClass('disabled');
			a.attr('aria-disabled', 'true');
		}
		li.append(a);
		ul.append(li);

		reports.append(ul);

		$('.page-link').on('click', function (e) {
			e.preventDefault();
			$('#page').val($(this).data('page'));
			IssuesSearch();
		});
	}
}

/**
 * Print a report entry
 *
 * @param   {object}  report
 * @param   {string}  userid
 * @param   {string}  cls
 * @return  {void}
 */
function IssuesPrintRow(report, userid, cls) {
	var id = report['id'];

	// determine if this entry can be edited
	var edit = false;
	//if (userid == report['userid'] && report['age'] <= 86400) {
	if (report['can']['edit']) {
		edit = true;
	}

	var tr, td, div, a, span, img, li, x;

	// create first row
	var container = document.getElementById('reports');

	var article = document.createElement("article");
	article.id = id;
	article.className = "crm-item " + cls;

	var panel = document.createElement("div");
	panel.className = "card";

	// -- Admin header
	tr = document.createElement("div");
	tr.className = 'card-header crm-admin';

	// ID
	td = document.createElement("span");
	td.className = "issuesid";

	var bits = report['datetimecreated'].match(/\d+/g);
	var d = new Date(bits[0], bits[1] - 1, bits[2], bits[3], bits[4], bits[5], 0);

	var hr = d.getHours();
	var min = d.getMinutes();
	if (min < 10) {
		min = "0" + min;
	}
	var ampm = "am";
	if (hr >= 12) {
		if (hr > 12) {
			hr -= 12;
		}
		ampm = "PM";
	}

	span = document.createElement("a");
	span.href = path + "?id=" + id + '&edit';
	span.className = "issuespostdate";
	span.innerHTML = d.getMonth() + " " + d.getDate() + ", " + d.getFullYear() + " @ " + hr + ":" + min + ampm;

	var t = document.createTextNode(" by " + report['username']);

	td.appendChild(span);
	td.appendChild(t);

	if (edit) {
		// Delete button
		a = document.createElement("a");
		a.href = path + "?id=" + id + "&delete";
		a.className = 'edit news-delete tip'; //btn btn-outline-secondary
		a.onclick = function (e) {
			e.preventDefault();
			IssuesDeleteReport(report['id']);
		};
		a.title = "Delete Report.";

		img = document.createElement("i");
		img.className = "issueseditdelete fa fa-trash";
		img.setAttribute('aria-hidden', true);
		img.id = report['id'] + "_issuesdeleteimg";

		a.appendChild(img);
		td.appendChild(a);

		// Edit button
		a = document.createElement("a");
		a.href = path + "?id=" + id + '&edit';
		a.className = "edit news-edit tip";
		a.title = "Edit report text.";
		a.id = report['id'] + "_textediticon";

		img = document.createElement("i");
		img.className = "issuesedittext fa fa-pencil";
		img.setAttribute('aria-hidden', true);
		img.id = report['id'] + "_textediticonimg";

		a.appendChild(img);
		td.appendChild(a);
	}

	tr.appendChild(td);

	panel.appendChild(tr);

	// -- Header
	var ul = document.createElement("ul");
	ul.className = 'card-meta panel-meta news-meta';

	// Resource list
	if (report.resources.length > 0) {
		ul = document.createElement("ul");
		ul.className = 'card-meta panel-meta news-meta';

		li = document.createElement("li");
		li.className = 'news-tags';

		span = document.createElement("span");
		span.className = "issuespostresources";

		var r = Array();
		for (x = 0; x < report.resources.length; x++) {
			r.push(report.resources[x].name);
		}
		span.innerHTML = '<span class="badge badge-secondary">' + r.join('</span> <span class="badge badge-secondary">') + '</span>';

		li.appendChild(span);

		ul.appendChild(li);
		tr.appendChild(ul);
	}

	panel.appendChild(tr);

	// --Body
	tr = document.createElement("div");
	tr.className = 'card-body';

	td = document.createElement("div");
	td.className = "newsposttext";

	// format text
	var rawtext = report['report'];
	report['report'] = report['formattedreport'];

	// determine the directory we are operating in
	//var page = document.location.href.split("/")[4];
	// if we are in issues, we are doing report searches, so we should highlight matches
	//if (page == 'issues') {
		report['report'] = HighlightMatches(report['report']);
	//}

	span = document.createElement("div");
	span.id = report['id'] + "_text";
	span.innerHTML = report['report'];

	td.appendChild(span);

	span = document.createElement("span");

	var label = document.createElement("label");
	label.className = "sr-only";
	label.innerHTML = "Report";
	label.setAttribute('for', report['id'] + "_textarea");

	span.appendChild(label);

	var textarea = document.createElement("textarea");
	textarea.id = report['id'] + "_textarea";
	textarea.innerHTML = rawtext;
	textarea.style.display = "none";
	textarea.rows = 7;
	textarea.cols = 45;
	textarea.className = "form-control issuesreportedittextbox";

	span.appendChild(textarea);
	td.appendChild(span);

	tr.appendChild(td);

	panel.appendChild(tr);

	article.appendChild(panel);

	// -- New Comment
	tr = document.createElement("div");
	tr.className = 'newcomment card';

	td = document.createElement("div");
	td.className = "card-body";

	div = document.createElement("div");
	div.id = report['id'] + "_newupdate";

	label = document.createElement("label");
	label.className = "sr-only";
	label.innerHTML = "Comment";
	label.setAttribute('for', report['id'] + "_newcommentbox");

	div.appendChild(label);

	textarea = document.createElement("textarea");
	textarea.className = "form-control issuescommentbox";
	textarea.placeholder = "Write a comment...";
	textarea.id = report['id'] + "_newcommentbox";
	textarea.rows = 1;
	textarea.cols = 45;
	textarea.onfocus = function () {
		IssuesExpandNewComment(this.id);
	};
	textarea.onblur = function () {
		IssuesCollapseNewComment(this.id);
	};

	div.appendChild(textarea);

	var rdiv = document.createElement("div");
	rdiv.id = report['id'] + "_newcommentboxcontrols";
	rdiv.className = 'row comment-controls hide';

	var cdiv = document.createElement("div");
	cdiv.className = 'col-md-3';
	cdiv.innerHTML = '<label for="' + report['id'] + '_newcommentresolution"><input type="checkbox" name="resolution" id="' + report['id'] + '_newcommentresolution" value="1" /> Mark as resolution</label>';

	rdiv.appendChild(cdiv);

	cdiv = document.createElement("div");
	cdiv.className = 'col-md-9 text-right';

	var b = document.createElement("button");
	b.className = 'btn btn-primary';
	b.id = report['id'] + "_newcommentboxsave";
	b.innerHTML = 'Save';
	b.onclick = function (e) {
		e.preventDefault();
		IssuesPostComment(report['id']);
	};

	cdiv.appendChild(b);

	rdiv.appendChild(cdiv);
	div.appendChild(rdiv);


	td.appendChild(div);
	tr.appendChild(td);

	article.appendChild(tr);

	// -- Comments
	ul = document.createElement("ul");
	ul.id = report['id'] + '_comments';
	ul.className = 'crm-comments';

	article.appendChild(ul);

	container.appendChild(article);

	if (report.resolution) {
		IssuesPrintComment(report['id'], report.resolution, userid);
	}

	var c = Array();
	for (x = 0; x < report.comments.length; x++) {
		c.push(report.comments[x]);
	}
	for (x = 0; x < c.length; x++) {
		if (c[x]['comment'] != '') {
			IssuesPrintComment(report['id'], c[x], userid);
		}
	}
}

/**
 * Print a report comment
 *
 * @param   {string}  issueid
 * @param   {array}   comments
 * @param   {string}  userid
 * @return  {void}
 */
function IssuesPrintComment(issueid, comment, userid) {
	var page = document.location.href.split("/")[4];
	if (page == 'issues') {
		comment['formattedcomment'] = HighlightMatches(comment['formattedcomment']);
	}
	// determine if we should edit comment
	var edit = false;
	if (userid == comment['user'] && comment['age'] <= 86400) {
		edit = true;
	}

	var container = document.getElementById(issueid + '_comments');

	var li = document.createElement("li");

	var panel = document.createElement("div");
	panel.id = 'comment' + comment['id'];
	if (comment.resolution == 1) {
		panel.className = "card issue-resolution";
	} else {
		panel.className = "card";
	}

	var div, span, a, img;

	var tr = document.createElement("div");
	tr.className = 'card-header crm-admin';

	var bits = comment['datetimecreated'].match(/\d+/g);
	var d = new Date(bits[0], bits[1] - 1, bits[2], bits[3], bits[4], bits[5], 0);

	var hr = d.getHours();
	var min = d.getMinutes();
	if (min < 10) {
		min = "0" + min;
	}
	var ampm = "am";
	if (hr >= 12) {
		if (hr > 12) {
			hr -= 12;
		}
		ampm = "PM";
	}

	span = document.createElement("span");
	span.className = "issuescommentpostedby";
	span.innerHTML = d.getMonth() + " " + d.getDate() + ", " + d.getFullYear() + " @ " + hr + ":" + min + ampm + " by " + comment['username'];

	tr.appendChild(span);

	if (edit) {
		a = document.createElement("a");
		a.className = 'edit issues-comment-delete tip';
		a.href = path + "?comment=" + comment['id'] + "&delete";
		a.onclick = function (e) {
			e.preventDefault();
			IssuesDeleteComment(comment['id']);//, issueid);
		};
		a.id = comment['id'] + "_commenticon";
		a.title = "Delete comment.";

		img = document.createElement("i");
		img.className = "issueseditdeletecomment fa fa-trash";
		img.setAttribute('aria-hidden', true);
		img.id = comment['id'] + "_commentdeleteimg";

		a.appendChild(img);
		tr.appendChild(a);

		a = document.createElement("a");
		a.href = path + "?id=" + issueid + "#" + comment['id'];
		a.className = 'edit issues-comment-edit tip';
		a.onclick = function (e) {
			e.preventDefault();
			IssuesEditCommentTextOpen(comment['id']);
		};
		a.id = comment['id'] + "_commenttextediticon";
		a.title = "Edit comment.";

		img = document.createElement("i");
		img.className = "issuesedittextcomment fa fa-pencil";
		img.setAttribute('aria-hidden', true);
		img.id = comment['id'] + "_commenttextediticonimg";

		a.appendChild(img);
		tr.appendChild(a);

		// Cancel button
		a = document.createElement("a");
		a.href = path + "?id=" + issueid;
		a.className = 'edit issues-comment-cancel tip';
		a.onclick = function (e) {
			e.preventDefault();
			IssuesCancelCommentText(comment['id']);
		};
		a.title = "Cancel edits to text";
		a.id = comment['id'] + "_commenttextcancelicon";
		a.style.display = "none";

		img = document.createElement("i");
		img.className = "issuessavetext fa fa-ban";
		img.setAttribute('aria-hidden', true);
		img.id = comment['id'] + "_commenttextcanceliconimg";

		a.appendChild(img);
		tr.appendChild(a);
	}

	panel.appendChild(tr);


	div = document.createElement("div");
	div.className = "card-body issuescomment issuescommenttext";

	span = document.createElement("span");
	span.id = comment['id'] + "_comment";
	span.innerHTML = comment['formattedcomment'];

	div.appendChild(span);

	if (edit) {
		// Text box
		span = document.createElement("span");

		var label = document.createElement("label");
		label.className = "sr-only";
		label.innerHTML = "Comment";
		label.setAttribute('for', comment['id'] + "_commenttextarea");

		span.appendChild(label);

		var textarea = document.createElement("textarea");
		textarea.id = comment['id'] + "_commenttextarea";
		textarea.innerHTML = comment['comment'];
		textarea.style.display = "none";
		textarea.rows = 3;
		textarea.className = "form-control issuescommentedittextbox";

		span.appendChild(textarea);
		div.appendChild(span);

		var rdiv = document.createElement("div");
		rdiv.id = comment['id'] + "_commenttextareacontrols";
		rdiv.className = 'row hide comment-controls';

		var cdiv = document.createElement("div");
		cdiv.className = 'col-md-3';
		cdiv.innerHTML = '<label for="' + comment['id'] + '_resolved"><input type="checkbox" name="resolved" id="' + comment['id'] + '_commentresolution" value="1"' + (comment['resolution'] == 1 ? ' checked="checked"' : '') + ' /> Mark as resolution</label>';
		//cdiv.innerHTML = '<select class="form-control" name="resolved" id="' + report['id'] + '_resolved"><option value="0">Note</option><option value="1">Resolution</option></select>';

		rdiv.appendChild(cdiv);

		cdiv = document.createElement("div");
		cdiv.className = 'col-md-9 text-right';

		var b = document.createElement("button");
		b.className = 'btn btn-primary';
		b.id = comment['id'] + "_commenttextsaveicon";
		b.innerHTML = '<span class="fa fa-save" id="' + comment['id'] + '_commenttextsaveiconimg" aria-hidden="true"></span> Save';
		b.onclick = function (e) {
			e.preventDefault();
			//IssuesPostComment(comment['id']);
			IssuesSaveCommentText(comment['id']);
		};

		cdiv.appendChild(b);
		rdiv.appendChild(cdiv);
		div.appendChild(rdiv);
	}

	panel.appendChild(div);

	li.appendChild(panel);
	container.appendChild(li);
}

/**
 * Delete a report comment
 *
 * @param   {string}  commentid
 * @param   {string}  issueid
 * @return  {void}
 */
function IssuesDeleteComment(commentid) {
	if (confirm("Are you sure you want to delete this comment?")) {
		fetch(root + "/comments/" + commentid, {
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
			}
		})
			.then(function (response) {
				if (response.ok) {
					document.getElementById(commentid + "_comment").parentNode.parentNode.parentNode.style.display = "none";
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
			.catch(function (error) {
				document.getElementById(commentid + "_commentdeleteimg").className = "fa fa-exclamation-circle";
				document.getElementById(commentid + "_commentdeleteimg").parentNode.title = error;
			});
	}
}

/**
 * Delete a report
 *
 * @param   {string}  issueid
 * @return  {void}
 */
function IssuesDeleteReport(issueid) {
	if (confirm("Are you sure you want to delete this report?")) {
		fetch(root + "/" + issueid, {
			method: 'DELETE',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
			}
		})
			.then(function (response) {
				if (response.ok) {
					document.getElementById(issueid).style.display = "none";
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
			.catch(function (error) {
				document.getElementById(issueid + "_issuesdeleteimg").className = "fa fa-exclamation-circle";
				document.getElementById(issueid + "_issuesdeleteimg").parentNode.title = error;
			});
	}
}

/**
 * Post a report comment
 *
 * @param   {string}  issueid
 * @return  {void}
 */
function IssuesPostComment(issueid) {
	var comment = document.getElementById(issueid + "_newcommentbox").value;
	var res = document.getElementById(issueid + "_newcommentresolution");

	var post = JSON.stringify({
		'issueid': issueid,
		'comment': comment,
		'resolution': (res.checked ? 1 : 0)
	});

	fetch(root + "/comments", {
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
		IssuesPrintComment(issueid, results, results['user']);
		document.getElementById(issueid + "_newcommentbox").value = "";
		IssuesCollapseNewComment(issueid + "_newcommentbox");
	})
	.catch(function () {
		document.getElementById(issueid + "_newcommentboxsave").className = "fa fa-exclamation-circle";
		document.getElementById(issueid + "_newcommentboxsave").parentNode.title = "An error occured while posting comment.";
	});
}

/**
 * Toggle controls open for editing comment text
 *
 * @param   {string}  comment
 * @return  {void}
 */
function IssuesEditCommentTextOpen(comment) {
	// hide text
	var text = document.getElementById(comment + "_comment");
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(comment + "_commenttextarea");
	box.style.display = "block";

	// hide edit icon
	var eicon = document.getElementById(comment + "_commenttextediticon");
	eicon.style.display = "none";

	var cicon = document.getElementById(comment + "_commenttextcancelicon");
	cicon.style.display = "block";

	var d = document.getElementById(comment + "_commenttextareacontrols");
	d.className = "row comment-controls";
}

/**
 * Callback after saving edited comment text
 *
 * @param   {string}  comment
 * @return  {void}
 */
function IssuesCancelCommentText(comment) {
	var text = document.getElementById(comment + "_comment");
	text.style.display = "block";

	var box = document.getElementById(comment + "_commenttextarea");
	box.style.display = "none";

	var eicon = document.getElementById(comment + "_commenttextediticon");
	eicon.style.display = "block";

	var cicon = document.getElementById(comment + "_commenttextcancelicon");
	cicon.style.display = "none";

	var d = document.getElementById(comment + "_commenttextareacontrols");
	d.className = "row hide comment-controls";
}

/**
 * Save edited comment text
 *
 * @param   {string}  comment
 * @return  {void}
 */
function IssuesSaveCommentText(comment) {
	// get text
	var text = document.getElementById(comment + "_commenttextarea").value;
	var res = document.getElementById(comment + "_commentresolution");

	// change save icon
	var icon = document.getElementById(comment + "_commenttextsaveicon");
	icon.disabled = true;
	var img = document.getElementById(comment + "_commenttextsaveiconimg");
	img.className = "fa fa-spinner fa-spin";
	img.parentNode.title = "Saving changes...";

	var post = {
		'comment': text,
		'resolution': (res.checked ? 1 : 0)
	};
	post = JSON.stringify(post);

	fetch(root + "/comments/" + comment, {
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
		var panel = document.getElementById("comment" + comment);
		if (results.resolution == 1) {
			panel.className = "card issue-resolution";
		} else {
			panel.className = "card";
		}

		var text = document.getElementById(comment + "_comment");
		text.style.display = "block";
		text.innerHTML = results['formattedcomment'];

		var box = document.getElementById(comment + "_commenttextarea");
		box.style.display = "none";

		var cicon = document.getElementById(comment + "_commenttextcancelicon");
		cicon.style.display = "none";

		var editicon = document.getElementById(comment + "_commenttextediticon");
		editicon.style.display = "block";

		var icon = document.getElementById(comment + "_commenttextsaveicon");
		icon.disabled = false;

		img.className = "fa fa-save";

		var d = document.getElementById(comment + "_commenttextareacontrols");
		d.className = "row comment-controls hide";
	})
	.catch(function (err) {
		var img = document.getElementById(comment + "_commenttextsaveiconimg");
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = err;
	});
}

/**
 * Expand comment box
 *
 * @param   {string}  comment
 * @return  {void}
 */
function IssuesExpandNewComment(comment) {
	var textarea = document.getElementById(comment);
	textarea.className = "form-control issuescommentboxexpand";
	textarea.rows = 3;

	var img = document.getElementById(comment + "controls");
	img.className = 'row comment-controls';
}

/**
 * Collapse comment box
 *
 * @param   {string}  comment
 * @return  {void}
 */
function IssuesCollapseNewComment(comment) {
	var textarea = document.getElementById(comment);
	if (textarea.value == "") {
		textarea.className = "form-control issuescommentbox";
		textarea.rows = 1;

		var img = document.getElementById(comment + "controls");
		img.className = 'row comment-controls hide';
	}
}

/**
 * Clear search values
 *
 * @return  {void}
 */
function IssuesClearSearch() {
	document.getElementById("keywords").value = "";
	document.getElementById("datestartshort").value = "";
	document.getElementById("datestopshort").value = "";
	document.getElementById("id").value = "";
	document.getElementById("NotesText").value = "";

	var resources = document.getElementById("resource");
	if (resources) {
		resources.value = '';
		if ($('.tagsinput').length) {
			$(resources).clearTags();
		}
	}

	if (window.location.href.match(/edit/)) {
		window.location = window.location.href.replace(/&edit/, "&search");
		return;
	}

	setTimeout(function () {
		IssuesSearch();
	}, 200);
}

/**
 * Get and return array of resource objects
 *
 * @param   {string}  url
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
	headers = {
		'Content-Type': 'application/json',
		'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
	};

	var frm = document.getElementById('DIV_search');
	if (frm) {
		document.querySelectorAll('.date-pick').forEach(function (el) {
			el.addEventListener('change', function () {
				IssuesDateSearch();
			});
		});

		$('.time-pick').timepicker({
			timeFormat: "h:i A",
			minTime: '8:00am',
			maxTime: '5:00pm',
			change: function () {
				$(this).trigger('change');
			}
		});

		document.getElementById('keywords').addEventListener('keyup', function (event) {
			IssuesKeywordSearch(event.keyCode);
		});
		document.getElementById('id').addEventListener('keyup', function (event) {
			IssuesKeywordSearch(event.keyCode);
		});
		document.getElementById('NotesText').addEventListener('keyup', function () {
			IssuesToggleAddButton();
		});
		document.getElementById('resolved').addEventListener('change', function (event) {
			event.preventDefault();
			IssuesSearch();
		});

		document.getElementById('INPUT_search').addEventListener('click', function (event) {
			event.preventDefault();
			IssuesSearch();
		});
		document.querySelectorAll('.btn-clear').forEach(function (el) {
			el.addEventListener('click', function (e) {
				e.preventDefault();
				IssuesClearSearch();
			});
		});

		document.getElementById('INPUT_add').addEventListener('click', function (event) {
			event.preventDefault();
			IssuesAddEntry();
		});

		var issuesresource = $("#resource");

		if (issuesresource.length) {
			issuesresource.tagsInput({
				placeholder: 'Select resource...',
				importPattern: /([^:]+):(.+)/i,
				'autocomplete': {
					source: autocompleteResource(issuesresource.attr('data-uri')),
					dataName: 'resources',
					height: 150,
					delay: 100,
					minLength: 1
				},
				'onAddTag': function () {
					IssuesSearch();
				},
				'onRemoveTag': function () {
					IssuesSearch();
				}
			});
		}

		var data = document.getElementById('report-data');
		if (data) {
			var orig = JSON.parse(data.innerHTML);
			var original = orig.original;

			document.getElementById('datestartshort').value = original.createddate.substring(0, 10);
			document.getElementById('NotesText').value = original.report;

			if (original.starttime != '') {
				document.getElementById('timestartshort').value = original.starttime;
			}

			//IssuesToggleSearch('none');
			var x;
			for (x = 0; x < original.resources.length; x++) {
				fetch(original.resources[x]['api'], {
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
					// reset search box
					var resource = $('#resource');

					if ($('.tagsinput').length) {
						if (!resource.tagExist(results['id'])) {
							resource.addTag({
								'id': results['id'],
								'label': results['name']
							});
						}
					} else {
						resource.val(resource.val() + (resource.val() ? ', ' : '') + results['name'] + ':' + results['id']);
					}
				})
				.catch(function (err) {
					alert(err);
				});
			}

			IssuesToggle('edit', false);
		}
	}

	var tabs = document.querySelectorAll('.issues-tabs a');

	if (tabs.length) {
		for (var i = 0; i < tabs.length; i++) {
			tabs[i].addEventListener('click', function (event) {
				event.preventDefault();

				IssuesToggle(this.getAttribute('href').replace('#', ''));
			});
		}

		var url = window.location.href.match(/[&?](\w+)$/);
		if (url != null) {
			IssuesToggle(url[1]);
			setTimeout(function () {
				IssuesSearch();
			}, 300);
		}

		$('.date-pick').on('change', function () {
			IssuesDateSearch();
		});
	}

	var reports = document.getElementById('reports');
	if (reports) {
		var q = '';
		if (reports.getAttribute('data-query')) {
			q = encodeURI(reports.getAttribute('data-query'));
		}

		fetch(root + "" + (q ? '?' + q : ''), {
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
			IssuesSearched(results);
		})
		.catch(function (err) {
			alert(err);
		});
	}

	document.querySelectorAll('.issue-todo').forEach(function (el) {
		el.addEventListener('change', function () {
			var myuserid = document.getElementById("myuserid").value;

			var post = {
				'report': this.getAttribute('data-name'),
				'userid': myuserid,
				'issuetodoid': this.getAttribute('data-id')
			};

			var that = $(this);

			post = JSON.stringify(post);

			fetch(root, {
				method: 'POST',
				headers: headers,
				body: post
			})
			.then(function (response) {
				if (response.ok) {
					$(that.closest('li')).fadeOut();
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
				var img = $(that.closest('li')).find('.fa')[0];
				img.className = "fa fa-exclamation-triangle";
				img.parentNode.title = "Unable to save changes, reload the page and try again.";
			});
		});
	});

	document.querySelectorAll('.issuetodo-edit').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var frm = document.getElementById(this.getAttribute('href').replace('#', ''));
			frm.style.display = 'block';

			var vals = document.getElementById(this.getAttribute('href').replace('#', '').replace('-form', '-values'));
			vals.style.display = 'none';

			frm = document.getElementById(this.getAttribute('href').replace('#', '').replace('-form', '-cancel'));
			frm.style.display = 'inline-block';

			document.getElementById('issuetodo-new').style.display = 'none';

			this.style.display = 'none';
		});
	});

	document.querySelectorAll('.issuetodo-cancel').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var frm = document.getElementById(this.getAttribute('href').replace('#', '') + '-form');
			frm.style.display = 'none';

			var vals = document.getElementById(this.getAttribute('href').replace('#', '') + '-values');
			vals.style.display = 'block';

			frm = document.getElementById(this.getAttribute('href').replace('#', '') + '-edit');
			frm.style.display = 'inline-block';

			document.getElementById('issuetodo-new').style.display = 'block';

			this.style.display = 'none';
		});
	});

	document.querySelectorAll('.issuetodo-delete').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			if (!confirm('Are you sure you want to delete this item?')) {
				return;
			}

			var that = $(this);

			fetch(that.data('id'), {
				method: 'DELETE',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					$(that.closest('li')).fadeOut();
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
				var img = $(that.closest('li')).find('.fa')[0];
				img.className = "fa fa-exclamation-triangle";
				img.parentNode.title = "Unable to save changes, reload the page and try again.";
			});
		});
	});

	document.querySelectorAll('.issuetodo-save').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var id = this.getAttribute('data-id');
			//var frm = document.getElementById('issuetodo' + id + '-form');
			//var id = original['id'].substr(original['id'].lastIndexOf("/")+1);

			var post = {
				'name': document.getElementById('issuetodo' + id + '-name').value,
				'description': document.getElementById('issuetodo' + id + '-description').value,
				'recurringtimeperiod': document.getElementById('issuetodo' + id + '-recurringtimeperiod').value,
				'userid': document.getElementById("myuserid").value
			};

			var url = id;
			if (id == 'new') {
				url = root + "/todos";
			}

			var that = $(this);

			post = JSON.stringify(post);

			fetch(url, {
				method: 'POST',
				headers: headers,
				body: post
			})
			.then(function (response) {
				if (response.ok) {
					location.reload();
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
				var img = $(that.closest('li')).find('.fa')[0];
				img.className = "fa fa-exclamation-triangle";
				img.parentNode.title = "Unable to save changes, reload the page and try again.";
			});
		});
	});
});
