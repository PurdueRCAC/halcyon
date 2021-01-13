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
function StatusSave(url, id, option) {
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

	WSPutURL(url, post, function (xml, option) {
		//var img = document.getElementById(comment + "_commenttextsaveiconimg");

		if (xml.status < 400) {
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
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function () {

	var statuses = document.querySelectorAll('.resource-status');

	if (statuses.length) {
		for (var i = 0; i < statuses.length; i++) {
			statuses[i].addEventListener('change', function (event) {
				event.preventDefault();

				StatusSave(
					this.getAttribute('data-api'),
					this.getAttribute('data-id'),
					this.options[this.selectedIndex]
				);
			});
		}
	}

});
