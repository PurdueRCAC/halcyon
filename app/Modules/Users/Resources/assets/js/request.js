/* global $ */ // jquery.js
/* global ROOT_URL */ // common.js
/* global WSPostURL */ // common.js
/* global WSDeleteURL */ // common.js
/* global SEARCH */ // search.js

var min_search_length = 2; // 0 for group
var add_button = "accept" // add
var search_path = "groupusername"; // groupname
var SELECTED = null;

/**
 * Perform search
 *
 * @param   {object}  clicked
 * @return  {void}
 */
/*function SearchEventHandler(clicked) {
	// don't do anything if nothing was selected
	if (clicked != null) {
		// Is this a user or group?
		if (clicked.match(/ws\//)) {
			if (clicked.match(/user/)) {
				document.getElementById("group").style.display = "none";
				document.getElementById("person").style.display = "block";
				document.getElementById("personname").innerHTML = SEARCH[clicked]['name'];
				document.getElementById("title").innerHTML = SEARCH[clicked]['title'];
			} else if (clicked.match(/group/)) {
				document.getElementById("person").style.display = "none";
				document.getElementById("group").style.display = "block";
				document.getElementById("groupname").innerHTML = SEARCH[clicked]['name'];
				document.getElementById("dept").innerHTML = SEARCH[clicked]['dept'];
			}
			PrintAccountResources(SEARCH[clicked]);
			SELECTED = SEARCH[clicked]['id'];
		} else {
			// if username doesn't exist, create it
			var post = JSON.stringify({ "name" : clicked });
			WSPostURL(ROOT_URL + "userusername", post, CreateUser, clicked);
		}
		ClearSearch();
	}
}*/

/**
 * Parse results, add to USER container and render them
 *
 * @param   {object}  xml
 * @param   {string}  clicked
 * @return  {void}
 */
/*function CreateUser(xml, clicked) {
	if (xml.status == 200) {
		var new_user = JSON.parse(xml.responseText);

		document.getElementById("group").style.display = "none";
		document.getElementById("person").style.display = "block";
		document.getElementById("personname").innerHTML = SEARCH[new_user['name']]['name'];

		PrintAccountResources(SEARCH[clicked]);
	}
}*/

/**
 * Output account resources
 *
 * @param   {object}  user
 * @return  {void}
 */
