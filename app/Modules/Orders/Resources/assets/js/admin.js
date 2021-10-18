/* global $ */ // jquery.js

/**
 * Format number as currency
 *
 * @param   {number}  num
 * @return  {string}
 */
function FormatNumber(num) {
	var neg = "";
	if (num < 0) {
		num = -num;
		neg = "-";
	}

	if (num > 99) {
		num = num.toString();
		var dollars = num.substr(0, num.length - 2);
		var p = 1;
		var end = dollars.length;

		if (dollars.lastIndexOf(".") != -1) {
			end = dollars.lastIndexOf(".");
		}
		for (var t = dollars; t > 999; t = t / 1000) {
			dollars = dollars.substr(0, end - p * 3) + "," + dollars.substr(end - p * 3, dollars.length);
			p++;
		}

		var cents = num.substr(num.length - 2, 2);
		num = dollars + "." + cents;
	} else if (num > 9 && num < 100) {
		num = num.toString();
		num = "0." + num;
	} else if (num > 0) {
		num = num.toString();
		num = "0.0" + num;
	} else {
		num = "0.00";
	}

	return neg + num;
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

	$('body').on('change', 'input[name=quantity]', function(){
		var row = $(this).closest('tr');

		row.find('.order-total').text(FormatNumber($(this).data('unitprice') * $(this).val()));
	});

	$('.basic-single').on('select2:select', function () {
		var opt = $($(this).find('option:selected')[0]);

		var row = $(this).closest('tr');

		row.find('.quantity-control')
			.val(1)
			.data('unitprice', opt.data('unitprice'));

		row.find('.unitprice').text(FormatNumber(opt.data('unitprice')));
		row.find('.unit').text(opt.data('unit'));
		row.find('.order-total').text(FormatNumber(opt.data('unitprice')));
	});
});