/* global $ */ // jquery.js

var keywords_pending = 0;
var LASTEDIT = new Array();
var root = document.querySelector('meta[name="base-url"]').getAttribute('content') + "/api/news";
var headers = {
	'Content-Type': 'application/json'
};

/**
 * gets the active tab
 *
 * @return  {void}
 */
function GetTab() {
	var tabs = document.getElementById("tabMain");

	// look for a tab that is not hidden
	//var bits = tablist.split(",");
	var t = tabs.getElementsByTagName("div");

	//var i = 0;
	for (var x = 0; x < t.length; x++) {
		if (t[x].id.substring(0, 3) == "DIV") {
			if (t[x].style.display != "none") {
				return t[x].id.substring(4); //bits[i];
			}
			//i++;
		}
	}
}

/**
 * Put an error into the action bar
 *
 * @param   {string}  text
 * @param   {string}  small
 * @return  {void}
 */
function SetError(message, small) {
	var group = GetTab();

	if (group) {
		var span = document.getElementById(group + "_action");
		if (span) {
			span.className = "alert alert-error";
			span.innerHTML = message + "<br />";

			if (typeof (small) != 'undefined' && small != '') {
				var span2 = document.createElement("span");
				span2.innerHTML = small;
				span2.className = "smallError";

				span.appendChild(span2);
			}
		}
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
 * Put an error into the action bar
 *
 * @param   {string}  message
 * @return  {void}
 */
function DisplayError(message) {
	var span = document.getElementById("news_action");
	if (span) {
		span.classList.remove('d-none');
		span.innerHTML = message;
	} else {
		alert(message);
	}
}

/**
 * Hide any displayed errors
 *
 * @return  {void}
 */
function ClearError() {
	var span = document.getElementById("news_action");
	if (span) {
		span.classList.add('d-none');
		span.innerHTML = '';
	}
}

/**
 * Callback for JS MarkDown parsing
 *
 * @param   {string}  text
 * @param   {object}  element
 * @return  {string}
 */
/* exported customMarkdownParser */
function customMarkdownParser(text, element) {
	text = text.replaceAll(/(contact|CRM?)(\s+report)?\s*#?(\d+)/g, '<a href="?id=$3">Contact Report #$3</a>');
	var matches = text.matchAll(/(news)\s*(story|item)?\s*#?(\d+)(\{.+?\})?/ig);

	for (const match of matches) {
		if (match[4]) {
			text = text.replace(match[0], '<a href="/news/' + match[3] + '">' + match[4].replace('{', '').replace('}', '') + '</a>');
		} else {
			text = text.replace(match[0], '<a href="/news/' + match[3] + '">News story #' + match[3] + '</a>');
		}
	}

	var vars = element.getAttribute('data-vars');
	if (vars) {
		vars = JSON.parse(vars);
	} else {
		vars = {};
	}

	var keywords = [
		'%date%',
		'%datetime%',
		'%time%',
		'%updatedatetime%',
		'%startdatetime%',
		'%startdate%',
		'%starttime%',
		'%enddatetime%',
		'%enddate%',
		'%endtime%',
		'%location%',
		'%resources%',
		'%updatedatetime%',
		'%updatedate%',
		'%updatetime%'
	];

	if (element.id == 'NotesText') {
		vars = NEWSPreviewVars();
		/*var post = { 'body': text };
		post['vars'] = NEWSPreviewVars();

		fetch(root + "/preview", {
			method: 'POST',
			headers: headers,
			body: JSON.stringify(post)
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
			vars = results.vars;

			var k;
			for (var x = 0; x < keywords.length; x++) {
				k = keywords[x].replaceAll('%', '');

				if (vars && typeof (vars[k]) != 'undefined') {
					text = text.replaceAll(keywords[x], vars[k]);
				} else {
					text = text.replaceAll(keywords[x], '<span style="color:red;">' + keywords[x] + '</span>');
				}
			}
		})
		.catch(function (err) {
			DisplayError(err);
		});
	} else {*/
		if (vars.resources.length > 2) {
			vars.resources[vars.resources.length - 1] = 'and ' + vars.resources[vars.resources.length - 1];
		}
		for (var i = 0; i < vars.resources.length; i++) {
			if (i == vars.resources.length - 1) {
				continue;
			}

			vars.resources[i] = vars.resources[i] + ',';
		}
		vars.resources = vars.resources.join(' ');
	}

	var k;
	for (var x = 0; x < keywords.length; x++) {
		k = keywords[x].replaceAll('%', '');

		if (vars && typeof (vars[k]) != 'undefined') {
			text = text.replaceAll(keywords[x], vars[k]);
		} //else {
			//text = text.replaceAll(keywords[x], '<span style="color:red;">' + keywords[x] + '</span>');
		//}
	}

	return text;
}

/**
 * Toggle UI tabs
 *
 * @param   {string}  on
 * @param   {bool}    refresh
 * @return  {void}
 */
function NEWSToggle(on, refresh) {
	if (typeof (refresh) == 'undefined') {
		refresh = true;
	}
	var option = document.getElementById("OPTION_all");

	var times = document.getElementsByClassName('input-time');
	for (var i = 0; i < times.length; i++) {
		if (!times[i].classList.contains('tab-' + on)) {
			if (!times[i].classList.contains('d-none')) {
				times[i].classList.add('d-none');
			}
		} else {
			times[i].classList.remove('d-none');
		}
	}

	// Set header
	var header = document.getElementById('SPAN_header');
	if (header) {
		header.innerHTML = header.getAttribute('data-' + on);
	}

	document.querySelectorAll('.tab-add').forEach(function(el) {
		el.classList.add('d-none');
	});
	document.querySelectorAll('.tab-edit').forEach(function (el) {
		el.classList.add('d-none');
	});
	document.querySelectorAll('.tab-search').forEach(function (el) {
		el.classList.add('d-none');
	});
	document.querySelectorAll('.tab-' + on).forEach(function (el) {
		el.classList.remove('d-none');
	});

	// Remove errors upon a toggle
	ClearError();

	if (on == 'search') {
		// toggle the search fields on
		var tab = document.getElementById("TAB_search");
		// Crude check for being on the search page vs manage page
		// as the search page doesn't have any tabs
		if (tab) {
			document.getElementById("TAB_search").className = "nav-link active tab activeTab";
			document.getElementById("TAB_add").className = "nav-link tab";

			document.getElementById("TAB_add").innerHTML = "Add News";
			document.getElementById("INPUT_clear").value = "Clear";
			document.getElementById("INPUT_preview").style.display = "none";
			document.getElementById("INPUT_add").value = "Add News";

			document.getElementById("published").checked = false;
			document.getElementById("template").checked = false;
		}

		document.getElementById("datestartshort").value = "";
		document.getElementById("timestartshort").value = "";
		document.getElementById("datestopshort").value = "";
		document.getElementById("timestopshort").value = "";

		document.getElementById("location").value = "";
		document.getElementById("id").value = "";

		// Add all news type option
		if (!option) {
			option = document.createElement("option");
			//option.name = "all";
			option.value = "-1";
			option.id = "OPTION_all";
			option.innerHTML = "All";
			var newstype = document.getElementById("newstype");
			newstype.insertBefore(option, newstype.firstChild);
			newstype.selectedIndex = 0;
		}

	} else if (on == 'add') {
		// toggle the new entry fields on
		document.getElementById("TAB_search").className = "nav-link tab";
		document.getElementById("TAB_add").className = "nav-link active tab activeTab";

		document.getElementById("TAB_add").innerHTML = "Add News";
		document.getElementById("INPUT_clear").value = "Clear";
		document.getElementById("INPUT_add").value = "Add News";
		document.getElementById("INPUT_preview").style.display = "inline";
		document.getElementById("INPUT_preview").addEventListener('click', function () { NEWSPreview('new'); });

		document.getElementById("Headline").value = "";
		document.getElementById("location").value = "";
		document.getElementById("NotesText").value = "";
		document.getElementById("published").checked = false;
		document.getElementById("template").checked = false;

		// Remove all news type option
		if (option) {
			document.getElementById('newstype').removeChild(option);
		}
	} else if (on == 'edit') {
		// toggle the new entry fields on
		document.getElementById("TAB_search").className = "nav-link tab";
		document.getElementById("TAB_add").className = "nav-link active tab activeTab";

		document.getElementById("TAB_add").innerHTML = "Edit News";
		document.getElementById("INPUT_clear").value = "Cancel";
		document.getElementById("INPUT_add").value = "Save Changes";
		document.getElementById("INPUT_preview").style.display = "inline";

		// Remove all news type option
		if (option) {
			document.getElementById('newstype').removeChild(option);
		}
	}

	if (refresh) {
		NEWSSearch();
	}
	NEWSTabURL(on);
}

/**
 * Set URL and history by active tab
 *
 * @param   {string}  tab
 * @return  {void}
 */
function NEWSTabURL(tab) {
	if (typeof (history.pushState) != 'undefined') {
		var url = window.location.href.match(/\?.*/);
		if (url != null) {
			url = url[0];
			if (url.match(/[&?](search|add|edit|all)/)) {
				url = url.replace(/edit/, tab);
				url = url.replace(/search/, tab);
				url = url.replace(/add/, tab);
			} else if (!url.match(/&$/)) {
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
 * Add an association to the list of associations
 *
 * @param   {array}  association
 * @return  {void}
 */
function NEWSAddAssociation(association) {
	var results;
	if (typeof association == 'string') {
		results = JSON.parse(association);
	} else {
		results = association;
	}

	var newsuser = document.getElementById('newsuser');
	if ($('.tagsinput').length) {
		if (!$(newsuser).tagExist(results['id'])) {
			$(newsuser).addTag({
				'id': results['associd'],
				'label': results['assocname']
			});
		}
	} else {
		newsuser.value = newsuser.value + (newsuser.value ? ', ' : '') + results['name'] + ':' + results['id'];
	}
}

/**
 * Show/hide fields by selected newstype
 *
 * @return  {void}
 */
function NEWSNewstypeSearch() {
	var newstype = document.getElementById("newstype");
	var index = newstype.selectedIndex;
	//var newstypeid = newstype.item(index).value;
	var tagresources = newstype.item(index).getAttribute("data-tagresources");
	var taglocation = newstype.item(index).getAttribute("data-taglocation");
	var tagurl = newstype.item(index).getAttribute("data-tagurl");
	var tagusers = newstype.item(index).getAttribute("data-tagusers");

	var row_resources = document.getElementById("TR_resource");
	if (row_resources) {
		if (tagresources == "0") {
			row_resources.classList.add('d-none');
		} else {
			row_resources.classList.remove('d-none');
		}
	}

	var row_location = document.getElementById("TR_location");
	if (row_location) {
		if (taglocation == "0") {
			row_location.classList.add('d-none');
		} else {
			row_location.classList.remove('d-none');
		}
	}

	var row_url = document.getElementById("TR_url");
	if (row_url) {
		if (tagurl == "0") {
			row_url.classList.add('d-none');
		} else {
			row_url.classList.remove('d-none');
		}
	}

	var row_user = document.getElementById("TR_user");
	if (row_user) {
		if (tagusers == "0") {
			row_user.classList.add('d-none');
		} else {
			row_user.classList.remove('d-none');
		}
	}

	NEWSSearch();
}

/**
 * Set appropriate start stop times for search
 *
 * @param   {string}  box
 * @return  {void}
 */
function NEWSDateSearch(box) {
	var start = document.getElementById("datestartshort").value;
	var stop = document.getElementById("datestopshort").value;
	var starttime = document.getElementById("timestartshort").value;
	var stoptime = document.getElementById("timestopshort").value;

	var STARTBOX = document.getElementById("datestartshort").getAttribute('data-start');
	var STOPBOX = document.getElementById("datestartshort").getAttribute('data-stop');
	if (!STARTBOX || STARTBOX == '0000-00-00') {
		STARTBOX = start;
	}
	if (!STOPBOX || STOPBOX == '0000-00-00') {
		STOPBOX = start;
	}

	if ((start.match(/^\d{4}-\d{2}-\d{2}$/) || start == "") &&
		(stop.match(/^\d{4}-\d{2}-\d{2}$/) || stop == "") &&
		(starttime.match(/^\d{1,2}:\d{2} ?(AM|PM)$/) || starttime == "") &&
		(stoptime.match(/^\d{1,2}:\d{2} ?(AM|PM)$/) || stoptime == "")) {

		// Check if we should auto-populate the stop boxes with the start boxes
		if (window.location.href.match(/(\?|&)edit/) ||
			window.location.href.match(/(\?|&)add/)) {
			if (stop == "" && start != "") {
				document.getElementById("datestopshort").value = start;
				STOPBOX = start;
			}
			// If a start time is set and an end time is not...
			if (stoptime == "" && starttime != "") {
				stoptime = starttime;

				var timeInt, minutes;

				// If it's the same day, and an end time isn't set,
				// default it to one hour later
				if (start) {
					if (!stop || stop == start) {
						timeInt = parseInt(starttime);
						minutes = starttime.substring(3, 5);
						var ampm = starttime.substring(-1);

						if (ampm == 'PM') {
							timeInt + 12;
						}
						if (timeInt < 10) {
							timeInt = '0' + timeInt;
						}

						var sptm = new Date(Date.parse(start + 'T' + timeInt + ':' + minutes + ':00'));
						sptm.setTime(sptm.getTime() + 1 * 60 * 60 * 1000);

						var hr = sptm.getHours();
						var min = sptm.getMinutes();
						if (min < 10) {
							min = '0' + min;
						}
						ampm = 'AM';
						if (hr > 12) {
							hr -= 12;
							ampm = 'PM';
						}

						stoptime = hr + ':' + min + ' ' + ampm;
					}
				} else {
					// No dates set, so just bump the time by an hour
					timeInt = parseInt(starttime) + 1;
					minutes = starttime.substring(3, 5);

					if (starttime > '12:00') {
						stoptime = `${timeInt - 12}:${minutes} PM`;
					} else {
						stoptime = `${timeInt}:${minutes} AM`;
					}
				}
				document.getElementById("timestopshort").value = stoptime;
			}
			if (stop != "" && start != "" && box == "start") {
				var START = new Date(STARTBOX);
				var STOP = new Date(STOPBOX);

				var diff = ((Date.parse(STARTBOX) + START.getTimezoneOffset() * 60 * 1000) - (Date.parse(STOPBOX) + STOP.getTimezoneOffset() * 60 * 1000));
				var now = new Date(start);
				var d = new Date(Date.parse(start) - diff + (now.getTimezoneOffset() * 60 * 1000));
				var year = d.getFullYear();
				var day = d.getDate();
				var month = d.getMonth() + 1;

				if (day < 10) {
					day = "0" + day;
				}
				if (month < 10) {
					month = "0" + month;
				}

				document.getElementById("datestopshort").value = year + "-" + month + "-" + day;
				STOPBOX = year + "-" + month + "-" + day;
			}
		}

		NEWSSearch();

		if (box == "start") {
			STARTBOX = start;
		}
		if (box == "stop") {
			STOPBOX = stop;
		}
	}
}

/**
 * Search by keywords
 *
 * @param   {string}  key
 * @return  {void}
 */
function NEWSKeywordSearch(key) {
	var text = document.getElementById("keywords").value;
	var keywords = text.split(/ /);

	// if someone hit enter
	if (key == 13) {
		NEWSSearch();
		return;
	}

	// make sure all the keywords are long enough
	var search = true;
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
				NEWSSearch();
			}
		}, 200);
	}
}

/**
 * post new entry to database
 *
 * @return  {void}
 */
function NEWSAddEntry() {
	//var resourcedata = new Array();
	var resource = new Array();
	var associations = new Array();
	var newstypeselect = document.getElementById("newstype");
	var index = newstypeselect.selectedIndex;
	var newstypeid = newstypeselect.item(index).value;
	var tagresources = newstypeselect.item(index).getAttribute("data-tagresources");
	var notes = document.getElementById("NotesText").value;
	var newsdate = document.getElementById("datestartshort").value;
	var newstime = document.getElementById("timestartshort").value;
	var newsdateend = document.getElementById("datestopshort").value;
	var newstimeend = document.getElementById("timestopshort").value;
	var headline = document.getElementById("Headline").value;
	var locale = document.getElementById("location").value;
	var url = document.getElementById("url").value;
	var published = document.getElementById("published").checked;
	var template = document.getElementById("template").checked;
	var match,
		i = 0;

	// clear error boxes
	ClearError();

	if (!newsdate.match(/^\d{4}-\d{2}-\d{2}$/) && !template) {
		SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
		return;
	}

	var hour = null;
	if (!newsdate) {
		newsdate = '0000-00-00';
	}
	match = newstime.match(/^(\d{1,2}):(\d{2}) ?(AM|PM)$/);
	if (match) {
		hour = parseInt(match[1]);
		if (hour != 12 && match[3] == "PM") {
			hour = hour + 12;
		}
		if (hour == 12 && match[3] == "AM") {
			hour = 0;
		}
		if (hour < 10) {
			hour = "0" + hour.toString(); // Pad with leading 0
		}
		newsdate += " " + hour + ":" + match[2] + ":00";
	} else {
		newsdate += " 00:00:00";
	}

	// newsdateend is optional, use default value if missing
	if (newsdateend.match(/^\d{4}-\d{2}-\d{2}$/)) {
		match = newstimeend.match(/^(\d{1,2}):(\d{2}) ?(AM|PM)$/);
		if (match) {
			hour = parseInt(match[1]);
			if (hour != 12 && match[3] == "PM") {
				hour = hour + 12;
			}
			if (hour == 12 && match[3] == "AM") {
				hour = 0;
			}
			if (hour < 10) {
				hour = "0" + hour.toString(); // Pad with leading 0
			}
			newsdateend += " " + hour + ":" + match[2] + ":00";
		} else {
			newsdateend += " 00:00:00";
		}
	} else {
		newsdateend = null; //"0000-00-00 00:00:00";
	}

	if (newsdateend && newsdateend != "0000-00-00 00:00:00" && newsdateend < newsdate) {
		SetError('End date must come after start date', 'Please enter a valid end date');
		return;
	}

	if (tagresources == "1") {
		resource = Array.prototype.slice.call(document.querySelectorAll('#newsresource option:checked'), 0).map(function (v) {
			return v.value;
		});
	}

	var usersdata = document.getElementById("newsuser").value.split(',');
	for (i = 0; i < usersdata.length; i++) {
		if (usersdata[i] != "") {
			associations.push(usersdata[i]);
		}
	}

	var post = {};
	if (window.location.href.match(/(\?|&)edit/)) {
		var original = {};
		var data = document.getElementById('news-data');
		if (data) {
			original = JSON.parse(data.innerHTML);
		}

		// update
		if (newsdate != original['datetimenews']) {
			post['datetimenews'] = newsdate;
		}
		if (headline != original['headline']) {
			post['headline'] = headline;
		}
		if (locale != original['location']) {
			post['location'] = locale;
		}
		if (url != original['url']) {
			post['url'] = url;
		}
		if (newstypeid != original['newstype']) {
			post['newstypeid'] = newstypeid;
		}
		if (notes != original['body']) {
			post['body'] = notes;
		}
		post['resources'] = resource;
		post['associations'] = associations;
		if (newsdateend != original['datetimenewsend']) {
			if (newsdateend != newsdate) {
				post['datetimenewsend'] = newsdateend;
			} else {
				post['datetimenewsend'] = null; //'0000-00-00 00:00:00';
			}
		}
		post['datetimenewsend'] = post['datetimenewsend'] ? post['datetimenewsend'] : null;
		if (published == true && original['published'] != '1') {
			post['published'] = "1";
		}
		if (published == false && original['published'] != '0') {
			post['published'] = "0";
		}

		post['lastedit'] = original['lastedit'];

		post = JSON.stringify(post);
		if (post != "{}") {
			var id = original['id']; //.substr(original['id'].lastIndexOf("/")+1);

			fetch(original['api'], {
				method: 'PUT',
				headers: headers,
				body: post
			})
			.then(function (response) {
				if (response.ok) {
					document.getElementById("INPUT_add").disabled = true;
					document.getElementById("location").value = "";
					NEWSToggle('search', false);
					document.getElementById("id").value = id;
					NEWSSearch();
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
				document.getElementById("INPUT_add").disabled = true;
				NEWSToggle('search');
				document.getElementById("id").value = id;
				NEWSSearch();
				//DisplayError(err);
				DisplayError(err);
			});
		}

		return;
	} else {
		if (notes == "") {
			SetError('Required field missing', 'Please enter content for the body.');
			return;
		}
		if (headline == "") {
			SetError('Required field missing', 'Please enter a headline');
			return;
		}
		// new post
		post = {
			'body': notes,
			'newstypeid': newstypeid,
			'headline': headline
			//'datetimenews': newsdate,
			//'datetimenewsend': newsdateend
		};

		if (newsdate && newsdate != '0000-00-00 00:00:00') {
			post['datetimenews'] = newsdate;
		}
		if (newsdateend
			&& newsdateend != '0000-00-00 00:00:00'
			&& newsdateend != newsdate) {
			post['datetimenewsend'] = newsdateend;
		}

		/*if (newsdateend == newsdate) {
			post['datetimenewsend'] = '0000-00-00 00:00:00';
		}*/
		if (resource.length > 0) {
			post['resources'] = resource;
		}
		if (associations.length > 0) {
			post['associations'] = associations;
		}
		if (published == true) {
			post['published'] = "1";
		}
		if (published == false) {
			post['published'] = "0";
		}
		if (template == true) {
			post['template'] = "1";
			post['published'] = "1";
		}
		if (template == false) {
			post['template'] = "0";
		}
		if (locale != "") {
			post['location'] = locale;
		}
		if (url != "") {
			post['url'] = url;
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
			if (results.template) {
				NEWSToggle('search', true);
			} else {
				window.location.href = results.uri;
			}
		})
		.catch(function (err) {
			DisplayError(err);
		});
	}
}

/**
 * Search news
 *
 * @return  {void}
 */
function NEWSSearch() {
	var resources = new Array();
	var keywords = document.getElementById("keywords").value;
	var start = document.getElementById("datestartshort").value;
	var stop = document.getElementById("datestopshort").value;
	var id = document.getElementById("id").value;

	var published = false;
	if (document.getElementById("published")) {
		published = document.getElementById("published").checked;
	}

	var template = false;
	if (document.getElementById("template")) {
		template = document.getElementById("template").checked;
	}

	var newstype = document.getElementById("newstype");
	var index = newstype.selectedIndex;
	var newstypeid = newstype.item(index).value;
	var tagresources = newstype.item(index).getAttribute("data-tagresources");
	var taglocation = newstype.item(index).getAttribute("data-taglocation");
	var tagurl = newstype.item(index).getAttribute("data-tagurl");
	var tagusers = newstype.item(index).getAttribute("data-tagusers");
	var list = document.getElementById("TR_resource");
	var locale = document.getElementById("TR_location");
	var url = document.getElementById("TR_url");
	var users = document.getElementById("TR_user");

	if (list) {
		if (tagresources == "0") {
			list.classList.add('d-none');
		} else {
			list.classList.remove('d-none');
		}
	}

	if (locale) {
		if (taglocation == "0") {
			locale.classList.add('d-none');
		} else {
			locale.classList.remove('d-none');
		}
	}

	if (url) {
		if (!tagurl || tagurl == "0") {
			url.classList.add('d-none');
		} else {
			url.classList.remove('d-none');
		}
	}

	if (users) {
		if (!tagusers || tagusers == "0") {
			users.classList.add('d-none');
		} else {
			users.classList.remove('d-none');
		}
	}

	locale = document.getElementById("location").value;

	// Fetch list of selected resources
	//var resourcedata = document.getElementById("newsresource").value.split(',');
	var resourcedata = Array.prototype.slice.call(document.querySelectorAll('#newsresource option:checked'), 0).map(function (v) {
		return v.value;
	});
	for (var i = 0; i < resourcedata.length; i++) {
		if (resourcedata[i] != "") {
			if (resourcedata[i].indexOf('/') !== -1) {
				var resource = resourcedata[i].split('/');
				resources.push(resource[resource.length - 1]);
			} else {
				resources.push(resourcedata[i]);
			}
		}
	}

	// sanity checks
	if (start != "") {
		if (!start.match(/^\d{4}-\d{2}-\d{2}$/)) {
			SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			ClearError();
		}
		start = start + "!00:00:00";
	}

	if (stop != "") {
		if (!stop.match(/^\d{4}-\d{2}-\d{2}$/)) {
			SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			ClearError();
		}
		stop = stop + "!23:59:59";
	}

	if (newstypeid != "") {
		if (!newstypeid.match(/^(-)?[0-9]+$/)) {
			SetError('Invalid news type', 'Please select a news type');
			return;
		}
	}

	// start assembling string
	//var searchstring = "";
	var querystring = "&";

	if (published) {
		querystring += "state=published";
	} else {
		querystring += "state=*";
	}

	var in_edit = false;
	if (window.location.href.match(/[&?]edit/)) {
		document.getElementById("INPUT_add").disabled = false;
		//querystring += "&edit";
		in_edit = true;
	} else {
		NEWSToggleAddButton();
	}

	var in_add = false;
	var tab = document.getElementById("TAB_add");
	if (tab != null && tab.className.match(/active/)) {
		in_add = true;
	}

	// if not add new
	if (!in_add) {
		if (start != "") {
			//searchstring += " start:"  + start;
			querystring += "&start=" + start;
		}
		if (stop != "") {
			//searchstring += " stop:" + stop;
			querystring += "&stop=" + stop;
		}
	}

	// Construct resource query
	if (!in_edit && resources.length > 0) {
		//searchstring += " resource:" + resources[0];
		querystring += "&resource=" + resources[0];
		for (var x = 1; x < resources.length; x++) {
			//searchstring += "," + resources[x];
			querystring += "," + resources[x];
		}
	}

	if (!in_edit && newstypeid != "" && newstypeid != '-1') {
		//searchstring += " type:" + newstypeid;
		querystring += "&type=" + newstypeid;
	}

	if (window.location.href.match(/[&?]all/)) {
		//searchstring += " limit:0";
		querystring += "&limit=0";
	}

	// if not add new
	if (!in_add) {
		if (keywords != "") {
			// format news ticket queries correctly
			keywords = keywords.replace(/NEWS#(\d+)/g, '$1 $2');

			// filter out potentially dangerous garbage
			keywords = keywords.replace(/[^a-zA-Z0-9_ ]/g, '');
			//searchstring += " " + keywords;
			querystring += "&keywords=" + keywords;
		}
	}
	if (!in_add) {
		if (locale != "") {
			//searchstring += " location:" + locale;
			querystring += "&location=" + locale;
		}
	}
	if (template) {
		//searchstring += " template:1";
		querystring += "&template=1";
	}

	// if not add new
	if (!in_add || in_edit) {
		if (id.match(/(\d)+/)) {
			//searchstring += " id:" + id;
			querystring += "&id=" + id;
		}
	}

	if (typeof (history.pushState) != 'undefined') {
		var tb = window.location.href.match(/[&?](\w+)&?$/);
		if (tb != null) {
			querystring = querystring + "&" + tb[1];
		}
		querystring = querystring.replace(/^&+/, '?');
		history.pushState(null, null, encodeURI(querystring));
	}

	var page = document.getElementById('page');
	if (page) {
		querystring += '&page=' + page.value;
	}
	querystring += '&api_token=' + $('meta[name="api-token"]').attr('content');

	fetch(root + "/" + encodeURI(querystring), {
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
	.then(function (data) {
		NEWSSearched(data);
	})
	.catch(function (err) {
		DisplayError(err);
	});
}

/**
 * enable or disable Add button
 *
 * @return  {void}
 */
function NEWSToggleAddButton() {
	var start = document.getElementById("datestartshort").value;

	// if add new
	var tab = document.getElementById("TAB_add");
	if (tab != null && tab.className.match(/active/)) {
		var notes = document.getElementById("NotesText").value;
		var headline = document.getElementById("Headline").value;
		var template = document.getElementById("template");
		var template_select = document.getElementById("template_select");

		if (template_select.options[template_select.selectedIndex].value == "savetemplate") {
			template.checked = true;
		} else if (template_select.options[template_select.selectedIndex].value != "0") {
			if (!document.getElementById("TR_use_template").classList.contains('d-none')) {
				NEWSUseTemplate();
			}
		}
		template = template.checked;

		if ((template || start) && headline && notes) {
			document.getElementById("INPUT_add").disabled = false;
		} else {
			document.getElementById("INPUT_add").disabled = true;
		}

		if (template) {
			document.getElementById('TR_date').classList.add('d-none');
			document.getElementById('TR_published').classList.add('d-none');
		} else {
			document.getElementById('TR_date').classList.remove('d-none');
			document.getElementById('TR_published').classList.remove('d-none');
		}
	}
}

/**
 * Builds the news div with results returned from the WS
 *
 * @param   {object}  results
 * @return  {void}
 */
function NEWSSearched(results) {
	var news = document.getElementById("news");

	// clear out reports
	news.innerHTML = "";

	// parse results
	//var results = JSON.parse(xml.responseText);
	//var edit = false;//results['canEdit']; //(results['authorized'] == 1) ? true : false;

	document.getElementById("matchingnews").innerHTML = "Found " + results.meta.total + " matching articles";
	for (var x = 0; x < results.data.length; x++) {
		NEWSPrintRow(results.data[x]);//, results.updates);
	}

	// Re-initialize tooltips
	$('.tip').tooltip({
		position: {
			my: 'center bottom',
			at: 'center top'
		},
		hide: false
	});

	var q = news.getAttribute('data-query');
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

	$(news).append(ul);

	$('.page-link').on('click', function (e) {
		e.preventDefault();
		$('#page').val($(this).data('page'));
		NEWSSearch();
	});
}

/**
 * Builds the news div with results returned from the WS
 *
 * @param   {object}  news
 * @return  {void}
 */
function NEWSPrintRow(news) {
	var edit = news.can.edit,
		del = news.can.delete;
	var tab = document.getElementById("TAB_search");
	if (!tab) {
		edit = false;
	}

	var id = news.id;
	var resources = news.resources;
	var headline = news.headline;
	var locale = news.location;
	var body = news.body;
	var published = news.published;
	var maildate = news.maildate;
	var mailuser = news.mailuser;
	var users = news.associations;

	LASTEDIT[id] = news.editdate;

	var container = document.getElementById('news');

	var article = document.createElement("article");
	article.id = id;
	article.setAttribute('aria-labelledby', id + '_headline');
	article.className = "news-item";

	var panel = document.createElement("div");
	panel.className = "card panel panel-default mb-3";

	var tr, td, div, a, img, span, li, x;

	// -- Admin header
	if (edit || del) {
		tr = document.createElement("div");
		tr.className = 'card-header panel-heading news-admin';

		// ID
		td = document.createElement("span");
		td.className = "newsid";

		a = document.createElement("a");
		a.href = news.uri;
		a.innerHTML = "#" + id;

		td.appendChild(a);
		tr.appendChild(td);

		// Publication status
		var td2 = document.createElement("span");
		td2.className = "newspublication";

		a = document.createElement("a");
		a.href = "?id=" + id + '&edit';

		if (published == "1") {
			a.onclick = function (e) {
				e.preventDefault();

				var post = {
					'published': '0',
					'lastedit': LASTEDIT[id]
				};

				fetch(root + "/" + id, {
					method: 'PUT',
					headers: headers,
					body: JSON.stringify(post)
				})
				.then(function (response) {
					if (response.ok) {
						document.getElementById("id").value = id;
						return NEWSSearch();
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
					DisplayError(err);
				});
			};
			a.appendChild(document.createTextNode("Published"));
			a.className = 'badge badge-success badge-published ml-3';
			a.title = "Recall news item.";
		} else {
			a.onclick = function (e) {
				e.preventDefault();

				var post = {
					'published': '1',
					'lastedit': LASTEDIT[id]
				};
				fetch(root + "/" + id, {
					method: 'PUT',
					headers: headers,
					body: JSON.stringify(post)
				})
				.then(function (response) {
					if (response.ok) {
						document.getElementById("id").value = id;
						return NEWSSearch();
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
					DisplayError(err);
				});
			};
			a.appendChild(document.createTextNode("Draft"));
			a.className = 'badge badge-danger badge-unpublished ml-3';
			a.title = "Publish news item.";
		}

		td2.appendChild(a);
		tr.appendChild(td2);

		if (del) {
			// Delete button
			a = document.createElement("a");
			a.href = "?delete&id=" + id;
			a.className = 'edit news-delete icn tip text-danger';
			a.title = "Delete News Story.";
			a.onclick = function (e) {
				e.preventDefault();
				NEWSDeleteNews(id);
			};

			img = document.createElement("i");
			img.className = "fa fa-trash";
			img.setAttribute('aria-hidden', true);
			img.id = id + "_newsdeleteimg";

			span = document.createElement('span');
			span.classList.add('sr-only');
			span.appendChild(document.createTextNode("Delete News Story."));

			a.appendChild(img);
			a.appendChild(span);
			tr.appendChild(a);
		}

		// Mailing
		if (resources.length > 0 && published == "1") {
			a = document.createElement("a");
			a.className = 'edit news-mail tip';
			a.href = '#mailpreview-modal'; //"?mail&id=" + id;
			a.setAttribute('data-toggle', 'modal');
			a.addEventListener('click', function (e) {
				e.preventDefault();
				NEWSSendMail(id);
			});
			a.id = "A_mail_" + id;
			a.title = "Preview mail to mailing lists.";

			if (maildate != undefined) {
				span = document.createElement("span");
				span.innerHTML = "Last sent " + news.formattedmaildate + " by " + mailuser['name'] + " ";
				span.className = "newspostedby";

				a.appendChild(span);
			}

			img = document.createElement("i");
			img.className = "newsedit fa fa-envelope";
			img.setAttribute('aria-hidden', true);
			img.id = "IMG_mail_" + id;

			a.appendChild(img);
			//a.appendChild(document.createTextNode("Preview mail to mailing lists."));
			tr.appendChild(a);
		}
		else if (users.length > 0 && published == "1") {
			a = document.createElement("a");
			a.className = 'edit news-mail tip';
			a.href = '#mailwrite-modal'; //"?mail&id=" + id;
			a.setAttribute('data-toggle', 'modal');
			a.addEventListener('click', function (e) {
				e.preventDefault();
				NEWSWriteMail(id);
			});
			a.id = "A_mail_" + id;
			a.title = "Write mail to users.";

			if (maildate != undefined) {
				span = document.createElement("span");
				span.innerHTML = "Last sent " + news.formattedmaildate + " by " + mailuser['name'] + " ";
				span.className = "newspostedby";

				a.appendChild(span);
			}

			img = document.createElement("i");
			img.className = "newsedit fa fa-envelope";
			img.setAttribute('aria-hidden', true);
			img.id = "IMG_mail_" + id;

			a.appendChild(img);
			//a.appendChild(document.createTextNode("Preview mail to mailing lists."));
			tr.appendChild(a);
		}

		panel.appendChild(tr);
	}

	// -- Header
	tr = document.createElement("div");
	tr.className = 'card-header panel-heading';

	td = document.createElement("h3");
	td.className = "card-title panel-title newsheadline";

	a = document.createElement("a");
	a.href = news.uri;
	a.innerHTML = headline;
	a.id = id + "_headline";

	td.appendChild(a);

	if (edit) {
		// Edit button
		a = document.createElement("a");
		a.href = "?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			NEWSEditNewsHeadlineOpen(id);
		};
		a.className = "news-action icn tip";
		a.title = "Edit news headline";

		img = document.createElement("i");
		img.id = id + "_headlineediticon";
		img.className = "newsedit fa fa-pencil";
		img.setAttribute('aria-hidden', true);

		a.appendChild(img);
		td.appendChild(a);
	}

	var input = document.createElement("input");
	input.id = id + "_headlineinput";
	input.type = "text";
	input.value = headline;
	input.style.display = "none";
	input.className = "form-control newsheadlineeditinput";

	span = document.createElement("span");
	span.appendChild(input);

	td.appendChild(span);

	if (edit) {
		// Save button
		a = document.createElement("a");
		a.href = "?save&id=" + id;
		a.onclick = function (e) {
			e.preventDefault();
			NEWSSaveNewsHeadline(id);
		};
		a.id = id + "_headlinesaveicon";
		a.className = "news-action icn tip";
		a.style.display = "none";
		a.title = "Edit news headline.";

		img = document.createElement("i");
		img.className = "newssaveheadline fa fa-save";
		img.id = id + "_headlinesaveiconimg";
		img.setAttribute('aria-hidden', true);

		a.appendChild(img);
		td.appendChild(a);

		// Cancel button
		a = document.createElement("a");
		a.href = news.uri;
		a.onclick = function (e) {
			e.preventDefault();
			NEWSCancelNewsHeadline(id);
		};
		a.id = id + "_headlinecancelicon";
		a.className = "news-action news-cancel icn tip";
		a.style.display = "none";
		a.title = "Cancel headline edits";

		img = document.createElement("i");
		img.className = "newssaveheadline fa fa-ban";
		img.id = id + "_headlinecanceliconimg";
		img.setAttribute('aria-hidden', true);

		a.appendChild(img);
		td.appendChild(a);
	}

	var ul = document.createElement("ul");
	ul.className = 'card-meta panel-meta news-meta';

	// Date
	li = document.createElement("li");
	li.className = 'news-date';

	span = document.createElement("span");
	span.className = "newsdate";
	span.innerHTML = news.formatteddate;

	li.appendChild(span);

	if (edit) {
		a = document.createElement("a");
		a.href = "?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			NEWSClearSearch();
			if (!window.location.href.match(/\/news/)) {
				window.location = "?id=" + id + "&edit";
			} else {
				NEWSClearSearch();
				var url = window.location.href.split("?");
				url = url[0];
				window.location = url + "?id=" + id + "&edit";
			}
		};
		a.className = "news-action icn tip";
		a.title = "Edit news date.";

		img = document.createElement("i");
		img.className = "newsedit fa fa-pencil";
		img.setAttribute('aria-hidden', true);

		a.appendChild(img);
		li.appendChild(a);
	}
	ul.appendChild(li);

	// Locale
	if (news.location) {
		li = document.createElement("li");
		li.className = 'news-location';

		span = document.createElement("span");
		span.innerHTML = locale;
		span.id = id + "_location";

		li.appendChild(span);

		if (edit) {
			a = document.createElement("a");
			a.href = "?id=" + id + '&edit';
			a.onclick = function (e) {
				e.preventDefault();
				NEWSEditNewsLocationOpen(id);
			};
			a.id = id + "_locationediticon";
			a.className = "news-action icn tip";
			a.title = "Edit news location";

			img = document.createElement("i");
			img.id = id + "_locationediticonimg";
			img.className = "newsedit fa fa-pencil";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);
			li.appendChild(a);
		}

		input = document.createElement("input");
		input.id = id + "_locationinput";
		input.type = "text";
		input.value = locale;
		input.style.display = "none";
		input.className = "form-control newslocationeditinput";

		span = document.createElement("span");
		span.appendChild(input);

		li.appendChild(span);

		if (edit) {
			a = document.createElement("a");
			a.href = "?id=" + id + '&edit';
			a.onclick = function (e) {
				e.preventDefault();
				NEWSSaveNewsLocation(id);
			};
			a.className = "news-action icn tip";
			a.id = id + "_locationsaveicon";
			a.title = "Edit news location.";
			a.style.display = "none";

			img = document.createElement("i");
			img.className = "newssavelocation fa fa-save";
			img.id = id + "_locationsaveiconimg";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);
			li.appendChild(a);

			a = document.createElement("a");
			a.href = news.uri;
			a.onclick = function (e) {
				e.preventDefault();
				NEWSCancelNewsLocation(id);
			};
			a.id = id + "_locationcancelicon";
			a.className = "news-action news-cancel icn tip";
			a.title = "Cancel location edits";
			a.style.display = "none";

			img = document.createElement("i");
			img.id = id + "_locationcanceliconimg";
			img.className = "newssaveheadline fa fa-ban";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);
			li.appendChild(a);
		}

		ul.appendChild(li);
	}

	// URL
	if (news.url) {
		li = document.createElement("li");
		li.className = 'news-url';

		span = document.createElement("span");
		span.innerHTML = news.url;
		span.id = id + "_url";

		li.appendChild(span);

		if (edit) {
			a = document.createElement("a");
			a.href = "?id=" + id + '&edit';
			a.onclick = function (e) {
				e.preventDefault();
				NEWSEditNewsUrlOpen(id);
			};
			a.className = "news-action icn tip";
			a.title = "Edit news URL";

			img = document.createElement("i");
			img.id = id + "_urlediticon";
			img.className = "newsedit fa fa-pencil";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);
			li.appendChild(a);
		}

		input = document.createElement("input");
		input.id = id + "_urlinput";
		input.type = "text";
		input.value = locale;
		input.style.display = "none";
		input.className = "form-control newsurleditinput";

		span = document.createElement("span");
		span.appendChild(input);

		li.appendChild(span);

		if (edit) {
			a = document.createElement("a");
			a.href = "?id=" + id + '&edit';
			a.onclick = function (e) {
				e.preventDefault();
				NEWSSaveNewsUrl(id);
			};
			a.className = "news-action icn tip";
			a.id = id + "_urlsaveicon";
			a.title = "Edit news URL.";
			a.style.display = "none";

			img = document.createElement("i");
			img.className = "newssaveurl fa fa-save";
			img.id = id + "_urlsaveiconimg";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);
			li.appendChild(a);

			a = document.createElement("a");
			a.href = news.uri;
			a.onclick = function (e) {
				e.preventDefault();
				NEWSCancelNewsUrl(id);
			};
			a.id = id + "_urlcancelicon";
			a.className = "news-action news-cancel icn tip";
			a.title = "Cancel url edits";
			a.style.display = "none";

			img = document.createElement("i");
			img.id = id + "_urlcanceliconimg";
			img.className = "newssaveurl fa fa-ban";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);
			li.appendChild(a);
		}

		ul.appendChild(li);
	}

	// Resource list
	var r;
	if (resources.length > 0) {
		li = document.createElement("li");
		li.className = 'news-tags';

		span = document.createElement("span");
		span.className = "newspostresources";

		r = Array();
		for (x = 0; x < resources.length; x++) {
			r.push(resources[x].name);
		}
		span.innerHTML = r.join(', ');

		li.appendChild(span);

		if (edit) {
			a = document.createElement("a");
			a.href = "?id=" + id + '&edit';
			a.title = "Edit tagged resources.";
			a.className = "news-action icn tip";

			img = document.createElement("i");
			img.className = "newsedit fa fa-pencil";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);

			span.appendChild(a);
			li.appendChild(span);
		}

		ul.appendChild(li);
	}

	// Users list
	if (edit && users.length > 0) {
		li = document.createElement("li");
		li.className = 'news-users';

		span = document.createElement("span");
		span.className = "newspostusers";

		r = Array();
		for (x = 0; x < users.length; x++) {
			r.push(users[x].name);
		}
		span.innerHTML = r.join(', ');

		li.appendChild(span);

		if (edit) {
			a = document.createElement("a");
			a.href = "?id=" + id + '&edit';
			a.title = "Edit associated users.";
			a.className = "news-action icn tip";

			img = document.createElement("i");
			img.className = "newsedit fa fa-pencil";
			img.setAttribute('aria-hidden', true);

			a.appendChild(img);

			span.appendChild(a);
			li.appendChild(span);
		}

		ul.appendChild(li);
	}

	// News type
	li = document.createElement("li");
	li.className = 'news-type';

	span = document.createElement("span");
	span.className = "newstype";
	span.appendChild(document.createTextNode(news.type.name));

	if (edit) {
		a = document.createElement("a");
		a.href = "?id=" + id + '&edit';
		a.title = "Edit news story, change text, headline, or tagged resources.";
		a.className = "news-action icn tip";

		img = document.createElement("i");
		img.className = "newsedit fa fa-pencil";
		img.setAttribute('aria-hidden', true);

		a.appendChild(img);
		span.appendChild(a);
	}
	li.appendChild(span);
	ul.appendChild(li);

	tr.appendChild(td);
	tr.appendChild(ul);
	panel.appendChild(tr);

	// --Body
	tr = document.createElement("div");
	tr.className = 'card-body panel-body';

	td = document.createElement("div");
	td.className = "newsposttext";

	tr.appendChild(td);

	// format text
	var rawtext = body;
	body = news.formattedbody;

	// determine the directory we are operating in
	//var page = document.location.href.split("/")[4];
	// if we are in news, we are doing report searches, so we should highlight matches
	//if (page == 'news') {
		body = HighlightMatches(body);
	//}

	span = document.createElement("span");
	span.id = id + "_text";
	span.innerHTML = body;

	td.appendChild(span);

	var label = document.createElement("label");
	label.setAttribute('for', id + "_textarea");
	label.className = 'sr-only';
	label.innerHTML = 'News text';

	var textarea = document.createElement("textarea");
	textarea.id = id + "_textarea";
	textarea.innerHTML = rawtext;
	//textarea.style.display = "none";
	textarea.rows = 7;
	textarea.cols = 45;
	textarea.className = "form-control md newspostedittextbox";
	textarea.setAttribute('data-vars', JSON.stringify(news.vars));

	span = document.createElement("span");
	span.appendChild(label);
	span.appendChild(textarea);
	span.style.display = "none";

	td.appendChild(span);

	if (edit) {
		var opt = document.createElement("div");
		opt.className = "card-options panel-options";

		a = document.createElement("a");
		a.href = "?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			NEWSEditNewsTextOpen(id);
		};
		a.className = "news-edit tip";
		a.title = "Edit news text.";
		a.id = id + "_textediticon";

		img = document.createElement("i");
		img.className = "newsedittext fa fa-pencil";
		img.setAttribute('aria-hidden', true);

		a.appendChild(img);
		opt.appendChild(a);

		// Preview button
		a = document.createElement("a");
		a.href = "?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			NEWSPreview(id);
		};
		a.className = "news-preview icn tip";
		a.id = id + "_textpreviewicon";
		a.title = "Preview news text";
		a.style.display = "none";

		img = document.createElement("i");
		img.className = "newssavetext fa fa-eye";
		img.setAttribute('aria-hidden', true);
		img.id = id + "_textpreviewiconimg";

		a.appendChild(img);
		//a.appendChild(document.createTextNode("Preview news text"));
		opt.appendChild(a);

		// Save button
		a = document.createElement("a");
		a.href = "e?id=" + id + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			NEWSSaveNewsText(id);
		};
		a.className = "news-save icn tip";
		a.id = id + "_textsaveicon";
		a.title = "Save news text";
		a.style.display = "none";

		img = document.createElement("i");
		img.className = "newssavetext fa fa-save";
		img.setAttribute('aria-hidden', true);
		img.id = id + "_textsaveiconimg";
		a.appendChild(img);
		//a.appendChild(document.createTextNode("Save news text"));
		opt.appendChild(a);

		// Cancel button
		a = document.createElement("a");
		a.href = news.uri;
		a.onclick = function (e) {
			e.preventDefault();
			NEWSCancelNewsText(id);
		};
		a.className = "news-cancel icn tip";
		a.id = id + "_textcancelicon";
		a.title = "Cancel edits to text";
		a.style.display = "none";

		img = document.createElement("i");
		img.className = "newssavetext fa fa-ban";
		img.setAttribute('aria-hidden', true);
		img.id = id + "_textcanceliconimg";
		a.appendChild(img);
		//a.appendChild(document.createTextNode("Cancel edits to text"));
		opt.appendChild(a);

		span = document.createElement("label");
		span.innerHTML = "Mark as updated: ";
		span.style.display = "none";
		span.id = id + "_textsaveupdate";
		span.setAttribute('for', id + "_textsaveupdatebox");

		var checkbox = document.createElement("input");
		checkbox.type = "checkbox";
		checkbox.id = id + "_textsaveupdatebox";
		span.appendChild(checkbox);
		td.appendChild(span);

		// help button
		a = document.createElement("a");
		a.href = "#markdown-help";
		a.setAttribute('data-toggle', 'modal');
		/*a.addEventListener('click', function (e) {
			e.preventDefault();
			//$('#help1').dialog({ modal: true, width: '553px' });
			//$('#help1').dialog('open');
		});*/
		a.id = id + "_texthelpicon";
		a.className = 'text-info tip';
		a.style.display = "none";
		a.title = 'Formatting news text';

		img = document.createElement("span");
		img.className = "fa fa-question-circle";
		img.id = id + "_texthelpiconimg";
		img.setAttribute('aria-hidden', true);

		a.appendChild(img);
		//a.appendChild(document.createTextNode("Formatting news text"));
		opt.appendChild(a);

		td.appendChild(opt);
	}

	panel.appendChild(tr);

	// -- Footer
	tr = document.createElement("div");
	tr.className = 'card-footer panel-footer';

	// Posted by
	td = document.createElement("div");
	td.className = "newspostedby";
	td.id = "POSTED_" + id;

	div = document.createElement("div");
	div.className = "newspostuser";

	var text = "Posted ";
	if (news['username']) {
		text = text + " by " + news['username'];
	}
	div.innerHTML = text + " on " + news.formattedcreateddate;

	td.appendChild(div);

	// Edited by
	if (news['editdate']
		&& news['editdate'] != '0000-00-00 00:00:00'
		&& news['editdate'] != news['createdate']) {
		div = document.createElement("div");
		div.className = "newspostedby newspostuser";

		text = "Edited ";
		if (news['editusername']) {
			text = text + " by " + news['editusername'];
		}
		div.innerHTML = text + " on " + news.formattededitdate;

		td.appendChild(div);
	}
	tr.appendChild(td);
	panel.appendChild(tr);

	article.appendChild(panel);

	ul = document.createElement("ul");
	ul.id = news.id + '_updates';
	ul.className = 'news-updates';

	// -- New update
	if (edit) {
		tr = document.createElement("div");
		tr.className = 'newsnewupdate card panel panel-default mb-3';

		td = document.createElement("div");
		td.className = "card-body panel-body";

		div = document.createElement("div");
		div.id = news['id'] + "_newupdate";

		var tselect = document.getElementById("template_select");
		if (tselect) {
			label = document.createElement("label");
			label.setAttribute('for', news['id'] + "_newupdatetemplate");
			label.className = 'sr-only';
			label.innerHTML = 'Use template';

			var select = document.createElement("select");
			select.id = news['id'] + "_newupdatetemplate";
			select.className = 'form-control';
			select.innerHTML = tselect.innerHTML;
			select.options[1] = null;
			select.onchange = function () {
				fetch(this.options[this.selectedIndex].value, {
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
				.then(function (data) {
					var textarea = document.getElementById(news['id'] + "_newupdatebox");
					textarea.value = data.body;
					textarea.rows = 7;
					textarea.focus();
					textarea.dispatchEvent(new Event('refreshEditor', { bubbles: true }));
				})
				.catch(function (err) {
					DisplayError(err);
				});
			};

			div.appendChild(label);
			div.appendChild(select);
		}

		label = document.createElement("label");
		label.setAttribute('for', news['id'] + "_newupdatebox");
		label.className = 'sr-only';
		label.innerHTML = 'Post an update';

		textarea = document.createElement("textarea");
		textarea.className = "form-control md crmupdatebox";

		var st = new Date();

		news.vars['updatedate'] = new Intl.DateTimeFormat("en-US", { weekday: "long" }).format(st) + ', '
			+ new Intl.DateTimeFormat("en-US", { month: "long" }).format(st) + ' ' + st.getDay() + ', '
			+ st.getFullYear();
		news.vars['updatetime'] = st.toLocaleTimeString('en-US').replace(':00 AM', ' AM').replace(':00 PM', ' PM');
		news.vars['updatedatetime'] = news.vars["updatedate"] + ' at ' + news.vars["updatetime"];

		textarea.setAttribute('data-vars', JSON.stringify(news.vars));
		textarea.placeholder = "Post an update...";
		textarea.id = news['id'] + "_newupdatebox";
		textarea.rows = 1;
		textarea.cols = 45;
		textarea.onfocus = function () {
			NewsExpandNewUpdate(this.id);
		};
		textarea.onblur = function () {
			NewsCollapseNewUpdate(this.id);
		};

		div.appendChild(label);
		div.appendChild(textarea);

		// Save button
		a = document.createElement("a");
		a.className = "news-save btn float-right tip";
		a.href = "?update&id=" + id;
		a.onclick = function (e) {
			e.preventDefault();
			NewsPostUpdate(news['id']);
		};
		a.title = "Post update";
		a.id = news['id'] + "_newupdateboxsave";
		//a.style.display = "none";

		img = document.createElement("span");
		img.className = "crmnewcommentsave fa fa-save";
		img.setAttribute('aria-hidden', true);
		//img.style.display = "none";
		//img.id = news['id'] + "_newupdateboxsave";

		a.appendChild(img);
		//a.appendChild(document.createTextNode("Post update"));
		div.appendChild(a);

		td.appendChild(div);
		tr.appendChild(td);
		article.appendChild(tr);
	}

	article.appendChild(ul);

	container.appendChild(article);

	textarea.dispatchEvent(new Event('initEditor', { bubbles: true }));

	//var c = Array();
	for (x = 0; x < news.updates.length; x++) {
		//if (root + "/" + news.updates[x]['newsid'] == news['id']) {
		//c.push(news.updates[x]);
		NewsPrintUpdate(news['id'], news.updates[x], edit);
		//}
	}
	/*for (x = 0; x < c.length; x++) {
		if (c[x]['update'] != '') {
			NewsPrintUpdate(news['id'], c[x], edit);
		}
	}*/
}

/**
 * Delete a news entry
 *
 * @param   {string}  newsid
 * @return  {void}
 */
function NEWSDeleteNews(newsid) {
	if (confirm("Are you sure you want to delete this news story?")) {
		fetch(root + "/" + newsid, {
			method: 'DELETE',
			headers: headers
		})
		.then(function (response) {
			if (response.ok) {
				document.getElementById(newsid).style.display = "none";
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
			var img = document.getElementById(newsid + "_newsdeleteimg");
			if (img) {
				img.className = "fa fa-exclamation-triangle";
				img.parentNode.title = error;
			}
		});
	}
}

/**
 * Toggle controls open for editing news text
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSEditNewsTextOpen(news) {
	// hide text
	var text = document.getElementById(news + "_text");
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(news + "_textarea");
	box.style.height = (25 + text.parentNode.offsetHeight) + "px";
	box.dispatchEvent(new Event('initEditor', { bubbles: true }));
	box.parentNode.style.display = "block";

	// hide edit icon
	var eicon = document.getElementById(news + "_textediticon");
	eicon.style.display = "none";

	// show save icon
	var sicon = document.getElementById(news + "_textsaveicon");
	sicon.style.display = "inline";

	var img = document.getElementById(news + "_textsaveiconimg");
	img.className = "fa fa-save";

	document.getElementById(news + "_textpreviewicon").style.display = "inline";
	document.getElementById(news + "_textcancelicon").style.display = "inline";
	document.getElementById(news + "_texthelpicon").style.display = "inline";

	// show update
	var update = document.getElementById(news + "_textsaveupdate");
	update.style.display = "inline";
}

/**
 * Toggle controls closed for editing news text
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSCancelNewsText(news) {
	// hide text
	var text = document.getElementById(news + "_text");
	text.style.display = "block";

	// show textarea
	var box = document.getElementById(news + "_textarea");
	box.parentNode.style.display = "none";

	// hide edit icon
	var eicon = document.getElementById(news + "_textediticon");
	eicon.style.display = "inline";

	// show save icon
	var sicon = document.getElementById(news + "_textsaveicon");
	sicon.style.display = "none";

	document.getElementById(news + "_textpreviewicon").style.display = "none";
	document.getElementById(news + "_textcancelicon").style.display = "none";
	document.getElementById(news + "_texthelpicon").style.display = "none";

	// show update
	var update = document.getElementById(news + "_textsaveupdate");
	update.style.display = "none";
}

/**
 * Save news text
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSaveNewsText(news) {
	// get text
	var text = document.getElementById(news + "_textarea").value;
	var update = document.getElementById(news + "_textsaveupdatebox").checked;

	// change save icon
	var icon = document.getElementById(news + "_textsaveicon");
	icon.onclick = function () { };

	var img = document.getElementById(news + "_textsaveiconimg");
	img.className = "fa fa-spin fa-spinner";
	img.parentNode.title = "Saving changes...";

	var post = {
		'body': text//,
		//'lastedit' : LASTEDIT[news]
	};

	if (update == true) {
		post['update'] = "1";
	}
	post = JSON.stringify(post);

	fetch(root + "/" + news, {
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
		LASTEDIT[news] = results['lastedit'];

		var icon = document.getElementById(news + "_textsaveicon");
		icon.onclick = function () {
			NEWSSaveNewsText(news);
		};
		icon.style.display = "none";

		document.getElementById(news + "_textarea").parentNode.style.display = "none";
		document.getElementById(news + "_textediticon").style.display = "block";
		document.getElementById(news + "_textsaveupdate").style.display = "none";
		document.getElementById(news + "_textsaveupdatebox").checked = false;
		document.getElementById(news + "_textpreviewicon").style.display = "none";
		document.getElementById(news + "_textcancelicon").style.display = "none";
		document.getElementById(news + "_texthelpicon").style.display = "none";

		var text = document.getElementById(news + "_text");
		text.innerHTML = results['formattedbody'];
		text.style.display = "block";
	})
	.catch(function (err) {
		DisplayError(err);
	});
}

/**
 * Toggle controls open for editing news headline
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSEditNewsHeadlineOpen(news) {
	// hide text
	var text = document.getElementById(news + "_headline");
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(news + "_headlineinput");
	box.style.display = "block";

	// hide edit icon
	var eicon = document.getElementById(news + "_headlineediticon");
	eicon.style.display = "none";

	var cicon = document.getElementById(news + "_headlinecancelicon");
	cicon.style.display = "inline";

	// show save icon
	var sicon = document.getElementById(news + "_headlinesaveicon");
	sicon.style.display = "inline";

	var img = document.getElementById(news + "_headlinesaveiconimg");
	img.className = "fa fa-save";
	img.parentNode.title = "Click to save changes.";
}

/**
 * Toggle controls open for editing news location
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSEditNewsLocationOpen(news) {
	// hide text
	var text = document.getElementById(news + "_location");
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(news + "_locationinput");
	box.style.display = "block";

	// hide edit icon
	var eicon = document.getElementById(news + "_locationediticon");
	eicon.style.display = "none";

	var cicon = document.getElementById(news + "_locationcancelicon");
	cicon.style.display = "inline";

	// show save icon
	var sicon = document.getElementById(news + "_locationsaveicon");
	sicon.style.display = "inline";

	var img = document.getElementById(news + "_locationsaveiconimg");
	img.className = "fa fa-save";
	img.parentNode.title = "Click to save changes.";
}

/**
 * Toggle controls closed for editing news headline
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSCancelNewsHeadline(news) {
	// hide text
	var text = document.getElementById(news + "_headline");
	text.style.display = "inline";

	// show textarea
	var box = document.getElementById(news + "_headlineinput");
	box.style.display = "none";

	// hide edit icon
	var eicon = document.getElementById(news + "_headlineediticon");
	eicon.style.display = "inline";

	document.getElementById(news + "_headlinecancelicon").style.display = "none";

	// show save icon
	var sicon = document.getElementById(news + "_headlinesaveicon");
	sicon.style.display = "none";
}

/**
 * Toggle controls closed for editing news location
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSCancelNewsLocation(news) {
	// hide text
	var text = document.getElementById(news + "_location");
	text.style.display = "inline";

	// show textarea
	var box = document.getElementById(news + "_locationinput");
	box.style.display = "none";

	// hide edit icon
	var eicon = document.getElementById(news + "_locationediticon");
	eicon.style.display = "inline";

	document.getElementById(news + "_locationcancelicon").style.display = "none";

	// show save icon
	var sicon = document.getElementById(news + "_locationsaveicon");
	sicon.style.display = "none";
}

/**
 * Save news headline
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSaveNewsHeadline(news) {
	// get text
	var text = document.getElementById(news + "_headlineinput").value;

	// change save icon
	var icon = document.getElementById(news + "_headlinesaveicon");
	icon.onclick = function () { };

	var img = document.getElementById(news + "_headlinesaveiconimg");
	img.className = "fa fa-spinner fa-spin";
	img.parentNode.title = "Saving changes...";

	var post = {
		'headline': text,
		'lastedit': LASTEDIT[news]
	};
	post = JSON.stringify(post);

	fetch(root + "/" + news, {
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
		LASTEDIT[news] = results['lastedit'];

		var icon = document.getElementById(news + "_headlinesaveicon");
		icon.onclick = function () {
			NEWSSaveNewsHeadline(news);
		};
		icon.style.display = "none";

		var cancelicon = document.getElementById(news + "_headlinecancelicon");
		cancelicon.style.display = "none";

		var text = document.getElementById(news + "_headline");
		text.style.display = "inline";
		text.innerHTML = results['headline'];

		var input = document.getElementById(news + "_headlineinput");
		input.style.display = "none";

		var editicon = document.getElementById(news + "_headlineediticon");
		editicon.style.display = "inline";
	})
	.catch(function (err) {
		var img = document.getElementById(news + "_headlinesaveiconimg");
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = err;
	});
}

/**
 * Save news location
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSaveNewsLocation(news) {
	// get text
	var text = document.getElementById(news + "_locationinput").value;

	// change save icon
	var icon = document.getElementById(news + "_locationsaveicon");
	icon.onclick = function () { };

	var img = document.getElementById(news + "_locationsaveiconimg");
	img.className = "fa fa-spinner fa-spin";
	img.parentNode.title = "Saving changes...";

	var post = {
		'location': text,
		'lastedit': LASTEDIT[news]
	};
	post = JSON.stringify(post);

	fetch(root + "/" + news, {
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
		LASTEDIT[news] = results['lastedit'];

		var icon = document.getElementById(news + "_locationsaveicon");
		icon.onclick = function () {
			NEWSSaveNewsLocation(news);
		};
		icon.style.display = "none";

		var cancelicon = document.getElementById(news + "_locationcancelicon");
		cancelicon.style.display = "none";

		var text = document.getElementById(news + "_location");
		text.style.display = "inline";
		text.innerHTML = results['location'];

		var input = document.getElementById(news + "_locationinput");
		input.style.display = "none";

		var editicon = document.getElementById(news + "_locationediticon");
		editicon.style.display = "inline";
	})
	.catch(function (err) {
		var img = document.getElementById(news + "_locationsaveiconimg");
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = err;
	});
}

/**
 * Toggle controls open for editing news URL
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSEditNewsUrlOpen(news) {
	// hide text
	var text = document.getElementById(news + "_url");
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(news + "_urlinput");
	box.style.display = "block";

	// hide edit icon
	var eicon = document.getElementById(news + "_urlediticon");
	eicon.style.display = "none";

	var cicon = document.getElementById(news + "_urlcancelicon");
	cicon.style.display = "inline";

	// show save icon
	var sicon = document.getElementById(news + "_urlsaveicon");
	sicon.style.display = "inline";

	var img = document.getElementById(news + "_urlsaveiconimg");
	img.className = "fa fa-save";
	img.parentNode.title = "Click to save changes.";
}

/**
 * Toggle controls closed for editing news URL
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSCancelNewsUrl(news) {
	// hide text
	var text = document.getElementById(news + "_url");
	text.style.display = "inline";

	// show textarea
	var box = document.getElementById(news + "_urlinput");
	box.style.display = "none";

	// hide edit icon
	var eicon = document.getElementById(news + "_urlediticon");
	eicon.style.display = "inline";

	document.getElementById(news + "_urlcancelicon").style.display = "none";

	// show save icon
	var sicon = document.getElementById(news + "_urlsaveicon");
	sicon.style.display = "none";
}

/**
 * Save news URL
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSaveNewsUrl(news) {
	// get text
	var text = document.getElementById(news + "_urlinput").value;

	// change save icon
	var icon = document.getElementById(news + "_urlsaveicon");
	icon.onclick = function () { };
	var img = document.getElementById(news + "_urlsaveiconimg");
	img.className = "fa fa-spinner fa-spin";
	img.parentNode.title = "Saving changes...";

	var post = {
		'url': text,
		'lastedit': LASTEDIT[news]
	};
	post = JSON.stringify(post);

	fetch(root + "/" + news, {
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
		LASTEDIT[news] = results['lastedit'];

		var icon = document.getElementById(news + "_urlsaveicon");
		icon.onclick = function () {
			NEWSSaveNewsUrl(news);
		};
		icon.style.display = "none";

		var cancelicon = document.getElementById(news + "_urlcancelicon");
		cancelicon.style.display = "none";

		var text = document.getElementById(news + "_url");
		text.style.display = "inline";
		text.innerHTML = results['location'];

		var input = document.getElementById(news + "_urlinput");
		input.style.display = "none";

		var editicon = document.getElementById(news + "_urlediticon");
		editicon.style.display = "inline";
	})
	.catch(function (err) {
		var img = document.getElementById(news + "_urlsaveiconimg");
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = err;
	});
}

/**
 * Clear search input
 *
 * @return  {void}
 */
function NEWSClearSearch() {
	document.getElementById("keywords").value = "";
	document.getElementById("datestartshort").value = "";
	document.getElementById("timestartshort").value = "";
	document.getElementById("datestopshort").value = "";
	document.getElementById("timestopshort").value = "";
	document.getElementById("id").value = "";
	document.getElementById("newstype").selectedIndex = 0;
	if (document.getElementById("NotesText")) {
		document.getElementById("NotesText").value = "";
		document.getElementById("NotesText").dispatchEvent(new Event('refreshEditor', { bubbles: true }));
	}
	if (document.getElementById("Headline")) {
		document.getElementById("Headline").value = "";
	}
	document.getElementById("location").value = "";
	if (document.getElementById("template")) {
		document.getElementById("template").checked = false;
	}
	if (document.getElementById("published")) {
		document.getElementById("published").checked = false;
	}

	if (window.location.href.match(/[&?]add/)) {
		document.getElementById("TR_use_template").style.display = "block";
		document.getElementById("template_select").selectedIndex = 0;
	}

	var resources = document.getElementById("newsresource");
	if (resources) {
		resources.value = '';
		resources.dispatchEvent(new Event('change'));
	}

	if (window.location.href.match(/[&?]edit/)) {
		window.location = window.location.href
			.replace(/&edit/, "&search")
			.replace(/\?edit/, "?search")
			.replace(/id=\d+/, "");
	}

	NEWSSearch();
}

/**
 * Preview news text
 *
 * @param   {string}  example
 * @return  {void}
 */
function PreviewExample(example) {
	var example_vars = {};
	example_vars["startDate"] = new Date();
	var d = new Date();
	d.setDate(d.getDate() + 1);
	example_vars["endDate"] = d;
	example_vars["resources"] = ["Foo", "Bar"];
	example_vars["location"] = "Building 1, Room 2";

	var post = {
		'body': document.getElementById('help1' + example + 'input').value,
		'vars': example_vars
	};

	fetch(document.getElementById('markdown-help').getAttribute('data-api'), {
		method: 'POST',
		headers: headers,
		body: JSON.stringify(post)
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
		document.getElementById('help1' + example + 'output').innerHTML = results['formattedbody'];
	})
	.catch(function (err) {
		DisplayError(err);
	});
}

/**
 * Convert AM/PM to 24 hour format
 *
 * @param {string} time12h
 * @returns string
 */
const convertTime12to24 = (time12h) => {
	const [time, modifier] = time12h.split(' ');

	let [hours, minutes] = time.split(':');

	if (hours === '12') {
		hours = '00';
	}

	if (modifier === 'PM') {
		hours = parseInt(hours, 10) + 12;
	}

	return `${hours}:${minutes}`;
}

/**
 * Build vars for news preview
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSPreviewVars() { // news
	var preview_vars = {};
	var startDate;
	var endDate;

	/* Grab the variables we need and populate the preview variables. */
	if (document.getElementById("datestartshort").value != "") {
		if (document.getElementById("timestartshort").value != "") {
			var t = document.getElementById("timestartshort").value;
			startDate = document.getElementById("datestartshort").value + " " + convertTime12to24(t) + ':00';
		} else {
			startDate = document.getElementById("datestartshort").value + ' 00:00:00'; //" 12:00 AM"
		}
		var st = new Date(startDate);

		preview_vars["startdate"] = new Intl.DateTimeFormat("en-US", { weekday: "long" }).format(st) + ', '
			+ new Intl.DateTimeFormat("en-US", { month: "long" }).format(st) + ' ' + st.getDay() + ', '
			+ st.getFullYear(); //startDate;
		preview_vars["starttime"] = st.toLocaleTimeString('en-US').replace(':00 AM', ' AM').replace(':00 PM', ' PM');
		preview_vars["startdatetime"] = preview_vars["startdate"] + ' at ' + preview_vars["starttime"];
		preview_vars["time"] = preview_vars["starttime"];
		preview_vars["date"] = preview_vars["startdate"];
		preview_vars["datetime"] = preview_vars["date"] + ' at ' + preview_vars["time"];
	}

	if (document.getElementById("datestopshort").value != "") {
		if (document.getElementById("timestopshort").value != "") {
			var ts = document.getElementById("timestopshort").value;
			endDate = document.getElementById("datestopshort").value + " " + convertTime12to24(ts) + ':00';
		} else {
			endDate = document.getElementById("datestopshort").value + ' 00:00:00'; //" 12:00 AM"
		}

		var et = new Date(endDate);

		preview_vars["enddate"] = new Intl.DateTimeFormat("en-US", { weekday: "long" }).format(et) + ', '
			+ new Intl.DateTimeFormat("en-US", { month: "long" }).format(et) + ' ' + et.getDay() + ', '
			+ et.getFullYear();
		preview_vars["endtime"] = et.toLocaleTimeString('en-US').replace(':00 AM', ' AM').replace(':00 PM', ' PM');
		preview_vars["enddatetime"] = preview_vars["enddate"] + ' at ' + preview_vars["endtime"];
		if (preview_vars["starttime"] != preview_vars["endtime"]) {
			preview_vars["time"] = preview_vars["starttime"] + ' - ' + preview_vars["endtime"];
			preview_vars["datetime"] = preview_vars["date"] + ' from ' + preview_vars["time"];
		}
	}

	preview_vars["resources"] = [];

	var resources = Array.prototype.slice.call(document.querySelectorAll('#newsresource option:checked'), 0).map(function (v) {
		return v.innerHTML;
	});

	resources.forEach(function (el, i) {
		preview_vars['resources'][i] = el;
	});

	preview_vars['update'] = "0";

	if (document.getElementById("newstype").value == 4) {
		if (document.getElementById("location").value != "") {
			preview_vars["location"] = document.getElementById("location").value;
		}
	}

	return preview_vars;
}

/**
 * Preview news entry
 *
 * @param   {string}  news
 * @param   {bool}    edit
 * @return  {void}
 */
function NEWSPreview(news, edit) {
	if (typeof (edit) == 'undefined') {
		edit = false;
	}

	var text = "";

	if (news == "new" || edit) {
		text = document.getElementById("NotesText").value;
	} else {
		text = document.getElementById(news + "_textarea").value;
	}
	if (text == "") {
		return;
	}

	var post = { 'body': text };
	if (news == "new") {
		post['vars'] = NEWSPreviewVars(news);
	} else {
		if (edit) {
			post['vars'] = NEWSPreviewVars(news);
		}
		post['id'] = news;
	}

	fetch(root + "/preview", {
		method: 'POST',
		headers: headers,
		body: JSON.stringify(post)
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
		document.getElementById("preview").innerHTML = results['formattedbody'];
	})
	.catch(function (err) {
		DisplayError(err);
	});

	//$('#preview').dialog({ modal: true, width: '691px' });
	//$('#preview').dialog('open');
}

/**
 * Send an email
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSendMail(news) {
	var txt = document.getElementById(news + '_textarea');
	if (txt && txt.parentNode.style.display == 'block') {
		// We're still editing. Need to save first.
		if (confirm(document.getElementById('mailsend').getAttribute('data-confirm'))) {
			NEWSSaveNewsText(news);
		}

		return;
	}

	// Get text and updates from WS
	fetch(root + "/" + news, {
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
	.then(function (data) {
		var text = document.getElementById(news + "_textarea").value;
		if (text == "") {
			return;
		}

		var x = 0;

		// Gather some  variables from DOM
		var formatteddate = $('#' + news).find(".newsdate").first().html().replace(/<a href.*/, '');
		var subject = $('#' + news + "_headline").text();
		var locale = $('#' + news + "_location").text();
		if (locale != '') {
			locale = locale + "<br/>";
		}
		var resources = $('#' + news).find(".newspostresources").first().text().replace(/^Resources?: /, '');
		var name = $(".login").find("a").first().text();

		// set up header for email preview
		var header = "<strong>To:</strong> " + resources + " Users<br />"
			+ "<strong>From:</strong> " + name + "<br/>"
			+ "<strong>Subject:</strong> " + subject + " - " + formatteddate + "<br/><hr />"
			+ "<strong>" + subject + "</strong><br/>" + formatteddate + "<br/>" + locale + "<br/>";

		// set up foot for email preview
		var footer = '<hr/><a href="/news/' + news + '">Article #' + news + '</a> posted on ' + data['formattedcreateddate'] + '.</a>';

		var body = "";
		if (data['updates'].length > 0) {
			for (x = 0; x < data['updates'].length; x++) {
				body = body + '<section><h3 class="newsupdate">UPDATE: ' + data['updates'][x]['formattedcreateddate'] + '</h3>' + data['updates'][x]['formattedbody'] + '</section>';
			}
			body = body + '<section><h3 class="newsupdate">ORIGINAL: ' + data['formattedcreateddate'] + '</h3>';
		}
		body = body + data['formattedbody'];
		if (data['updates'].length > 0) {
			body += '</section>';
		}

		if (data['resources'].length > 0) {
			footer += '<hr /><p>Send to resource mailing lists:</p><div class="row">';
			for (x = 0; x < data['resources'].length; x++) {
				footer += '<div class="col-md-3"><label><input type="checkbox" checked="checked" value="' + data['resources'][x]['resourceid'] + '" class="preview-resource" /> ' + data['resources'][x]['name'] + '</label></div>';
			}
			footer += '</div>';
		}

		document.getElementById("mailpreview").innerHTML = header + body + footer;

		var mailsend = document.getElementById('mailsend');
		if (mailsend && mailsend.getAttribute('data-listening') != '1') {
			mailsend.addEventListener('click', function (e) {
				e.preventDefault();

				var post = {
					'mail': 1,
					'lastedit': LASTEDIT[news]
				};

				var resources = [];
				document.querySelectorAll('.preview-resource').forEach(function (el) {
					if (el.checked) {
						resources.push(el.value);
					}
				});
				post.resources = resources;

				post = JSON.stringify(post);

				fetch(root + "/" + news + "/email", {
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
					document.getElementById("IMG_mail_" + news).className = "fa fa-check";
					document.getElementById("A_mail_" + news).onclick = function () { };

					LASTEDIT[news] = results['lastedit'];

					NEWSSearch();
				})
				.catch(function (err) {
					document.getElementById("IMG_mail_" + news).className = "fa fa-exclamation-circle";
					document.getElementById("A_mail_" + news).onclick = function () { };
					DisplayError(err);
				});
			});
			mailsend.setAttribute('data-listening', 1);
		}
	})
	.catch(function (err) {
		DisplayError(err);
	});
}

