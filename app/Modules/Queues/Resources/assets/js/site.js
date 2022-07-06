/* global $ */ // jquery.js
/* global WSPostURL */ // common.js
/* global WSDeleteURL */ // common.js

if (ERRORS === undefined) {
	var ERRORS = Object();
}
ERRORS['queue'] = "Unable to create queue.";
ERRORS['queueconflict'] = "A queue by this name for this scheduler already exists.";
ERRORS['queueformat'] = "A required option is missing or in an incorrect format.";
ERRORS['deletequeue'] = "An error occurred while deleting queue.";
ERRORS['purchase'] = "An error occurred while creating purchase.";
ERRORS['loan'] = "An error occurred while creating loan.";
ERRORS['queueinvalid'] = "Invalid condition. Invalid date or source does not have enough cores for the duration of the purchase/loan.";
ERRORS['modifyloan'] = "An error occurred while modifying loan. Reload page and try again.";
ERRORS['modifypurchase'] = "An error occurred while modifying purchase. Reload page and try again.";
ERRORS['deletepurchase'] = "An error occurred while deleting purchase.";
ERRORS['deleteloan'] = "An error occurred while deleting loan.";
ERRORS['accountingfailed'] = "Failed to maintain proper accounting. Ensure proper accounting before continuing.";
ERRORS['accountingmissing'] = "Failed to find counter entry. Ensure proper accounting before continuing.";
ERRORS['createreservation'] = "Unable to create a new reservation.";
ERRORS['deletereservation'] = "An error occurred while deleting reservation.";

/**
 * Show queue purchase
 *
 * @return  {void}
 */
/*function ShowQueuePurchase() {
	document.getElementById("new_queuesizeloan").style.display = "block";
	document.getElementById("enddate").style.display = "none";
	document.getElementById("enddatealt").style.display = "block";
	document.getElementById("group").childNodes[0].innerHTML = "(Select Group)";
	//document.getElementById("commentrow").style.display = "none";
	document.getElementById("submitloan").style.display = "none";
	document.getElementById("submitpurchase").style.display = "inline";
	document.getElementById("targetloan").style.display = "none";
	document.getElementById("targetsale").style.display = "block";
}*/

/**
 * Show queue loan
 *
 * @return  {void}
 */
/*function ShowQueueLoan() {
	document.getElementById("new_queuesizeloan").style.display = "block";
	document.getElementById("enddate").style.display = "block";
	document.getElementById("enddatealt").style.display = "none";
	document.getElementById("group").childNodes[0].innerHTML = "(Select Group)";
	//document.getElementById("commentrow").style.display = "table-row";
	document.getElementById("submitloan").style.display = "inline";
	document.getElementById("submitpurchase").style.display = "none";
	document.getElementById("targetloan").style.display = "block";
	document.getElementById("targetsale").style.display = "none";
}*/

/**
 * Create a purchase
 *
 * @return  {void}
 */
/*function CreatePurchase() {
	// get input
	var cores = document.getElementById("cores").value.replace(/(^\s+|\s+$)/g, "");
	var startdate = document.getElementById("datestart").value.replace(/(^\s+|\s+$)/g, "");
	var starttime = document.getElementById("timestartshort").value.replace(/(^\s+|\s+$)/g, "");
	var comment = document.getElementById("comment").value.replace(/(^\s+|\s+$)/g, "");
	var queue = document.getElementById("queueid").value;
	var group = document.getElementById("group");
	if (group.selectedIndex == 0) {
		group = ROOT_URL + "group/0";
	} else {
		group = group.options[group.selectedIndex].value;
	}
	var recipient = document.getElementById("queue");
	if (recipient.selectedIndex == 0 && recipient.length > 1) {
		recipient = ROOT_URL + "queue/0";
	} else {
		recipient = recipient.options[recipient.selectedIndex].value;
	}

	// validate
	if (!cores.match(RegExp('^[\-]?[0-9]+$')) || cores < 0) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (!startdate.match(RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (!starttime.match(RegExp('^[0-9]{1,2}\:[0-9]{2} ?(AM|PM)$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (recipient == ROOT_URL + "queue/0") {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (queue == recipient) {
		SetError(ERRORS['queueformat'], null);
		return;
	}

	// swap if this a new purchase
	if (queue == "0") {
		if (window.location.href.match(/newhardware/) && window.location.href.match(/reduce/)) {
			cores = -cores;
		}
		queue = ROOT_URL + "queue/0";
	}

	// do AM + PM
	var match = starttime.match(/(\d{1,2})\:(\d{2}) ?(AM|PM)/);

	if (match[3] == "PM") {
		if (match[1] != "12") {
			match[1] = parseInt(match[1]) + 12;
		}
		starttime = match[1].toString() + ":" + match[2];
	} else {
		if (match[1] == "12") {
			match[1] = "00";
		}
		starttime = match[1] + ":" + match[2];
	}

	// adjust time accordingly
	var start = startdate + " " + starttime + ":00";

	// assemble POST
	var post = JSON.stringify({
		'seller': queue,
		'queue': recipient,
		'corecount': cores,
		'comment': comment,
		'start': start,
		'stop': '0000-00-00 00:00:00'
	});

	// disable button
	document.getElementById("submitpurchase").disabled = "true";
	document.getElementById("cancel").disabled = "true";

	WSPostURL(ROOT_URL + "queuesize", post, CreatedPurchase);
}*/

/**
 * Callback after creating a purchase
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function CreatedPurchase(xml) {
	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);
		if (results['seller'] == ROOT_URL + "queue/0") {
			var queue = results['queue'].split("/");
			queue = queue[3];
			window.location = "/admin/queue/edit/?q=" + queue + "&e=" + results['id'];
		} else {
			window.location.reload(true);
		}
	} else if (xml.status == 415) {
		// enable button
		document.getElementById("submitpurchase").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['queueformat'], null);
	} else if (xml.status == 409) {
		// enable button
		document.getElementById("submitpurchase").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['queueinvalid'], null);
	} else if (xml.status == 500) {
		// enable button
		document.getElementById("submitpurchase").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['accountingfailed'], null);
	} else if (xml.status == 506) {
		// enable button
		document.getElementById("submitpurchase").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['accountingmissing'], null);
	} else {
		// enable button
		document.getElementById("submitpurchase").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['purchase'], null);
	}
}*/

/**
 * Create a loan
 *
 * @return  {void}
 */
