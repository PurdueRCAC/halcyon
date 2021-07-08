/* global $ */ // jquery.js
/* global root */ // common.js
/* global WSGetURL */ // common.js
/* global WSPostURL */ // common.js
/* global WSDeleteURL */ // common.js
/* global SetError */ // common.js
/* global PrepareText */ // common.js
/* global HighlightMatches */ // text.js

var keywords_pending = 0;
//var img_url = "/include/images/";
var LASTEDIT = new Array();
var root = base_url + "/api/";

/**
 * Functions common to the user (queue management page)
 */

/**
 * Toggle UI tabs
 *
 * @param   {string}  on
 * @param   {bool}    refresh
 * @return  {void}
 */
function NEWSToggle(on, refresh) {
	if (typeof(refresh) == 'undefined') {
		refresh = true;
	}
	var option = document.getElementById("OPTION_all");

	var times = document.getElementsByClassName('input-time');
	for (var i = 0; i < times.length; i++)
	{
		if (!$(times[i]).hasClass('tab-' + on)) {
			if (!$(times[i]).hasClass('d-none')) {
				$(times[i]).addClass('d-none');
			}
		} else {
			$(times[i]).removeClass('d-none');
		}
	}

	// Set header
	var header = document.getElementById('SPAN_header');
	if (header) {
		header.innerHTML = header.getAttribute('data-' + on);
	}

	$(".tab-add").addClass('d-none');
	$(".tab-edit").addClass('d-none');
	$(".tab-search").addClass('d-none');
	$(".tab-" + on).removeClass('d-none');

	// Remove errors upon a toggle
	document.getElementById("TAB_search_action").innerHTML = "";
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
		document.getElementById("INPUT_preview").onclick = function () { NEWSPreview('new'); };

		document.getElementById("location").value = "";
		document.getElementById("NotesText").value = "";
		document.getElementById("published").checked = false;
		document.getElementById("template").checked = false;

		// Remove all news type option
		if (option) {
			document.getElementById('newstype').removeChild(option);
		}

		/*if ($('#DIV_resource').length) {
			document.getElementById("DIV_resourcesearch").style.display = "none";
			document.getElementById("DIV_resource").innerHTML = "Click to add resource to post.";
		}*/
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

		/*if ($('#DIV_resource').length) {
			document.getElementById("DIV_resource").style.display = "block";
			document.getElementById("DIV_resourcesearch").style.display = "none";
			document.getElementById("DIV_resource").innerHTML = "Click to add resource to post.";
		}*/
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
	if (typeof(history.pushState) != 'undefined') {
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
 * Add a resource to the list of resources
 *
 * @param   {array}  resource
 * @return  {void}
 */
function NEWSAddResource(resource) {
	var results;
	if (typeof resource == 'string') {
		results = JSON.parse(resource);
	} else {
		results = resource;
	}

	$('#newsresource')
		.val(results['resourceid'])
		.trigger('change');
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

	if ($('.tagsinput').length) {
		if (!$('#newsuser').tagExist(results['id'])) {
			$('#newsuser').addTag({
				'id': results['associd'],
				'label': results['assocname']
			});
		}
	} else {
		$('#newsuser').val($('#newsuser').val() + ($('#newsuser').val() ? ', ' : '') + results['name'] + ':' + results['id']);
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
	if (document.getElementById("TR_resource")) {
		if (tagresources == "0") {
			$('#TR_resource').addClass('d-none');
		} else {
			$('#TR_resource').removeClass('d-none');
		}
	}
	if (document.getElementById("TR_location")) {
		if (taglocation == "0") {
			$('#TR_location').addClass('d-none');
		} else {
			$('#TR_location').removeClass('d-none');
		}
	}
	if (document.getElementById("TR_url")) {
		if (tagurl == "0") {
			$('#TR_url').addClass('d-none');
		} else {
			$('#TR_url').removeClass('d-none');
		}
	}
	if (document.getElementById("TR_user")) {
		if (tagusers == "0") {
			$('#TR_user').addClass('d-none');
		} else {
			$('#TR_user').removeClass('d-none');
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
			if (stoptime == "" && starttime != "") {
				document.getElementById("timestopshort").value = starttime;
			}
			if (stop != "" && start != "" && box == "start") {
				var START = new Date(STARTBOX);
				var STOP = new Date(STOPBOX);

				var diff = ((Date.parse(STARTBOX) + START.getTimezoneOffset()*60*1000) - (Date.parse(STOPBOX) + STOP.getTimezoneOffset()*60*1000));
				var now = new Date(start);
				var d = new Date(Date.parse(start) - diff + (now.getTimezoneOffset()*60*1000));
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
	for (var x=0;x<keywords.length;x++) {
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
 * replace <'s and '>
 *
 * @param   {string}  text
 * @return  {string}
 */
function PrepareText(text) {
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');

	return text;
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
	var notes = PrepareText(document.getElementById("NotesText").value);
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
	document.getElementById("TAB_search_action").innerHTML = "";
	document.getElementById("TAB_add_action").innerHTML = "";

	if (!newsdate.match(/^\d{4}-\d{2}-\d{2}$/) && !template) {
		SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
		return;
	}

	if (!newsdate) {
		newsdate = '0000-00-00';
	}
	match = newstime.match(/^(\d{1,2}):(\d{2}) ?(AM|PM)$/);
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
		newsdate += " " + hour + ":" + match[2] + ":00";
	} else {
		newsdate += " 00:00:00";
	}

	// newsdateend is optional, use default value if missing
	if (newsdateend.match(/^\d{4}-\d{2}-\d{2}$/)) {
		match = newstimeend.match(/^(\d{1,2}):(\d{2}) ?(AM|PM)$/);
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
			newsdateend += " " + hour + ":" + match[2] + ":00";
		} else {
			newsdateend += " 00:00:00";
		}
	} else {
		newsdateend = "0000-00-00 00:00:00";
	}

	if (newsdateend != "0000-00-00 00:00:00" && newsdateend < newsdate) {
		SetError('End date must come after start date', 'Please enter a valid end date');
		return;
	}

	if (tagresources == "1") {
		resource = Array.prototype.slice.call(document.querySelectorAll('#newsresource option:checked'), 0).map(function (v) {
			return v.value;
		});
	}

	var usersdata = document.getElementById("newsuser").value.split(',');
	for (i=0; i<usersdata.length; i++) {
		if (usersdata[i] != "") {
			associations.push(usersdata[i]);
		}
	}

	var post = {};
	if (window.location.href.match(/(\?|&)edit/)) {
		var original = {};
		var data = $('#news-data');
		if (data.length) {
			original = JSON.parse(data.html());
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
				post['datetimenewsend'] = '0000-00-00 00:00:00';
			}
		}
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
			WSPutURL(original['api'], post, NEWSUpdatedNews, id);
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

		WSPostURL(root + "news", post, NEWSNewNews);
	}
}

/**
 * Update news search
 *
 * @param   {object}  xml
 * @param   {string}  id
 * @return  {void}
 */
function NEWSUpdatedNews(xml, id) {
	if (xml.status < 400) {
		document.getElementById("INPUT_add").disabled = true;
		document.getElementById("location").value = "";
		NEWSToggle('search', false);
		document.getElementById("id").value = id;
		NEWSSearch();
	} else if (xml.status == 409) {
		document.getElementById("id").value = id;
		WSGetURL(root + "news/" + id, function (xml) {
			if (xml.status < 400) {
				var results = JSON.parse(xml.responseText);
				alert("Unable to save changes. This news item has been edited by " + results['editusername'] + " since you loaded this page. Please make note of your changes and reload the page to try editing again.");
			}
		});
	} else {
		document.getElementById("INPUT_add").disabled = true;
		NEWSToggle('search');
		document.getElementById("id").value = id;
		NEWSSearch();
		alert("Unable to save changes.");
	}
}

/**
 * Clear the form for adding a new News entry
 *
 * @param   {object}  xml
 * @return  {void}
 */
function NEWSNewNews(xml) {
	document.getElementById("INPUT_add").disabled = false;

	if (xml.status < 400) {
		var results = JSON.parse(xml.responseText);

		// Clear the resource listing
		/*if ($('.tagsinput').length) {
			$('#newsresource').clearTags();
		} else {
			document.getElementById('newsresource').value = '';
		}
		document.getElementById("Headline").value = "";
		document.getElementById("location").value = "";
		document.getElementById("url").value = "";
		document.getElementById("NotesText").value = "";
		document.getElementById("datestartshort").value = "";
		document.getElementById("timestartshort").value = "";
		document.getElementById("datestopshort").value = "";
		document.getElementById("timestopshort").value = "";

		//var id = results['id'];
		//id = id.split('/');
		//id = id[id.length-1];*/

		if (results.template) {
			NEWSToggle('search', true);
		} else {
			window.location.href = results.uri;
		}
	} else if (xml.status == 409 || xml.status == 415) {
		SetError('Invalid date.', 'Please pick the current date or a date in the past.');
	} else {
		SetError('Unable to create news story.', 'An error occurred during processing of news story.');
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
			$(list).addClass('d-none');
		} else {
			$(list).removeClass('d-none');
		}
	}

	if (locale) {
		if (taglocation == "0") {
			$(locale).addClass('d-none');
		} else {
			$(locale).removeClass('d-none');
		}
	}

	if (url) {
		if (!tagurl || tagurl == "0") {
			$(url).addClass('d-none');
		} else {
			$(url).removeClass('d-none');
		}
	}

	if (users) {
		if (!tagusers || tagusers == "0") {
			$(users).addClass('d-none');
		} else {
			$(users).removeClass('d-none');
		}
	}

	locale = document.getElementById("location").value;

	// Fetch list of selected resources
	//var resourcedata = document.getElementById("newsresource").value.split(',');
	var resourcedata = Array.prototype.slice.call(document.querySelectorAll('#newsresource option:checked'), 0).map(function (v) {
		return v.value;
	});
	for (var i=0; i<resourcedata.length; i++) {
		if (resourcedata[i] != "") {
			if (resourcedata[i].indexOf('/') !== -1) {
				var resource = resourcedata[i].split('/');
				resources.push(resource[resource.length-1]);
			} else {
				resources.push(resourcedata[i]);
			}
		}
	}

	// sanity checks
	if (start != "") {
		if(!start.match(/^\d{4}-\d{2}-\d{2}$/)) {
			SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			// clear error boxes
			document.getElementById("TAB_search_action").innerHTML = "";
			document.getElementById("TAB_add_action").innerHTML = "";
		}
		start = start + "!00:00:00";
	}

	if (stop != "") {
		if(!stop.match(/^\d{4}-\d{2}-\d{2}$/)) {
			SetError('Date format invalid', 'Please enter date as YYYY-MM-DD.');
			return;
		} else {
			// clear error boxes
			document.getElementById("TAB_search_action").innerHTML = "";
			document.getElementById("TAB_add_action").innerHTML = "";
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
	var searchstring = "";
	var querystring = "&";

	if (published) {
		querystring += "state=published";
	} else {
		querystring += "state=*";
	}

	var in_edit = false;
	if (window.location.href.match(/[&?]edit/)) {
		document.getElementById("INPUT_add").disabled = false;
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
			searchstring += " start:"  + start;
			querystring += "&start="  + start;
		}
		if (stop != "") {
			searchstring += " stop:" + stop;
			querystring += "&stop=" + stop;
		}
	}

	// Construct resource query
	if (!in_edit && resources.length > 0) {
		searchstring += " resource:" + resources[0];
		querystring += "&resource=" + resources[0];
		for (var x=1;x<resources.length;x++) {
			searchstring += "," + resources[x];
			querystring += "," + resources[x];
		}
	}

	if (!in_edit && newstypeid != "" && newstypeid != '-1') {
		searchstring += " type:" + newstypeid;
		querystring += "&type=" + newstypeid;
	}

	if (window.location.href.match(/[&?]all/)) {
		searchstring += " limit:0";
		querystring += "&limit=0";
	}

	// if not add new
	if (!in_add) {
		if (keywords != "") {
			// format news ticket queries correctly
			keywords = keywords.replace(/NEWS#(\d+)/g, '$1 $2');

			// filter out potentially dangerous garbage
			keywords = keywords.replace(/[^a-zA-Z0-9_ ]/g, '');
			searchstring += " " + keywords;
			querystring += "&keywords=" + keywords;
		}
	}
	if (!in_add) {
		if (locale != "") {
			searchstring += " location:" + locale;
			querystring += "&location=" + locale;
		}
	}
	if (template) {
		searchstring += " template:1";
		querystring += "&template=1";
	}

	// if not add new
	if (!in_add || in_edit) {
		if (id.match(/(\d)+/)) {
			searchstring += " id:" + id;
			querystring += "&id=" + id;
		}
	}

	if (typeof(history.pushState) != 'undefined') {
		var tb = window.location.href.match(/[&?](\w+)&?$/);
		if (tb != null) {
			querystring = querystring + "&" + tb[1];
		}
		querystring = querystring.replace(/^&+/, '?');
		history.pushState(null, null, encodeURI(querystring));
	}

	//if (searchstring == "") {
	//	searchstring = "start:0000-00-00";
	//}
	var page = document.getElementById('page');
	if (page) {
		querystring += '&page=' + page.value;
	}
	querystring += '&api_token=' + $('meta[name="api-token"]').attr('content');
	console.log(root + "news/" + encodeURI(querystring));

	WSGetURL(root + "news/" + encodeURI(querystring), NEWSSearched);
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
		} else  {
			document.getElementById("INPUT_add").disabled = true;
		}

		if (template) {
			$('#TR_date').addClass('d-none');
			$('#TR_published').addClass('d-none');
			//document.getElementById("TR_date").style.display = "none";
			//document.getElementById("TR_published").style.display = "none";
		} else {
			//document.getElementById("TR_date").style.display = "block";
			//document.getElementById("TR_published").style.display = "block";
			$('#TR_date').removeClass('d-none');
			$('#TR_published').removeClass('d-none');
		}
	}
}

/**
 * Builds the news div with results returned from the WS
 *
 * @param   {object}  xml
 * @return  {void}
 */
function NEWSSearched(xml) {
	if (xml.status == 200) {
		var news = document.getElementById("news");

		// clear out reports
		news.innerHTML = "";

		// parse results
		var results = JSON.parse(xml.responseText);
		//var edit = false;//results['canEdit']; //(results['authorized'] == 1) ? true : false;

		document.getElementById("matchingnews").innerHTML = "Found " + results.meta.total + " matching News Articles";
		for (var x=0;x<results.data.length;x++) {
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

		/*var footer = document.createElement("div");
			footer.id = "newsfooter";
			footer.innerHTML = "Displaying " + (results.from * results.per_page) + " of " + results.total + " News Articles...<br />";

		if ((results.from * results.per_page) < results.total || window.location.href.match(/[\?&]all/)) {
			var a = document.createElement("a");

			if (!window.location.href.match(/[\?&]all/)) {
				a.innerHTML = "Show All News";
				var url = window.location.href.split("?");
				if (typeof(url[1]) != 'undefined' || url[1] == '') {
					a.href = url[0] + "?" + url[1] + "&all";
				} else {
					a.href = url[0] + "?all";
				}
			} else {
				a.innerHTML = "Show Less News";
				a.href = window.location.href.replace(/[&?]all/, '');
			}
			footer.appendChild(a);
		}

		news.appendChild(footer);

		if (results.data.length == 0) {
			var noresults = document.createElement("div");
				noresults.id = "newnews";
				noresults.innerHTML = "No matching news stories found.";

			news.appendChild(noresults);
		}*/
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
}

/**
 * Builds the news div with results returned from the WS
 *
 * @param   {object}  news
 * @param   {bool}    edit
 * @param   {array}   updates
 * @return  {void}
 */
function NEWSPrintRow(news) {
	var edit = news.can.edit,
		del = news.can.delete;
	var tab = document.getElementById("TAB_search");
	if (!tab) {
		edit = false;
	}

	var id = news.id; //.split('/');
		//id = id[id.length - 1];

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
					'published' : '0',
					'lastedit'  : LASTEDIT[id]
				};
				post = JSON.stringify(post);

				WSPostURL(root + "news/" + id, post, function (xml) {
					if (xml.status < 400) {
						document.getElementById("id").value = id;
						NEWSSearch();
					} else if (xml.status == 409) {
						WSGetURL(root + "news/" + id, function (xml) {
							if (xml.status < 400) {
								var results = JSON.parse(xml.responseText);
								alert("Unable to save changes. This news item has been edited by " + results['editusername'] + " since you loaded this page. Please make note of your changes and reload the page to try editing again.");
							}
						});
					}
				});
			};
			a.appendChild(document.createTextNode("Published"));
			a.className = 'badge badge-published';
			a.title = "Recall news item.";
		} else {
			a.onclick = function (e) {
				e.preventDefault();

				var post = {
					'published' : '1',
					'lastedit'  : LASTEDIT[id]
				};
				post = JSON.stringify(post);

				WSPostURL(root + "news/" + id, post, function (xml) {
					if (xml.status < 400) {
						document.getElementById("id").value = id;
						NEWSSearch();
					} else if (xml.status == 409) {
						WSGetURL(root + "news/" + id, function (xml) {
							if (xml.status < 400) {
								var results = JSON.parse(xml.responseText);
								alert("Unable to save changes. This news item has been edited by " + results['editusername'] + " since you loaded this page. Please make note of your changes and reload the page to try editing again.");
							}
						});
					}
				});
			};
			a.appendChild(document.createTextNode("Draft"));
			a.className = 'badge badge-unpublished';
			a.title = "Publish news item.";
		}

		td2.appendChild(a);
		tr.appendChild(td2);

		if (del) {
			// Delete button
			a = document.createElement("a");
			a.href = "?delete&id=" + id;
			a.className = 'edit news-delete icn tip';
			a.title = "Delete News Story.";
			a.onclick = function (e) {
				e.preventDefault();
				NEWSDeleteNews(id);
			};

			img = document.createElement("i");
			img.className = "fa fa-trash";
			img.setAttribute('aria-hidden', true);
			img.id = id + "_newsdeleteimg";

			a.appendChild(img);
			a.appendChild(document.createTextNode("Delete News Story."));
			tr.appendChild(a);
		}

		// Mailing
		if (resources.length > 0 && published == "1") {
			a = document.createElement("a");
			a.className = 'edit news-mail tip';
			a.href = "?mail&id=" + id;
			a.onclick = function (e) {
				e.preventDefault();
				NEWSSendMail(id);
			};
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
			a.href = "?mail&id=" + id;
			a.onclick = function (e) {
				e.preventDefault();
				NEWSWriteMail(id);
			};
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
	if (users.length > 0) {
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
	var page = document.location.href.split("/")[4];
	// if we are in news, we are doing report searches, so we should highlight matches
	if (page == 'news') {
		body = HighlightMatches(body);
	}

	span = document.createElement("span");
	span.id = id + "_text";
	span.innerHTML = body;

	td.appendChild(span);

	var label = document.createElement("label");
		label.for = id + "_textarea";
		label.className = 'sr-only';
		label.innerHTML = 'News text';

	var textarea = document.createElement("textarea");
		textarea.id = id + "_textarea";
		textarea.innerHTML = rawtext;
		textarea.style.display = "none";
		textarea.rows = 7;
		textarea.cols = 45;
		textarea.className = "form-control newspostedittextbox";

	span = document.createElement("span");
	span.appendChild(label);
	span.appendChild(textarea);

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
		span.for = id + "_textsaveupdatebox";

		var checkbox = document.createElement("input");
			checkbox.type = "checkbox";
			checkbox.id = id + "_textsaveupdatebox";
		span.appendChild(checkbox);
		td.appendChild(span);

		// help button
		a = document.createElement("a");
		a.href = "#help1";
		a.onclick = function (e) {
			e.preventDefault();
			$('#help1').dialog({ modal: true, width: '553px' });
			$('#help1').dialog('open');
		};
		a.id = id + "_texthelpicon";
		a.className = 'help icn tip';
		a.style.display = "none";
		a.title = 'Formatting news text';

		img = document.createElement("i");
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
			label.for = news['id'] + "_newupdatetemplate";
			label.className = 'sr-only';
			label.innerHTML = 'Use template';

			var select = document.createElement("select");
				select.id = news['id'] + "_newupdatetemplate";
				select.className = 'form-control';
				select.innerHTML = tselect.innerHTML;
				select.options[1] = null;
				select.onchange = function () {
					WSGetURL(this.options[this.selectedIndex].value, function(xml, news) {
						if (xml.status < 400) {
							var n = JSON.parse(xml.responseText);
							document.getElementById(news + "_newupdatebox").value = n.body;
							document.getElementById(news + "_newupdatebox").rows = 7;
							document.getElementById(news + "_newupdatebox").focus();
						} else {
							alert("Error fetching template");
						}
					}, news['id']);
				};

			div.appendChild(label);
			div.appendChild(select);
		}

		label = document.createElement("label");
		label.for = news['id'] + "_newupdatebox";
		label.className = 'sr-only';
		label.innerHTML = 'Post an update';

		textarea = document.createElement("textarea");
		textarea.className = "form-control crmupdatebox";
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
		a.className = "news-save icn tip";
		a.href = "?update&id=" + id;
		a.onclick = function (e) {
			e.preventDefault();
			NewsPostUpdate(news['id']);
		};
		a.title = "Post an update.";
		a.id = news['id'] + "_newupdateboxsave";
		a.style.display = "none";

		img = document.createElement("i");
		img.className = "crmnewcommentsave fa fa-save";
		img.setAttribute('aria-hidden', true);
		//img.style.display = "none";
		//img.id = news['id'] + "_newupdateboxsave";

		a.appendChild(img);
		a.appendChild(document.createTextNode("Post an update."));
		div.appendChild(a);

		td.appendChild(div);
		tr.appendChild(td);
		article.appendChild(tr);
	}

	article.appendChild(ul);

	container.appendChild(article);

	var c = Array();
	for (x = 0; x < news.updates.length; x++) {
		//if (root + "news/" + news.updates[x]['newsid'] == news['id']) {
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
		WSDeleteURL(root + "news/" + newsid, function(xml, newsid) {
			if (xml.status < 400) {
				document.getElementById(newsid).style.display = "none";
			} else {
				var img = document.getElementById(newsid + "_newsdeleteimg");
				if (img) {
					if (xml.status == 403) {
						img.className = "fa fa-exclamation-triangle";
						img.parentNode.title = "Unable to delete story. Permission denied.";
					} else {
						img.className = "fa fa-exclamation-circle";
						img.parentNode.title = "An error occurred while deleting story.";
					}
				}
			}
		}, newsid);
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
		document.getElementById(news + "_textarea").style.height = (25+text.parentNode.offsetHeight)+"px";
		text.style.display = "none";

	// show textarea
	var box = document.getElementById(news + "_textarea");
		box.style.display = "block";

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
		box.style.display = "none";

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
	var text = PrepareText(document.getElementById(news + "_textarea").value);
	var update = document.getElementById(news + "_textsaveupdatebox").checked;

	// change save icon
	var icon = document.getElementById(news + "_textsaveicon");
		icon.onclick = function () {};

	var img = document.getElementById(news + "_textsaveiconimg");
		img.className = "fa fa-spin fa-spinner";
		img.parentNode.title = "Saving changes...";

	var post = {
		'body' : text//,
		//'lastedit' : LASTEDIT[news]
	};

	if (update == true) {
		post['update'] = "1";
	}
	post = JSON.stringify(post);

	WSPutURL(root + "news/" + news, post, NEWSSavedNewsText, news);
}

/**
 * Callback after saving news text
 *
 * @param   {object}  xml
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSavedNewsText(xml, news) {
	var img = document.getElementById(news + "_textsaveiconimg");

	if (xml.status < 400) {
		var results = JSON.parse(xml.responseText);
		LASTEDIT[news] = results['lastedit'];

		var icon = document.getElementById(news + "_textsaveicon");
			icon.onclick = function () {
				NEWSSaveNewsText(news);
			};
			icon.style.display = "none";

		document.getElementById(news + "_textarea").style.display = "none";
		document.getElementById(news + "_textediticon").style.display = "block";
		document.getElementById(news + "_textsaveupdate").style.display = "none";
		document.getElementById(news + "_textsaveupdatebox").checked = false;
		document.getElementById(news + "_textpreviewicon").style.display = "none";
		document.getElementById(news + "_textcancelicon").style.display = "none";
		document.getElementById(news + "_texthelpicon").style.display = "none";

		var text = document.getElementById(news + "_text");
			text.innerHTML = results['formattedbody'];
			text.style.display = "block";
	} else if (xml.status == 403) {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes, grace editing window has passed.";
	} else if (xml.status == 409) {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes. This item has been edited by another user since loading this page.";
		WSGetURL(root + "news/" + news, function (xml) {
			if (xml.status < 400) {
				var results = JSON.parse(xml.responseText);
				alert("Unable to save changes. This news item has been edited by " + results['editusername'] + " since you loaded this page. Please make note of your changes and reload the page to try editing again.");
			}
		});
	} else {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes, reload the page and try again.";
	}
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
		icon.onclick = function () {};

	var img = document.getElementById(news + "_headlinesaveiconimg");
		img.className = "fa fa-spinner fa-spin";
		img.parentNode.title = "Saving changes...";

	var post = {
		'headline' : text,
		'lastedit' : LASTEDIT[news]
	};
	post = JSON.stringify(post);

	WSPostURL(root + "news/" + news, post, NEWSSavedNewsHeadline, news);
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
		icon.onclick = function () {};
	var img = document.getElementById(news + "_locationsaveiconimg");
		img.className = "fa fa-spinner fa-spin";
		img.parentNode.title = "Saving changes...";

	var post = {
		'location' : text,
		'lastedit' : LASTEDIT[news]
	};
	post = JSON.stringify(post);

	WSPostURL(root + "news/" + news, post, NEWSSavedNewsLocation, news);
}

/**
 * Callback after saving news headline
 *
 * @param   {object}  xml
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSavedNewsHeadline(xml, news) {
	var img = document.getElementById(news + "_headlinesaveiconimg");

	if (xml.status < 400) {
		var editicon = document.getElementById(news + "_headlineediticon");
		var icon = document.getElementById(news + "_headlinesaveicon");
		var cancelicon = document.getElementById(news + "_headlinecancelicon");
		var text = document.getElementById(news + "_headline");
		var input = document.getElementById(news + "_headlineinput");
		var results = JSON.parse(xml.responseText);
		LASTEDIT[news] = results['lastedit'];

		icon.onclick = function () { NEWSSaveNewsHeadline(news); };
		icon.style.display = "none";

		cancelicon.style.display = "none";
		text.style.display = "inline";
		input.style.display = "none";
		editicon.style.display = "inline";
		text.innerHTML = results['headline'];
	} else if (xml.status == 403) {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes, grace editing window has passed.";
	} else if (xml.status == 409) {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes. This item has been edited by another user since loading this page.";
		WSGetURL(root + "news/" + news, function (xml) {
			if (xml.status < 400) {
				var results = JSON.parse(xml.responseText);
				alert("Unable to save changes. This news item has been edited by " + results['editusername'] + " since you loaded this page. Please make note of your changes and reload the page to try editing again.");
			}
		});
	} else {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes, reload the page and try again.";
	}
}

/**
 * Callback after saving news location
 *
 * @param   {object}  xml
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSavedNewsLocation(xml, news) {
	var img = document.getElementById(news + "_locationsaveiconimg");

	if (xml.status < 400) {
		var editicon = document.getElementById(news + "_locationediticon");
		var icon = document.getElementById(news + "_locationsaveicon");
		var cancelicon = document.getElementById(news + "_locationcancelicon");
		var text = document.getElementById(news + "_location");
		var input = document.getElementById(news + "_locationinput");
		var results = JSON.parse(xml.responseText);
		LASTEDIT[news] = results['lastedit'];

		icon.onclick = function () { NEWSSaveNewsLocation(news); };
		icon.style.display = "none";

		cancelicon.style.display = "none";
		text.style.display = "inline";
		input.style.display = "none";
		editicon.style.display = "inline";
		text.innerHTML = results['location'];
	} else if (xml.status == 403) {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes, grace editing window has passed.";
	} else if (xml.status == 409) {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes. This item has been edited by another user since loading this page.";
		WSGetURL(root + "news/" + news, function (xml) {
			if (xml.status < 400) {
				var results = JSON.parse(xml.responseText);
				alert("Unable to save changes. This news item has been edited by " + results['editusername'] + " since you loaded this page. Please make note of your changes and reload the page to try editing again.");
			}
		});
	} else {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes, reload the page and try again.";
	}
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
		icon.onclick = function () {};
	var img = document.getElementById(news + "_urlsaveiconimg");
		img.className = "fa fa-spinner fa-spin";
		img.parentNode.title = "Saving changes...";

	var post = {
		'url' : text,
		'lastedit' : LASTEDIT[news]
	};
	post = JSON.stringify(post);

	WSPostURL(root + "news/" + news, post, function(xml, news) {
		var img = document.getElementById(news + "_urlsaveiconimg");

		if (xml.status < 400) {
			var editicon = document.getElementById(news + "_urlediticon");
			var icon = document.getElementById(news + "_urlsaveicon");
			var cancelicon = document.getElementById(news + "_urlcancelicon");
			var text = document.getElementById(news + "_url");
			var input = document.getElementById(news + "_urlinput");
			var results = JSON.parse(xml.responseText);
			LASTEDIT[news] = results['lastedit'];

			icon.onclick = function () {
				NEWSSaveNewsUrl(news);
			};
			icon.style.display = "none";

			cancelicon.style.display = "none";
			text.style.display = "inline";
			input.style.display = "none";
			editicon.style.display = "inline";
			text.innerHTML = results['location'];
		} else if (xml.status == 403) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, grace editing window has passed.";
		} else if (xml.status == 409) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes. This item has been edited by another user since loading this page.";
			WSGetURL(root + "news/" + news, function (xml) {
				if (xml.status < 400) {
					var results = JSON.parse(xml.responseText);
					alert("Unable to save changes. This news item has been edited by " + results['editusername'] + " since you loaded this page. Please make note of your changes and reload the page to try editing again.");
				}
			});
		} else {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, reload the page and try again.";
		}
	}, news);
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
		$("#newsresource").val(null).trigger('change');
		/*resources.value = '';
		if ($('.tagsinput').length) {
			$(resources).clearTags();
		}*/
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
	example_vars["resources"] = ["Anvil", "Bell"];//[{"resourcename": "Carter"}, {"resourcename": "Conte"}];
	example_vars["location"] = "Envision Center";

	WSPostURL(root + "news/preview", JSON.stringify({ 'body' : document.getElementById('help1' + example + 'input').value, 'vars' : example_vars }), function (xml) {
		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);
			document.getElementById('help1' + example + 'output').innerHTML = results['formattedtext'];
		} else {
			alert("An error occurred while generating preview.");
		}
	});
}

/**
 * Build vars for news preview
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSPreviewVars(news) {
	var preview_vars = {};
	var startDate;
	var endDate;

	/* Grab the variables we need and populate the preview variables. */
	if (document.getElementById("datestartshort").value != "") {
		if (document.getElementById("timestartshort").value != "") {
			startDate = document.getElementById("datestartshort").value + " " + document.getElementById("timestartshort").value;
		} else {
			startDate = document.getElementById("datestartshort").value + " 12:00 AM"
		}
		preview_vars["startdate"] = startDate;
	}

	if (document.getElementById("datestopshort").value != "") {
		if (document.getElementById("timestopshort").value != "") {
			endDate = document.getElementById("datestopshort").value + " " + document.getElementById("timestopshort").value;
		} else {
			endDate = document.getElementById("datestopshort").value + " 12:00 AM";
		}
		preview_vars["enddate"] = endDate;
	}

	if (document.getElementById("newstype").value <= 2) {
		preview_vars["resources"] = [];

		var resources = Array.prototype.slice.call(document.querySelectorAll('#newsresource option:checked'), 0).map(function (v) {
			return v.innerHTML;
		});

		/*if ($('.tagsinput').length) {
			var resources = $('.tagsinput').find('.tag-text');*/
		$.each(resources, function (i, el) {
			preview_vars['resources'][i] = el; //{ "resourcename": el };
		});
		//}
	}
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
	if (typeof(edit) == 'undefined') {
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

	WSPostURL(root + "news/preview", JSON.stringify(post), function (xml) {
		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);
			document.getElementById("preview").innerHTML = results['formattedbody'];
		} else {
			alert("An error occurred while generating preview.");
		}
	});

	$('#preview').dialog({ modal: true, width: '691px' });
	$('#preview').dialog('open');
}

