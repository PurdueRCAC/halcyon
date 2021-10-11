// Common Javascript functions for all web app pages

// define a couple global variables
if (typeof(base_url) === 'undefined') {
	base_url = document.querySelector('meta[name="base-url"]').getAttribute('content');
}
var ROOT_URL = base_url + "/api/";
var tablist = '';
var supergroup = null;
var USER = null;
var GROUPS = Array();
var USERS = Array();
var QUEUES = Array();
var MEMBERS = Array();
var SEARCH = Array();
var HOVER = null;
var FIRST = null;
var PENDING = Array();
var last_search = null;
var IMAGE_URL = "";
var superuser = false;
var pending = 0;
var column_groups;

// define error messages
var ERRORS = Object();
ERRORS['generic'] = "An error has occurred.";
ERRORS['changegroupname'] = "Unable to change group name.";
ERRORS['deleteviewer'] = "Unable to remove group viewer";
ERRORS['deleteowner'] = "Unable to remove group manager";
ERRORS['addviewer'] = "Unable to add new group viewer";
ERRORS['addowner'] = "Unable to add new group manager";
ERRORS['deletequeuemember'] = "Unable to disable queue for user.";
ERRORS['deleteunixgroupmember'] = "Unable to disable Unix group for user.";
ERRORS['addqueuemember'] = "Unable to enable queue for user.";
ERRORS['addunixgroupmember'] = "Unable to enable Unix group for user.";
ERRORS['addgfos'] = "Unable to set this category.";
ERRORS['creategroupduplicate'] = "Unable to create a new group. Group by this name already exists.";
ERRORS['creategroup'] = "Unable to create a new group.";

ERRORS['0'] = "Your session has expired. Try reloading the page.";
ERRORS['403'] = "Unable to authenticate. Session may have expired, try refreshing the page and logging back in. Contact help if problem persists.";
ERRORS['403_generic'] = "Unable to authenticate. Session may have expired, try refreshing the page and logging back in. Contact help if problem persists.";
ERRORS['404_generic'] = "Could not find object. Reload page and try again.";
ERRORS['410'] = "User no longer has an active Career Account.";
ERRORS['500'] = "There was an error loading your information/processing your request.  Please try again soon or contact help if the problem persists.";
ERRORS['unknown'] = "Unknown error. Reload page and try again. If problem continues contact help.";
ERRORS['reload'] = "Reload page and try again. If problem continues contact help.";