/*function CreateLoan() {
	// get input
	var cores = document.getElementById("cores").value.replace(/(^\s+|\s+$)/g, "");
	var startdate = document.getElementById("datestart").value.replace(/(^\s+|\s+$)/g, "");
	var starttime = document.getElementById("timestartshort").value.replace(/(^\s+|\s+$)/g, "");
	var enddate = document.getElementById("datestop").value.replace(/(^\s+|\s+$)/g, "");
	var endtime = document.getElementById("timestopshort").value.replace(/(^\s+|\s+$)/g, "");
	var queue = document.getElementById("queueid").value;
	var comment = document.getElementById("comment").value.replace(/(^\s+|\s+$)/g, "");
	var group = document.getElementById("group");
	if (group.selectedIndex == 0) {
		group = ROOT_URL + "group/0";
	} else {
		group = group.options[group.selectedIndex].value;
	}
	var recipient = document.getElementById("queue");
	if (recipient.selectedIndex == 0 && recipient.length > 1) {
		recipient = ROOT_URL + "queue/0";
	} else {
		recipient = recipient.options[recipient.selectedIndex].value;
	}

	// validate
	if (!cores.match(RegExp('^[\-]?[0-9]+$')) || cores < 0) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (!startdate.match(RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (!starttime.match(RegExp('^[0-9]{1,2}\:[0-9]{2} ?(AM|PM)$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (enddate && !enddate.match(RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (endtime && !endtime.match(RegExp('^[0-9]{1,2}\:[0-9]{2} ?(AM|PM)$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (recipient == ROOT_URL + "queue/0") {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (queue == recipient) {
		SetError(ERRORS['queueformat'], null);
		return;
	}

	// do AM + PM
	var match = starttime.match(/(\d{1,2})\:(\d{2}) ?(AM|PM)/);

	if (match[3] == "PM") {
		if (match[1] != "12") {
			match[1] = parseInt(match[1]) + 12;
		}
		starttime = match[1].toString() + ":" + match[2];
	} else {
		if (match[1] == "12") {
			match[1] = "00";
		}
		starttime = match[1] + ":" + match[2];
	}

	// adjust time accordingly
	var start = startdate + " " + starttime + ":00";

	var end = "";
	if (!enddate) {
		end = "0000-00-00 00:00:00";
	} else {
		// do AM + PM
		match = endtime.match(/(\d{1,2})\:(\d{2}) ?(AM|PM)/);

		if (match[3] == "PM") {
			if (match[1] != "12") {
				match[1] = parseInt(match[1]) + 12;
			}
			endtime = match[1].toString() + ":" + match[2];
		} else {
			if (match[1] == "12") {
				match[1] = "00";
			}
			endtime = match[1] + ":" + match[2];
		}

		end = enddate + " " + endtime + ":59";
	}

	// assemble POST
	var post = JSON.stringify({
		'lender': queue,
		'queue': recipient,
		'corecount': cores,
		'comment': comment,
		'start': start,
		'stop': end
	});

	// disable button
	document.getElementById("submitloan").disabled = "true";
	document.getElementById("cancel").disabled = "true";

	WSPostURL(ROOT_URL + "queueloan", post, CreatedLoan);
}*/

/**
 * Callback after creating a loan
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function CreatedLoan(xml) {
	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);
		var queue = results['queue'].split("/");
		queue = queue[3];
		window.location = "/admin/queue/edit/?q=" + queue + "&e=" + results['id'];
	} else if (xml.status == 415) {
		// enable button
		document.getElementById("submitloan").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['queueformat'], null);
	} else if (xml.status == 409) {
		// enable button
		document.getElementById("submitloan").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['queueinvalid'], null);
	} else if (xml.status == 500) {
		// enable button
		document.getElementById("submitloan").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['accountingfailed'], null);
	} else if (xml.status == 506) {
		// enable button
		document.getElementById("submitloan").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['accountingmissing'], null);
	} else {
		// enable button
		document.getElementById("submitloan").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['loan'], null);
	}
}*/

/**
 * Modify a loan
 *
 * @param   {string}  id
 * @return  {void}
 */
/*function ModifyLoan(id) {
	// get input
	var cores = document.getElementById("cores").value.replace(/(^\s+|\s+$)/g, "");
	var changedate = document.getElementById("datechange");
	if (changedate) {
		changedate = changedate.value.replace(/(^\s+|\s+$)/g, "");
	}
	var changetime = document.getElementById("timechangeshort");
	if (changetime) {
		changetime = changetime.value.replace(/(^\s+|\s+$)/g, "");
	}
	var startdate = document.getElementById("datestart");
	if (startdate) {
		startdate = startdate.value.replace(/(^\s+|\s+$)/g, "");
	}
	var starttime = document.getElementById("timestartshort");
	if (starttime) {
		starttime = starttime.value.replace(/(^\s+|\s+$)/g, "");
	}
	var enddate = document.getElementById("datestop");
	var endtime = document.getElementById("timestopshort");
	if (enddate) {
		enddate = enddate.value;
		if (enddate == '') {
			enddate = '0000-00-00';
		}
		endtime = endtime.value;
		if (endtime == '') {
			endtime = '12:00 AM';
		}
	}
	var comment = document.getElementById("comment").value;

	// get reference data
	var coresref = document.getElementById("coresref").value;
	var startref = document.getElementById("startref").value;
	var stopref = document.getElementById("stopref").value;
	var commentref = document.getElementById("commentref").value;
	var queue = document.getElementById("queueid").value;
	var source = document.getElementById("source").value;

	// validate
	if (cores && (!cores.match(RegExp('^[\-]?[0-9]+$')) || cores < 0)) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (changedate && !changedate.match(RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (changetime && !changetime.match(RegExp('^[0-9]{1,2}\:[0-9]{2} ?(AM|PM)$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (startdate && !startdate.match(RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (starttime && !starttime.match(RegExp('^[0-9]{1,2}\:[0-9]{2} ?(AM|PM)$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (enddate && !enddate.match(RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (endtime && !endtime.match(RegExp('^[0-9]{1,2}\:[0-9]{2} ?(AM|PM)$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (cores && changedate == '') {
		SetError(ERRORS['queueformat'], null);
		return;
	}

	var match;

	if (changetime) {
		// do AM + PM
		match = changetime.match(/(\d{1,2})\:(\d{2}) ?(AM|PM)/);

		if (match[3] == "PM") {
			if (match[1] != "12") {
				match[1] = parseInt(match[1]) + 12;
			}
			changetime = match[1].toString() + ":" + match[2];
		} else {
			if (match[1] == "12") {
				match[1] = "00";
			}
			changetime = match[1] + ":" + match[2];
		}
	}
	if (starttime) {
		// do AM + PM
		match = starttime.match(/(\d{1,2})\:(\d{2}) ?(AM|PM)/);

		if (match[3] == "PM") {
			if (match[1] != "12") {
				match[1] = parseInt(match[1]) + 12;
			}
			starttime = match[1].toString() + ":" + match[2];
		} else {
			if (match[1] == "12") {
				match[1] = "00";
			}
			starttime = match[1] + ":" + match[2];
		}
	}
	if (endtime) {
		// do AM + PM
		match = endtime.match(/(\d{1,2})\:(\d{2}) ?(AM|PM)/);

		if (match[3] == "PM") {
			if (match[1] != "12") {
				match[1] = parseInt(match[1]) + 12;
			}
			endtime = match[1].toString() + ":" + match[2];
		} else {
			if (match[1] == "12") {
				match[1] = "00";
			}
			endtime = match[1] + ":" + match[2];
		}
	}

	var update_post = {};
	var new_post = {};
	// if we are changing start date
	if (startdate && startdate + " " + starttime != startref) {
		update_post['start'] = startdate + " " + starttime + ":00";
	}

	// if we are changing only the end time
	if (enddate && !changedate && enddate + " " + endtime != stopref) {
		if (enddate == '0000-00-00 00:00') {
			update_post['stop'] = enddate + " " + endtime + ":00";
		} else {
			update_post['stop'] = enddate + " " + endtime + ":59";
		}
	}

	// if we are changing comment
	if (comment && comment != commentref) {
		update_post['comment'] = comment;
	}

	// if we are changing sizes of something that hasn't started
	if (cores && enddate && !changedate && -cores != coresref) {
		update_post['corecount'] = -cores;
	}

	// if we are changing sizes en route
	if (cores && changedate) {
		update_post['stop'] = changedate + " " + changetime + ":00";
		new_post['start'] = changedate + " " + changetime + ":00";
		if (enddate == '0000-00-00 00:00') {
			new_post['stop'] = enddate + " " + endtime + ":00";
		} else {
			new_post['stop'] = enddate + " " + endtime + ":59";
		}
		new_post['comment'] = comment;
		new_post['queue'] = source;
		new_post['lender'] = queue;
		new_post['corecount'] = cores;
	}

	if (JSON.stringify(new_post) != '{}') {
		// disable button
		document.getElementById("modifyloan").disabled = "true";
		document.getElementById("cancel").disabled = "true";

		WSPostURL(id, JSON.stringify(update_post), ModifiedLoanA, new_post);
	} else if (JSON.stringify(update_post) != '{}') {
		// disable button
		document.getElementById("modifyloan").disabled = "true";
		document.getElementById("cancel").disabled = "true";

		WSPostURL(id, JSON.stringify(update_post), ModifiedLoanA, null);
	}
}*/