/**
 * Toggle 'more' news
 *
 * @return  {void}
 */
function NEWSToggleMoreNews() {
	var more = document.getElementById("morenews");
	var moretext = document.getElementById("morenewstext");
	if (more.style.display == "none") {
		$( "#morenews" ).toggle( "blind", {'direction': 'up', 'easing': 'swing', 'duration': 1000} );
		moretext.innerHTML = "Less";
	} else {
		$( "#morenews" ).toggle( "blind", {'direction': 'up', 'easing': 'swing', 'duration': 1000} );
		moretext.innerHTML = "More";
	}
}

/**
 * Send an email
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSendMail(news) {
	if ($( '#' + news + '_textarea').css('display') == 'block') {
		// We're still editing. Need to save first.
		$("#dialog-confirm").dialog({
			resizable: false,
			modal: true,
			height: 'auto',
			width: 300,
			buttons: {
				"Yes": function () {
					$(this).dialog('close');
					NEWSSaveNewsText(news);
					return;
				},
				"No": function () {
					$(this).dialog('close');
					return;
				}
			}
		});
		$("#dialog-confirm").dialog('open');

		return;
	}

	// Get text and updates from WS
	$.getJSON(root + "news/" + news, function(data) {
		var text = document.getElementById(news + "_textarea").value;
		if (text == "") {
			return;
		}

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
		var header = "To: " + resources + " Users<br />From: " + name + " via Research Computing<br/>Subject: " + subject + " - " + formatteddate + "<br/><hr /><strong>" + subject + "</strong><br/>" + formatteddate + "<br/>" + locale + "<br/>";

		// set up foot for email preview
		var footer = '<hr/><a href="/news/' + news + '">ITaP Research Computing News</a> from ' + name + '<br/><br/>Please reply to <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> with any questions or concerns.<br/><a href="/news/' + news + '">View this article on the web.</a>';

		var body = "";
		if (data['updates'].length > 0) {
			for (var x = 0; x < data['updates'].length; x++) {
				body = body + '<span class="newsupdate">UPDATE: ' + data['updates'][x]['formattedcreateddate'] + '</span>' + data['updates'][x]['formattedbody'] + '<br/>';
			}
			body = body + '<span class="newsupdate">ORIGINAL: ' + data['formattedcreateddate'] + "</span>";
		}
		body = body + data['formattedbody'];

		if (data['resources'].length > 0) {
			footer += '<hr /><p>Send to resource mailing lists:</p><div class="row">';
			for (var x = 0; x < data['resources'].length; x++) {
				footer += '<div class="col-md-3"><label><input type="checkbox" checked="checked" value="' + data['resources'][x]['resourceid'] + '" class="preview-resource" /> ' + data['resources'][x]['resource']['name'] + '</label></div>';
			}
			footer += '</div>';
		}

		document.getElementById("mailpreview").innerHTML = header + body + footer;

		$('#mailpreview').dialog({ modal: true,
			width: '691px',
			buttons: {
				"Cancel": function() {
					$( this ).dialog("close");
				},
				"Send mail": function () {
					$( this).dialog("close");

					var post = {
						'mail': 1,
						'lastedit': LASTEDIT[news]
					};

					var resources = [];
					$('.preview-resource').each(function (i, el) {
						if ($(el).is(':checked')) {
							resources.push($(el).val());
						}
					});

					//if ($('.preview-resource').length != resources.length) {
						post.resources = resources;
					//}

					post = JSON.stringify(post);

					WSPutURL(ROOT_URL + "news/" + news + "/email", post, NEWSSentMail, news);
				}
			}
		});
		if ( $(".ui-dialog-buttonpane").find("div").length == 1) {
			$(".ui-dialog-buttonpane").prepend('<div style="float:left;padding-top:1em;padding-left:18em">Send this email message?</div>');
		}
		$('#mailpreview').dialog('open');
	});
}

/**
 * Write an email
 *
 * @param   {string}  news
 * @return  {void}
 */