function PrintAccountResources(user) {
	var resources = Array();
	var pendingresources = Array();
	var queues = Array();
	var memberofqueues = Array();
	var pendingmemberofqueues = Array();
	var fortress = false;
	var x, div;

	if (typeof (user['resources']) != 'undefined') {
		document.getElementById("resources").style.display = "block";
		document.getElementById("queues").style.display = "none";
		div = document.getElementById("resourcelist");
		resources = user['resources'];
		if (typeof (user['pendingresources']) != 'undefined') {
			pendingresources = user['pendingresources'];
		}
	} else if (typeof (user['queues']) != 'undefined') {
		document.getElementById("queues").style.display = "block";
		document.getElementById("resources").style.display = "none";
		div = document.getElementById("queuelist");
		queues = user['queues'];
		memberofqueues = user['memberofqueues'];
		if (typeof (user['pendingmemberofqueues']) != 'undefined') {
			pendingmemberofqueues = user['pendingmemberofqueues'];
		}
		fortress = true;
	}

	document.getElementById("searchbox").style.display = "none";
	document.getElementById("comments").style.display = "block";
	document.getElementById("controls").style.display = "block";

	if (div) {
		div.innerHTML = "";
	}

	var d, box, label, resource, queue;
	for (x = 0; x < resources.length; x++) {
		resource = resources[x];

		if (resource['name'] == "BoilerGrid") {
			continue;
		}
		if (resource['name'] == "Radon") {
			continue;
		}
		if (resource['name'] == "Hathi") {
			continue;
		}
		d = document.createElement("div");
		box = document.createElement("input");
		box.type = "checkbox";
		box.id = resource['id'];

		d.appendChild(box);

		label = document.createElement("label");
		label.innerHTML = resource['name'];
		label.setAttribute('for', box.id);
		d.appendChild(label);

		d.id = resource['id'] + "_name";

		if (resource['name'] == "Fortress") {
			fortress = true;
		}

		div.appendChild(d);
	}

	for (x = 0; x < pendingresources.length; x++) {
		resource = pendingresources[x];

		d = document.createElement("div");
		box = document.createElement("input");
		box.type = "checkbox";
		box.id = resource['id'];
		box.checked = true;
		box.disabled = true;

		d.appendChild(box);

		label = document.createElement("label");
		label.innerHTML = resource['name'] + " (request pending)";
		label.setAttribute('for', box.id);
		d.appendChild(label);

		d.id = resource['id'] + "_name";

		if (resource['name'] == "Fortress") {
			fortress = true;
		}

		div.appendChild(d);
	}

	if (fortress) {
		for (x = 0; x < resources.length; x++) {
			resource = resources[x];

			box = document.getElementById(resource['id']);
			box.onchange = function () {
				CheckFortress(this.id);
			};
		}
	}

	for (x = 0; x < queues.length; x++) {
		queue = queues[x];

		d = document.createElement("div");

		box = document.createElement("input");
		box.type = "checkbox";
		box.id = queue['id'];

		d.appendChild(box);

		if (queue['resource'] == "BoilerGrid") {
			continue;
		}
		if (queue['resource'] == "Radon") {
			continue;
		}
		if (queue['resource'] == "Hathi") {
			continue;
		}

		label = document.createElement("label");
		label.setAttribute('for', box.id);
		if (queue['resource'].match(/Radon/)
			|| queue['resource'].match(/Fortress/)
			|| queue['resource'].match(/BoilerGrid/)) {
			label.innerHTML = queue['resource'] + " (" + queue['name'] + ")";
		} else {
			label.innerHTML = queue['name'] + " (" + queue['subresource'] + ")";
		}

		d.appendChild(label);
		d.id = queue['id'] + "_name";

		div.appendChild(d);
	}

	for (x = 0; x < memberofqueues.length; x++) {
		queue = memberofqueues[x];

		d = document.createElement("div");

		box = document.createElement("input");
		box.type = "checkbox";
		box.checked = "true";
		box.disabled = "true";
		box.id = queue['id'];

		d.appendChild(box);

		span = document.createElement("span");
		if (queue['resource'].match(/Radon/)
			|| queue['resource'].match(/Fortress/)
			|| queue['resource'].match(/BoilerGrid/)) {
			span.innerHTML = queue['resource'] + " (" + queue['name'] + ", access authorized)";
		} else {
			span.innerHTML = queue['name'] + " (" + queue['subresource'] + ", access authorized)";
		}

		d.appendChild(span);
		d.id = queue['id'] + "_name";

		div.appendChild(d);
	}

	for (x = 0; x < pendingmemberofqueues.length; x++) {
		queue = pendingmemberofqueues[x];

		d = document.createElement("div");

		box = document.createElement("input");
		box.type = "checkbox";
		box.checked = "true";
		box.disabled = "true";
		box.id = queue['id'];

		d.appendChild(box);

		span = document.createElement("span");
		if (queue['resource'].match(/Radon/)
			|| queue['resource'].match(/Fortress/)
			|| queue['resource'].match(/BoilerGrid/)) {
			span.innerHTML = queue['resource'] + " (" + queue['name'] + ", request pending)";
		} else {
			span.innerHTML = queue['name'] + " (" + queue['subresource'] + ", request pending)";
		}

		d.appendChild(span);
		d.id = queue['id'] + "_name";

		div.appendChild(d);
	}

	if (queues.length == 0 && pendingmemberofqueues.length == 0 && pendingresources.length == 0 && fortress) {
		div.appendChild(document.createTextNode("These resources are available to anyone on campus with the approval of a faculty or staff member. An email notification will be sent to this person to review this request."));
	}

	if (resources.length == 0 && queues.length == 0 && pendingmemberofqueues.length == 0 && pendingresources.length == 0) {
		var text = document.createElement("p");
		text.innerHTML = 'The faculty or group you select does not participate in any current <a href="/services/communityclusters/">Community Clusters</a>.<br/><br/><strong>NOTE:</strong> This request form does not support requesting Data Depot at this time. You will need to ask your faculty member/advisor directly to add you to the appropriate groups from the <a href="/account/user/">Manage Users</a> page (they should have access to this link).<br/><br/>You may try searching by your Department as some have queues they may grant you access to. If you are collaborating with another faculty member, try searching their name instead.<br/><br/>Otherwise, your faculty may purchase access to the <a href="/services/communityclusters/">Community Cluster Program</a>.';
		text.className = 'alert alert-warning';
		div.appendChild(text);
		document.getElementById("comments").style.display = "none";
		document.getElementById("controls").style.display = "none";
	}
}

/**
 * Submit request
 *
 * @return  {void}
 */