/**
 * Callback after modifying a loan
 *
 * @param   {object}  xml
 * @param   {object}  post
 * @return  {void}
 */
/*function ModifiedLoanA(xml, post) {
	if (xml.status == 200) {
		if (post == null) {
			window.location.reload(true);
		} else {
			WSPostURL(ROOT_URL + "queueloan", JSON.stringify(post), ModifiedLoanB);
		}
	} else if (xml.status == 409) {
		// enable button
		document.getElementById("modifyloan").disabled = undefined;
		document.getElementById("modifyloan").disabled = undefined;

		SetError(ERRORS['queueinvalid'], null);
	} else if (xml.status == 506) {
		// enable button
		document.getElementById("modifyloan").disabled = undefined;
		document.getElementById("modifyloan").disabled = undefined;

		SetError(ERRORS['accountingmissing'], null);
	} else {
		// enable button
		document.getElementById("modifyloan").disabled = undefined;
		document.getElementById("modifyloan").disabled = undefined;

		SetError(ERRORS['modifypurchase'], null);
	}
}*/

/*function ModifiedLoanB(xml) {
	if (xml.status == 200) {
		window.location.reload(true);
	} else if (xml.status == 409) {
		SetError(ERRORS['queueinvalid'], null);
	} else if (xml.status == 506) {
		SetError(ERRORS['accountingmissing'], null);
	} else {
		SetError(ERRORS['modifyloan'], null);
	}
}*/

/**
 * Modify a purchase
 *
 * @param   {string}  id
 * @return  {void}
 */
/*function ModifyPurchase(id) {
	// get input
	var cores = document.getElementById("cores").value.replace(/(^\s+|\s+$)/g, "");
	var startdate = document.getElementById("datestart").value.replace(/(^\s+|\s+$)/g, "");
	var starttime = document.getElementById("timestartshort").value.replace(/(^\s+|\s+$)/g, "");

	// get reference data
	var coresref = document.getElementById("coresref").value;
	var startref = document.getElementById("startref").value;
	//var queue = document.getElementById("queueid").value;
	var source = document.getElementById("source").value;

	// validate
	if (cores && (!cores.match(RegExp('^[\-]?[0-9]+$')) || cores < 0)) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (!startdate.match(RegExp('^[0-9]{4}-[0-9]{2}-[0-9]{2}$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}
	if (!starttime.match(RegExp('^[0-9]{1,2}\:[0-9]{2} ?(AM|PM)$'))) {
		SetError(ERRORS['queueformat'], null);
		return;
	}

	// do AM + PM
	var match = starttime.match(/(\d{1,2})\:(\d{2}) ?(AM|PM)/);

	if (match[3] == "PM") {
		if (match[1] != "12") {
			match[1] = parseInt(match[1]) + 12;
		}
		starttime = match[1].toString() + ":" + match[2];
	} else {
		if (match[1] == "12") {
			match[1] = "00";
		}
		starttime = match[1] + ":" + match[2];
	}

	var update_post = {};
	// if we are changing start date
	if (startdate && startdate + " " + starttime != startref) {
		update_post['start'] = startdate + " " + starttime + ":00";
	}

	// if we are changing sizes of something that hasn't started
	if (cores && cores != -coresref) {
		if (source) {
			update_post['corecount'] = -cores;
		} else {
			update_post['corecount'] = cores;
		}
	}

	if (JSON.stringify(update_post) != '{}') {
		// disable button
		document.getElementById("modifypurchase").disabled = "true";
		document.getElementById("cancel").disabled = "true";

		WSPostURL(id, JSON.stringify(update_post), ModifiedPurchase);
	}
}*/

/**
 * Callback after modifying a purchase
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function ModifiedPurchase(xml) {
	if (xml.status == 200) {
		window.location.reload(true);
	} else if (xml.status == 409) {
		// enable button
		document.getElementById("modifypurchase").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['queueinvalid'], null);
	} else if (xml.status == 506) {
		// enable button
		document.getElementById("modifypurchase").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['accountingmissing'], null);
	} else {
		// enable button
		document.getElementById("modifypurchase").disabled = undefined;
		document.getElementById("cancel").disabled = undefined;

		SetError(ERRORS['modifypurchase'], null);
	}
}*/

/**
 * Delete a purchase
 *
 * @param   {strong}  id
 * @return  {void}
 */
/*function DeletePurchase(id) {
	if (confirm("Are you sre you want to delete this sale?")) {
		WSDeleteURL(id, DeletedPurchase);
	}
}*/

/**
 * Callback after deleting a purchase
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function DeletedPurchase(xml) {
	if (xml.status == 200) {
		var queue = document.getElementById("queueid").value;
		queue = queue.split("/");
		queue = queue[3];
		window.location = "/admin/queue/edit/?q=" + queue;
	} else {
		SetError(ERRORS['deletepurchase'], null);
	}
}*/

/**
 * Delete a loan
 *
 * @param   {string}  id
 * @return  {void}
 */
/*function DeleteLoan(id) {
	if (confirm("Are you sre you want to delete this loan?")) {
		WSDeleteURL(id, DeletedLoan);
	}
}*/

/**
 * Callback after deleting a loan
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function DeletedLoan(xml) {
	if (xml.status == 200) {
		var queue = document.getElementById("queueid").value;
		queue = queue.split("/");
		queue = queue[3];
		window.location = "/admin/queue/edit/?q=" + queue;
	} else {
		SetError(ERRORS['deleteloan'], null);
	}
}*/

/**
 * Pending items count
 *
 * @var  {number}
 */
var pending = 0;

/**
 * List of pending resources
 *
 * @var  {array}
 */
var pending_resources = [];

/**
 * All state
 *
 * @var  {number}
 */
var all_state = 0;

/**
 * Set status for all queues
 *
 * @param   {array}   subresources
 * @param   {number}  state
 * @param   {string}  tab_id
 * @param   {array}   resources
 * @return  {void}
 */
function SetAllQueueStatus(subresources, state, tab_id, resources) {
	var i;
	pending = 0;
	pending_resources = [];
	all_state = state;
	if (typeof (resources) == 'undefined') {
		resources = [];
	}
	setStatusIndicator(tab_id + "_total_status", 'loading');
	for (i = 0; i < resources.length; i++) {
		setStatusIndicator(resources[i] + "_total_status", 'loading');
		pending_resources[i] = resources[i];
	}
	pending_resources[pending_resources.length] = tab_id;
	for (i = 0; i < subresources.length; i++) {
		pending++;
		SetQueueStatus(subresources[i], state);
	}
}

/**
 * Set status for queue and subresources
 *
 * @param   {string}  queue
 * @param   {array}   subresources
 * @param   {number}  state
 * @param   {string}  tab_id
 * @return  {void}
 */
function SetQueueAndSubresourceStatus(queue, subresource, state, tab_id) {
	SetQueueStatus(queue, state);

	// now change the subresource status image to the appropriate color
	//
	// first, collect all owner queue statuses for each subresource
	var table = document.getElementById('owner_queues_' + tab_id);
	var td;
	//var subresource;
	//var color;
	var total = {};
	var total_active = {};
	var subresources = new Array();
	var regex = new RegExp(queue, "i");

	for (var i = 0; i < table.rows.length; i++) {
		td = table.rows[i].cells[0];
		subresource = td.id;
		for (var x = 0; x < td.children.length; x++) {
			if (isImage(td.children[x])) {
				if (Object.prototype.hasOwnProperty.call(total, subresource)) {
					//if (total.hasOwnProperty(subresource)) {
					total[subresource] += 1;
					if ((td.children[x].id.match(regex) && state == 1) || td.children[x].src.match(/green/)) {
						total_active[subresource] += 1;
					}
				} else {
					total[subresource] = 1;
					subresources.push(subresource);
					if ((td.children[x].id.match(regex) && state == 1) || td.children[x].src.match(/green/)) {
						total_active[subresource] = 1;
					} else {
						total_active[subresource] = 0;
					}
				}
			}
		}
	}

	// now loop through each subresource, check its total vs active and set green, yellow, or red light appropriately
	// also keep track of individual subresource colors so subresource-ALL status can be set
	var total_subresources = subresources.length;
	var active_subresources = 0;
	var yellow_subresources = 0;

	for (var z = 0; z < subresources.length; z++) {
		if (total[subresources[z]] == total_active[subresources[z]]) {
			setStatusIndicator(subresources[z] + "_status", 'enabled');
			active_subresources += 1;
		} else if ((total[subresources[z]] > total_active[subresources[z]]) && (total_active[subresources[z]] > 0)) {
			setStatusIndicator(subresources[z] + "_status", 'error');
			yellow_subresources += 1;
		} else {
			setStatusIndicator(subresources[z] + "_status", 'disabled');
		}
	}

	if (total_subresources == active_subresources) {
		setStatusIndicator(tab_id + "_total_status", 'enabled');
	} else if (active_subresources == 0 && yellow_subresources == 0) {
		setStatusIndicator(tab_id + "_total_status", 'disabled');
	} else {
		setStatusIndicator(tab_id + "_total_status", 'error');
	}
}