function NEWSWriteMail(news) {
	$.getJSON(ROOT_URL + "news/" + news, function (data) {
		$('#mail-subject').val(data.headline);

		var body = '**Date:** ' + data.formatteddate.replace(/(<([^>]+)>)/ig, '').replace(/&nbsp;/g, ' ').replace('&#8211;', '-') + "\n";

		if (data.location) {
			body += '**Location:** ' + data.location + "\n";
		}
		if (data.url) {
			body += '**URL:** ' + data.url + "\n";
		}

		//var name = $( ".login").find( "a" ).first().text();

		$('#mail-body').val(body + "\n\n");

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
		console.log(data.associations);
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

		$('#mailwrite').dialog({
			modal: true,
			width: '691px',
			buttons: {
				"Cancel": function () {
					$(this).dialog("close");
				},
				"Send mail": function () {
					var usersdata = document.getElementById("mail-to").value.split(',');
					var associations = [],
						i;
					for (i = 0; i < usersdata.length; i++) {
						if (usersdata[i] != "") {
							associations.push(usersdata[i]);
						}
					}

					$(this).dialog("close");
					var post = JSON.stringify({
						'mail': 1,
						'lastedit': LASTEDIT[news],
						'headline': $('#mail-subject').val(),
						'body': $('#mail-body').val(),
						'associations': associations
					});
					WSPutURL(ROOT_URL + "news/" + news + "/email", post, NEWSSentMail, news);
				}
			}
		});
		if ($(".ui-dialog-buttonpane").find("div").length == 1) {
			$(".ui-dialog-buttonpane").prepend('<div style="float:left;padding-top:1em;padding-left:18em">Send this email message?</div>');
		}
		$('#mailwrite').dialog('open');
	});
}