/**
 * Write an email
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSWriteMail(news) {
	fetch(root + "/" + news, {
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
	.then(function (data) {
		document.getElementById('mail-subject').value = data.headline;

		var body = '**Date:** ' + data.formatteddate.replace(/(<([^>]+)>)/ig, '').replace(/&nbsp;/g, ' ').replace('&#8211;', '-') + "\n";

		if (data.location) {
			body += '**Location:** ' + data.location + "\n";
		}
		if (data.url) {
			body += '**URL:** ' + data.url + "\n";
		}

		document.getElementById('mail-body').value = body + "\n\n";

		var to = $('#mail-to');
		to.val('');
		to.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(to.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1
			}
		});
		to.clearTags();

		var x;
		for (x = 0; x < data.associations.length; x++) {
			if ($('.tagsinput').length) {
				if (!to.tagExist(data.associations[x]['id'])) {
					to.addTag({
						'id': data.associations[x]['associd'],
						'label': data.associations[x]['name']
					});
				}
			}
		}

		document.getElementById('mailsend-write').addEventListener('click', function (e) {
			e.preventDefault();

			var usersdata = document.getElementById("mail-to").value.split(',');
			var associations = [],
				i;
			for (i = 0; i < usersdata.length; i++) {
				if (usersdata[i] != "") {
					associations.push(usersdata[i]);
				}
			}

			var post = JSON.stringify({
				'mail': 1,
				'lastedit': LASTEDIT[news],
				'headline': document.getElementById('mail-subject').value,
				'body': document.getElementById('mail-body').value,
				'associations': associations
			});

			fetch(root + "/" + news + "/email", {
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
				document.getElementById("IMG_mail_" + news).className = "fa fa-check";
				document.getElementById("A_mail_" + news).onclick = function () { };

				LASTEDIT[news] = results['lastedit'];

				NEWSSearch();
			})
			.catch(function (err) {
				document.getElementById("IMG_mail_" + news).className = "fa fa-exclamation-circle";
				document.getElementById("A_mail_" + news).onclick = function () { };
				DisplayError(err);
			});
		});
	})
	.catch(function (err) {
		DisplayError(err);
	});
}

/**
 * Use selected news template
 *
 * @param   {object}  xml
 * @param   {string}  news
 * @return  {void}
 */