/**
 * Is the element an image?
 *
 * @param   {object}  i
 * @return  {bool}
 */
function isImage(i) {
	return i instanceof HTMLImageElement;
}

/**
 * Set status for queue
 *
 * @param   {array}   subresources
 * @param   {number}  state
 * @return  {void}
 */
function SetQueueStatus(subresource, state) {
	if (state == 0 || state == 1) {
		setStatusIndicator(subresource + "_status", 'loading');
	}

	var post;
	if (subresource.match(/queue/)) {
		post = JSON.stringify({ 'started': state });
	} else {
		post = JSON.stringify({ 'queuestatus': state });
	}

	WSPostURL(subresource, post, SetQueueStatusB, subresource);
}

/**
 * Callback after setting status for all queue
 *
 * @param   {object}  xml
 * @param   {array}   subresources
 * @return  {void}
 */
function SetQueueStatusB(xml, subresource) {
	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);
		if (typeof (results['resource']) != 'undefined') {
			if (window.location.pathname == "/admin/queue/") {
				window.location = "/admin/queue/#" + results['resource'];
				location.reload(true);
			} else {
				pending--;
				setStatusIndicator(subresource + "_status", (results['queuestatus'] == "1" ? 'enabled' : 'disabled'));
			}
		} else {
			setStatusIndicator(subresource + "_status", (results['started'] == "1" ? 'enabled' : 'disabled'));
		}
	} else {
		pending--;
		setStatusIndicator(subresource + "_status", 'error');
	}

	if (pending == 0) {
		for (var i = 0; i < pending_resources.length; i++) {
			setStatusIndicator(pending_resources[i] + "_total_status", (all_state == "1" ? 'enabled' : 'disabled'));
		}
	}
}

/**
 * Set indicator status
 *
 * @param   {string}  id
 * @param   {string}  status
 * @return  {void}
 */
function setStatusIndicator(id, status) {
	var img = document.getElementById("IMG_" + id);

	if (img != null) {
		if (status == 'enabled') {
			img.src = "/include/images/check.png";
			img.alt = "Enabled";
		} else if (status == 'disabled') {
			img.src = "/include/images/x.png";
			img.alt = "Disabled";
		} else if (status == 'error') {
			img.src = "/include/images/error.png";
			img.alt = "Error";
		} else if (status == 'loading') {
			img.src = "/include/images/loading.gif";
			img.alt = "Processing..."
		}
	}
}

/**
 * Update Node Type select on New Queue Page
 *
 * @return  {void}
 */
/*function UpdateNodeType() {
	var select = document.getElementById("SELECT_scheduler");

	document.getElementById("SPAN_nodecores").innerHTML = "--";
	document.getElementById("SPAN_nodemem").innerHTML = "--";

	if (select.selectedIndex > 0) {
		var schedid = select.options[select.selectedIndex].value;
		var img = document.getElementById("IMG_scheduler");
		if (img) {
			img.style.visibility = "visible";
			img.src = "/include/images/loading.gif";
		}
		WSGetURL(schedid, UpdateNodeTypeScheduler);
	}
}*/

/**
 * Update node type scheduler
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function UpdateNodeTypeScheduler(xml) {
	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);
		document.getElementById("INPUT_maxwalltime").value = results['defaultmaxwalltime'] / 60 / 60;
		var policies = document.getElementById("SELECT_schedulerpolicy");
		for (var x = 0; x < policies.options.length; x++) {
			if (policies.options[x].value == results['defaultpolicy']['id']) {
				policies.options[x].selected = "true";
			} else {
				policies.options[x].selected = "";
			}
		}
		WSGetURL(results['resourceid'], UpdateNodeTypeSelect);
	} else {
		var img = document.getElementById("IMG_scheduler");
		if (img) {
			img.style.visibility = "visible";
			img.src = "/include/images/error.png";
		}
	}
}*/

/**
 * Update node type select
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function UpdateNodeTypeSelect(xml) {
	var img = document.getElementById("IMG_scheduler");

	if (xml.status == 200) {
		if (img) {
			img.style.visibility = "hidden";
		}

		var results = JSON.parse(xml.responseText);
		var select = document.getElementById("SELECT_nodetype");
		select.options.length = 0;

		var opt = document.createElement("option");
		opt.innerHTML = "(Select Node Type)";
		select.appendChild(opt);

		for (var x in results['subresources']) {
			opt = document.createElement("option");
			opt.value = results['subresources'][x]['id'];
			opt.innerHTML = results['subresources'][x]['name'];
			select.appendChild(opt);
		}
	} else {
		if (img) {
			img.style.visibility = "visible";
			img.src = "/include/images/error.png";
		}
	}
}*/

/**
 * Change node type
 *
 * @return  {void}
 */
/*function ChangeNodeType() {
	document.getElementById("SPAN_nodecores").innerHTML = "--";
	document.getElementById("SPAN_nodemem").innerHTML = "--";
	var select = document.getElementById("SELECT_nodetype");
	if (select.selectedIndex > 0) {
		var img = document.getElementById("IMG_nodetype");
		if (img) {
			img.style.visibility = "visible";
			img.src = "/include/images/loading.gif";
		}

		var subresourceid = select.options[select.selectedIndex].value;
		WSGetURL(subresourceid, ChangeNodeTypeText);
	}
}*/

/**
 * Change node type text
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function ChangeNodeTypeText(xml) {
	var img = document.getElementById("IMG_nodetype");

	if (xml.status == 200) {
		img.style.visibility = "hidden";

		var results = JSON.parse(xml.responseText);
		var nodecores = document.getElementById("SPAN_nodecores");
		var nodemem = document.getElementById("SPAN_nodemem");
		var cluster = document.getElementById("HIDDEN_cluster");

		if (results['nodecores'] != "") {
			nodecores.innerHTML = results['nodecores'];
		} else {
			nodecores.innerHTML = "--";
		}
		if (results['nodemem'] != "") {
			nodemem.innerHTML = results['nodemem'];
		} else {
			nodecores.innerHTML = "--";
		}
		cluster.value = results['cluster'];

	} else {
		img.style.visibility = "visible";
		img.src = "/include/images/error.png";
	}
}*/

/**
 * Create a queue
 *
 * @return  {void}
 */
