@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
$(document).ready(function() {
	$('.edit-property').on('click', function(e){
		e.preventDefault();
		EditProperty($(this).data('prop'), $(this).data('value'));
	});
	$('.edit-property-input').on('keyup', function(event){
		if (event.keyCode == 13) {
			EditProperty($(this).data('prop'), $(this).data('value'));
		}
	});
	$('.cancel-edit-property').on('click', function(e){
		e.preventDefault();
		CancelEditProperty($(this).data('prop'), $(this).data('value'));
	});

	$('#order_group_save').on('click', function(e){
		e.preventDefault();
		SaveOrderGroup();
	});
	$('#order_user_save').on('click', function(e){
		e.preventDefault();
		SaveOrderUser();
	});

	$('.copy-doc').on('blur', function(e){
		CopyDoc(this);
	});
	$('.copy-docdate').on('change', function(e){
		CopyDocDate(this);
	});

	$('.order')
		.on('keyup', '.balance-update', function(e){
			UpdateBalance(true);
		})
		.on('blur', '.balance-update', function(e){
			UpdateBalance();
		});

	$('.balance-divide').on('click', function(e){
		e.preventDefault();
		DivideBalance();
	});

	$('.order-fulfill').on('click', function(e){
		e.preventDefault();
		FulfillItem($(this).data('api'), this);
	});

	$('#orderheaderpopup').dialog({
		modal: true,
		width: '550px',
		autoOpen: false/*,
		buttons : {
			OK: {
				text: 'OK',
				click: function() {
					$(this).dialog('close'); 
				}
			}
		}*/
	});
	$('.order-status').on('click', function(e){
		e.preventDefault();
		$( '#orderheaderpopup' ).dialog('open');
	});

	$('#save_quantities').on('click', function(e){
		e.preventDefault();
		EditQuantities();
	});
	$('#cancel_quantities').on('click', function(e){
		e.preventDefault();
		CancelEditAccounts();
	});

	$('#printorder').on('click', function(e){
		e.preventDefault();
		PrintOrder();
	});
	$('#remindorder').on('click', function(e){
		e.preventDefault();
		RemindOrder($('#order').data('api'), this);
	});
	$('#cancelorder').on('click', function(e){
		e.preventDefault();
		CancelOrder(this);
	});
	$('#restoreorder').on('click', function(e){
		e.preventDefault();
		RestoreOrder(this);
	});

	// Account creation/editing
	$('.account-add').on('click', function(e){
		e.preventDefault();
		AddNewAccountRow();
	});
	$('.account-save').on('click', function(e){
		e.preventDefault();
		SaveAccounts();
	});
	$('.account-edit').on('click', function(e){
		e.preventDefault();
		EditAccounts();
	});
	$('.account-edit-cancel').on('click', function(e){
		e.preventDefault();
		CancelEditAccounts();
	});
	// Account status
	$('.account-approve').on('click', function(e){
		e.preventDefault();
		ApproveAccount($(this).data('api'), this);
	});
	$('.account-deny').on('click', function(e){
		e.preventDefault();
		if (confirm($(this).attr('data-confirm'))) {
			DenyAccount($(this).data('api'), this);
		}
	});
	$('.account-remind').on('click', function(e){
		e.preventDefault();
		RemindAccount($(this).data('api'), this);
	});
	$('.account-collect').on('click', function(e){
		e.preventDefault();
		CollectAccount($(this).data('api'), this);
	});
	$('.account-reset').on('click', function(e){
		e.preventDefault();
		if (confirm($(this).attr('data-confirm'))) {
			ResetAccount($(this).data('api'), this);
		}
	});

	// Adding products to an order
	$('.item-add').on('click', function(e) {
		e.preventDefault();
		AddNewProductRow();
	});

	$('.contentInner')
		.on('click', '.account-remove', function(e){
			e.preventDefault();
			if (confirm($(this).data('confirm'))) {
				EditRemoveAccount($(this), this);
			}
		})
		.on('click', '.item-remove', function(e){
			e.preventDefault();
			if (confirm($(this).data('confirm'))) {
				EditRemoveProduct($(this), this);
			}
		})
		.on('change', '.total-update', function(e){
			if ($(this).data('override') == '1') {
				UpdateTotal(true);
			} else {
				UpdateTotal();
			}
		})
		.on('change', '.item-product', function(e) {
			var opt = $($(this).find('option:selected'));
			var price = opt.data('price');

			var container = $($(this).closest('tr'));

			container.find('.item-price').html(FormatNumber(price));
			container.find('.item-unit').html(opt.data('unit'));
			container.find('.item-quantity').val(1).trigger('change');

			event = jQuery.Event('change');
			event.target = container.find('.item-quantity')[0];

			$('.contentInner').trigger(event);

			var parent = $(container.find('.item-period')[0]);
			if (opt.data('recurringtimeperiodid')) {
				parent.removeClass('stash')
					.find('span')
					.text(opt.data('recurringtimeperiod'));
			} else {
				parent.addClass('stash');
			}
			
			//console.log('price:' + FormatNumber(price));
			//$('#new_itemtotal').val(FormatNumber(price));
			//console.log($('#new_itemtotal').val());
		})
		.on('change', '.item-quantity', function(e) {
			var container = $($(this).closest('tr'));
			var val = $(this).val() * container.find('.item-price').html().replace(/[,\.]/g, "") * container.find('.item-periods').val();
			container.find('.item-total').val(FormatNumber(val));
		})
		.on('change', '.item-periods', function(e) {
			//var val = $(this).val() * $('#new_price').html().replace(/[,\.]/g, "") * $('#new_quantity').val();
			//$('#new_itemtotal').val(FormatNumber(val));
			var container = $($(this).closest('tr'));
			var val = $(this).val() * container.find('.item-price').html().replace(/[,\.]/g, "") * container.find('.item-quantity').val();
			container.find('.item-total').val(FormatNumber(val));
		});

	var dates = document.getElementsByName("docdate");

	var autocompleteOrderPurchaseAccount = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.purchasewbse ? el.purchasewbse : el.purchaseio,
						purchaseorder: el.purchaseorder,
						purchasefund: el.purchasefund,
						order: el.order,
						id: el.purchasewbse ? el.purchasewbse : el.purchaseio
					};
				}));
			});
		};
	};

	var docids = document.getElementsByName("docid");
	for (var x=0;x<docids.length;x++) {
		$(docids[x]).autocomplete({
			source: autocompleteOrderPurchaseAccount($(docids[x]).attr('data-api-list') + '?docid=%s'),
			dataName: 'accounts',
			height: 150,
			delay: 100,
			minLength: 0,
			prefix: 'docid:',
			noResultsText: '',
			autoText: false
		});
	}

	var funds = document.getElementsByName("account");
	for (var x=0;x<funds.length;x++) {
		$(funds[x]).autocomplete({
			source: autocompleteOrderPurchaseAccount($(funds[x]).attr('data-api-list') + '?fund=%s'),
			dataName: 'accounts',
			height: 150,
			delay: 100,
			minLength: 0,
			prefix: 'fund:',
			filter: /^[a-zA-Z]?[0-9\.]*$/i, noResultsText: '',
			autoText: false
		});
	}

	var autocompleteGroup = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
				response($.map(data.data, function (el) {
					return {
						label: el.name,
						id: el.id
					};
				}));
			});
		};
	};

	$("#search_group").autocomplete({
		source: autocompleteGroup($("#search_group").data('uri')),
		dataName: 'data',
		height: 150,
		delay: 100,
		minLength: 2,
		filter: /^[a-z0-9\-_ \.,\'\(\)]+$/i,
		select: function (event, ui) {
			event.preventDefault();

			$("#search_group")
				.attr('data-groupid', ui['item'].id)
				.val(ui['item'].label);
		}
	});

	/*var autocompleteName = function(url) {
		return function(request, response) {
			console.log(url.replace('%s', encodeURIComponent(request.term)));
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

			if (typeof(ui['item'].username) != 'undefined') {
				thing = thing + " (" + ui['item'].username + ")";
			} else if (typeof(ui['item'].priorusername) != 'undefined') {
				thing = thing + " (" + ui['item'].priorusername + ")";
			}
			$("#search_user").val(thing);
		},
		create: function () {
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				var thing = item.label;

				if (typeof(item.username) != 'undefined') {
					thing = thing + " (" + item.username + ")";
				} else if (typeof(item.priorusername) != 'undefined') {
					thing = thing + " (" + item.priorusername + ")";
				}
				return $("<li>")
					.append($("<div>").text(thing))
					.appendTo(ul);
			};
		}
	});
	$("#search_user").on("autocompleteselect", SearchEventHandler);*/
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
					return $.getJSON(user.attr('data-uri').replace('%s', encodeURIComponent(request.term)), function (data) {
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

	/*var group = $("#search_group");
	if (group.length) {
		var cl = group.clone()
			.attr('type', 'hidden')
			.val(group.val().replace(/([^:]+):/, ''));
		group
			.attr('name', 'groupid' + 'copy')
			.attr('id', group.attr('id') + 'copy')
			.val(group.val().replace(/(:\d+)$/, ''))
			.after(cl);
		group.autocomplete({
			minLength: 2,
			source: function( request, response ) {
				return $.getJSON(group.attr('data-uri').replace('%s', encodeURIComponent(request.term)), function (data) {
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
	}*/

	AccountApproverSearch();
});
</script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('admin.orders.index')
	)
	->append(
		($order->id ? trans('global.edit') . ' #' . $order->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	{!! Toolbar::link('back', trans('orders::orders.back'), route('admin.orders.index'), false) !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('orders::orders.module name') }}: {{ $order->id ? '#' . $order->id : 'Create' }}
@stop

@php
$myorder = (auth()->user()->id == $order->submitteruserid || auth()->user()->id == $order->userid);
$canEdit = (auth()->user()->can('edit orders') || (auth()->user()->can('edit.own orders') && $myorder));
@endphp

@section('content')
<!-- <form action="{{ route('admin.orders.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate"> -->

<form action="{{ route('admin.orders.store') }}" method="post" name="adminForm" id="item-form" class="editform order">
@if ($order->id)
	<div class="tabs">
		<ul>
			<li><a href="#order-info">Order</a></li>
			<li><a href="#order-notes">Notes</a></li>
			<li><a href="#order-history">History</a></li>
		</ul>
		<div id="order-info">
@endif
<div class="row">
		<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">

			<div class="orderstatusblocks">
			@if ($order->status == 'pending_payment')
				<div class="alert alert-success">
					<p>Your order has been submitted. Thank you for your order!</p>
					<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
				</div>
				<ol class="steps">
					<li class="text-success complete">Submit order</li>
					<li class="step current">Enter payment information</li>
					<li class="notmystep">Awaiting business office assignment by staff</li>
					<li class="notmystep">Awaiting approval by your business office</li>
					<li class="notmystep">Awaiting fulfillment by staff</li>
					<li class="notmystep">Order completion</li>
				</ol>
				@if ($order->status == 'pending_payment' && auth()->user()->can('manage orders'))
					<p class="text-center">
						<button class="btn btn-secondary" id="remindorder" data-txt="Reminded">Remind Customer</button>
						<span id="remindorderspan"></span>
					</p>
				@endif
				<div id="orderheaderpopup" class="hide" title="Order Submitted">
					<p>Your order has been submitted. Thank you for your order! You should receive an email confirmation shortly.</p>
					<p>Please review the order and enter Purdue account numbers to be used for payment below. You may also add any special instructions for the order in the notes box at the bottom of the page at any time.</p>
					<p>Use the form directly below this box to enter your payment information. You or the person this order is for (if placing on behalf) may return to this page at a later time to enter payment information.</p>
				</div>
			@elseif ($order->status == 'pending_boassignment')
				<div class="alert alert-info">
					<p>Payment information has been entered for this order.</p>
					<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
				</div>
				<ol class="steps">
					<li class="text-success complete">Submit order</li>
					<li class="text-success complete">Enter payment information</li>
					<li class="current step">Awaiting business office assignment by staff</li>
					<li class="notmystep">Awaiting approval by your business office</li>
					<li class="notmystep">Awaiting fulfillment by staff</li>
					<li class="notmystep">Order Completion</li> 
				</ol>
				<div id="orderheaderpopup" class="hide" title="Payment Information Entered">
					<p>Payment information has been entered. No further action is required.</p>
					<p>Staff will assign this to your business office for approval. You will be updated on the progress of this order via email. You may return to this page at any time to view the current status.</p>
				</div>
			@elseif ($order->status == 'pending_approval')
				<div class="alert alert-info">
					<p>Order has been assigned to your business office and is awaiting their approval.</p>
					<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
				</div>
				<ol class="steps">
					<li class="text-success complete">Submit order</li>
					<li class="text-success complete">Enter payment information</li>
					<li class="text-success complete">Awaiting business office assignment by staff</li>
					<li class="current step">Awaiting approval by your business office</li>
					<li class="notmystep">Awaiting fulfillment by staff</li>
					<li class="notmystep">Order completion</li>
				</ol>
				<div id="orderheaderpopup" class="hide" title="Awaiting Business Office Approval">
					<p>Order has been assigned to your business office and is awaiting their approval.</p>
					<p>Please contact your business office directly if you have any questions about approval on this order. The assigned approver for each account is listed below.</p>
				</div>
			@elseif ($order->status == 'pending_fulfillment')
				<div class="alert alert-info">
					<p>This order has been approved by your business office(s). Staff have begun the process of fulfilling this order.</p>
					<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
				</div>
				<ol class="steps">
					<li class="text-success complete">Submit order</li>
					<li class="text-success complete">Enter payment information</li>
					<li class="text-success complete">Awaiting business office assignment by staff</li>
					<li class="text-success complete">Awaiting approval by your business office</li>
					<li class="current step">Awaiting fulfillment by staff</li>
					<li class="notmystep">Order completion</li>
				</ol>
				<div id="orderheaderpopup" class="hide" title="Awaiting Fulfillment">
					<p>This order has been approved by your business office(s). Staff have begun the process of fulfilling this order.</p>
					<p> You may be contacted directly by staff if further information is needed to fulfill the order or with information on how to access your new resources.</p>
				</div>
			@elseif ($order->status == 'pending_collection')
				<div class="alert alert-success">
					<p>This order has been fulfilled. Please contact <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a> if you have questions on how to use new resources.</p>
					<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
				</div>
				<ol class="steps">
					<li class="text-success complete">Submit order</li>
					<li class="text-success complete">Enter payment information</li>
					<li class="text-success complete">Awaiting business office assignment by staff</li>
					<li class="text-success complete">Awaiting approval by your business office</li>
					<li class="text-success complete">Awaiting fulfillment by staff</li>
					<li class="current step">Order completion</li>
				</ol>
				<div id="orderheaderpopup" class="hide" title="Order Complete">
					<p>This order has been fulfilled. Please contact <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a> if you have  questions on how to use new resources.</p>
					<p>The financial transactions may take several more weeks to process and complete between the business offices by this order is complete and resources are ready for you to use.</p>
				</div>
			@elseif ($order->status == 'complete')
				<ol class="steps">
					<li class="text-success complete">Submit order</li>
					<li class="text-success complete">Enter payment information</li>
					<li class="text-success complete">Awaiting business office assignment by staff</li>
					<li class="text-success complete">Awaiting approval by your business office</li>
					<li class="text-success complete">Awaiting fulfillment by staff</li>
					<li class="text-success complete">Order completion</li>
				</ol>
				<div id="orderheaderpopup" class="hide" title="Order Complete">
					<p>This order is complete.</p>
				</div>
			@elseif ($order->status == 'canceled')
				<p class="alert alert-danger">This order was canceled.</p>
				<ol class="steps">
					<li class="text-success complete">Submit order</li>
					<li class="step"><del>Enter payment information</del></li>
					<li class="step"><del>Awaiting business office assignment by staff</del></li>
					<li class="step"><del>Awaiting approval by your business office</del></li>
					<li class="step"><del>Awaiting fulfillment by staff</del></li>
					<li class="step"><del>Order completion</del></li>
					<li class="text-success complete">Order Canceled</li>
				</ol>
				<div id="orderheaderpopup" class="hide" title="Order Canceled">
					<p>This order was canceled.</p>
				</div>
			@endif
			</div><!-- / .orderstatusblock -->
		</div>
		<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
				<input type="hidden" name="id" id="order" data-api="{{ route('api.orders.update', ['id' => $order->id]) }}" value="{{ $order->id }}" />

				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col col-md-6">
								<h3 class="pane-title card-title">{{ '#' . $order->id }}</h3>
							</div>
							<div class="col col-md-6 text-right">
								<?php
								if ($order->status == 'pending_payment'
								|| $order->status == 'pending_boassignment'
								|| (($order->status == 'pending_approval' || $order->status == 'pending_fulfillment') && auth()->user()->can('manage orders'))
								|| ($order->status == 'pending_approval' && !$myorder)): ?>
									<button class="btn btn-sm btn-danger" id="cancelorder" data-confirm="Are you sure you wish to cancel this order?">Cancel Order</button>
								<?php else: ?>
									<button class="btn btn-sm tip" id="printorder" title="Print Order">
										<span class="fa fa-print" aria-hidden="true"></span>
										<span class="sr-only">Print Order</span>
									</button>
									@if ($order->status == 'canceled' && auth()->user()->can('manage orders'))
										<button class="btn btn-sm btn-secondary" id="restoreorder">
											Restore Order
										</button>
									@endif
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col col-md-6">
								<div class="form-group">
									<label for="search_group">{{ trans('orders::orders.submitted') }}:</label>
									<p class="form-text">{{ $order->datetimecreated->format('F j, Y g:ia') }}</p>
									<input type="hidden" class="form-control form-control-plaintext" disabled="disabled" value="{{ $order->datetimecreated }}" />
								</div>
							</div>
							<div class="col col-md-6">
								<div class="form-group">
									<label for="search_group">{{ trans('orders::orders.submitter') }}:</label>
									<p class="form-text">{{ $order->submitter->name }}</p>
									<input type="hidden" class="form-control form-control-plaintext" disabled="disabled" value="{{ $order->submitteruserid }}" />
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col col-md-6">
								<div class="form-group{{ $errors->has('userid') ? ' has-error' : '' }}">
									<label for="field-userid">{{ trans('orders::orders.user') }}:</label>
									@if (auth()->user()->can('manage orders') && $order->status != 'canceled')
										<p class="form-text">
											<span id="edit_user" data-userid="{{ $order->userid }}">
												@if ($order->user)
													{{ $order->user->name }} ({{ $order->user->username }})
												@else
													<span class="none">{{ trans('global.none') }}</span>
												@endif
											</span>

											<span class="hide">
											<input type="text" name="search_user" id="search_user" class="form-control form-users" value="{{ ($order->user ? $order->user->name . ':' . $order->userid : '') }}" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" placeholder="{{ trans('global.none') }}" />
											</span>

											<a href="#edit_user" id="order_user_save" class="order-edit" title="{{ trans('global.button.edit') }}" data-txt-save="Save Change">
												<span id="user_save" class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.edit') }}</span>
											</a>
										</p>
										@if ($order->user)
											@if ($order->user->title)
												<p class="form-text">{{ $order->user->title }}</p>
											@endif

											@if ($order->user->department)
												<p class="form-text">{{ $order->user->department }}</p>
											@endif

											@if ($order->user->school)
												<p class="form-text">{{ $order->user->school }}</p>
											@endif
										@endif
									@else
										<p class="form-text">
											@if ($order->user)
												{{ $order->user->name }} ({{ $order->user->username }})
											@else
												<span class="none">{{ trans('global.none') }}</span>
											@endif
										</p>
									@endif
								</div>
							</div>
							<div class="col col-md-6">
								<div class="form-group{{ $errors->has('groupid') ? ' has-error' : '' }}">
									<label for="search_group">{{ trans('orders::orders.group') }}:</label>
									@if (auth()->user()->can('manage orders') && $order->status != 'canceled')
										<p class="form-text">
											<span id="edit_group" data-groupid="{{ $order->groupid }}">
												@if ($order->groupid)
													{{ $order->group->name }}
												@else
													<span class="none">{{ trans('global.none') }}</span>
												@endif
											</span>

											<input type="text" name="search_group" id="search_group" class="form-control form-groups hide" data-groupid="{{ $order->groupid }}" value="{{ $order->group ? $order->group->name : '' }}" data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" placeholder="{{ trans('global.none') }}" />

											<a href="#edit_group" id="order_group_save" class="order-edit" title="{{ trans('global.button.edit') }}" data-txt-save="Save Change">
												<span id="group_save" class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only">{{ trans('global.button.edit') }}</span>
											</a>
										</p>
									@else
										<p class="form-text">
											@if ($order->groupid)
												{{ $order->group->name }}
											@else
												<span class="none">{{ trans('global.none') }}</span>
											@endif
										</p>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div><!-- / .card -->

				<?php $history = $order->history()->orderBy('created_at', 'desc')->get(); ?>

				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col col-md-6">
								<h3 class="card-title">{{ trans('orders::orders.items') }}</h3>
							</div>
							<div class="col col-md-6 text-right">
								<?php
								if (
								($order->status == 'pending_payment'
								|| $order->status == 'pending_boassignment'
								|| (
									($order->status == 'pending_approval' || $order->status == 'pending_fulfillment') && auth()->user()->can('manage orders'))
									|| ($order->status == 'pending_approval' && !$myorder)) && (auth()->user()->can('manage orders') || $myorder)): ?>
									<a href="#help4" class="btn text-info help help-dialog" data-tip="Help">
										<span class="icon-help-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
									</a>

									<button id="save_quantities" class="btn btn-sm btn-secondary" data-state="inactive" data-inactive="Edit Items" data-active="Save Changes">Edit Items</button>
									<button id="cancel_quantities" class="btn btn-sm btn-link item-edit-show hide">Cancel Changes</button>

									<div id="error1" title="Cancel Order?" class="dialog dialog-confirm">
										<p>Removing the last item will <strong>cancel</strong> your order. Do you wish to continue?</p>
									</div>

									<div id="help4" title="Edit Quantities" class="dialog dialog-help">
										<p>
											Quantities may be edited while payment information is being approved. You will need to redistribute or remove the total cost difference from your accounts. Accounts that have already been approved may only be deleted.
										</p>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="card-body">
						<table class="table">
							<caption class="sr-only">{{ trans('orders::orders.items') }}</caption>
							<thead>
								<tr>
									<th scope="col">{{ trans('orders::orders.status') }}</th>
									<th scope="col">{{ trans('orders::orders.item') }}</th>
									<th scope="col" class="text-right">{{ trans('orders::orders.quantity') }}</th>
									<th scope="col" class="text-right">{{ trans('orders::orders.price') }}</th>
									<th scope="col" class="text-right">{{ trans('orders::orders.total') }}</th>
									@if (auth()->user()->can('manage orders'))
										<th class="item-edit-show hide"></th>
									@endif
								</tr>
							</thead>
							<tbody>
								<?php foreach ($order->items as $item) {
									$history = $history->merge($item->history()->orderBy('created_at', 'desc')->get());
									?>
									<tr>
										@if (!$item->isFulfilled())
											@if ($order->status != 'canceled' && $order->status == 'pending_fulfillment')
												<td>
													<div class="badge order-status {{ str_replace(' ', '-', $order->status) }}" id="status_{{ $item->id }}">{{ trans('orders::orders.pending_fulfillment') }}</div>

													<div class="mt-3 text-center item-edit-hide">
														<button class="btn btn-sm btn-secondary order-fulfill" id="button_{{ $item->id }}" data-api="{{ route('api.orders.items.update', ['id' => $item->id]) }}" data-txt="Fulfilled" data-id="{{ $item->id }}">{{ trans('orders::orders.fulfill') }}</button>
													</div>
												</td>
											@else
												<td>
													<div class="badge order-status {{ str_replace(' ', '-', $order->status) }}">
														@if ($order->status == 'pending_fulfillment' || $order->status == 'canceled')
															{{ trans('orders::orders.' . $order->status) }}
														@else
															{{ trans('orders::orders.pending_approval') }}
														@endif
													</div>
												</td>
											@endif
										@else
											<td>
												<div class="badge order-status fulfilled">{{ trans('orders::orders.fulfilled') }}</div>
												<time datetime="{{ $item->fulfilled }}">{{ Carbon\Carbon::parse($item->fulfilled)->format('M j, Y') }}</time>
											</td>
										@endif
										<td>
											<strong>{{ $item->product->name }}</strong>
											<p class="form-text text-muted">
												@if ($item->origorderitemid)
													@if ($item->start() && $item->end())
														@if ($item->id == $item->origorderitemid)
															{{ trans('orders::orders.new service', ['start' => $item->start()->format('M j, Y'), 'end' => $item->end()->format('M j, Y')]) }}
														@else
															{{ trans('orders::orders.service renewal', ['start' => $item->start()->format('M j, Y'), 'end' => $item->end()->format('M j, Y')]) }}
														@endif
													@else
														{{ 'Service for ' . $item->timeperiodcount . ' ' }}
														@if ($item->timeperiodcount > 1)
															{{ $item->product->timeperiod->plural }}
															{{ trans('orders::orders.service for', ['count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->plural]) }}
														@else
															{{ trans('orders::orders.service for', ['count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->singular]) }}
														@endif
													@endif
												@endif
											</p>
										</td>
										<td class="text-right">
											<input type="hidden" name="item" value="{{ $item->id }}" data-api="{{ route('api.orders.items.update', ['id' => $item->id]) }}" />
											<input type="hidden" name="original_quantity" value="{{ $item->quantity }}" />

											<span class="item-edit-hide quantity_span">{{ $item->quantity }}</span>

											<input type="hidden" name="original_periods" value="{{ $item->timeperiodcount }}" />
											<input type="number" name="quantity" value="{{ $item->quantity }}" size="4" class="item-edit-show hide form-control total-update" />
											@if ($item->origorderitemid)
												for<br/>
												<span class="item-edit-hide periods_span">{{ $item->timeperiodcount }}</span>
												<input type="number" max="999" name="periods" value="{{ $item->timeperiodcount }}" class="item-edit-show hide form-control total-update" />
												@if ($item->timeperiodcount > 1)
													<!-- {{ trans('orders::orders.quantity for', ['quantity' => $item->quantity, 'count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->plural]) }} -->
													{{ $item->product->timeperiod->plural }}
												@else
													<!-- {{ trans('orders::orders.quantity for', ['quantity' => $item->quantity, 'count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->singular]) }} -->
													{{ $item->product->timeperiod->singular }}
												@endif
											@else
												<span class="stash">
													<span class="periods_span">
														{{ $item->timeperiodcount }}
													</span>
													<input type="number" max="999" name="periods" value="{{ $item->timeperiodcount }}" class="stash form-control total-update" />
												</span>
											@endif
										</td>
										<td class="text-right">
											{{ config('orders.currency', '$') }} <span name="price">{{ $item->formattedPrice }}</span><br/>
											<span class="text-nowrap">per {{ $item->product->unit }}</span>
										</td>
										<td class="text-right text-nowrap">
											<span class="item-edit-hide">{{ config('orders.currency', '$') }} <span name="itemtotal">{{ $item->formattedTotal }}</span></span>
											@if ($item->origorderitemid)
												<a href="{{ route('site.orders.recurring.read', ['id' => $order->id]) }}" class="text-success tip" title="This is a recurring item.">
													<span class="fa fa-undo" aria-hidden="true"></span><span class="sr-only">This is a recurring item.</span>
												</a>
											@endif
											@if (auth()->user()->can('manage orders'))
												<input type="text" name="linetotal" value="{{ $item->formattedTotal }}" class="item-edit-show hide form-control total-update" data-override="1" />
												<input type="hidden" name="original_total" value="{{ $item->price }}" />
											@endif
											<input type="hidden" name="itemid" id="{{ $item->id }}" value="{{ $item->isFulfilled() ? 'FULFILLED' : 'PENDING_FULFILLMENT' }}" />
										</td>
										@if ($order->isActive() && auth()->user()->can('manage orders'))
											<td class="item-edit-show hide">
												<a href="{{ route('site.orders.read', ['id' => $order->id, 'remove' => $item->id]) }}"
													title="Remove product"
													class="btn btn-dangerd text-danger item-remove tip"
													data-api="{{ route('api.orders.items.delete', ['id' => $item->id]) }}"
													data-confirm="{{ trans('orders::orders.confirm.item removal') }}">
													<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Remove product</span>
												</a>
											</td>
										@endif
									</tr>
								<?php } ?>
								@if ($order->isActive() && auth()->user()->can('manage orders'))
									<tr id="item_new_row" class="hide">
										<td></td>
										<td>
											<select name="new_product" class="form-control item-product searchable-select">
												<option value="0">{{ trans('orders::orders.select product') }}</option>
												@foreach ($products as $product)
													<option value="{{ $product->id }}"
														data-price="{{ $product->unitprice }}"
														data-unit="{{ $product->unit }}"
														data-recurringtimeperiodid="{{ $product->recurringtimeperiodid }}"
														data-recurringtimeperiod="{{ $product->recurringtimeperiodid ? $product->timeperiod->plural : '' }}">{{ $product->name }}</option>
												@endforeach
											</select>
										</td>
										<td class="text-right">
											<span class="quantity_span hide"></span>
											<input type="number" name="quantity" value="0" size="4" class="item-quantity form-control total-update" />
											<span class="item-period hide">
												for
												<input type="number" name="periods" min="1" max="999" value="1" class="item-periods form-control total-update" />
												<span class="periods_span"></span>
											</span>
										</td>
										<td class="text-right">
											{{ config('orders.currency', '$') }} <span name="price" class="item-price">0.00</span><br/>
											<span class="text-nowrap">per <span class="item-unit">unit</span></span>
										</td>
										<td class="text-right text-nowrap">
											<span class="hide" name="itemtotal"></span>
											<input type="text" name="linetotal" value="0.00" class="item-total form-control total-update" />
										</td>
										<td>
											<a href="#item_new_row"
												title="Remove product"
												class="btn btn-link text-danger item-remove tip"
												data-api=""
												data-confirm="{{ trans('orders::orders.confirm.account removal') }}">
												<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Remove product</span>
											</a>
										</td>
									</tr>
								@endif
							</tbody>
							<tfoot>
								<tr>
									<th class="text-right" colspan="4" scope="row">
										<strong>{{ trans('orders::orders.order total') }}</strong>
									</th>
									<td class="text-right text-nowrap orderprice">
										{{ config('orders.currency', '$') }} <span id="ordertotal">{{ $order->formattedTotal }}</span>
									</td>
									@if ($order->isActive() && auth()->user()->can('manage orders'))
										<td class="item-edit-show hide">
											<a href="{{ route('site.orders.read', ['id' => $order->id]) }}"
												data-orderid="{{ $order->id }}"
												data-api="{{ route('api.orders.items.create') }}"
												class="btn btn-successd text-success item-add tip"
												title="Add product">
												<span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only">Add</span>
											</a>
										</td>
									@endif
								</tr>
							</tfoot>
						</table>
					</div>
				</div><!-- / .card -->
			</fieldset>

				@if (($order->status != 'canceled' || count($order->accounts) > 0) && $order->total)
				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col col-md-6">
								<h3 class="card-title">
									{{ trans('orders::orders.payment information') }}
								</h3>
							</div>
							<div class="col col-md-6 text-right">
								@if (count($order->accounts) == 0 && $canEdit)
									<a href="#help2" class="help text-info help-dialog" data-tip="Help on Payment Information">
										<span class="icon-help-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
									</a>
									<button class="btn btn-secondary account-save" id="save_accounts" disabled="true">Save Accounts</button>
								@else
									<?php if ($order->status == 'pending_payment'
										|| $order->status == 'pending_boassignment'
										|| (($order->status == 'pending_approval' || $order->status == 'pending_fulfillment') && auth()->user()->can('manage orders'))
										|| ($order->status == 'pending_approval' && !$myorder)): ?>
									<button class="btn btn-sm btn-secondary account-edit" id="save_accounts" data-save-txt="Save Changes" data-edit-txt="Edit Accounts">Edit Accounts</button>
									<a href="{{ route('site.orders.read', ['id' => $order->id]) }}" class="btn btn-sm btn-link account-edit-cancel hide" id="cancel_accounts">Cancel Changes</a>
									<?php endif; ?>
								@endif
							</div>
						</div>
					</div>
					<div class="card-body">
						<table class="table">
							<caption class="sr-only">{{ trans('orders::orders.payment information') }}</caption>
							<thead>
								<tr>
									<th scope="col"{!! count($order->accounts) == 0 ? ' class="hide"' : '' !!}>{{ trans('orders::orders.status') }}</th>
									<th scope="col">{{ trans('orders::orders.account') }}</th>
									<th scope="col" class="text-nowrap">{{ trans('orders::orders.account approver') }}</th>
									<th scope="col" class="text-right text-nowrap">
										@if (count($order->accounts) == 0 && $canEdit)
											<button title="Divide total evenly." class="balance-divide btn btn-link tip">
												<span class="fa fa-arrow-down" aria-hidde="true"></span><span class="sr-only">Divide total evenly.</span>
											</button>
										@endif
										{{ trans('orders::orders.amount') }}
									</th>
									@if ($canEdit)
										<th class="account-edit-show{{ count($order->accounts) > 0 ? ' hide' : '' }}"></th>
									@endif
								</tr>
							</thead>
							<tbody>
								<?php
								$total = 0;
								foreach ($order->accounts as $account)
								{
									$history = $history->merge($account->history()->orderBy('updated_at', 'desc')->get());

									$s = $account->status;

									$text = trans('global.unknown');
									$cls = 'warning';

									if ($s == 'canceled')
									{
										$text = trans('orders::orders.' . $order->status);
										$cls = 'danger';
									}
									else if ($s == 'denied')
									{
										$text = trans('orders::orders.' . $s);
										$cls = 'danger';
									}
									else if ($s == 'pending_assignment')
									{
										$text = trans('orders::orders.' . $s);
									}
									else if ($s == 'pending_approval')
									{
										$text = trans('orders::orders.' . $s);
									}
									else if ($s == 'pending_collection')
									{
										if ($order->status != 'pending_collection')
										{
											$text = trans('orders::orders.approved on date', ['date' => date("M j, Y", strtotime($account->datetimeapproved))]);
										}
										else
										{
											$text = trans('orders::orders.' . $s);
										}
									}
									else if ($s == 'paid')
									{
										$cls = 'success';
										$text = trans('orders::orders.paid on date', ['date' => date("M j, Y", strtotime($account->docdate)), 'docid' => $account->docid]);
									}

									$total += $account->amount;
									?>
									<tr id="account_{{ $account->id }}">
										<td>
											<div id="status_{{ $account->id }}" class="badge order-status {{ $account->status }}">{{ $text }}</div>
											<input type="hidden" name="accountid" data-api="{{ route('api.orders.accounts.read', ['id' => $account->id]) }}" id="{{ $account->id }}" value="{{ strtoupper($account->status) }}" />

											@if ($s == 'pending_approval')
												<div class="form-group mt-3 account-edit-hide" id="button_{{ $account->id }}">
													@if ($account->approveruserid == auth()->user()->id || auth()->user()->can('manage orders'))
														<button name="adbutton" id="button_{{ $account->id }}_approve" class="btn btn-sm btn-success account-approve" data-id="{{ $account->id }}" data-api="{{ route('api.orders.accounts.update', ['id' => $account->id]) }}" data-txt="Approved">Approve</button>
														<button name="adbutton" id="button_{{ $account->id }}_deny" class="btn btn-sm btn-danger account-deny" data-id="{{ $account->id }}" data-api="{{ route('api.orders.accounts.update', ['id' => $account->id]) }}" data-txt="Denied" data-confirm="Are you sure you want to deny payment with this account?">Deny</button>
													@endif
													@if (auth()->user()->can('manage orders'))
														<button name="remind" id="button_{{ $account->id }}_remind" class="btn btn-sm btn-secondary account-remind" data-id="{{ $account->id }}" data-api="{{ route('api.orders.accounts.update', ['id' => $account->id]) }}" data-txt="Reminded">Remind</button>
													@endif
													@if (auth()->user()->can('manage orders'))
														<button name="adbutton" id="button_{{ $account->id }}_reset" class="btn btn-sm btn-warning account-reset hide" disabled data-id="{{ $account->id }}" data-api="{{ route('api.orders.accounts.update', ['id' => $account->id]) }}" data-confirm="Are you sure you want to reset approved/paid/denied status for this account?">Reset</button>
													@endif
												</div>
											@elseif ($s == 'pending_collection' && auth()->user()->can('manage orders'))
												<div class="form-group mt-3 account-edit-hide">
													<button name="adbutton" id="button_{{ $account->id }}_reset" class="btn btn-sm btn-warning account-reset" data-id="{{ $account->id }}" data-api="{{ route('api.orders.accounts.update', ['id' => $account->id]) }}" data-confirm="Are you sure you want to reset approved/denied status for this account?">Reset</button>
												</div>
											@elseif ($s == 'denied' && auth()->user()->can('manage orders'))
												<div class="form-group mt-3 account-edit-hide" id="button_{{ $account->id }}">
													<button name="adbutton" id="button_{{ $account->id }}_reset" class="btn btn-sm btn-warning account-reset" data-id="{{ $account->id }}" data-api="{{ route('api.orders.accounts.update', ['id' => $account->id]) }}" data-confirm="Are you sure you want to reset approved/denied status for this account?">Reset</button>
												</div>
											@endif
										</td>
										<td>
											@if ($account->purchaseio)
												<input type="text" class="account-edit-show hide balance-update form-control" name="account" data-api-list="{{ route('api.orders.accounts') }}" data-api="{{ route('api.orders.accounts.read', ['id' => $account->id]) }}" maxlength="17" value="{{ $account->purchaseio }}" />
											@else
												<input type="text" class="account-edit-show hide num8 balance-update form-control" name="account" data-api-list="{{ route('api.orders.accounts') }}" data-api="{{ route('api.orders.accounts.read', ['id' => $account->id]) }}" maxlength="17" value="{{ $account->purchasewbse }}" />
											@endif
											@if ($account->purchaseio)
												<span class="account-edit-hide">Internal order: <span class="account_span">{{ $account->purchaseio }}</span></span>
											@else
												<span class="account-edit-hide">WBSE: <span class="account_span">{{ $account->purchasewbse }}</span></span>
											@endif
											<br />
											<label class="sr-only" for="justification{{ $account->id }}">Budget justification:</label>
											<span class="account-edit-hide form-text text-muted justification_span">{{ $account->budgetjustification ? $account->budgetjustification : '' }}</span>
											<textarea name="justification" id="justification{{ $account->id }}" rows="3" maxlength="2000" cols="68" class="account-edit-show hide form-control balance-update">{{ $account->budgetjustification }}</textarea>
											@if ($order->status == 'pending_collection' && auth()->user()->can('manage orders'))
												<div class="form-inline mt-3 account-edit-hide" id="button_{{ $account->id }}">
													<label for="docid_{{ $account->id }}" class="sr-only">Doc ID</label>
													<input type="text" class="doc copy-doc form-control" name="docid" id="docid_{{ $account->id }}" data-api-list="{{ route('api.orders.accounts') }}" placeholder="Doc ID" size="15" maxlength="12" />

													<label for="docdate_{{ $account->id }}" class="sr-only">Doc Date</label>
													<input type="text" class="doc copy-docdate form-control date-pick" name="docdate" id="docdate_{{ $account->id }}" placeholder="Doc Date (YYYY-MM-DD)" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}" size="10" />

													<button class="btn btn-secondary account-collect" data-txt="Paid" data-id="{{ $account->id }}" data-api="{{ route('api.orders.accounts.update', ['id' => $account->id]) }}">{{ trans('orders::orders.collect') }}</button>
												</div>
											@endif
										</td>
										<!-- <td class="orderproductitem">
											<span name="account_span">{{ $account->purchasefund }}</span>
										</td>
										<td class="orderproductitem">
											<span name="costcenter_span">{{ $account->purchasecostcenter }}</span>
										</td>
										<td class="orderproductitem">
											<span name="purchaseorder_span">{{ $account->purchaseorder }}</span>
										</td> -->
										<td>
											@if ($account->approver)
												@if (auth()->user()->can('manage users'))
													<a class="account-edit-hide" href="{{ route('admin.users.edit', ['id' => $account->approver->id]) }}">
														<span class="approver_span" data-approverid="{{ $account->approveruserid ? $account->approveruserid : '' }}">{{ $account->approver->name }} ({{ $account->approver->username }})</span>
													</a>
												@else
													<span class="account-edit-hide approver_span" data-approverid="{{ $account->approveruserid ? $account->approveruserid : '' }}">{{ $account->approver->name }} ({{ $account->approver->username }})</span>
												@endif
											@elseif ($account->approveruserid)
												<span class="account-edit-hide approver_span unknown" data-approverid="{{ $account->approveruserid ? $account->approveruserid : '' }}">{{ trans('global.unknown') }}</span> ({{ $account->approveruserid }})
											@else
												<span class="account-edit-hide approver_span none" data-approverid="{{ $account->approveruserid ? $account->approveruserid : '' }}">{{ trans('global.none') }}</span>
											@endif
											<span class="account-edit-show hide">
												<input type="text" id="search_{{ $account->id }}" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" data-id="{{ $account->approveruserid }}" class="form-control form-users" name="approver" value="{{ $account->approver ? $account->approver->name . ' (' . $account->approver->username . ')' : '' }}" />
											</span>
										</td>
										<td class="text-right text-nowrap">
											<a href="#help2" class="help help-dialog" data-tip="Help">
												<span class="amount_error hide"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span><span class="sr-only">Required field is missing or invalid format</span></span>
											</a>
											<span class="account-edit-hide">{{ config('orders.currency', '$') }} <span class="account_amount_span">{{ $account->formattedAmount }}</span></span>
											<input type="text" class="account-edit-show hide form-control balance-update" size="8" name="account_amount" value="{{ $account->formattedAmount }}" />
										</td>
										@if (($s == 'pending_approval' && $myorder || auth()->user()->can('manage orders')) && $order->isActive())
											<td class="account-edit-show hide">
												<a href="#account_{{ $account->id }}"
													title="Remove account"
													class="btn btn-link text-danger account-remove tip"
													data-api="{{ route('api.orders.accounts.delete', ['id' => $account->id]) }}"
													data-confirm="{{ trans('orders::orders.confirm.account removal') }}">
													<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Remove account</span>
												</a>
											</td>
										@endif
									</tr>
								<?php } ?>
								@if ($canEdit)
									@if (count($order->accounts) == 0)
										<tr id="account0">
											<td class="hide"></td>
											<td>
												<input type="text" maxlength="17" class="form-control num8 balance-update" name="account" data-api-list="{{ route('api.orders.accounts') }}" data-api="{{ route('api.orders.accounts.create') }}" placeholder="Account" value="" />
												<br />
												<label class="sr-only" for="justification0">Budget justification</label>
												<textarea name="justification" id="justification0" rows="3" cols="35" maxlength="2000" placeholder="Budget justification" class="form-control balance-update"></textarea>
											</td>
											<td>
												<span class="approver_span hide" data-approverid=""></span>
												<input type="text" name="approver" id="approver0" class="form-control form-users" value="" placeholder="Approver" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" />
											</td>
											<td class="text-right text-nowrap">
												<!-- <span class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text">{{ config('orders.currency', '$') }}</span>
													</span> -->
													<input type="text" name="account_amount" class="form-control balance-update" size="8" value="0.00" />
												<!-- </span> -->
											</td>
											<td>
											</td>
										</tr>
									@endif
									<tr id="account_new_row" class="hide">
										<td{!! count($order->accounts) == 0 ? ' class="hide"' : '' !!}></td>
										<td>
											<input type="text" maxlength="17" class="form-control num8 balance-update" name="account" data-api-list="{{ route('api.orders.accounts') }}" data-api="{{ route('api.orders.accounts.create') }}" placeholder="Account" value="" />
											<br />
											<label class="sr-only" for="new_justification">Budget justification</label>
											<textarea name="justification" id="new_justification" rows="3" cols="35" maxlength="2000" placeholder="Budget justification" class="form-control balance-update"></textarea>
										</td>
										<td>
											<span class="approver_span hide" data-approverid=""></span>
											<input type="text" name="approver" id="new_approver" class="form-control form-users" value="" placeholder="Approver" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" />
										</td>
										<td class="text-right text-nowrap">
											<input type="text" name="account_amount" class="form-control balance-update" size="8" value="0.00" />
										</td>
										<td>
											<a href="#account_new_row"
												title="Remove account"
												class="btn btn-link text-danger account-remove tip"
												data-api=""
												data-confirm="{{ trans('orders::orders.confirm.account removal') }}">
												<span class="icon-trash" aria-hidden="true"></span><span class="sr-only">Remove account</span>
											</a>
										</td>
									</tr>
								@endif
							</tbody>
							<tfoot>
								<tr>
									<td class="text-right" colspan="{{ count($order->accounts) == 0 ? 2 : 3 }}">
										<a href="#help2" class="help help-dialog icn" data-tip="Balance should be $0.00 before saving changes."><!--
											--><span id="balance_error" aria-hidden="true" class="fa fa-exclamation-triangle text-warning hide"></span><span class="sr-only">Balance should be $0.00 before saving changes.</span><!--
										--></a>
										<strong>{{ trans('orders::orders.balance remaining') }}</strong>
									</td>
									<td class="text-right text-nowrap orderprice">
										{{ config('orders.currency', '$') }} <span id="balance">{{ $item->formatCurrency($order->total - $total) }}</span>
									</td>
									@if ($canEdit)
										<td class="account-edit-show{{ count($order->accounts) > 0 ? ' hide' : '' }}">
											<a href="#account_new_row" title="Add account" class="btn btn-link text-success account-add tip">
												<span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only">Add account</span>
											</a>
										</td>
									@endif
								</tr>
							</tfoot>
						</table>

						<div id="help2" title="Payment Information" class="dialog dialog-help">
							<p>
								Please enter the accounts to be used for payment and the dollar amount to be charged to each account. Changes are not saved until you click the "Save Accounts" button.
							</p>
							<p>
								Balance remaining must be <strong>$0.00</strong> after allocating amounts before you may save changes.
							</p>
							<p>
								<img src="{{ asset('modules/orders/img/account_example.png') }}" alt="Example of payment allocation divided by multiple accounts." />
							</p>
						</div>
					</div>
				</div><!-- / .card -->
				@endif
			</div><!-- / .contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12 -->
		</div><!-- / .row -->
		</div>
		<div id="order-notes">
			@if (auth()->user()->can('manage orders'))
			<div class="row">
				<div class="col-md-6">
			@endif
				<div class="card">
					<div class="card-header">
						@if ($canEdit)
							<div class="row">
								<div class="col-md-6">
									<h3 class="panel-title card-title">
										{{ trans('orders::orders.notes') }}
										<a href="#help1" class="help btn help-dialog text-info" data-tip="Help on Order Notes">
											<span class="icon-help-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
										</a>
									</h3>
								</div>
								<div class="col-md-6 text-right">
									@if ($order->status != 'canceled')
									<a href="{{ route('site.orders.read', ['id' => $order->id, 'edit' => 'usernotes']) }}" class="edit-property btn" data-tip="Edit" data-prop="usernotes" data-value="{{ $order->id }}">
										<span class="fa fa-pencil" aria-hidden="true" id="IMG_{{ $order->id }}_usernotes"></span><span class="sr-only">Edit</span>
									</a>
									<a href="{{ route('site.orders.read', ['id' => $order->id]) }}" id="CANCEL_{{ $order->id }}_usernotes" class="hide cancel-edit-property btn" title="Cancel" data-prop="usernotes" data-value="{{ $order->id }}">
										<span class="fa fa-ban" aria-hidden="true"></span><span class="sr-only">Cancel</span>
									</a>
									@endif
								</div>
							</div>
						@else
							<h3 class="panel-title card-title">
								{{ trans('orders::orders.notes') }}
								<a href="#help1" class="help btn help-dialog tip" data-tip="Help on Order Notes">
									<span class="icon-help-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
								</a>
							</h3>
						@endif
					</div>
					<div class="card-body">
						<div id="help1" title="Order Notes" class="dialog dialog-help">
							<p>Use this section to leave any special instructions, extra contact information, or any other notes for this order. {{ config('app.name') }} and your business office staff will be able to view these notes.</p>
						</div>

						<p class="ordernotes">
							<span id="SPAN_{{ $order->id }}_usernotes">{!! $order->usernotes ? nl2br($order->usernotes) : '<span class="none">' . trans('global.none') . '</span>' !!}</span>

							@if ($canEdit)
								<label for="INPUT_{{ $order->id }}_usernotes" class="sr-only">{{ trans('orders::orders.user notes') }}:</label>
								<textarea name="fields[usernotes]" maxlength="2000" cols="80" rows="10" class="form-control hide" id="INPUT_{{ $order->id }}_usernotes">{{ $order->usernotes }}</textarea>
							@endif
						</p>
					</div>
				</div><!-- / .card -->
				<div class="card">
					@foreach ($order->items as $item)
						@if ($item->origorderitemid)
							<div class="card-header">
								User Notes for recurring item:
								<br /><strong>{{ $item->product->name }}</strong>
							</div>
							<ul class="list-group list-group-flush p-0">
								<?php
								foreach ($item->sequence() as $usernote):
									if ($usernote->id == $item->id || $usernote->datetimecreated > $item->datetimecreated):
										continue;
									endif;
									?>
									<li class="list-group-item">
										<div class="mb-1">
											<strong>Order <a href="{{ route('site.orders.read', ['id' => $usernote->orderid]) }}">#{{ $usernote->orderid }}</a></strong>
											<div class="float-right">{{ $usernote->datetimecreated->format('M d, Y') }}</div>
										</div>
										<blockquote>
											<p>{!! $usernote->order->usernotes ? nl2br($usernote->order->usernotes) : '<span class="none">' . trans('global.none') . '</span>' !!}</p>
										</blockquote>
									</li>
									<?php
								endforeach;
								?>
							</ul>
						@endif
					@endforeach
				</div>

				@if (auth()->user()->can('manage orders'))
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-md-6">
									<h3 class="pane-title card-title">{{ trans('orders::orders.staff notes') }}</h3>
								</div>
								<div class="col-md-6 text-right">
								@if ($order->status != 'canceled')
									<a href="{{ route('site.orders.read', ['id' => $order->id, 'edit' => 'usernotes']) }}" class="edit-property btn" data-tip="Edit" data-prop="staffnotes" data-value="{{ $order->id }}">
										<span class="fa fa-pencil" aria-hidden="true" id="IMG_{{ $order->id }}_staffnotes"></span><span class="sr-only">Edit</span>
									</a>
									<a href="{{ route('site.orders.read', ['id' => $order->id]) }}" id="CANCEL_{{ $order->id }}_staffnotes" class="hide btn cancel-edit-property" data-tip="Cancel" data-prop="staffnotes" data-value="{{ $order->id }}">
										<span class="fa fa-ban" aria-hidden="true"></span><span class="sr-only">Cancel</span>
									</a>
								@endif
								</div>
							</div>
						</div>
						<div class="card-body">
							<p class="ordernotes">
								<span id="SPAN_{{ $order->id }}_staffnotes">{!! $order->staffnotes ? nl2br($order->staffnotes) : '<span class="none">' . trans('global.none') . '</span>' !!}</span>

								<label for="INPUT_{{ $order->id }}_staffnotes" class="sr-only">{{ trans('orders::orders.staff notes') }}:</label>
								<textarea name="fields[staffnotes]" maxlength="2000" cols="80" rows="10" class="form-control hide" id="INPUT_{{ $order->id }}_staffnotes">{{ $order->staffnotes }}</textarea>
							</p>
						</div>
					</div><!-- / .card -->

					<div class="card">
						@foreach ($order->items as $item)
							@if ($item->origorderitemid)
								<div class="card-header">
									Staff Notes for recurring item:
									<br /><strong>{{ $item->product->name }}</strong>
								</div>
								<ul class="list-group list-group-flush p-0">
									<?php
									foreach ($item->sequence() as $usernote):
										if ($usernote->id == $item->id || $usernote->datetimecreated > $item->datetimecreated):
											continue;
										endif;
										?>
										<li class="list-group-item">
											<div class="mb-1">
												<strong>Order <a href="{{ route('site.orders.read', ['id' => $usernote->orderid]) }}">#{{ $usernote->orderid }}</a></strong>
												<div class="float-right">{{ $usernote->datetimecreated->format('M d, Y') }}</div>
											</div>
											<blockquote>
												<p>{!! $usernote->order->staffnotes ? nl2br($usernote->order->staffnotes) : '<span class="none">' . trans('global.none') . '</span>' !!}</p>
											</blockquote>
										</li>
										<?php
									endforeach;
									?>
								</ul>
							@endif
						@endforeach
					</div>
					</div>
				</div>
				@endif
@if ($order->id)
		</div>
		<div id="order-history">
			<div id="order-history" class="card">
						<div class="card-header">
							<h3 class="card-title">{{ trans('history::history.history') }}</h3>
						</div>
						<ul class="list-group list-group-flush">
							<?php
							if (count($history)):
								$sorted = $history->sortByDesc('updated_at');

								foreach ($sorted as $action):
									$actor = trans('global.unknown');

									if ($action->user):
										$actor = e($action->user->name);
									endif;

									if ($action->action == 'created'):
										$dt = $action->created_at;
									elseif ($action->action == 'updated'):
										$dt = $action->updated_at;
									endif;

									$fields = array_keys(get_object_vars($action->new));
									foreach ($fields as $i => $k):
										if (in_array($k, ['created_at', 'updated_at', 'deleted_at'])):
											unset($fields[$i]);
										endif;
									endforeach;
									$old = Carbon\Carbon::now()->subDays(2); //->toDateTimeString();

									$type = $action->historable_type;
									$type = explode('\\', $type);
									$type = end($type);

									$entity = strtolower($type);

									$did = '';
									if ($entity == 'account')
									{
										if ($action->action == 'created')
										{
											$did = '<span class="text-info">added</span> payment account #' . $action->historable_id;
										}
										if ($action->action == 'updated')
										{
											$did = '<span class="text-info">edited</span> a payment account';
										}
										if ($action->action == 'deleted')
										{
											$did = '<span class="text-danger">removed</span> a payment account';
										}
									}
									if ($entity == 'item')
									{
										if ($action->action == 'created')
										{
											$did = '<span class="text-info">added</span> an item to the order';
										}
										if ($action->action == 'updated')
										{
											$did = '<span class="text-info">edited</span> an item';
										}
										if ($action->action == 'deleted')
										{
											$did = '<span class="text-danger">removed</span> an item';
										}
									}
									if ($entity == 'order')
									{
										if ($action->action == 'created')
										{
											$did = '<span class="text-success">placed</span> this order';
										}
										if ($action->action == 'updated')
										{
											$did = '<span class="text-info">edited</span> this order';
										}
									}
									if (in_array('approveruserid', $fields))
									{
										$did = '<span class="text-info">set</span> approver for payment account #' . $action->historable_id;
									}
									if (in_array('datetimedenied', $fields))
									{
										$did = '<span class="text-danger">denied</span> payment account #' . $action->historable_id;
									}
									?>
									<li class="list-group-item">
										<!-- <span class="entry-log-action">{{ trans('history::history.action ' . $action->action, ['user' => $actor, 'entity' => $entity]) }}</span><br /> -->
										<div class="row">
											<div class="col-md-8">
											{!! $actor . ' ' . $did !!}
											</div>
											<div class="col-md-4 text-right">
										<time datetime="{{ $action->dt }}" class="entry-log-date">
											@if ($dt < $old)
												{{ $dt->format('d M Y') }}
											@else
												{{ $dt->diffForHumans() }}
											@endif
										</time>
											</div>
										</div>
									</li>
									<?php
								endforeach;
							else:
								?>
								<li class="list-group-item">
									<span class="entry-diff">{{ trans('history::history.none found') }}</span>
								</li>
								<?php
							endif;
							?>
						</ul>
					</div><!-- / #order-history -->
		</div>
	</div><!-- / .tabs -->
	@endif

	<input type="hidden" name="id" id="field-id" value="{{ $order->id }}" />
	@csrf
</form>
@stop