function NEWSUseTemplate() {
	var template = document.getElementById("template_select");
	template = template.options[template.selectedIndex].value;//.getAttribute('data-api')

	if (template != "0" && template != "savetemplate") {

		var overwrite = false;
		if (document.getElementById("Headline").value != "") {
			overwrite = true;
		}
		if (document.getElementById("NotesText").value != "") {
			overwrite = true;
		}

		if (overwrite) {
			overwrite = !confirm("Are you sure you wish to overwrite text with this template? Any work will be lost.");
		}

		if (!overwrite) {
			document.getElementById("TR_use_template").classList.add('d-none');
			document.getElementById("TR_template").classList.add('d-none');
			document.getElementById("TR_date").classList.remove('d-none');
			document.getElementById("template").checked = false;
			document.getElementById("published").checked = true;

			fetch(template, {
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
			.then(function (news) {
				document.getElementById("Headline").value = news.headline.replace(/&#039;/g, "'").replace(/&quot;/g, '"');
				document.getElementById("NotesText").value = news.body.replace(/&#039;/g, "'").replace(/&quot;/g, '"');
				document.getElementById("NotesText").dispatchEvent(new Event('refreshEditor', { bubbles: true }));

				var newstype = document.getElementById("newstype");
				var x;

				for (x = 0; x < newstype.options.length; x++) {
					if (newstype.options[x].value == news.newstypeid) {
						newstype.selectedIndex = x;
					}
				}

				var resources = Array.prototype.slice.call(document.querySelectorAll('#newsresource option:checked'), 0).map(function (v) {
					return v.value;
				});

				for (x = 0; x < news.resources.length; x++) {
					resources.push(news.resources[x]['resourceid']);
				}

				$('#newsresource')
					.val(resources)
					.trigger('change');

				NEWSSearch();
			})
			.catch(function (err) {
				DisplayError(err);
			});
		} else {
			document.getElementById("template_select").selectedIndex = 0;
		}
	}
}

/**
 * Expand the text area for a new update
 *
 * @param   {string}  comment
 * @return  {void}
 */
function NewsExpandNewUpdate(comment) {
	var textarea = document.getElementById(comment);
	textarea.setAttribute('rows', 7);

	var img = document.getElementById(comment + "save");
	if (img) {
		img.style.display = "inline-block";
	}
}

/**
 * Collapse the text area for a new update
 *
 * @param   {string}  comment
 * @return  {void}
 */
function NewsCollapseNewUpdate(comment) {
	var textarea = document.getElementById(comment);
	if (textarea.value == "") {
		textarea.setAttribute('rows', 1);

		var img = document.getElementById(comment + "save");
		if (img) {
			img.style.display = "none";
		}
	}
}

/**
 * Save a new update
 *
 * @param   {string}  postid
 * @return  {void}
 */
function NewsPostUpdate(newsid) {
	var body = document.getElementById(newsid + "_newupdatebox").value;

	var post = JSON.stringify({
		'newsid': newsid,
		'body': body
	});

	fetch(root + "/" + newsid + "/updates", {
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
		NewsPrintUpdate(newsid, results);
		document.getElementById(newsid + "_newupdatebox").value = "";
		NewsCollapseNewUpdate(newsid + "_newupdatebox");
	})
	.catch(function (err) {
		document.getElementById(newsid + "_newupdateboxsave").className = "fa fa-exclamation-circle";
		document.getElementById(newsid + "_newupdateboxsave").parentNode.title = err;
	});
}

/**
 * Call back after saving a new update
 *
 * @param   {string}  newsid
 * @param   {array}   update
 * @param   {bool}    edit
 * @return  {void}
 */
function NewsPrintUpdate(newsid, update, edit) {
	if (typeof (edit) === 'undefined') {
		edit = update.can.edit;
	}

	var page = document.location.href.split("/")[4];
	if (page == 'news') {
		update['body'] = HighlightMatches(update['body']);
	}

	var container = document.getElementById(newsid + '_updates');

	var li = document.createElement("li");

	var panel = document.createElement("div");
	panel.className = "card panel panel-default mb-3";

	var img, a, span;

	if (edit) {
		var tr = document.createElement("div");
		tr.className = 'card-header panel-heading news-admin';

		span = document.createElement("span");
		span.className = 'newsid';
		span.innerHTML = '#' + update['id'];

		tr.appendChild(span);

		a = document.createElement("a");
		a.className = 'edit news-update-delete tip text-danger';
		a.href = "?delete&update=" + update['id'];
		a.onclick = function (e) {
			e.preventDefault();
			NewsDeleteUpdate(update['id'], newsid);
		};
		a.id = update['id'] + "_deleteicon";
		a.title = "Delete update.";

		img = document.createElement("i");
		img.className = "crmeditdeletecomment fa fa-trash";
		img.setAttribute('aria-hidden', true);
		img.id = update['id'] + "_updatedeleteimg";

		a.appendChild(img);
		tr.appendChild(a);

		panel.appendChild(tr);
	}

	var div = document.createElement("div");
	div.className = "card-body panel-body crmcomment crmcommenttext";

	span = document.createElement("span");
	span.id = update['id'] + "_update";
	span.innerHTML = update['formattedbody'];

	div.appendChild(span);

	if (edit) {
		// Edit button
		a = document.createElement("a");
		a.href = "?update=" + update['id'] + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			NewsEditUpdateTextOpen(update['id']);
		};
		a.className = "news-update-edit tip";
		a.title = "Edit update.";
		a.id = update['id'] + "_updatetextediticon";

		img = document.createElement("i");
		img.className = "crmedittextcomment fa fa-pencil";
		img.setAttribute('aria-hidden', true);
		img.id = update['id'] + "_updatetextediticonimg";

		a.appendChild(img);
		div.appendChild(a);

		var label = document.createElement("label");
		label.setAttribute('for', update['id'] + "_updatetextarea");
		label.className = 'sr-only';
		label.innerHTML = 'Update text';

		// Text box
		var textarea = document.createElement("textarea");
		textarea.id = update['id'] + "_updatetextarea";
		textarea.innerHTML = update['body'];
		//textarea.style.display = "none";
		textarea.className = "form-control md newsupdateedittextbox";
		textarea.setAttribute('data-vars', JSON.stringify(update.vars));

		span = document.createElement("span");
		span.appendChild(label);
		span.appendChild(textarea);
		span.style.display = "none";

		div.appendChild(span);

		// Save button
		a = document.createElement("a");
		a.href = "?update=" + update['id'] + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			NewsSaveUpdateText(newsid, update['id']);
		};
		a.className = "news-update-save btn float-right tip";
		a.id = update['id'] + "_updatetextsaveicon";
		a.style.display = "none";
		a.title = "Save update text.";

		img = document.createElement("i");
		img.className = "crmsavetext fa fa-save";
		img.setAttribute('aria-hidden', true);
		img.id = update['id'] + "_updatetextsaveiconimg";

		a.appendChild(img);
		div.appendChild(a);
	}

	panel.appendChild(div);

	div = document.createElement("div");
	div.className = "card-footer panel-footer";

	var div2 = document.createElement("div");
	div2.innerHTML += "Posted by " + update['username'] + " on " + update['formattedcreateddate'];
	div2.className = "crmcommentpostedby";

	div.appendChild(div2);
	panel.appendChild(div);

	li.appendChild(panel);
	container.appendChild(li);
}

/**
 * Toggle controls open for editing news update text
 *
 * @param   {string}  update
 * @return  {void}
 */
function NewsEditUpdateTextOpen(update) {
	// hide text
	var text = document.getElementById(update + "_update");
	text.style.display = "none";

	// show textarea
	var box = document.getElementById(update + "_updatetextarea");
	box.dispatchEvent(new Event('initEditor', { bubbles: true }));
	box.parentNode.style.display = "block";

	// hide edit icon
	var eicon = document.getElementById(update + "_updatetextediticon");
	eicon.style.display = "none";

	// show save icon
	var sicon = document.getElementById(update + "_updatetextsaveicon");
	sicon.style.display = "block";

	var img = document.getElementById(update + "_updatetextsaveiconimg");
	img.className = "fa fa-save";
}

/**
 * Save news update text
 *
 * @param   {string}  update
 * @return  {void}
 */
function NewsSaveUpdateText(newsid, update) {
	// get text
	var text = document.getElementById(update + "_updatetextarea").value;

	// change save icon
	var icon = document.getElementById(update + "_updatetextsaveicon");
	icon.onclick = function () { };

	var img = document.getElementById(update + "_updatetextsaveiconimg");
	img.className = "fa fa-spinner fa-spin";

	var post = { 'body': text };
	post = JSON.stringify(post);

	fetch(root + "/" + newsid + "/updates/" + update, {
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
	.then(function(results) {
		var icon = document.getElementById(update + "_updatetextsaveicon");
		icon.style.display = "none";
		icon.onclick = function () {
			NewsSaveUpdateText(results.data.newsid, update);
		};
		var text = document.getElementById(update + "_update");
		text.style.display = "block";
		text.innerHTML = results.formattedbody;

		var box = document.getElementById(update + "_updatetextarea");
		box.parentNode.style.display = "none";

		var editicon = document.getElementById(update + "_updatetextediticon");
		editicon.style.display = "block";
	})
	.catch(function (error) {
		var img = document.getElementById(update + "_updatetextsaveiconimg");
		if (img) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = error;
		}
	});
}
/*
function joinDate(t, a, s) {
	function format(m) {
		let f = new Intl.DateTimeFormat('en', m);
		return f.format(t);
	}
	return a.map(format).join(s);
}

function NEWSSetContentVars()
{
	var vars = NEWSPreviewVars();
	var start = new Date(vars.startdate);
	console.log(vars.startdate);
	let a = [{ day: 'numeric' }, { month: 'short' }, { year: 'numeric' }];
	vars.startdate = joinDate(start, a, '-');
	//vars.startdate = start.getMonth() + ' ' + start.getDay() + ', ' + start.getFullYear();
	vars.startdatetime = vars.startdate + ' at ' + start.getHours() + ':' + start.getMinutes();

	if (vars.enddate) {
		var end = new Date(vars.enddate);
		vars.enddate = end.getMonth() + ' ' + end.getDay() + ', ' + end.getFullYear();
		vars.enddatetime = vars.enddate + ' at ' + end.getHours() + ':' + end.getMinutes();
	}

	document.getElementById("NotesText").setAttribute('data-vars', JSON.stringify(vars));
}*/

/**
 * Delete a news update
 *
 * @param   {string}  updateid
 * @param   {string}  reportid
 * @return  {void}
 */
function NewsDeleteUpdate(updateid, reportid) {
	if (confirm("Are you sure you want to delete this update?")) {
		fetch(root + "/" + reportid + "/updates/" + updateid, {
			method: 'DELETE',
			headers: headers
		})
		.then(function (response) {
			if (response.ok) {
				$('#' + updateid + "_update").closest('li').remove();
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
			var img = document.getElementById(updateid + "_updatedeleteimg");
			if (img) {
				img.className = "fa fa-exclamation-circle";
				img.parentNode.title = error;
			}
		});
	}
}

var autocompleteUsers = function (url) {
	return function (request, response) {
		return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
			response($.map(data.data, function (el) {
				return {
					label: el.name + ' (' + el.username + ')',
					name: el.name + ' (' + el.username + ')',
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

	var tabs = document.querySelectorAll('#tabs a');

	for (i = 0; i < tabs.length; i++) {
		tabs[i].addEventListener('click', function (event) {
			event.preventDefault();

			NEWSToggle(this.getAttribute('href').replace('#', ''));
		});
	}

	// Date/time
	$('.date-pick,.time-pick').on('change', function () {
		//NEWSSetContentVars();
		NEWSDateSearch($(this).attr('name'));
	});

	if ($('.time-pick').length) {
		$('.time-pick').timepicker({
			timeFormat: "h:i A",
			minTime: '8:00am',
			maxTime: '5:00pm',
			change: function () {
				$(this).trigger('change');
			}
		}).keyup(function (e) {
			if (e.keyCode == 8 || e.keyCode == 46) {
				$(this).val('');
			}
		});
	}

	// Dialogs
	document.querySelectorAll('.samplebox').forEach(function(el) {
		el.addEventListener('keyup', function () {
			PreviewExample(this.getAttribute('data-sample'));
		});
	});

	var location = document.getElementById('location');
	if (location) {
		location.addEventListener('keyup', function () {
			NEWSToggleAddButton();
		});
	}
	var newstype = document.getElementById('newstype');
	if (newstype) {
		newstype.addEventListener('change', function () {
			NEWSNewstypeSearch(this.value);
		});
	}
	var headline = document.getElementById('Headline');
	if (headline) {
		headline.addEventListener('keyup', function () {
			NEWSToggleAddButton();
		});
	}
	var notes = document.getElementById('NotesText');
	if (notes) {
		notes.addEventListener('change', function () {
			NEWSToggleAddButton();
		});
	}
	var keywords = document.getElementById('keywords');
	if (keywords) {
		keywords.addEventListener('keyup', function (event) {
			NEWSKeywordSearch(event.keyCode);
		});
	}
	var id = document.getElementById('id');
	if (id) {
		id.addEventListener('keyup', function (event) {
			NEWSKeywordSearch(event.keyCode);
		});
	}
	var templatesel = document.getElementById('template_select');
	if (templatesel) {
		templatesel.addEventListener('change', function () {
			NEWSSearch();
		});
	}
	var datesegmented = document.getElementById('datesegmented');
	if (datesegmented) {
		datesegmented.addEventListener('change', function () {
			$('#TR_date').toggle();
			$('#TR_newstime').toggle();
		});
	}
	var template = document.getElementById('template');
	if (template) {
		template.addEventListener('change', function () {
			NEWSSearch();
		});
	}
	var published = document.getElementById('published');
	if (published) {
		published.addEventListener('change', function () {
			NEWSSearch();
		});
	}

	// Buttons
	var search = document.getElementById('INPUT_search');
	if (search) {
		search.addEventListener('click', function (event) {
			event.preventDefault();
			NEWSSearch();
		});
	}
	var clear = document.getElementById('INPUT_clearsearch');
	if (clear) {
		clear.addEventListener('click', function (event) {
			event.preventDefault();
			NEWSClearSearch();
		});
	}
	var add = document.getElementById('INPUT_add');
	if (add) {
		add.addEventListener('click', function (event) {
			event.preventDefault();
			NEWSAddEntry();
		});
	}
	var preview = document.getElementById('INPUT_preview');
	if (preview) {
		preview.addEventListener('click', function (event) {
			event.preventDefault();
			NEWSPreview(this.getAttribute('data-id'), true);
		});
	}

	var searchfrm = document.querySelector('#DIV_news form');
	if (searchfrm) {
		searchfrm.addEventListener('submit', function (e) {
			e.preventDefault();
			return false;
		});
	}

	var rselects = $(".searchable-select-multi");
	if (rselects.length) {
		rselects.select2({
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
						NEWSSearch();
					});

					var elp = $('<span class="my_select2_optgroup">' + item.text + '</span>');
					elp.append(el);

					return elp;
				}
				return item.text;
			}
		})
		.on('select2:select', function () {
			//NEWSSetContentVars();
			NEWSSearch();
		})
		.on('select2:unselect', function () {
			//NEWSSetContentVars();
			NEWSSearch();
		});
	}

	var newsuser = $("#newsuser");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1
			}/*,
			'onAddTag': function(input, value) {
				NEWSSearch();
			},
			'onRemoveTag': function(input, value) {
				NEWSSearch();
			}*/
		});
	}

	document.querySelectorAll('.btn-attend').forEach(function (btn) {
		btn.addEventListener('click', function (event) {
			event.preventDefault();

			var el = $(this);
			var id = btn.getAttribute('data-newsid');
			var post = {
				'associd': btn.getAttribute('data-assoc'),
				'assoctype': 'user',
				'newsid': id
			};

			el.parent().find('.alert').remove();

			if (btn.getAttribute('data-comment') && $(btn.getAttribute('data-comment'))) {
				post['comment'] = $(btn.getAttribute('data-comment')).val().trim();
				var words = post['comment']
					.replace(/^[\s,.;]+/, "")
					.replace(/[\s,.;]+$/, "")
					.split(/[\s,.;]+/)
					.length;

				if (!post['comment'] || words < 1) {
					$(btn.getAttribute('data-comment')).addClass('is-invalid');
					btn.parentNode.innerHTML = btn.parentNode.innerHTML + '<span class="alert alert-danger">Please provide a comment.</span>';
					return;
				} else {
					$(btn.getAttribute('data-comment')).removeClass('is-invalid');
				}
			}

			fetch(root + "/associations", {
				method: 'POST',
				headers: headers,
				body: JSON.stringify(post)
			})
			.then(function (response) {
				if (response.ok) {
					btn.parentNode.innerHTML = '<span class="alert alert-success">Thank you for your interest!</span>';
					setTimeout(function () {
						window.location.reload(true);
					}, 1000);
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
				btn.parentNode.innerHTML = btn.parentNode.innerHTML + '<span class="alert alert-error">' + error + '</span>';
			});
		});
	});

	document.querySelectorAll('.btn-notattend').forEach(function (el) {
		el.addEventListener('click', function (event) {
			event.preventDefault();

			fetch(root + "/associations/" + el.getAttribute('data-id'), {
				method: 'DELETE',
				headers: headers
			})
			.then(function (response) {
				if (response.ok) {
					el.parentNode.innerHTML = '<span class="alert alert-success">Successfully cancelled.</span>';
					setTimeout(function () {
						window.location.reload(true);
					}, 1000);
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
				el.parentNode.innerHTML = el.parentNode.innerHTML + '<span class="alert alert-error">' + error + '</span>';
			});
		});
	});

	var revealbtn = document.getElementById('attendees-reveal');
	if (revealbtn) {
		revealbtn.addEventListener('click', function (e) {
			e.preventDefault();

			this.classList.add('d-none');
			document.getElementById('attendees-all').classList.remove('d-none');
			document.getElementById('attendees').classList.add('d-none');
		});
	}

	var url = window.location.href.match(/[&?](\w+)$/),
		on = 'search',
		refresh = true;

	if (url != null) {
		on = url[1];
	}

	var data = document.getElementById('news-data');
	if (data) {
		var original = JSON.parse(data.innerHTML);
		var i = 0;

		document.getElementById('newstype').value = original['newstypeid'];
		/*$("#newstype > option").each(function () {
			if (this.value == original['newstypeid']) {
				$('#newstype > option:selected', 'select[name="options"]').removeAttr('selected');
				$(this).attr('selected', true);
			}
		});*/

		document.getElementById('datestartshort').value = original.startdate;
		if (original.newsdateend != '0000-00-00 00:00:00') {
			document.getElementById('datestopshort').value = original.stopdate;
		}
		if (original.starttime != '') {
			document.getElementById('timestartshort').value = original.starttime;
		}
		if (original.stoptime != '') {
			document.getElementById('timestopshort').value = original.stoptime;
		}

		document.getElementById('Headline').value = original.headline;
		document.getElementById('location').value = original.location;
		document.getElementById('url').value = original.url;
		document.getElementById('NotesText').innerHTML = original.news;
		var results = [];
		for (i = 0; i < original.resources.length; i++) {
			//NEWSAddResource(original.resources[i]);
			results[i] = original.resources[i]['resourceid'];
		}
		$('#newsresource')
			.val(results)
			.trigger('change');
		for (i = 0; i < original.associations.length; i++) {
			NEWSAddAssociation(original.associations[i]);
		}
		if (original.published == "1") {
			document.getElementById('published').checked = true;
		} else {
			document.getElementById('published').checked = false;
		}
		if (original.template == "1") {
			document.getElementById('template').checked = true;
		} else {
			document.getElementById('template').checked = false;
		}

		on = 'edit';
		refresh = false;
	}

	var news = document.getElementById('news');
	if (news) {
		root = news.getAttribute('data-api');
		NEWSToggle(on, refresh);
		NEWSSearch();
	}

	var stats = document.getElementById('articlestats');
	if (stats) {
		fetch(stats.getAttribute('data-api'), {
			method: 'GET',
			headers: headers
		})
		.then(function (response) {
			if (response.ok) {
				return response.json();
			}
		})
		.then(function (data) {
			document.getElementById('viewcount').innerHTML = data.viewcount;
			document.getElementById('uniqueviewcount').innerHTML = data.uniquecount;
		});
	}
});