/**
 * Callback after an email has been sent
 *
 * @param   {object}  xml
 * @param   {string}  news
 * @return  {void}
 */
function NEWSSentMail(xml, news) {
	if (xml.status == 200) {
		document.getElementById("IMG_mail_" + news).className = "fa fa-check";
		document.getElementById("A_mail_" + news).onclick = function () {};

		var results = JSON.parse(xml.responseText);
		LASTEDIT[news] = results['lastedit'];

		NEWSSearch();
	} else {
		document.getElementById("IMG_mail_" + news).className = "fa fa-exclamation-circle";
		document.getElementById("A_mail_" + news).onclick = function () {};
		if (xml.status == 409) {
			WSGetURL(root + "news/" + news, function (xml) {
				if (xml.status == 200) {
					var results = JSON.parse(xml.responseText);
					alert("Unable to save changes. This news item has been edited or mailed by " + results['editusername'] + " since you loaded this page. Please refresh the page and check for edits or mailing.");
				}
			});
		} else {
			alert("An error occurred while sending mail.");
		}
	}
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
			overwrite = ! confirm("Are you sure you wish to overwrite text with this template? Any work will be lost.");
		}

		if (!overwrite) {
			document.getElementById("TR_use_template").classList.add('d-none');
			document.getElementById("TR_template").classList.add('d-none');
			document.getElementById("TR_date").classList.remove('d-none');
			document.getElementById("template").checked = false;
			document.getElementById("published").checked = true;

			WSGetURL(template, NEWSPopulateTemplate);
		} else {

			document.getElementById("template_select").selectedIndex = 0;
		}
	}
}