/*function CreateQueue() {
	var btn = document.getElementById('create_queue_btn');

	if (btn) {
		btn.disabled = true;
		btn.setAttribute('data-loading', 'true');
	}

	var name = document.getElementById("INPUT_name").value;

	if (!name) {
		if (btn) {
			btn.disabled = false;
			btn.removeAttribute('data-loading');
		}
		SetError('Missing Required Input', 'The field "Queue Name" is required.');
		return;
	}

	var group = document.getElementById("SELECT_group").options[document.getElementById("SELECT_group").selectedIndex].value;
	var scheduler = document.getElementById("SELECT_scheduler").options[document.getElementById("SELECT_scheduler").selectedIndex].value;
	var schedulerpolicy = document.getElementById("SELECT_schedulerpolicy").options[document.getElementById("SELECT_schedulerpolicy").selectedIndex].value;
	var priority = document.getElementById("INPUT_priority").value;
	var defaultwalltime = document.getElementById("INPUT_defaultwalltime").value * 60 * 60;
	var maxjobsqueued = document.getElementById("INPUT_maxjobsqueued").value;
	var maxjobsqueueduser = document.getElementById("INPUT_maxjobsqueueduser").value;
	var maxjobsrun = document.getElementById("INPUT_maxjobsrun").value;
	var maxjobsrunuser = document.getElementById("INPUT_maxjobsrunuser").value;
	var nodecoresmin = document.getElementById("SPAN_nodecores").innerHTML;
	var nodecoresmax = document.getElementById("SPAN_nodecores").innerHTML;
	var nodememmin = document.getElementById("SPAN_nodemem").innerHTML;
	var nodememmax = document.getElementById("SPAN_nodemem").innerHTML;

	if (nodecoresmin == "--") {
		nodecoresmin = "";
		nodecoresmax = "";
	}
	if (nodememmax == "--") {
		nodememmax = "";
		nodememmin = "";
	}

	if (maxjobsqueued.match(/none/i) || maxjobsqueued.match(/unlimited/i) || maxjobsqueued == '') {
		maxjobsqueued = "0";
	}
	if (maxjobsqueueduser.match(/none/i) || maxjobsqueueduser.match(/unlimited/i) || maxjobsqueueduser == '') {
		maxjobsqueueduser = "0";
	}
	if (maxjobsrun.match(/none/i) || maxjobsrun.match(/unlimited/i) || maxjobsrun == '') {
		maxjobsrun = "0";
	}
	if (maxjobsrunuser.match(/none/i) || maxjobsrunuser.match(/unlimited/i) || maxjobsrunuser == '') {
		maxjobsrunuser = "0";
	}

	var aclusersenabled = 1;

	var type = $('#SELECT_queuetype').val();

	var cluster = document.getElementById("HIDDEN_cluster").value;
	var subresource = document.getElementById("SELECT_nodetype").options[document.getElementById("SELECT_nodetype").selectedIndex].value;
	if (type == 'standby' || type == 'workq' || type == 'debug') {
		cluster = $('input:checkbox[name=nodetype_checkbox]:checked').map(function () { return this.value; }).get().join(",");
		if (cluster.length == 1 && type == 'debug') {
			subresource = $('#SELECT_nodetype option[data-cluster=' + cluster + ']').val();
		} else {
			subresource = $('#HIDDEN_nonspecific').val();
		}
		nodememmax = "";
		nodememmin = "";
		nodecoresmin = "";
		nodecoresmax = "";
		aclusersenabled = 0;
	}

	var post = {
		"name": name,
		"cluster": cluster,
		"group": group,
		"scheduler": scheduler,
		"schedulerpolicy": schedulerpolicy,
		"subresource": subresource,
		"priority": priority,
		"defaultwalltime": defaultwalltime,
		"maxjobsqueued": maxjobsqueued,
		"maxjobsqueueduser": maxjobsqueueduser,
		"maxjobsrun": maxjobsrun,
		"maxjobsrunuser": maxjobsrunuser,
		"nodecoresmin": nodecoresmin,
		"nodecoresmax": nodecoresmax,
		"nodememmin": nodememmin,
		"nodememmax": nodememmax,
		"aclusersenabled": aclusersenabled,
		"queuetype": "/ws/queuetype/1"
	};

	if (type == 'debug') {
		var maxjobcores = $('#INPUT_maxjobcores').val();
		if (maxjobcores == 'none') {
			if (btn) {
				btn.disabled = false;
				btn.removeAttribute('data-loading');
			}

			alert("'Max Job Cores per Job' should probably be set for debug queues. Usually two nodes worth.");
			return;
		}
		post["reservation"] = 1;
		post["maxjobcores"] = maxjobcores;
	}

	post = JSON.stringify(post);

	WSPostURL(ROOT_URL + "queue", post, CreatedQueue);
}*/

/**
 * Callback after creating a queue
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function CreatedQueue(xml) {
	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);
		var maxwalltime = document.getElementById("INPUT_maxwalltime").value * 60 * 60;

		if ($('#SELECT_queuetype').val() == 'standby') {
			var post = JSON.stringify({
				"queue": results['id'],
				"corecount": "20000",
				"start": results['created']
			});
			WSPostURL(ROOT_URL + "queuesize", post);
		}

		post = JSON.stringify({
			"queue": results['id'],
			"walltime": maxwalltime
		});

		WSPostURL(ROOT_URL + "queuewalltime", post, CreatedWalltime, results['id']);
	} else {
		var btn = document.getElementById('create_queue_btn');
		if (btn) {
			btn.disabled = false;
			btn.removeAttribute('data-loading');
		}

		if (xml.status == 409) {
			SetError(ERRORS['queue'], ERRORS['queueconflict']);
		} else if (xml.status == 415) {
			SetError(ERRORS['queue'], ERRORS['queueformat']);
		} else {
			SetError(ERRORS['queue'], ERRORS['unknown']);
		}
	}
}*/

/**
 * Callback after creating walltime
 *
 * @param   {object}  xml
 * @param   {string}  queue
 * @return  {void}
 */
/*function CreatedWalltime(xml, queue) {
	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);
		queue = results['queue'].split("/");
		queue = queue[3];
		window.location = "/admin/queue/edit/?q=" + queue;
	} else {
		var btn = document.getElementById('create_queue_btn');
		if (btn) {
			btn.disabled = false;
			btn.removeAttribute('data-loading');
		}

		if (xml.status == 415) {
			SetError(ERRORS['queue'], ERRORS['queueformat']);
		} else {
			SetError(ERRORS['queue'], ERRORS['unknown']);
		}
	}
}*/

/**
 * Delete queue link
 *
 * @param   {string}  queue
 * @return  {void}
 */
/*function DeleteQueueLink(queue) {
	if (confirm("Are you sure you want to delete this queue?")) {
		WSDeleteURL(queue, DeletedQueueLink);
	}
}*/

/**
 * Callback after deleting queue link
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function DeletedQueueLink(xml) {
	if (xml.status == 200) {
		window.location = "/admin/queue/";
	} else {
		SetError(ERRORS['deletequeue'], null);
	}
}*/

/**
 * New queue core
 *
 * @param   {number}  nodecores
 * @return  {void}
 */
/*function NewQueueCore(nodecores) {
	if (nodecores == 0) {
		return;
	}

	var cores = document.getElementById("cores").value.replace(/(^\s+|\s+$)/g, "");
	var nodes = document.getElementById("nodes");

	if (cores.match(RegExp("^[\-]?[0-9]+$"))) {
		nodes.value = (cores / nodecores);
	} else {
		nodes.value = "";
	}
}*/

/**
 * New queue node
 *
 * @param   {number}  nodecores
 * @return  {void}
 */
/*function NewQueueNode(nodecores) {
	var cores = document.getElementById("cores");
	var nodes = document.getElementById("nodes").value.replace(/(^\s+|\s+$)/g, "");

	if (nodes.match(RegExp("^[\-]?[0-9]+$"))) {
		cores.value = (nodes * nodecores);
	} else {
		cores.value = "";
	}
}*/

/**
 * New queue group
 *
 * @param   {string}  subresource
 * @return  {void}
 */
/*function NewQueueGroup(subresource) {
	var group = document.getElementById("group");
	var queue = document.getElementById("queue");
	var opt = document.createElement("option");

	queue.options.length = 0;
	opt.innerHTML = "(Select Queue)";
	queue.appendChild(opt);

	if (group.selectedIndex > 0) {
		group = group.options[group.selectedIndex].value;

		queue.disabled = false;

		WSGetURL(group, NewQueuePopulateQueue, subresource);
	} else {
		queue.disabled = true;
	}
}*/