function SubmitRequest() {
	var queues = Array();
	var resources = Array();
	var free = false;
	var x, boxes, box;

	//if (document.getElementById("resources").style.display != "none") {
	//boxes = document.getElementById("resourcelist").getElementsByTagName("div");
	boxes = document.querySelectorAll("#resourcelist input[type=checkbox]"); //.getElementsByTagName("div");
	for (x = 0; x < boxes.length; x++) {
		box = boxes[x];
		//box = boxes[x].getElementsByTagName("input")[0];
		//var text = boxes[x].getElementsByTagName("label")[0].innerHTML;
		if (box.checked == true
			&& box.disabled == false) {
			resources.push(box.id);
		}
		/*if (text.match(/Radon/)) {
			free = true;
		} else if (box.disabled == true && text.match(/Fortress/)) {
			resources.push(box.id);
		}*/
	}
	//} else if (document.getElementById("queues").style.display != "none") {
	boxes = document.querySelectorAll("queuelist input[type=checkbox]"); //.getElementsByTagName("div");
	for (x = 0; x < boxes.length; x++) {
		box = boxes[x]; //.getElementsByTagName("input")[0];

		if (box.checked == true
			&& box.disabled == false) {
			queues.push(box.id);
		}
	}
	//}

	if (queues.length == 0
		&& resources.length == 0) {
		alert("Please make a selection");
		return;
	}

	/*if (free) {
		document.getElementById("free_confirmation").style.display = "block";
		document.getElementById("cluster_confirmation").style.display = "none";
	} else {*/
	document.getElementById("cluster_confirmation").style.display = "block";
	document.getElementById("free_confirmation").style.display = "none";
	//}

	if (document.getElementById("person").style.display != "none") {
		document.getElementById("person_confirmation").style.display = "block";
		document.getElementById("group_confirmation").style.display = "none";
		document.getElementById("personname_confirmation").innerHTML = document.getElementById("personname").innerHTML;
	} else if (document.getElementById("group").style.display != "none") {
		document.getElementById("person_confirmation").style.display = "none";
		document.getElementById("group_confirmation").style.display = "block";
		document.getElementById("groupname_confirmation").innerHTML = document.getElementById("groupname").innerHTML;
	}

	var list, div;
	if (queues.length > 0) {
		document.getElementById("queues_confirmation").style.display = "block";
		list = document.getElementById("queuelist_confirmation");
		for (x = 0; x < queues.length; x++) {
			div = document.createElement("div");
			div.innerHTML = document.getElementById(queues[x] + "_name").getElementsByTagName("label")[0].innerHTML;

			list.appendChild(div);
		}
	} else if (resources.length > 0) {
		document.getElementById("resources_confirmation").style.display = "block";
		list = document.getElementById("resourcelist_confirmation");
		for (x = 0; x < resources.length; x++) {
			div = document.createElement("div");
			div.innerHTML = document.getElementById(resources[x] + "_name").getElementsByTagName("label")[0].innerHTML;

			list.appendChild(div);
		}
	}

	document.getElementById("comment_confirmation").style.display = "block";
	if (document.getElementById("commenttext").value != "") {
		document.getElementById("commenttext_confirmation").innerHTML = document.getElementById("commenttext").value;
	} else {
		document.getElementById("commenttext_confirmation").innerHTML = "(No Comments Entered)";
	}

	document.getElementById("group").style.display = "none";
	document.getElementById("person").style.display = "none";
	document.getElementById("resources").style.display = "none";
	document.getElementById("queues").style.display = "none";
	document.getElementById("comments").style.display = "none";
	document.getElementById("controls").style.display = "none";
	document.getElementById("searchbox").style.display = "none";
	document.getElementById("request_header").style.display = "none";

	// assemble and submit request
	var post = { 'comment': document.getElementById("commenttext").value };

	post['group'] = document.getElementById("selected-group").value;
	post['userid'] = document.getElementById("selected-user").value;
	post['resources'] = Array();
	post['queues'] = Array();

	for (x = 0; x < resources.length; x++) {
		post['resources'].push(resources[x]);
	}

	for (x = 0; x < queues.length; x++) {
		post['queues'].push(queues[x]);
	}

	//post = JSON.stringify(post);

	$.when(
		$.post(ROOT_URL + 'queues/requests', post).fail(function () {
			$('#errors').addClass('alert').addClass('alert-danger').text("There was an error processing your request.");
		})
	).done(function (request) {
		//window.location = url;
		$('#errors').addClass('alert').addClass('alert-success').text("Your request has been submitted.");
	});
	/*WSPostURL(ROOT_URL + "userrequest", post, function(xml) {
		if (xml.status != 200) {
			alert("There was an error processing your request. Please reload page and try request again. If problem persists contact rcac-help@purdue.edu with request.");
		} else {
			document.getElementById("account").onclick = function() {
				window.location = "/account/myinfo/";
			};
		}
	});*/
}

/**
 * Check Fortress
 *
 * @param   {string}  id
 * @return  {void}
 */