/**
 * Populate the form with template contents
 *
 * @param   {object}  xml
 * @return  {void}
 */
function NEWSPopulateTemplate(xml) {
	if (xml.status == 200) {
		var news = JSON.parse(xml.responseText);
		document.getElementById("Headline").value = news.headline.replace(/&#039;/g, "'").replace(/&quot;/g, '"');
		document.getElementById("NotesText").value = news.body.replace(/&#039;/g, "'").replace(/&quot;/g, '"');

		var newstype = document.getElementById("newstype");
		var x;

		for (x=0;x<newstype.options.length;x++) {
			if (newstype.options[x].value == news.newstypeid) {
				newstype.selectedIndex = x;
			}
		}

		var resources = Array.prototype.slice.call(document.querySelectorAll('#newsresource option:checked'), 0).map(function (v) {
			return v.value;
		});

		for (x = 0; x < news.resources.length; x++) {
			resources.push(news.resources[x]['resourceid']);
			/*if ($('.tagsinput').length) {
				if (!$('#newsresource').tagExist(news.resources[x]['resourceid'])) {
					$('#newsresource').addTag({
						'id': news.resources[x]['resourceid'],
						'label': news.resources[x]['resourcename']
					});
				}
			}*/
		}

		$('#newsresource')
			.val(resources)
			.trigger('change');

		NEWSSearch();
	} else {
		alert("An error ocurred while fetching template.");
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

	WSPostURL(root + "news/" + newsid + "/updates", post, function(xml, newsid) {
		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);

			NewsPrintUpdate(newsid, results);
			document.getElementById(newsid + "_newupdatebox").value = "";
			NewsCollapseNewUpdate(newsid + "_newupdatebox");
		} else {
			document.getElementById(newsid + "_newupdateboxsave").className = "fa fa-exclamation-circle";
			document.getElementById(newsid + "_newupdateboxsave").parentNode.title = "An error occured while posting comment.";
		}
	}, newsid);
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
	if (typeof(edit) === 'undefined') {
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
		a.className = 'edit news-update-delete tip';
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
			label.for = update['id'] + "_updatetextarea";
			label.className = 'sr-only';
			label.innerHTML = 'Update text';

		// Text box
		var textarea = document.createElement("textarea");
			textarea.id = update['id'] + "_updatetextarea";
			textarea.innerHTML = update['body'];
			textarea.style.display = "none";
			textarea.className = "form-control newsupdateedittextbox";

		span = document.createElement("span");
		span.appendChild(label);
		span.appendChild(textarea);

		div.appendChild(span);

		// Save button
		a = document.createElement("a");
		a.href = "?update=" + update['id'] + '&edit';
		a.onclick = function (e) {
			e.preventDefault();
			NewsSaveUpdateText(newsid, update['id']);
		};
		a.className = "news-update-save tip";
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
		box.style.display = "block";

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
		icon.onclick = function () {};

	var img = document.getElementById(update + "_updatetextsaveiconimg");
		img.className = "fa fa-spinner fa-spin";

	var post = { 'body' : text };
		post = JSON.stringify(post);

	WSPutURL(root + "news/" + newsid + "/updates/" + update, post, function(xml, update) {
		var editicon = document.getElementById(update + "_updatetextediticon");
		var icon = document.getElementById(update + "_updatetextsaveicon");
		var img = document.getElementById(update + "_updatetextsaveiconimg");
		var text = document.getElementById(update + "_update");
		var box = document.getElementById(update + "_updatetextarea");

		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);
			icon.style.display = "none";
			icon.onclick = function () {
				NewsSaveUpdateText(results.data.newsid, update);
			};
			text.style.display = "block";
			box.style.display = "none";
			editicon.style.display = "block";
			text.innerHTML = results.formattedbody;
		} else if (xml.status == 403) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, grace editing window has passed.";
		} else {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, reload the page and try again.";
		}
	}, update);
}