/**
 * New queue populate queue
 *
 * @param   {object}  xml
 * @param   {string}  subresource
 * @return  {void}
 */
/*function NewQueuePopulateQueue(xml, subresource) {
	if (xml.status == 200) {
		var group = JSON.parse(xml.responseText);
		var queue = document.getElementById("queue");
		var dest_queue = document.getElementById("queueid").value;

		queue.options.length = 0;
		var count = 0;
		var x, opt;
		for (x in group['queues']) {
			if (group['queues'][x]['subresource']['id'] == subresource || group['queues'][x]['name'].match(/^(rcac|workq|debug)/)) {
				if (group['queues'][x]['id'] != dest_queue) {
					count++;
				}
			}
		}
		if (count > 1) {
			opt = document.createElement("option");
			opt.innerHTML = "(Select Queue)";
			queue.appendChild(opt);
		}

		for (x in group['queues']) {
			if (group['queues'][x]['subresource']['id'] == subresource || group['queues'][x]['name'].match(/^(rcac|workq|debug)/)) {
				if (group['queues'][x]['id'] != dest_queue) {
					opt = document.createElement("option");
					opt.innerHTML = group['queues'][x]['name'] + " (" + group['queues'][x]['subresource']['name'] + ")";
					opt.value = group['queues'][x]['id'];

					queue.appendChild(opt);
				}
			}
		}
	} else {
		SetError(ERRORS['unknown'], null);
	}
}*/

/**
 * Update walltime
 *
 * @param   {string}  queue
 * @return  {void}
 */
/*function UpdateWalltime(queue) {
	var img = document.getElementById("IMG_maxwalltime");
	var span = document.getElementById("SPAN_maxwalltime");
	var input = document.getElementById("INPUT_maxwalltime");
	var queuewalltime = document.getElementById("HIDDEN_queuewalltime");

	if (img.src.match(/edit/) || img.src.match(/error/)) {
		// turn to edit mode
		img.src = "/include/images/save.png";
		img.title = "Click to save changes.";
		span.style.display = "none";
		input.style.display = "block";
		input.value = span.innerHTML;

		var cancel = document.getElementById("CANCELIMG_maxwalltime");
		if (cancel) {
			cancel.style.visibility = "visible";
		}
	} else {
		// turn to save mode
		img.src = "/include/images/loading.gif";
		img.title = "Click to edit field.";
		span.style.display = "block";
		input.style.display = "none";

		// don't send a post if it isn't changing.
		if (span.innerHTML == input.value || input.value == "0") {
			img.src = "/include/images/edit.png";
			return;
		}

		if (span.innerHTML != "0") {
			WSDeleteURL(queuewalltime.value, DeletedWalltime, queue);
		} else {
			var post = JSON.stringify({
				'queue': queue,
				'walltime': input.value * 60 * 60
			});
			WSPostURL(ROOT_URL + "queuewalltime", post, UpdatedWalltime);
		}
	}
}*/

/**
 * Callback after deleting walltime
 *
 * @param   {object}  xml
 * @param   {string}  queue
 * @return  {void}
 */
/*function DeletedWalltime(xml, queue) {
	if (xml.status == 200) {
		var input = document.getElementById("INPUT_maxwalltime");
		var post = JSON.stringify({
			'queue': queue,
			'walltime': input.value * 60 * 60
		});
		WSPostURL(ROOT_URL + "queuewalltime", post, UpdatedWalltime);
	} else {
		var img = document.getElementById("IMG_maxwalltime");
		if (img) {
			img.src = "/include/images/error.png";
			img.title = "An error has occurred. Please try again.";
		}
	}
}*/

/**
 * Callback after updating walltime
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function UpdatedWalltime(xml) {
	var img = document.getElementById("IMG_maxwalltime");
	var input = document.getElementById("INPUT_maxwalltime");
	var span = document.getElementById("SPAN_maxwalltime");
	if (xml.status == 200) {
		img.title = "Click to edit field.";
		img.src = "/include/images/edit.png";
		span.innerHTML = input.value;

		var results = JSON.parse(xml.responseText);
		document.getElementById("HIDDEN_queuewalltime").value = results['id'];
	} else {
		img.src = "/include/images/error.png";
		img.title = "An error has occurred. Please try again.";
		span.innerHTML = "0";
		input.value = "0";
	}
}*/

/**
 * Reservation edit
 *
 * @param   {string}  r_id
 * @return  {void}
 */
/*function ReservationEdit(r_id) {
	var edit_div = document.getElementById("stoptime_" + r_id);
	var endtime = edit_div.innerHTML;

	edit_div.innerHTML = '<input id="stoptime_edit_' + r_id + '" type="text" value="' + endtime + '" onblur="ReservationUpdate(\'' + r_id + "','" + endtime + '\')">';

	var edit_field = document.getElementById("stoptime_edit_" + r_id);
	edit_field.focus();
}*/

/**
 * Reservation update
 *
 * @param   {string}  r_id
 * @param   {string}  old_time
 * @return  {void}
 */
/*function ReservationUpdate(r_id, old_time) {
	var tab_id = GetTab();
	var new_time = document.getElementById("stoptime_edit_" + r_id).value;
	var edit_div = document.getElementById("stoptime_" + r_id);
	var error = document.getElementById("reservation_error_" + tab_id);

	if (old_time != new_time) {
		var pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;
		if (!pattern.test(new_time)) {
			error.style.display = "block";
			error.innerHTML = "Error updating end time. Must be in the format 'yyyy-mm-dd hh:mm:ss'";
		} else {
			error.style.display = "none";
			var post = JSON.stringify({
				'reservation': r_id,
				'datetimestop': new_time
			});
			WSPostURL(r_id, post, ReservationUpdated, r_id);
		}
	} else {
		edit_div.innerHTML = old_time;
		error.style.display = "none";
	}
}*/

/**
 * Callback after updating reservation
 *
 * @param   {object}  xml
 * @param   {string}  r_id
 * @return  {void}
 */
/*function ReservationUpdated(xml, r_id) {
	var tab_id = GetTab();
	var edit_div = document.getElementById("stoptime_" + r_id);
	var time = document.getElementById("stoptime_edit_" + r_id).value;
	var error = document.getElementById("reservation_error_" + tab_id);
	if (xml.status == 415) {
		error.innerHTML = "Invalid scheduler id.";
		error.style.display = "block";
	} else if (xml.status == 416) {
		error.innerHTML = "Invalid date format.";
		error.style.display = "block";
	} else if (xml.status == 500) {
		error.innerHTML = "Error updating reservation.";
		error.style.display = "block";
	} else if (xml.status == 200) {
		edit_div.innerHTML = time;
		error.style.display = "none";
	} else {
		error.innerHTML = "Error updating reservation.";
		error.style.display = "block";
	}
}*/

/**
 * Create a reservation
 *
 * @param   {string}  tab_id
 * @return  {void}
 */