function CheckFortress(id) {
	var fortress = document.getElementById(ROOT_URL + "resource/48");
	var clicked = document.getElementById(id);

	if (fortress == clicked) {
		return;
	}

	if (clicked.checked == true) {
		fortress.checked = true;
		fortress.disabled = true;
	} else if (clicked.checked == false) {
		var resources = document.getElementById("resourcelist").getElementsByTagName("input");
		var keepfortress = false;
		for (var x = 0; x < resources.length; x++) {
			if (resources[x].id != ROOT_URL + "resource/48" && resources[x].checked == true) {
				keepfortress = true;
				break;
			}
		}

		if (!keepfortress) {
			fortress.disabled = false;
		}
	}
}

/**
 * Remove a queue
 *
 * @param   {string}  id
 * @param   {string}  name
 * @param   {string}  resource
 * @return  {void}
 */
function RemoveQueue(id, name, resource) {
	if (confirm("Are you sure you wish to remove access to the queue '" + name + "' (" + resource + ")? \n\nIf this is your last queue on this cluster your account will be removed. Please make an off-site backup of all important data before removing access.")) {
		WSDeleteURL(id, function (xml) {
			if (xml.status < 400) {
				location.reload(true);
			} else {
				alert("An error occurred while processing request. Please wait a few minutes and try again. If problem persists contact rcac-help@purdue.edu");
			}
		});
	}
}

/**
 * Cancel a queue request
 *
 * @param   {string}  id
 * @param   {string}  name
 * @param   {string}  resource
 * @return  {void}
 */
function CancelQueueRequest(id, name, resource) {
	if (confirm("Are you sure you wish to cancel this request for access to the queue '" + name + "' (" + resource + ")? ")) {
		WSDeleteURL(id, function (xml) {
			if (xml.status < 400) {
				location.reload(true);
			} else {
				alert("An error occurred while processing request. Please wait a few minutes and try again. If problem persists contact rcac-help@purdue.edu");
			}
		});
	}
}

/**
 * Cancel a resource request
 *
 * @param   {string}  id
 * @param   {array}   resources
 * @return  {void}
 */
function CancelResourceRequest(id, resources) {
	var del = false;

	if (resources.length > 0) {
		var r = "";
		for (var x = 0; x < resources.length; x++) {
			r = r + resources[x]['name'] + "\n";
		}
		del = confirm("Are you sure you wish this request?\n\nYou must request cancel this request in full, including requests for:\n\n" + r);
	} else {
		del = confirm("Are you sure you wish this request?\n\nYou must request cancel this request in full.\n\n");
	}

	if (del) {
		WSDeleteURL(id, function (xml) {
			if (xml.status < 400) {
				location.reload(true);
			} else {
				alert("An error occurred while processing request. Please wait a few minutes and try again. If problem persists contact rcac-help@purdue.edu");
			}
		});
	}
}

/**
 * Request group
 *
 * @param   {string}  longname
 * @param   {string}  user
 * @return  {void}
 */
function RequestGroup(longname, user) {
	var post = {
		'longname': longname,
		'user': user
	};

	WSPostURL(ROOT_URL + "unixgroups/members", JSON.stringify(post), function (xml) {
		if (xml.status == 200) {
			alert("You have been added to the access list for this software. Changes will take up to 4 hours to propagate to all cluster nodes.");
			location.reload(true)
		} else {
			alert("An error occurred.")
		}
	});
}

/**
 * Initiate event hooks
 */
$(document).ready(function () {
	var results = [];

	$('.searchgroups').autocomplete({
		//source: autocompleteName(this.getAttribute('data-source')),
		source: function (request, response) {
			var url = $(this.element).data('source');

			//return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
			return $.getJSON(url + encodeURIComponent(request.term), function (data) {
				response($.map(data.data, function (el) {
					results[el.id] = el;
					return {
						label: el.name,
						name: el.name,
						id: el.id
					};
				}));
			});
		},
		dataName: 'data',
		height: 150,
		delay: 100,
		minLength: 2,
		filter: /^[a-z0-9\-_ .,@+]+$/i,
		select: function (event, ui) {
			var data = results[ui.item.id];

			document.getElementById("person").style.display = "none";
			document.getElementById("group").style.display = "block";
			document.getElementById("groupname").innerHTML = data['name'];
			document.getElementById("selected-group").value = data['id'];

			var names = [];
			for (x = 0; x < data['department'].length; x++) {
				names.push(data['department'][x]['name']);
			}
			document.getElementById("dept").innerHTML = names.join(', ');

			PrintAccountResources(data);
		}
	});

	$('.request-clear').on('click', function (e) {
		e.preventDefault();

		var els = document.querySelectorAll('.request-selection');

		for (x = 0; x < els.length; x++) {
			els[x].style.display = 'none';
		}

		document.getElementById("searchbox").style.display = 'block';
		document.getElementById("newuser").value = '';
	});

	$('.request-submit').on('click', function (e) {
		e.preventDefault();
		SubmitRequest();
	});
});
