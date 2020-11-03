/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
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
	var users = $(".form-users");
	if (users.length) {
		users.each(function (i, user) {
			user = $(user);
			var cl = user.clone()
				.attr('type', 'hidden')
				.val(user.val().replace(/([^:]+):/, ''));
			user
				.attr('name', 'userid' + i)
				.attr('id', user.attr('id') + i)
				.val(user.val().replace(/(:\d+)$/, ''))
				.after(cl);
			user.autocomplete({
				minLength: 2,
				source: function (request, response) {
					return $.getJSON(user.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
						response($.map(data.data, function (el) {
							return {
								label: el.name + ' (' + el.username + ')',
								name: el.name,
								id: el.id,
							};
						}));
					});
				},
				select: function (event, ui) {
					event.preventDefault();
					// Set selection
					user.val(ui.item.label); // display the selected text
					cl.val(ui.item.id); // save selected id to input
					return false;
				}
			});
		});
	}

	var groups = $(".form-groups");
	if (groups.length) {
		groups.each(function (i, group) {
			group = $(group);
			var cl = group.clone()
				.attr('type', 'hidden')
				.val(group.val().replace(/([^:]+):/, ''));
			group
				.attr('name', 'groupid' + i)
				.attr('id', group.attr('id') + i)
				.val(group.val().replace(/(:\d+)$/, ''))
				.after(cl);
			group.autocomplete({
				minLength: 2,
				source: function (request, response) {
					return $.getJSON(group.attr('data-uri').replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
						response($.map(data.data, function (el) {
							return {
								label: el.name,
								name: el.name,
								id: el.id,
							};
						}));
					});
				},
				select: function (event, ui) {
					event.preventDefault();
					// Set selection
					group.val(ui.item.label); // display the selected text
					cl.val(ui.item.id); // save selected id to input
					return false;
				}
			});
		});
	}

	$('.basic-single').select2({
		//placeholder: $(this).data('placeholder')
	});
	$('.basic-single').on('select2:select', function (e) {
		var opt = $($(this).find('option:selected')[0]);
		$(this).closest('tr').find('.unitprice').text(opt.data('unitprice'));
		$(this).closest('tr').find('.unit').text(opt.data('unit'));
	});
});