// this function returns a HttpRequest object
function GetXmlHttpObject()
{
	var xmlHttp = null;

	try
	{
		// Firefox, Opera 8.0+, Safari
		xmlHttp = new XMLHttpRequest();
	}
	catch (e)
	{
		//Internet Explorer
		try
		{
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}

	return xmlHttp;
}

// event handler from body onload - this kicks everything off
/*function BodyLoad(username, group, image_url) {
	// if this is being served from a "dev" instance,
	// use the "dev" instance of web services as well
	url_components = document.location.href.split("/");
	if (url_components[3].match(/_dev$/) != null) {
		ROOT_URL = ROOT_URL.substring(0, ROOT_URL.length - 1) + "_dev/";
	}
	PAGE_ROOT_URL = "/" + url_components[3];

	IMAGE_URL = image_url;
}*/

function WSGetURL(id, result_function, arg1) {
	if (id.substring(0, 4) != 'http') {
		if (id.substring(0, 1) != '/') {
			id = '/' + id;
		}
		id = 'https://' + window.location.hostname + id;
	}
	var url = id;
	var xml = GetXmlHttpObject();
	xml.onreadystatechange = function () {
		if (xml.readyState==4 || xml.readyState=="complete")
		{
			// Authentication is stale, kick the page around
			if (xml.status == 408) {
				window.location.reload(true);
			}
			if (result_function) {
				if (typeof(arg1) != 'undefined') {
					result_function(xml, arg1);
				} else {
					result_function(xml);
				}
			}
		}
	}
	xml.open('GET', url, true);
	xml.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
	xml.send(null);
}

function WSPostURL(id, json, result_function, arg1) {
	if (id.substring(0, 4) != 'http') {
		if (id.substring(0, 1) != '/') {
			id = '/' + id;
		}
		id = 'https://' + window.location.hostname + id;
	}
	var url = id;
	var xml = GetXmlHttpObject();
	xml.onreadystatechange = function () {
		if (xml.readyState==4 || xml.readyState=="complete") {
			// Authentication is stale, kick the page around
			if (xml.status == 408) {
				window.location.reload(true);
			}
			if(typeof(arg1) != 'undefined') {
				result_function(xml, arg1);
			}
			else {
				result_function(xml);
			}
		}
	}
	xml.open("POST",url,true);
	xml.setRequestHeader("Content-type", "application/json");
	xml.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
	xml.send(json);
}

function WSPutURL(id, json, result_function, arg1) {
	if (id.substring(0, 4) != 'http') {
		if (id.substring(0, 1) != '/') {
			id = '/' + id;
		}
		id = 'https://' + window.location.hostname + id;
	}
	var url = id;
	var xml = GetXmlHttpObject();
	xml.onreadystatechange = function () {
		if (xml.readyState == 4 || xml.readyState == "complete") {
			// Authentication is stale, kick the page around
			if (xml.status == 408) {
				window.location.reload(true);
			}
			if (typeof (arg1) != 'undefined') {
				result_function(xml, arg1);
			}
			else {
				result_function(xml);
			}
		}
	}
	xml.open("PUT", url, true);
	xml.setRequestHeader("Content-type", "application/json");
	xml.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
	xml.send(json);
}

function WSDeleteURL(id, result_function, arg1) {
	if (id.substring(0, 4) != 'http') {
		if (id.substring(0, 1) != '/') {
			id = '/' + id;
		}
		id = 'https://' + window.location.hostname + id;
	}
	var url = id;
	var xml = GetXmlHttpObject();
	xml.onreadystatechange = function () {
		if (xml.readyState==4 || xml.readyState=="complete")
		{
			// Authentication is stale, kick the page around
			if (xml.status == 408) {
				window.location.reload(true);
			}
			if (typeof(arg1) != 'undefined') {
				result_function(xml, arg1);
			} else {
				result_function(xml);
			}
		}
	}
	xml.open("DELETE",url,true);
	xml.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
	xml.send(null);
}

// start bits for tabbing //
function ShowTab(tabname, tablist)
{
	HideAll(tablist);
	Show(tabname);
	if (document.getElementById(tabname).className.match(/small/)) {
		document.getElementById(tabname).className = "nav-link active tab smallTab smallActiveTab"
	} else {
		document.getElementById(tabname).className = "nav-link active tab activeTab"
	}
	if(tabname.match(/resource/)) {
		if (typeof(history.pushState) != 'undefined') {
			history.pushState(null, null, encodeURI("#" + tabname));
		}
	}
	//document.dispatchEvent(new Event('show.bs.tab'));
	$('a.activeTab').trigger('shown.bs.tab');
}

function QShowTab(tabname, tablist1, tablist2) {
	tablist1 = tablist1.concat(',');
	var tablist = tablist1.concat(tablist2);

	HideAll(tablist);
	Show(tabname);
	if (document.getElementById(tabname).className.match(/small/)) {
		document.getElementById(tabname).className = "nav-link active tab smallTab smallActiveTab"
	} else {
		document.getElementById(tabname).className = "nav-link active tab activeTab"
	}
	if(tabname.match(/resource/)) {
		if (typeof(history.pushState) != 'undefined') {
			history.pushState(null, null, encodeURI("#" + tabname));
		}
	}
}

function HideTab(tabname)
{
	Hide(tabname);

	if (document.getElementById(tabname).className.match(/small/)) {
		document.getElementById(tabname).className = "nav-link tab smallTab"
	} else {
		document.getElementById(tabname).className = "nav-link tab"
	}
}

function HideAll(tablist)
{
	tabs = tablist.split(",");
	for (var i=0; i<tabs.length; i++)
	{
		HideTab(tabs[i]);
	}
}

function ShowHide(ID, NoIMG)
{
	var objDiv = document.getElementById("DIV_" + ID);
	var strStatus = objDiv.style.display;
	var objImgID = null;

	if (typeof NoIMG != "undefined") {
		objImgID = document.getElementById("IMG_" + ID);
	}

	if (strStatus == "none") {
		objDiv.style.display = "";
		if (objImgID) {
			objImgID.src = "/include/images/minus.gif";
		}
	} else {
		objDiv.style.display = "none";
		if (objImgID) {
			objImgID.src = "/include/images/plus.gif";
		}
	}
}

function Hide(ID, NoIMG)
{
	var objDiv = document.getElementById("DIV_" + ID);

	if (!objDiv) {
		return;
	}

	objDiv.style.display = "none";

	if (typeof NoIMG != "undefined") {
		var objImgID = document.getElementById("IMG_" + ID);
		objImgID.src = "/include/images/plus.gif";
	}
}

function Show(ID, NoIMG)
{
	var objDiv = document.getElementById("DIV_" + ID);

	if (!objDiv) {
		return;
	}

	objDiv.style.display = "block";

	if (typeof NoIMG != "undefined") {
		var objImgID = document.getElementById("IMG_" + ID);
		objImgID.src = "/include/images/minus.gif";
	}
}

// end bits for tabbing //

// gets the active tab
function GetTab() {
	var tabs = document.getElementById("tabMain");

	// look for a tab that is not hidden
	//var bits = tablist.split(",");
	var t = tabs.getElementsByTagName("div");

	var i = 0;
	for (var x=0; x<t.length; x++) {
		if (t[x].id.substring(0,3) == "DIV") {
			if (t[x].style.display != "none") {
				return t[x].id.substring(4); //bits[i];
			}
			i++;
		}
	}
}

// clear out the action bar
function ClearActions(tables) {
	if (typeof(tables) == 'undefined') {
		tables = true;
	}
	var group = GetTab();
	var s = document.getElementById(group + "_action");
	while (s.childNodes.length) {
		s.removeChild(s.firstChild);
	}
	s.className = "normal";

	if (tables) {
		if (typeof(TABLES) == 'undefined') {
			var TABLES = Array();
			TABLES[group] = Array("coowners", "viewers", "inactive", "queues", "inactive_managers", "inactive_viewers");
		}
		// unhighlight affected rows
		for (var i=0; i<TABLES[group].length;i++) {
			var table = document.getElementById(TABLES[group][i] + "_" + group);
			var length = table.rows.length;
			for (var x=0;x<length;x++) {
				if (table.rows[x].className == "action") {
					table.rows[x].className = "normal";
				}
			}
		}
	}
}

// set the action bar
function SetAction(message, undo) {
	var group = GetTab();
	var span = document.getElementById(group + "_action");
		span.className = "action";

	var t = document.createTextNode(message);
	span.appendChild(t);

	if (undo != null ) {
		var a = document.createElement("a");
			a.href = "javascript:" + undo;
			a.title = "Undo last action";

		var img = document.createElement("img");
			img.border = "0";
			img.src = "/include/images/undo.png";
			img.className = "icon";

		a.appendChild(img);
		span.appendChild(a);
	}
}

// put an error into the action bar
function SetError(message, small) {
	var group = GetTab();

	if (group) {
		var span = document.getElementById(group + "_action");
		if (span) {
			span.className = "alert alert-error";
			span.innerHTML = message + "<br />";

			if (typeof(small) != 'undefined' && small != '') {
				var span2 = document.createElement("span");
					span2.innerHTML = small;
					span2.className = "smallError";

				span.appendChild(span2);
			}
		}
	}
}

function ShowHideFAQ(box_name, a) { 
	var box = document.getElementById(box_name);
	var img = document.getElementById(box_name + "_img");
	if (box.className == "faqBox") {
		box.className = "faqBoxHidden";
		img.src = "/include/images/plus.gif";
	} else {
		box.className = "faqBox";
		img.src = "/include/images/minus.gif";
	}
}

function ShowHideBox(box_name, a) {
	var group = GetTab();
	var box = document.getElementById(box_name + "_" + group);
	// if we aren't on a tabbed box...
	var cl = "floatingBox";
	if (box == null) {
		box = document.getElementById(box_name);
		cl = "floatingBoxSmall";
	}
	if (box == null) { // something horrible happened
		return;
	}
	// allow other links to open the box
	if (a == null) {
		a = box.previousSibling.previousSibling;
	}
	if (box.className == cl) {
		box.className = "floatingBoxHidden";
	} else {
		box.className = cl;
	}
}

// Used for javascript code manipulating hidden forms to send to export_to_csv.php
function csvEscapeJSON(s) { 
	return s.replace(/./g, function(x) {
		return {
			'<': '&lt;',
			'>': '&gt;',
			'&': '&amp;',
			'"': '&quot;',
			'+': ' '
		}
		[x] || x;
	});
}

$(document).ready(function() {
	$('html').removeClass('no-js').addClass('js');

	var tabs = document.querySelectorAll('.tabs a');

	if (tabs.length) {
		// Get a list of all tabs
		var tlist = [];

		for (i = 0; i < tabs.length; i++)
		{
			tlist.push(tabs[i].getAttribute('href').replace('#DIV_', ''));
		}

		tablist = tlist.join(',');

		// Attach event handler
		for (i = 0; i < tabs.length; i++)
		{
			tabs[i].addEventListener('click', function (event) {
				event.preventDefault();

				ShowTab(this.getAttribute('id'), tablist);
			});
		}

		if (window.location.href.match(/\#/)) {
			var bits = window.location.href.split('#');
			tab = bits[1];
			ShowTab(tab, tablist);
		}
	}

	$('.dialog-help').dialog({
		autoOpen: false,
		modal: true,
		width: 550
	});

	$('.help').on('click', function(e){
		e.preventDefault();

		if ($($(this).attr('href')).length) {
			$($(this).attr('href')).dialog('open');
		}
	});

	$('.editicon').tooltip({
		position: {
			my: 'center bottom',
			at: 'center top'
		},
		// When moving between hovering over many elements quickly, the tooltip will jump around
		// because it can't start animating the fade in of the new tip until the old tip is
		// done. Solution is to disable one of the animations.
		hide: false,
		items: "img[alt]",
		content: function () {
			return $(this).attr('alt');
		}
	});

	$('.tip').tooltip({
		position: {
			my: 'center bottom',
			at: 'center top'
		},
		// When moving between hovering over many elements quickly, the tooltip will jump around
		// because it can't start animating the fade in of the new tip until the old tip is
		// done. Solution is to disable one of the animations.
		hide: false
	});

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
			'Authorization': 'Bearer ' + $('meta[name="api-token"]').attr('content'),
		}
	});

	$('.date-pick').datepicker({ dateFormat: 'yy-mm-dd' });

	$('.navbar .dropdown').hover(
		function () {
			$(this).find('.dropdown-menu').first().stop(true, true).delay(10).slideDown();
		},
		function () {
			$(this).find('.dropdown-menu').first().stop(true, true).delay(10).slideUp();
		}
	);

	$('.navbar .dropdown > a').on('click', function (e) {
		location.href = this.href;
	});
});