/**
 * Delete a news update
 *
 * @param   {string}  updateid
 * @param   {string}  reportid
 * @return  {void}
 */
function NewsDeleteUpdate(updateid, reportid) {
	if (confirm("Are you sure you want to delete this update?")) {
		WSDeleteURL(root + "news/" + reportid + "/updates/" + updateid, function(xml, arg) {
			if (xml.status < 400) {
				$('#' + arg['updateid'] + "_update").closest('li').remove();
			} else if (xml.status == 403) {
				document.getElementById(arg['reportid'] + "_updatedeleteimg").className = "fa fa-exclamation-circle";
				document.getElementById(arg['reportid'] + "_updatedeleteimg").parentNode.title = "Unable to save changes, grace editing window has passed.";
			} else {
				document.getElementById(arg['updateid'] + "_updatedeleteimg").className = "fa fa-exclamation-circle";
				document.getElementById(arg['updateid'] + "_updatedeleteimg").parentNode.title = "An error occurred while deleting update.";
			}
		}, { 'updateid' : updateid, 'reportid' : reportid });
	}
}

var autocompleteUsers = function(url) {
	return function(request, response) {
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
document.addEventListener('DOMContentLoaded', function() {
	var tabs = document.querySelectorAll('.nav-tabs a');

	for (i = 0; i < tabs.length; i++)
	{
		tabs[i].addEventListener('click', function (event) {
			event.preventDefault();

			NEWSToggle(this.getAttribute('href').replace('#', ''));
		});
	}

	// Date/time
	$('.date-pick,.time-pick').on('change', function(){
		NEWSDateSearch($(this).attr('name'));
	});

	if ($('.time-pick').length) {
		$('.time-pick').timepicker({
			timeFormat: "h:i A",
			minTime: '8:00am',
			maxTime: '5:00pm',
			change: function() {
				$(this).trigger('change');
			}
		});
	}

	// Dialogs
	if ($('.samplebox').length) {
		$('.samplebox').on('keyup', function (){
			PreviewExample($(this).data('sample'));
		});

		$('#help1').tabs();

		//Load the formatting guide example variables into the text box.
		PreviewExample('h');
	}

	$('#location').on('keyup', function(){
		NEWSToggleAddButton();
	});
	$('#newstype').on('change', function(){
		NEWSNewstypeSearch($(this).val());
	});
	$('#Headline').on('keyup', function(){
		NEWSToggleAddButton();
	});
	$('#NotesText').on('keyup', function(){
		NEWSToggleAddButton();
	});

	$('#keywords').on('keyup', function(event){
		NEWSKeywordSearch(event.keyCode);
	});
	$('#id').on('keyup', function(event){
		NEWSKeywordSearch(event.keyCode);
	});
	$('#template_select').on('change', function(){
		NEWSSearch();
	});
	$('#datesegmented').on('change', function(){
		$('#TR_date').toggle();
		$('#TR_newstime').toggle();
	});
	$('#template').on('change', function(){
		NEWSSearch();
	});
	$('#published').on('change', function(){
		NEWSSearch();
	});

	// Buttons
	$('#INPUT_search').on('click', function(event){
		event.preventDefault();
		NEWSSearch();
	});
	$('#INPUT_clear').on('click', function(event){
		event.preventDefault();
		NEWSClearSearch();
	});
	$('#INPUT_add').on('click', function(event){
		event.preventDefault();
		NEWSAddEntry();
	});
	$('#INPUT_preview').on('click', function(event){
		event.preventDefault();
		NEWSPreview($(this).data('id'), true);
	});

	$('#DIV_news form').on('submit', function(e){
		e.preventDefault();
		return false;
	});

	var rselects = $(".searchable-select-multi");
	if (rselects.length) {
		$(".searchable-select-multi").select2({
			multiple: true,
			closeOnSelect: false,
			templateResult: function (item) {
				if (typeof item.children != 'undefined') {
					var s = $(item.element).find('option').length - $(item.element).find('option:selected').length;
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
			NEWSSearch();
		})
		.on('select2:unselect', function () {
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

	$('.btn-attend').on('click', function (event) {
		event.preventDefault();

		var el = $(this);

		var id = el.data('newsid');
		//var id = newsid.substr(newsid.lastIndexOf("/") + 1);

		//var parts = el.data('assoc').split('/');

		var post = {
			'associd': el.data('assoc'), //parts[parts.length - 1],
			'assoctype': 'user',//parts[parts.length - 2],
			'newsid': id
		};

		post = JSON.stringify(post);

		WSPostURL(root + "news/associations", post, function (xml) {
			if (xml.status < 400) {
				el.parent().html('<span class="alert alert-success">Thank you for your interest!</span>');
			} else if (xml.status == 403) {
				el.parent().append('<span class="alert alert-warning">Unable to register changes.</span>');
			} else {
				el.parent().append('<span class="alert alert-error">An error occurred.</span>');
			}
		});
	});

	$('.btn-notattend').on('click', function (event) {
		event.preventDefault();

		var el = $(this);

		WSDeleteURL(root + "news/associations/" + el.data('id'), function (xml, id) {
			if (xml.status < 400) {
				el.parent().html('<span class="alert alert-success">Successfully cancelled.</span>');
			} else if (xml.status == 403) {
				el.parent().append('<span class="alert alert-warning">Unable to register changes.</span>');
			} else {
				el.parent().append('<span class="alert alert-error">An error occurred.</span>');
			}
		});
	});

	$('#attendees-reveal').on('click', function (e) {
		e.preventDefault();

		$(this).addClass('stash');
		$('#attendees-all').removeClass('stash');
		$('#attendees').addClass('stash');
	});

	var url = window.location.href.match(/[&?](\w+)$/),
		on = 'search',
		refresh = true;

	if (url != null) {
		on = url[1];
	}

	var data = $('#news-data');
	if (data.length) {
		var original = JSON.parse(data.html());
		var i = 0;

		$("#newstype > option").each(function() {
			if (this.value == original['newstypeid']) {
				$('#newstype > option:selected', 'select[name="options"]').removeAttr('selected');
				$(this).attr('selected', true);
			}
		});

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
		for (i = 0; i < original.resources.length; i++)
		{
			//NEWSAddResource(original.resources[i]);
			results[i] = original.resources[i]['resourceid'];
		}
		$('#newsresource')
			.val(results)
			.trigger('change');
		for (i = 0; i < original.associations.length; i++)
		{
			NEWSAddAssociation(original.associations[i]);
		}
		if (original.published == "1") {
			document.getElementById('published').checked = true;
		}
		if (original.template == "1") {
			document.getElementById('template').checked = true;
		}

		on = 'edit';
		refresh = false;
	}

	if ($('#news').length) {
		NEWSToggle(on, refresh);
		NEWSSearch();
	}

	var stats = $('#articlestats');
	if (stats.length) {
		$.getJSON(stats.attr('data-api'), function (data) {
			if (data) {
				$('#viewcount').html(data.viewcount);
				$('#uniqueviewcount').html(data.uniquecount);
			}
		});
	}
});
