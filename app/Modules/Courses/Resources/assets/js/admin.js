/**
 * @package    halcyon
 * @copyright  Copyright 2019 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

/**
 * Initiate event hooks
 */
document.addEventListener('DOMContentLoaded', function() {
	var users = $(".form-users");
	if (users.length) {
		users.each(function(i, user){
			user = $(user);
			var cl = user.clone()
				.attr('type', 'hidden')
				.val(user.val().replace(/([^:]+):/, ''));
			user
				.attr('name', user.attr('id') + i)
				.attr('id', user.attr('id') + i)
				.val(user.val().replace(/(:\d+)$/, ''))
				.after(cl);
			user.autocomplete({
				minLength: 2,
				source: function( request, response ) {
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

					window.location(window.location.href + '?userid=' + ui.item.id);
					/*$.getJSON(user.attr('data-user-uri') + ui.item.id + '?api_token=' + $('meta[name="api-token"]').attr('content'), function (data) {
						$(user.data('list')).empty();
						data.
					});*/
					return false;
				}
			});
		});
	}

	$('.type-dependant').hide();
	//$('.type-'+$('[name="type"]').val()).show();
	//$('.menu-page').fadeIn();
	$('[name="type"]')
		.on('change', function () {
			$('.type-dependant').hide();
			$('.type-' + $(this).val()).show();

			/*if ($(this).val() == 'separator') {
				if (!$('#fields_title').val()) {
					$('#fields_title').val('[ separator ]');
				}
			}*/
		})
		.each(function (i, el) {
			$('.type-' + $(el).val()).show();
		});

	$('#fields_page_id').on('change', function (e) {
		if ($('#fields_title').val() == '') {
			$('#fields_title').val($(this).children("option:selected").text().replace(/\|\— /g, ''));
		}
	});
});