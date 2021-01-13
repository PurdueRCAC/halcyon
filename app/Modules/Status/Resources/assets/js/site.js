/* global $ */ // jquery.js
/* global ROOT_URL */ // common.js
/* global WSGetURL */ // common.js
/* global WSPostURL */ // common.js
/* global WSDeleteURL */ // common.js
/* global ERRORS */ // common.js
/* global SetError */ // common.js
/* global HighlightMatches */ // text.js

var keywords_pending = 0;

/**
 * Save edited comment text
 *
 * @param   {string}  comment
 * @return  {void}
 */
function StatusSave(id, option) {
	var img = document.getElementById(id + "_icon");

	//var icon = img.querySelectorAll('.fa');
	img.className = "fa fa-spinner fa-spin";
	img.parentNode.title = "Saving changes...";
	img.parentNode.className = 'tip';

	var post = {
		'id': id,
		'status': option.value
	};
	post = JSON.stringify(post);
	console.log(post);
	WSPostURL(id, post, function (xml, option) {
		//var img = document.getElementById(comment + "_commenttextsaveiconimg");

		if (xml.status == 200) {
			var results = JSON.parse(xml.responseText);

			/*if (option.value == 1) {
				img.className = option.getAttribute('data-class'); //"fa fa-check";
				img.parentNode.title = option.text;
				img.parentNode.className = option.getAttribute('data-status');
			} else if (option.value == 2) {
				img.className = option.getAttribute('data-class'); //"fa fa-exclamation-triangle";
				img.parentNode.title = option.text;
				img.parentNode.className = option.getAttribute('data-status');
			} else if (option.value == 3) {
				img.className = option.getAttribute('data-class'); //"fa fa-exclamation-circle";
				img.parentNode.title = option.text;
				img.parentNode.className = option.getAttribute('data-status');
			} else if (option.value == 4) {*/
			img.className = option.getAttribute('data-class'); //"fa fa-wrench";
			img.parentNode.title = option.text;
			img.parentNode.className = option.getAttribute('data-status');
			//}
		} else if (xml.status == 403) {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, grace editing window has passed.";
		} else {
			img.className = "fa fa-exclamation-circle";
			img.parentNode.title = "Unable to save changes, reload the page and try again.";
		}
	}, option);
}

/**
 * Callback after saving edited report text
 *
 * @param   {object}  xml
 * @param   {string}  report
 * @return  {void}
 */
/*function IssuesSavedReportText(xml, report) {
	var img = document.getElementById(report + "_textsaveiconimg");

	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);

		var icon = document.getElementById(report + "_textsaveicon");
			icon.onclick = function () {
				IssuesSaveReportText(report);
			};
			icon.style.display = "none";
		var text = document.getElementById(report + "_text");
			text.style.display = "block";
			text.innerHTML = results['formattedreport'];
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
}*/

/**
 * Callback after saving edited comment text
 *
 * @param   {object}  xml
 * @param   {string}  report
 * @return  {void}
 */
/*function IssuesSavedCommentText(xml, comment) {
	var img = document.getElementById(comment + "_commenttextsaveiconimg");

	if (xml.status == 200) {
		var results = JSON.parse(xml.responseText);
		var panel = document.getElementById("comment" + comment);
		if (results.resolution == 1) {
			panel.className = "panel panel-default issue-resolution";
		} else {
			panel.className = "panel panel-default";
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
		var img = document.getElementById(comment + "_commenttextsaveiconimg");
			img.className = "fa fa-save";

		var d = document.getElementById(comment + "_commenttextareacontrols");
			d.className = "row comment-controls hide";
	} else if (xml.status == 403) {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes, grace editing window has passed.";
	} else {
		img.className = "fa fa-exclamation-circle";
		img.parentNode.title = "Unable to save changes, reload the page and try again.";
	}
}*/

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {

	var statuses = document.querySelectorAll('.resource-status');

	if (statuses.length) {
		for (var i = 0; i < statuses.length; i++) {
			statuses[i].addEventListener('change', function (event) {
				event.preventDefault();

				StatusSave(this.getAttribute('data-id'), this.options[this.selectedIndex]);
			});
		}
	}

});
