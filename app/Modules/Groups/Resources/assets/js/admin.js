/**
 * @package    halcyon
 * @copyright  Copyright 2019 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

Halcyon.submitbutton = function(task) {
	var frm = document.getElementById('item-form');

	if (frm) {
		$(document).trigger('editorSave');

		if (task == 'cancel' || document.formvalidator.isValid(frm)) {
			Halcyon.submitform(task, frm);
		} else {
			alert(frm.getAttribute('data-invalid-msg'));
		}
	}
}

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	$('.searchable-select').select2();
});