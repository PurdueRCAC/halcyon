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
	var autocompleteUsers = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)) + '&api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name + ' (' + el.username + ')',
						name: el.name,
						id: el.id,
					};
				}));
			});
		};
	};

	var newsuser = $(".form-users");
	if (newsuser.length) {
		newsuser.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			'autocomplete': {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1,
				limit: 1
			}/*,
			'onAddTag': function(input, value) {
				NEWSSearch();
			},
			'onRemoveTag': function(input, value) {
				NEWSSearch();
			}*/
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