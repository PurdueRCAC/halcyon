
/* global $ */ // jquery.js
/* global ForMe */ // orders.js
/* global CancelMou */ // orders.js
/* global UpdateOrderTotal */ // orders.js
/* global OpenUserSearch */ // orders.js

// Force update of totals in case browswer is caching values
$(document).ready(function () {
	// ---- Cart page

	$('#continue').on('click', function (e) {
		e.preventDefault();
		ForMe();
	});
	$('#cancel').on('click', function (e) {
		e.preventDefault();
		CancelMou();
	});

	UpdateOrderTotal();

	var autocompleteName = function (url) {
		return function (request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name,
						name: el.name,
						id: el.id,
						username: el.username
						//priorusernames: el.priorusernames
					};
				}));
			});
		};
	};
	$("#search_user").autocomplete({
		source: autocompleteName($("#search_user").data('api')),
		dataName: 'users',
		height: 150,
		delay: 100,
		minLength: 2,
		select: function (event, ui) {
			event.preventDefault();
			var thing = ui['item'].label;

			if (typeof (ui['item'].username) != 'undefined') {
				thing = thing + " (" + ui['item'].username + ")";
			} else if (typeof (ui['item'].priorusername) != 'undefined') {
				thing = thing + " (" + ui['item'].priorusername + ")";
			}

			$("#search_user").val(thing);
		},
		create: function () {
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				var thing = item.label;

				if (typeof (item.username) != 'undefined') {
					thing = thing + " (" + item.username + ")";
				} else if (typeof (item.priorusername) != 'undefined') {
					thing = thing + " (" + item.priorusername + ")";
				}
				return $("<li>")
					.append($("<div>").text(thing))
					.appendTo(ul);
			};
		}
	});
	$("#search_user").on("autocompleteselect", SearchEventHandler);

	$('#formeyes,#formno').on('click', function (e) {
		OpenUserSearch();
	});

	$('.quantity-input').on('change', function (e) {
		UpdateOrderTotal(this);
	});
	$('.total-input').on('change', function (e) {
		UpdateOrderTotal(this, true);
	});

	$('.form-block-radio').on('click', function (e) {
		$(this).find('input[type="radio"]').not(':checked').prop("checked", true);

		var c = $(this).find('input[type="checkbox"]');
		if (c.length) {
			c.each(function () {
				var el = $(this);
				if (!el.prop("checked")) {
					el.prop("checked", true);
				} else {
					el.prop("checked", false);
				}
			});
			$(this).toggleClass('checked');
		} else {
			$('.form-block').removeClass('checked');
			$(this).addClass('checked');
		}
	});
	$('.form-block-check input[type="checkbox"]').on('change', function (e) {
		if ($(this).is(':checked')) {
			$(this).closest('.form-block').addClass('checked');
		} else {
			$(this).closest('.form-block').removeClass('checked');
		}
	});

	$('.btn-cart-remove').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);

		$.ajax({
			url: btn.data('api'),
			type: 'DELETE',
			dataType: 'json',
			async: false,
			success: function (response) {
				$(btn.data('item')).remove();

				$('#ordertotal').text(response.total);

				// Disable the 'continue' button if the cart is empty
				if ($('.cart-item').length <= 0) {
					$('#continue').prop('disabled', true);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.log('Failed to update member type.');
			}
		});
	});

	// ---- Products page

	// Add event listener for filters
	var filters = document.getElementsByClassName('filter-submit');
	for (i = 0; i < filters.length; i++) {
		filters[i].addEventListener('change', function (e) {
			this.form.submit();
		});
	}

	// Enable/disable button when quantity changes
	$('.quantity-input').on('change', function (e) {
		var inp = $(this);
		if (inp.val() > 0) {
			$(inp.closest('tr')).find('.btn-secondary').prop('disabled', false);
		} else {
			$(inp.closest('tr')).find('.btn-secondary').prop('disabled', true);
		}
	});

	// Confirm deletion
	$('.btn-delete').on('click', function (e) {
		e.preventDefault();
		if (confirm($(this).data('conrim'))) {
			return true;
		}
		return false;
	});

	// Update something in the cart
	$('.btn-cart-update').on('click', function (e) {
		e.preventDefault();

		var btn = $(this),
			qty = $(btn.closest('tr')).find('.quantity-input')[0].value;

		if (!qty) {
			return;
		}

		btn.addClass('processing');

		$.ajax({
			url: btn.data('api'),
			type: 'PUT',
			data: {
				quantity: qty
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				updateCart(response);
				btn.removeClass('processing');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.responseJSON.message);
				btn.removeClass('processing');
			}
		});
	});

	// Add to the cart
	$('.btn-cart-add').on('click', function (e) {
		e.preventDefault();

		var btn = $(this),
			qty = $(btn.closest('tr')).find('.quantity-input')[0].value;

		if (!qty) {
			return;
		}

		btn.addClass('processing');

		$.ajax({
			url: btn.data('api'),
			type: 'POST',
			data: {
				productid: btn.data('product'),
				quantity: qty
			},
			dataType: 'json',
			async: false,
			success: function (response) {
				updateCart(response);

				for (var i = 0; i < response.data.length; i++) {
					if (response.data[i].id == btn.data('product')) {
						btn.attr('data-api', response.data[i].api);
						btn.removeClass('btn-cart-add')
							.addClass('btn-cart-update')
							.text(btn.data('text-update'));
					}
				}

				btn.removeClass('processing');

				$('#' + btn.data('product') + "_product").addClass('selected');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.responseJSON.message);
				btn.removeClass('processing');
			}
		});
	});

	// Update the cart display
	function updateCart(response) {
		var cart = $('#cart');
		cart.find('.cart-item').remove();

		var t = $(cart.find('.template')[0]);

		for (var i = 0; i < response.data.length; i++) {
			var tmpl = t.clone();
			tmpl.removeClass('hide')
				.removeClass('template')
				.addClass('cart-item');

			var content = tmpl.html()
				.replace(/\{name\}/g, response.data[i].name)
				.replace(/\{price\}/g, response.data[i].price)
				.replace(/\{total\}/g, response.data[i].subtotal)
				.replace(/\{qty\}/g, response.data[i].qty);

			tmpl.html(content);

			cart.prepend(tmpl);
		}

		$('#order-total').text(response.total);
	}
});