/*function ReservationCreate(tab_id) {
	var name = document.getElementById("ReservationName_" + tab_id).value;
	var scheduler = document.getElementById("ReservationScheduler_" + tab_id).innerHTML;
	var nodes = document.getElementById("ReservationNodes_" + tab_id).value;

	var startdate = document.getElementsByClassName("ReservationStartDate_" + tab_id)[0].value;
	var starthour = document.getElementById("ReservationStartHour_" + tab_id).value;
	var startminute = document.getElementById("ReservationStartMinute_" + tab_id).value;
	var datetimestart = startdate + " " + starthour + ":" + startminute + ":00";

	var stopdate = document.getElementsByClassName("ReservationStopDate_" + tab_id)[0].value;
	var stophour = document.getElementById("ReservationStopHour_" + tab_id).value;
	var stopminute = document.getElementById("ReservationStopMinute_" + tab_id).value;
	var datetimestop = stopdate + " " + stophour + ":" + stopminute + ":00";

	var error = document.getElementById(tab_id + "_action");

	if (name != "" && scheduler != "" && startdate != "" && starthour != "" && startminute != "" && stopdate != "" && stophour != "" && stopminute != "") {
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		if (!date_pattern.test(startdate) || !date_pattern.test(stopdate)) {
			error.innerHTML = "Invalid date format. Use the calendar to select the desired date.";
		} else {
			var post = {
				'name': name,
				'scheduler': scheduler,
				'nodes': nodes,
				'datetimestart': datetimestart,
				'datetimestop': datetimestop
			};
			post = JSON.stringify(post);
			WSPostURL(ROOT_URL + "reservation", post, ReservationCreated);
		}
	} else {
		error.innerHTML = "Ensure all form fields are filled in before submitting."
	}
}*/

/**
 * Callback after creating a reservation
 *
 * @param   {object}  xml
 * @return  {void}
 */
/*function ReservationCreated(xml) {
	if (xml.status == 412) {
		SetError("Error creating reservation. Ensure all form fields are filled in.", null);
	} else if (xml.status == 413) {
		SetError("Date time values must be after the current time.", null);
	} else if (xml.status == 415) {
		SetError("Invalid scheduler id.", null);
	} else if (xml.status == 409) {
		SetError("Reservation already exists.", null);
	} else if (xml.status == 500) {
		SetError(ERRORS['createreservation'], null);
	} else if (xml.status == 200) {
		window.location.reload();
	} else {
		SetError("Error creating reservation.", null);
	}
}*/

/**
 * Delete a reservation
 *
 * @param   {string}  r_id
 * @return  {void}
 */
/*function ReservationDelete(r_id) {
	//var reservation = document.getElementById("RESERVATION_" + r_id);

	setStatusIndicator(r_id, 'loading');

	WSDeleteURL(ROOT_URL + "reservation/" + /\d+$/.exec(r_id), ReservationDeleted, r_id);
}*/

/**
 * Callback after deleting a reservation
 *
 * @param   {object}  xml
 * @param   {string}  r_id
 * @return  {void}
 */
/*function ReservationDeleted(xml, r_id) {
	if (xml.status == 200) {
		// success. remove the reservation row from the table
		var reservation = document.getElementById("RESERVATION_" + r_id);
		reservation.parentNode.removeChild(reservation);
	} else if (xml.status == 404) {
		SetError(ERRORS['deletereservation'], null);
	}
}*/

/**
 * Change date
 *
 * @param   {string}  field
 * @return  {void}
 */
/*function ChangeDate(field) {
	var d = document.getElementById("date" + field);
	var time = document.getElementById("time" + field + "short");

	var d1 = new Date();
	if (time.value == "" && d.value != "") {
		var month = d1.getMonth() + 1;
		if (month < 10) {
			month = "0" + month;
		}
		var day = d1.getDate();
		if (day < 10) {
			day = "0" + day;
		}
		if (field == "start" && d1.getFullYear() + "-" + month + "-" + day == d.value) {
			// Add 10 minutes and round up to nearest 5 minute
			var d2 = new Date(d1);
			d2.setMinutes(d1.getMinutes() + 10 + (5 - d1.getMinutes() % 5));

			var hour = d2.getHours();
			var minute = d2.getMinutes();
			if (minute < 10) {
				minute = "0" + minute.toString();
			}

			if (hour == 0 && minute < 15) {
				time.value = "11:59 PM";
			} else {
				if (hour > 12) {
					hour -= 12;
					time.value = hour + ":" + minute + " PM";
				} else if (hour == 12) {
					time.value = hour + ":" + minute + " PM";
				} else {
					time.value = hour + ":" + minute + " AM";
				}
			}
		} else if (field == "stop") {
			time.value = "11:59 PM";
		} else {
			time.value = "12:00 AM";
		}
	}
}*/

/**
 * Update queue type
 *
 * @return  {void}
 */
/*function UpdateQueueType() {
	var type = $('#SELECT_queuetype').val();

	var name = "";
	var priority = "1000";
	var walltime = "0.5";
	var maxwalltime = "720";
	var maxjobsqueued = "12000";
	var maxjobsqueueduser = "5000";
	var maxjobsrunning = "none";
	var maxjobsrunninguser = "none";

	if (type == 'owner') {
		$("#SELECT_group option[id='/ws/group/-1']").remove();
		$('#SELECT_group').prop('disabled', false);
		$('#DIV_nodetype_select').show();
		$('#DIV_nodetype_checkbox').hide();
		$('#TR_maxjobcores').hide();
	}

	if (type == 'standby') {
		name = "standby";
		priority = "-1000";
		walltime = "0.5";
		maxwalltime = "4";
		maxjobsqueued = "none";
		maxjobsqueueduser = "1000";
		maxjobsrunning = "none";
		maxjobsrunninguser = "250";

		$('#SELECT_group').prepend('<option value="/ws/group/-1" selected="selected">(null)</option>');
		$('#SELECT_group').prop('disabled', true);
		$('#DIV_nodetype_select').hide();
		$('#DIV_nodetype_checkbox').show();
		$('#TR_maxjobcores').hide();
	}

	if (type == 'workq') {
		name = "workq";
		priority = "1000";
		walltime = "0.5";
		maxwalltime = "336";
		maxjobsqueued = "none";
		maxjobsqueueduser = "500";
		maxjobsrunning = "none";
		maxjobsrunninguser = "100";

		$('#SELECT_group').prepend('<option value="/ws/group/-1" selected="selected">(null)</option>');
		$('#SELECT_group').prop('disabled', true);
		$('#DIV_nodetype_select').hide();
		$('#DIV_nodetype_checkbox').show();
		$('#TR_maxjobcores').hide();
	}

	if (type == 'debug') {
		name = "debug";
		priority = "2500";
		walltime = "0.25";
		maxwalltime = "0.5";
		maxjobsqueued = "none";
		maxjobsqueueduser = "4";
		maxjobsrunning = "none";
		maxjobsrunninguser = "1";

		$('#SELECT_group').prepend('<option value="/ws/group/-1" selected="selected">(null)</option>');
		$('#SELECT_group').prop('disabled', true);
		$('#DIV_nodetype_select').hide();
		$('#DIV_nodetype_checkbox').show();
		$('#TR_maxjobcores').show();
	}

	$('#INPUT_name').val(name);
	$('#INPUT_priority').val(priority);
	$('#INPUT_defaultwalltime').val(walltime);
	$('#INPUT_maxwalltime').val(maxwalltime);
	$('#INPUT_maxjobsqueued').val(maxjobsqueued);
	$('#INPUT_maxjobsqueueduser').val(maxjobsqueueduser);
	$('#INPUT_maxjobsrun').val(maxjobsrunning);
	$('#INPUT_maxjobsrunuser').val(maxjobsrunninguser);
}*/

/**
 * Display retired
 *
 * @return  {void}
 */
/*function DisplayRetired() {
	var elem = document.getElementById('drop');

	if (elem.style.display === "none") {
		elem.style.display = "block";
	}
	else {
		elem.style.display = "none";
	}
}*/

/**
 * Initiate event hooks
 */
