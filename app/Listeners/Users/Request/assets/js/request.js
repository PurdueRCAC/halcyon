/* global $ */ // jquery.js
/* global ROOT_URL */ // common.js

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
	var x, div, span;

	if (typeof (user['queues']) != 'undefined') {
		document.getElementById("queues").style.display = "block";
		document.getElementById("resources").style.display = "none";
		div = document.getElementById("queuelist");
		queues = user['queues'];
		if (typeof (user['memberofqueues']) != 'undefined') {
			memberofqueues = user['memberofqueues'];
		}
		if (typeof (user['pendingmemberofqueues']) != 'undefined') {
			pendingmemberofqueues = user['pendingmemberofqueues'];
		}
	} else if (typeof (user['resources']) != 'undefined') {
		document.getElementById("resources").style.display = "block";
		document.getElementById("queues").style.display = "none";
		div = document.getElementById("resourcelist");
		resources = user['resources'];
		if (typeof (user['pendingresources']) != 'undefined') {
			pendingresources = user['pendingresources'];
		}
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

		div.appendChild(d);
	}

	for (x = 0; x < queues.length; x++) {
		queue = queues[x];

		d = document.createElement("div");

		box = document.createElement("input");
		box.type = "checkbox";
		box.id = queue['id'];

		d.appendChild(box);

		label = document.createElement("label");
		label.setAttribute('for', box.id);
		label.innerHTML = queue['name'] + " (" + queue['resource']['name'] + ")";

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
		span.innerHTML = queue['name'] + " (" + queue['resource']['name'] + ", access authorized)";

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
		span.innerHTML = queue['name'] + " (" + queue['subresource'] + ", request pending)";

		d.appendChild(span);
		d.id = queue['id'] + "_name";

		div.appendChild(d);
	}

	if (queues.length == 0
	&& pendingmemberofqueues.length == 0
	&& pendingresources.length == 0
	&& resources.length == 0) {
		var notice = document.getElementById("no-resources");
		notice.classList.remove('d-none');

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

	boxes = document.querySelectorAll("#resourcelist input[type=checkbox]");
	for (x = 0; x < boxes.length; x++) {
		box = boxes[x];

		if (box.checked == true
			&& box.disabled == false) {
			resources.push(box.id);
		}
	}

	boxes = document.querySelectorAll("#queuelist input[type=checkbox]");
	for (x = 0; x < boxes.length; x++) {
		box = boxes[x];

		if (box.checked == true
			&& box.disabled == false) {
			queues.push(box.id);
		}
	}

	if (queues.length == 0
		&& resources.length == 0) {
		alert("Please make a selection");
		return;
	}

	if (free) {
		document.getElementById("free_confirmation").style.display = "block";
		document.getElementById("cluster_confirmation").style.display = "none";
	} else {
		document.getElementById("cluster_confirmation").style.display = "block";
		document.getElementById("free_confirmation").style.display = "none";
	}

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

	// Pending group membership
	var grouppost = {
		'groupid': post['group'],
		'userid': 0,
		'userrequestid': 0,
		'membertype': 4
	};

	//post = JSON.stringify(post);

	$.when(
		$.post(ROOT_URL + 'queues/requests', post, function (response) {
			grouppost['userid'] = response.userid;
			grouppost['userrequestid'] = response.id;

			$.post(ROOT_URL + 'groups/members', grouppost).fail(function () {
				$('#errors').addClass('alert').addClass('alert-danger').text("There was an error processing your request.");
			});
		})
		.fail(function () {
			$('#errors').addClass('alert').addClass('alert-danger').text("There was an error processing your request.");
		})
	).done(function () {
		//window.location = url;
		$('#errors').addClass('alert').addClass('alert-success').text("Your request has been submitted.");
	});
	/*WSPostURL(ROOT_URL + "userrequest", post, function(xml) {
		if (xml.status != 200) {
			alert("There was an error processing your request. Please reload page and try request again. If problem persists contact help.");
		} else {
			document.getElementById("account").onclick = function() {
				window.location = "/account/myinfo/";
			};
		}
	});*/
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {
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

			fetch(data['api'], {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				}
			})
			.then(function (response) {
				if (response.ok) {
					return response.json();
				}
				return response.json().then(function (data) {
					var msg = data.message;
					msg = (typeof msg === 'object' ? Object.values(msg).join('<br />') : msg);
					throw msg;
				});
			})
			.then(function (res) {
				var notice = document.getElementById("no-resources");
				notice.classList.add('d-none');

				document.getElementById("person").style.display = "none";
				document.getElementById("group").style.display = "block";
				document.getElementById("groupname").innerHTML = res['name'];
				document.getElementById("selected-group").value = res['id'];

				var names = [];
				for (var x = 0; x < res['department'].length; x++) {
					names.push(res['department'][x]['name']);
				}
				document.getElementById("dept").innerHTML = names.join(', ');

				PrintAccountResources(res);
			})
			.catch(function (error) {
				alert(error);
			});
		}
	});

	document.querySelectorAll('.request-clear').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var els = document.querySelectorAll('.request-selection');

			for (var x = 0; x < els.length; x++) {
				els[x].style.display = 'none';
			}

			document.getElementById("searchbox").style.display = 'block';
			document.getElementById("newuser").value = '';
		});
	});

	document.querySelectorAll('.request-submit').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			SubmitRequest();
		});
	});
});
