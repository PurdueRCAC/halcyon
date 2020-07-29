/* global $ */ // jquery.js

/**
 * List of resources
 *
 * @var  {array}
 */
var resourceArray = [];

/**
 * Toggle resource
 *
 * @param   {string}  name
 * @return  {void}
 */
function toggleResource(name) {
	// Remove resource if found.
	for (var i = 0; i < resourceArray.length; i++) {
		if (resourceArray[i] == name) {
			resourceArray.splice(i, 1);
			return;
		}
	}

	// Otherwise; add resource.
	resourceArray.push(name);
}

/**
 * Initiate event hooks
 */
$(document).ready(function() {
	$('.rssCustomize').on('click', function(e){
		e.preventDefault();

		var txt = $(this).text();
		$(this).text($(this).data('txt'));
		$(this).data('txt', txt);

		$('.rssCheckbox').toggle();
		$('.rssCheckboxInfo').toggle();
		$('.customRSS').toggle();
	});

	$('.rssCheckbox').on('change', function() {
		toggleResource($(this).val());

		var url = "";
		for (var i = 0; i < resourceArray.length; i++) {
			url += resourceArray[i];
			if (i != resourceArray.length - 1) {
				url += ",";
			}
		}

		if (resourceArray.length == 0) {
			url = "#";
		}

		$('#customRSS').attr('href', url);
	});
});