$(document).ready(function () {
	$('.set-queue-status').on('click', function (e) {
		e.preventDefault();
		SetQueueStatus(
			$(this).data('resource'),
			parseInt($(this).data('status'))
		);
	});

	$('.set-queue-all-status').on('click', function (e) {
		e.preventDefault();
		var queues = $(this).data('queues').split(',');
		SetAllQueueStatus(
			queues,
			parseInt($(this).data('status')),
			$(this).data('resource')
		);
	});

	$('.set-queues-all-status').on('click', function (e) {
		e.preventDefault();
		var queues = $(this).data('queues').split(',');
		var resources = $(this).data('resources').split(',');
		SetAllQueueStatus(
			queues,
			parseInt($(this).data('status')),
			'all',
			resources
		);
	});

	$('.set-queue-subresource-status').on('click', function (e) {
		e.preventDefault();
		SetQueueAndSubresourceStatus(
			$(this).data('queue'),
			$(this).data('subresource'),
			parseInt($(this).data('status')),
			$(this).data('resource')
		);
	});

	$('.delete-queue').on('click', function (e) {
		e.preventDefault();

		if (confirm($(this).attr('data-confirm'))) {
			WSDeleteURL($(this).attr('data-api'), function (xml) {
				if (xml.status < 400) {
					window.location.reload();
				} else {
					alert("An error occurred.");
				}
			});
		}
	});

	// --- Purchases & Loans

	$('.dialog-pl-btn').on('click', function (e) {
		e.preventDefault();

		$($(this).attr('href')).dialog({
			modal: true,
			width: '550px',
			open: function () {
				//var d = $(this);

				var groups = $(".form-group-queues");
				if (groups.length) {
					$(".form-group-queues")
						.select2({})
						.on('select2:select', function (e) {
							e.preventDefault();

							var group = $(this);

							var queue = $('#' + group.data('update'));
							//var dest_queue = group.attr('data-queueid');

							$.ajax({
								url: group.data('queue-api'),
								type: 'get',
								data: {
									'group': group.val(),
									'subresource': group.attr('data-subresource')
								},
								dataType: 'json',
								async: false,
								success: function (data) {
									if (data.data.length > 0) {
										queue.prop('disabled', false);
										queue.empty();//options.length = 0;

										opt = document.createElement("option");
										opt.value = 0;
										opt.innerHTML = "(Select Queue)";
										queue.append(opt);

										var x, opt;
										for (x in data.data) {
											//if (data.data[x]['name'].match(/^(rcac|workq|debug)/)) {
											//if (data.data[x]['id'] != dest_queue) {
											opt = document.createElement("option");
											opt.innerHTML = data.data[x]['name'] + " (" + data.data[x]['subresource']['name'] + ")";
											opt.value = data.data[x]['id'];

											queue.append(opt);
											//}
											//}
										}
									}
								},
								error: function (xhr) {
									var msg = 'Failed to retrieve queues.';
									if (xhr.responseJSON && xhr.responseJSON.message) {
										msg = xhr.responseJSON.message;
									}
									alert(msg);

									console.log(xhr.responseText);
								}
							});
							return false;
						});
				}
			}
		});
	});

	$('.nodes').on('change', function () {
		var nodecores = $(this).data('nodes');

		var cores = document.getElementById(this.getAttribute('data-cores-field'));
		var nodes = this.value.replace(/(^\s+|\s+$)/g, "");

		if (nodes.match(RegExp("^[-]?[0-9]+$"))) {
			cores.value = (nodes * nodecores);
		} else {
			cores.value = "";
		}
	});

	$('.cores').on('change', function () {
		var nodecores = $(this).data('cores');

		if (nodecores == 0) {
			return;
		}

		var cores = this.value.replace(/(^\s+|\s+$)/g, "");
		var nodes = document.getElementById(this.getAttribute('data-nodes-field'));

		if (cores.match(RegExp("^[-]?[0-9]+$"))) {
			nodes.value = (cores / nodecores);
		} else {
			nodes.value = "";
		}
	});

	$('.queue-dialog-submit').on('click', function (e) {
		e.preventDefault();

		var btn = this,
			frm = $(this).closest('form'),
			invalid = false;

		if (frm.length) {
			var elms = frm[0].querySelectorAll('input[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			elms = frm[0].querySelectorAll('select[required]');
			elms.forEach(function (el) {
				if (!el.value || el.value <= 0) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			elms = frm[0].querySelectorAll('textarea[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});

			if (invalid) {
				return;
			}
		}

		$.ajax({
			url: frm.attr('data-api'),
			type: btn.getAttribute('data-action') == 'update' ? 'put' : 'post',
			data: frm.serialize(),
			dataType: 'json',
			async: false,
			success: function () {
				window.location.reload(true);
			},
			error: function (xhr) { //xhr, reason, thrownError
				var msg = 'Failed to create item.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					msg = xhr.responseJSON.message;
				}
				alert(msg);
			}
		});
	});

	$('.queue-pl-delete').on('click', function (e) {
		e.preventDefault();

		var btn = this;

		if (confirm(btn.getAttribute('data-confirm'))) {
			$.ajax({
				url: btn.getAttribute('data-api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
					window.location.reload(true);
				},
				error: function (xhr) {
					var msg = 'Failed to delete item.';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						msg = xhr.responseJSON.message;
					}

					alert(msg);
				}
			});
		}
	});

	$('.queue-pl-edit').on('click', function (e) {
		e.preventDefault();

		$($(this).attr('href')).dialog({
			modal: true,
			width: '550px'
		});
	});

	// Create queue
	$('#queue-name').on('keyup', function () {
		var val = $(this).val();

		val = val.toLowerCase()
			.replace(/\s+/g, '_')
			.replace(/[^a-z0-9_-]+/g, '');

		$(this).val(val);
	});

	$('#queue-queueclass').on('change', function () {
		var val = $(this).val();

		if (val == 'debug') {
			$('#queue-reservation').prop('checked', true);
		}
	});

	$('#queue-subresourceid').on('change', function () {
		var opt = this.options[this.selectedIndex];

		var nodecores = document.getElementById("SPAN_nodecores");
		var nodemem = document.getElementById("SPAN_nodemem");
		var cluster = document.getElementById("queue-cluster");

		document.getElementById('queue-clusterlabel').innerHTML = opt.getAttribute('data-clusterlabel');

		nodecores.innerHTML = opt.getAttribute('data-nodecores');
		nodemem.innerHTML = opt.getAttribute('data-nodemem');

		var nodememmin = document.getElementById('queue-nodememmin');
		nodememmin.value = opt.getAttribute('data-nodemem');

		var nodememmax = document.getElementById('queue-nodememmax');
		nodememmax.value = opt.getAttribute('data-nodemem');

		var nodecoresmin = document.getElementById('queue-nodecoresmin');
		nodecoresmin.value = opt.getAttribute('data-nodecores');

		var nodecoresmax = document.getElementById('queue-nodecoresmax');
		nodecoresmax.value = opt.getAttribute('data-nodecores');

		cluster.value = opt.getAttribute('data-cluster');
	});

	// Clone the select to preserve all the optgroups
	var select = document.getElementById("queue-subresourceid");
	var sclone = $(select).clone().attr('id', $(select).attr('id') + '-clone');

	$('#queue-schedulerid').on('change', function () {
		if (this.selectedIndex == 0) {
			return;
		}

		// Clear some values
		document.getElementById("SPAN_nodecores").innerHTML = '-';
		document.getElementById("SPAN_nodemem").innerHTML = '-';

		var sched = this,
			opt = sched.options[sched.selectedIndex];

		// Start processing
		sched.parentNode.className = sched.parentNode.className + ' loading';

		// Set max wall time
		document.getElementById("queue-maxwalltime").value = parseInt(opt.getAttribute('data-defaultmaxwalltime')) / 60 / 60;

		// Set policy
		var policies = document.getElementById("queue-schedulerpolicyid");

		for (var x = 0; x < policies.options.length; x++) {
			if (policies.options[x].value == opt.getAttribute('data-schedulerpolicyid')) {//results['defaultpolicy']['id']) {
				policies.options[x].selected = "true";
			} else {
				policies.options[x].selected = "";
			}
		}

		// Get the optgroup for the selected resource
		$(select).find("optgroup").remove();
		$(select).append(sclone.find("optgroup[data-resourceid='" + opt.getAttribute('data-resourceid') + "']").clone());

		// Finished processing
		sched.parentNode.className = sched.parentNode.className.replace(' loading', '');
	});
});
