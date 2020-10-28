@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/orders/js/orders.js') }}"></script>
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
	$('.total-update').on('blur', function(e){
		if ($(this).data('override') == '1') {
			UpdateTotal(true);
		} else {
			UpdateTotal();
		}
	});
	$('.order-fulfill').on('click', function(e){
		e.preventDefault();
		FulfillItem($(this).data('id'), this);
	});

	$( '#orderheaderpopup' ).dialog({
		modal: true,
		width: '550px',
		autoOpen: false,
		buttons : {
			OK: {
				text: 'OK',
				click: function() {
					$(this).dialog('close'); 
				}
			}
		}
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
		RemindOrder($(this).data('id'));
	});
	$('#cancelorder').on('click', function(e){
		e.preventDefault();
		CancelOrder();
	});

	$('.contentInner')
		.on('click', '.account-remove', function(e){
			e.preventDefault();
			EditRemoveAccount($(this).data('id'), this);
		});
	$('.account-add').on('click', function(e){
		e.preventDefault();
		AddNewAccountRow();
	});
	/*$('.account-remove').on('click', function(e){
		e.preventDefault();
		EditRemoveAccount($(this).data('id'), this);
	});*/
	$('.account-approve').on('click', function(e){
		e.preventDefault();
		ApproveAccount($(this).data('id'), this);
	});
	$('.account-deny').on('click', function(e){
		e.preventDefault();
		DenyAccount($(this).data('id'), this);
	});
	$('.account-remind').on('click', function(e){
		e.preventDefault();
		RemindAccount($(this).data('id'), this);
	});
	$('.account-collect').on('click', function(e){
		e.preventDefault();
		CollectAccount($(this).data('id'), this);
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

	var dates = document.getElementsByName("docdate");

	var autocompleteOrderPurchaseAccount = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
				response($.map(data.accounts, function (el) {
					return {
						label: el.purchaseorder,
						purchaseorder: el.purchaseorder,
						purchasefund: el.purchasefund,
						order: el.order,
						id: el.id
					};
				}));
			});
		};
	};

	var docids = document.getElementsByName("docid");
	for (var x=0;x<docids.length;x++) {
		$( docids[x] ).autocomplete({
			source: autocompleteOrderPurchaseAccount('{{ route("api.orders.accounts") }}?docid=%s'),
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
		$( funds[x] ).autocomplete({
			source: autocompleteOrderPurchaseAccount('{{ route("api.orders.accounts") }}?fund=%s'),
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
		filter: /^[a-z0-9\-_ \.,\'\(\)]+$/i
	});

	/*var autocompleteName = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
				response($.map(data.users, function (el) {
					return {
						label: el.name,
						name: el.name,
						id: el.id,
						usernames: el.usernames,
						priorusernames: el.priorusernames
					};
				}));
			});
		};
	};
	$("#search_user").autocomplete({
		source: autocompleteName('{{ route("api.users.index") }}?search=%s'),
		dataName: 'users',
		height: 150,
		delay: 100,
		minLength: 2,
		filter: /^[a-z0-9\-_ .,@+]+$/i,
		select: function (event, ui) {
			event.preventDefault();
			var thing = ui['item'].label;
			if (typeof(ui['item'].usernames) != 'undefined') {
				thing = thing + " (" + ui['item'].usernames[0]['name'] + ")";
			} else if (typeof(ui['item'].priorusernames) != 'undefined') {
				thing = thing + " (" + ui['item'].priorusernames[0]['name'] + ")";
			}
			$("#search_user" ).val( thing );
		},
		create: function () {
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				var thing = item.label;
				if (typeof(item.usernames) != 'undefined') {
					thing = thing + " (" + item.usernames[0]['name'] + ")";
				} else if (typeof(item.priorusernames) != 'undefined') {
					thing = thing + " (" + item.priorusernames[0]['name'] + ")";
				}
				return $( "<li>" )
					.append( $( "<div>" ).text( thing ) )
					.appendTo( ul );
			};
		}
	});
	$("#search_user").on("autocompleteselect", SearchEventHandler);*/

	var newsuser = $(".form-users");
	if (newsuser.length) {
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
		newsuser.tagsInput({
			placeholder: 'Select user...',
			importPattern: /([^:]+):(.+)/i,
			autocomplete: {
				source: autocompleteUsers(newsuser.attr('data-uri')),
				dataName: 'users',
				height: 150,
				delay: 100,
				minLength: 1,
				limit: 1
			}
		});
	}
});
</script>
@endpush

@php
$myorder = (auth()->user()->id == $order->submitteruserid);
$canEdit = (auth()->user()->can('edit orders') || (auth()->user()->can('edit.own orders') && $myorder));
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
@component('orders::site.submenu')
	orders
@endcomponent
</div>
<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	<div class="row">
		<div class="col-md-6">
			<h2>{{ trans('orders::orders.orders') }}: {{ $order->id ? '#' . $order->id : 'Create' }}</h2>
		</div>
		<div class="col-md-6 text-right">
			<?php
			if ($order->status == 'pending_payment'
			 || $order->status == 'pending_boassignment'
			 || (($order->status == 'pending_approval' || $order->status == 'pending_fulfillment') && auth()->user()->can('manage orders'))
			 || ($order->status == 'pending_approval' && !$myorder)) { ?>
					<?php if ($order->status == 'pending_payment' && auth()->user()->can('manage orders')) { ?>
						<button class="btn btn-secondary" id="remindorder" data-id="<?php echo $order->id; ?>">Remind Customer</button>
						<span id="remindorderspan"></span>
					<?php } ?>

					<button class="btn btn-danger" id="cancelorder">Cancel Order</button>
			<?php } else { ?>
				<button class="btn btn-secondary" id="printorder">Print Order</button>
			<?php } ?>
		</div>
	</div>

	@if ($order->status == 'pending_payment')
		<div class="alert alert-success">
			<p>Your order has been submitted. Thank you for your order!</p>
			<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
		</div>

		<div id="orderheaderpopup" class="stash" title="Order Submitted">
			<p>Your order has been submitted. Thank you for your order! You should receive an email confirmation shortly.</p>
			<p>Please review the order and enter Purdue account numbers to be used for payment below. You may also add any special instructions for the order in the notes box at the bottom of the page at any time.</p>
			<p>Use the form directly below this box to enter your payment information. You or the person this order is for (if placing on behalf) may return to this page at a later time to enter payment information.</p>
			<p>Order status tracking:</p>
			<ol class="order">
				<li class="stepcomplete">1) Submit order</li>
				<li class="step currentstep">2) Enter payment information</li>
				<li class="notmystep">3) Awaiting business office assignment by ITaP</li>
				<li class="notmystep">4) Awaiting approval by your business office</li>
				<li class="notmystep">5) Awaiting fulfillment by ITaP</li>
				<li class="notmystep">6) Order completion</li>
			</ol>
		</div>
	@elseif ($order->status == 'pending_boassignment')
		<div class="alert alert-info">
			<p>Payment information has been entered for this order.</p>
			<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
		</div>
		<div id="orderheaderpopup" class="stash" title="Payment Information Entered">
			<p>Payment information has been entered. No further action is required.</p>
			<p>ITaP Staff will assign this to your business office for approval. You will be updated on the progress of this order via email. You may return to this page at any time to view the current status.</p>
			<p>Order status tracking:</p>
			<ol class="order">
				<li class="stepcomplete">1) Submit order</li>
				<li class="stepcomplete">2) Enter payment information</li>
				<li class="currentstep step">3) Awaiting business office assignment by ITap</li>
				<li class="notmystep">4) Awaiting approval by your business office</li>
				<li class="notmystep">5) Awaiting fulfillment by ITaP</li>
				<li class="notmystep">6) Order Completion</li> 
			</ol>
		</div>
	@elseif ($order->status == 'pending_approval')
		<div class="alert alert-info">
			<p>Order has been assigned to your business office and is awaiting their approval.</p>
			<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
		</div>

		<div id="orderheaderpopup" class="stash" title="Awaiting Business Office Approval">
			<p>Order has been assigned to your business office and is awaiting their approval.</p>
			<p>Please contact your business office directly if you have any questions about approval on this order. The assigned approver for each account is listed below.</p>
			<p>Order status tracking:</p>
			<ol class="order">
				<li class="stepcomplete">1) Submit order</li>
				<li class="stepcomplete">2) Enter payment information</li>
				<li class="stepcomplete">3) Awaiting business office assignment by ITaP</li>
				<li class="currentstep step">4) Awaiting approval by your business office</li>
				<li class="notmystep">5) Awaiting fulfillment by ITaP</li>
				<li class="notmystep">6) Order completion</li>
			</ol>
		</div>
	@elseif ($order->status == 'pending_fulfillment')
		<div class="alert alert-info">
			<p>This order has been approved by your business office(s). ITaP staff have begun the process of fulfilling this order.</p>
			<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
		</div>

		<div id="orderheaderpopup" class="stash" title="Awaiting Fulfillment">
			<p>This order has been approved by your business office(s). ITaP staff have begun the process of fulfilling this order.</p>
			<p> You may be contacted directly by ITaP staff if further information is needed to fulfill the order or with information on how to access your new resources.</p>
			<p>Order status tracking:</p>
			<ol class="order">
				<li class="stepcomplete">1) Submit order</li>
				<li class="stepcomplete">2) Enter payment information</li>
				<li class="stepcomplete">3) Awaiting business office assignment by ITaP</li>
				<li class="stepcomplete">4) Awaiting approval by your business office</li>
				<li class="currentstep step">5) Awaiting fulfillment by ITaP</li>
				<li class="notmystep">6) Order completion</li>
			</ol>
		</div>
	@elseif ($order->status == 'pending_collection')
		<div class="alert alert-success">
			<p>This order has been fulfilled. Please contact <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> if you have questions on how to use new resources.</p>
			<p><a href="#orderheaderpopup" class="order-status">Order status information</a></p>
		</div>

		<div id="orderheaderpopup" class="stash" title="Order Complete">
			<p>This order has been fulfilled. Please contact <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> if you have  questions on how to use new resources.</p>
			<p>The financial transactions may take several more weeks to process and complete between the business offices by this order is complete and resources are ready for you to use.</p>
			<p>Order status tracking:</p>
			<ol class="order">
				<li class="stepcomplete">1) Submit order</li>
				<li class="stepcomplete">2) Enter payment information</li>
				<li class="stepcomplete">3) Awaiting business office assignment by ITaP</li>
				<li class="stepcomplete">4) Awaiting approval by your business office</li>
				<li class="stepcomplete">5) Awaiting fulfillment by ITaP</li>
				<li class="currentstep step">6) Order completion</li>
			</ol>
		</div>
	@elseif ($order->status == 'complete')
		<div id="orderheaderpopup" class="stash" title="Order Complete">
			<p>This order is complete.</p>
			<p>Order status tracking:</p>
			<ol class="order">
				<li class="stepcomplete">1) Submit order</li>
				<li class="stepcomplete">2) Enter payment information</li>
				<li class="stepcomplete">3) Awaiting business office assignment by ITaP</li>
				<li class="stepcomplete">4) Awaiting approval by your business office</li>
				<li class="stepcomplete">5) Awaiting fulfillment by ITaP</li>
				<li class="stepcomplete">6) Order completion</li>
			</ol>
		</div>
	@elseif ($order->status == 'canceled')
		<p class="alert alert-error">This order was canceled.</p>
		<div id="orderheaderpopup" class="stash" title="Order Canceled">
			<p>This order was canceled.</p>
			<p>Order status tracking:</p>
			<ol class="order">
				<li class="stepcomplete">1) Submit order</li>
				<li class="step"><del>2) Enter payment information</del></li>
				<li class="step"><del>3) Awaiting business office assignment by ITaP</del></li>
				<li class="step"><del>4) Awaiting approval by your business office</del></li>
				<li class="step"><del>5) Awaiting fulfillment by ITaP</del></li>
				<li class="step"><del>6) Order completion</del></li>
				<li class="stepcomplete">2) Order Canceled</li>
			</ol>
		</div>
	@endif

	<form action="{{ route('site.orders.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

		<input type="hidden" name="id" id="order" data-api="{{ route('api.orders.update', ['id' => $order->id]) }}" value="{{ $order->id }}" />

		<div class="panel panel-default card">
			<div class="panel-heading card-header">
				<h3 class="panel-title card-title">{{ trans('global.details') }}</h3>
			</div>
			<div class="panel-body card-body">

				<div class="orderstatusblocks">
					<div class="orderstatus">
						<span class="orderstatus {{ $order->status }}">{{ trans('orders::orders.' . $order->status) }}</span>
						<a href="#orderheaderpopup" class="order-status icn tip" title="Help">
							<i class="fa fa-question-circle" aria-hidden="true"></i> Help
						</a>
					</div><!-- / .orderstatus -->
				</div><!-- / .orderstatusblock -->
				<div class="form-group">
					<label for="field-state">{{ trans('global.state') }}:</label>
					<select class="form-control" name="state" id="field-state">
						<option value="pending_payment"<?php if ($order->status == 'pending_payment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending_payment') }}</option>
						<option value="pending_boassignment"<?php if ($order->status == 'pending_boassignment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending_boassignment') }}</option>
						<option value="pending_approval"<?php if ($order->status == 'pending_approval'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending_approval') }}</option>
						<option value="pending_collection"<?php if ($order->status == 'pending_collection'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending_collection') }}</option>
						<option value="pending_fulfillment"<?php if ($order->status == 'pending_fulfillment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending_fulfillment') }}</option>
						<!-- <option value="pending"<?php if ($order->status != 'complete' && $order->status != 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending') }}</option> -->
						<option value="complete"<?php if ($order->status == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.complete') }}</option>
						<option value="canceled"<?php if ($order->status == 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.canceled') }}</option>
					</select>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<label for="search_group">{{ trans('orders::orders.created') }}:</label>
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
							@if (auth()->user()->can('manage orders'))
								<!--<span class="input-group">
									<input type="text" name="userid" id="userid" class="form-control form-users" value="{{ ($order->user ? $order->user->name : trans('global.unknown')) . ':' . $order->userid }}" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" placeholder="{{ trans('global.none') }}" />
									<span class="input-group-addon"><span class="input-group-text fa fa-user" aria-hidden="true"></span></span>
								</span>-->
								<p class="form-text">
									<span id="edit_user" data-userid="{{ $order->userid }}">{{ $order->user ? $order->user->name . ' (' . $order->user->username . ')' : trans('global.none') }}</span>
									<!-- <input type="text" id="search_user" class="stash" value="{{ $order->userid }}" /> -->
									<span class="stash">
									<input type="text" name="search_user" id="search_user" class="form-control form-users" value="{{ ($order->user ? $order->user->name : trans('global.unknown')) . ':' . $order->userid }}" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" placeholder="{{ trans('global.none') }}" />
									</span>
									<!--<input type="text" name="userid" id="userid" class="form-control stash form-users" value="{{ ($order->user ? $order->user->name : trans('global.unknown')) . ':' . $order->userid }}" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" placeholder="{{ trans('global.none') }}" />-->
									<a href="#edit_user" id="order_user_save" title="Save Change">
										<i id="user_save" class="fa fa-pencil"></i><span class="sr-only">Edit</span>
									</a>
								</p>
								@if ($order->user->title)
									<p class="form-text">{{ $order->user->title }}</p>
								@endif

								@if ($order->user->department)
									<p class="form-text">{{ $order->user->department }}</p>
								@endif

								@if ($order->user->school)
									<p class="form-text">{{ $order->user->school }}</p>
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
							@if (auth()->user()->can('manage orders'))
								<p class="form-text">
								<!-- <span class="input-group input-user">
									<input type="text" name="fields[groupid]" id="search_group" class="form-control" value="{{ $order->group ? $order->group->name . ':' . $order->groupid : '' }}" data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" placeholder="{{ trans('global.none') }}" />
									<span class="input-group-addon"><span class="input-group-text fa fa-users" aria-hidden="true"></span></span>
								</span> -->
									<span id="edit_group">{{ $order->group ? $order->group->name : trans('global.none') }}</span>
									<input type="text" name="search_group" id="search_group" class="form-control form-groups stash" value="{{ $order->group ? $order->group->name : '' }}" data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" placeholder="{{ trans('global.none') }}" />
									<!--<input type="text" id="search_group" class="stash" value="{{ $order->groupid }}" />-->
									<a href="#edit_group" id="order_group_save" title="Save Change">
										<i id="group_save" class="fa fa-pencil"></i><span class="sr-only">Edit</span>
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
		</div>

		<?php $history = $order->history()->orderBy('created_at', 'desc')->get(); ?>

		<div class="panel panel-default card">
			<div class="panel-heading card-header">
				<div class="row">
					<div class="col col-md-6">
						<h3 class="panel-title card-title">{{ trans('orders::orders.items') }}</h3>
					</div>
					<div class="col col-md-6 text-right">
						<?php
						if (
						($order->status == 'pending_payment'
						|| $order->status == 'pending_boassignment'
						|| (
							($order->status == 'pending_approval' || $order->status == 'pending_fulfillment') && auth()->user()->can('manage orders'))
							|| ($order->status == 'pending_approval' && !$myorder)) && (auth()->user()->can('manage orders') || $myorder)) { ?>
							<a href="#help4" class="help icn tip" title="Help">
								<i class="fa fa-question-circle" aria-hidden="true"></i> Help
							</a>

							<input type="button" id="save_quantities" data-state="inactive" data-inactive="Edit Quantities" data-active="Save Changes" class="btn btn-sm btn-secondary" value="Edit Quantities" />
							<input type="button" id="cancel_quantities" class="btn btn-sm btn-danger stash" value="Cancel Changes" />

							<div id="error1" title="Cancel Order?" class="dialog dialog-confirm">
								<p>Removing the last item will <strong>cancel</strong> your order. Do you wish to continue?</p>
							</div>

							<div id="help4" title="Edit Quantities" class="dialog dialog-help">
								<p>
									Quantities may be edited while payment information is being approved. You will need to redistribute or remove the total cost difference from your accounts. Accounts that have already been approved may only be deleted.
								</p>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="panel-body card-body">
				<table class="table">
					<caption class="sr-only">{{ trans('orders::orders.items') }}</caption>
					<thead>
						<tr>
							<th scope="col" colspan="2">{{ trans('orders::orders.status') }}</th>
							<th scope="col">{{ trans('orders::orders.item') }}</th>
							<th scope="col">{{ trans('orders::orders.quantity') }}</th>
							<th scope="col" class="text-right">{{ trans('orders::orders.price') }}</th>
							<th scope="col" class="text-right">{{ trans('orders::orders.total') }}</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($order->items as $item) {
							$history = $history->merge($item->history()->orderBy('created_at', 'desc')->get());
							?>
							<tr>
								<td>
									@if (!$item->isFulfilled())
										@if ($order->status != 'canceled' && $order->status == 'pending_fulfillment')
											<button class="btn btn-sm btn-secondary order-fulfill" id="button_<?php echo $item->id; ?>" data-id="<?php echo $item->id; ?>">{{ trans('orders::orders.fulfill') }}</button>
										</td>
										<td>
											<span class="order-status {{ $order->status }}" id="status_<?php echo $item->id; ?>">{{ trans('orders::orders.pending_fulfillment') }}</span>
										@else
												<span class="order-status {{ $order->status }}">
													@if ($order->status == 'pending_fulfillment')
														{{ trans('orders::orders.' . $order->status) }}
													@else
														{{ trans('orders::orders.pending_approval') }}
													@endif
												</span>
											</td>
											<td>
										@endif
									@else
											<span class="order-status fulfilled">{{ trans('orders::orders.fulfilled') }}</span>
										</td>
										<td>
											{{ Carbon\Carbon::parse($item->fulfilled)->format('M j, Y') }}
									@endif
								</td>
								<td>
									<strong>{{ $item->product->name }}</strong>
									<p class="form-text text-muted">
									<?php
									if ($item->origorderitemid)
									{
										if ($item->start() && $item->end())
										{
											if ($item->id == $item->origorderitemid)
											{
												echo trans('orders::orders.new service', ['start' => $item->start()->format('M j, Y'), 'end' => $item->end()->format('M j, Y')]);
											}
											else
											{
												echo trans('orders::orders.service renewal', ['start' => $item->start()->format('M j, Y'), 'end' => $item->end()->format('M j, Y')]);
											}
										}
										else
										{

											echo 'Service for ' . $item->timeperiodcount . ' ';
											if ($item->timeperiodcount > 1)
											{
												echo $item->product->timeperiod->plural;
												echo trans('orders::orders.service for', ['count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->plural]);
											}
											else
											{
												echo trans('orders::orders.service for', ['count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->singular]);
											}
										}
									}
									?>
									</p>
								</td>
								<td>
									<input type="hidden" name="item" value="<?php echo $item->id; ?>" />
									<input type="hidden" name="original_quantity" value="<?php echo $item->quantity; ?>" />

									<span class="quantity_span">{{ $item->quantity }}</span>

									<input type="hidden" name="original_periods" value="{{ $item->timeperiodcount }}" />
									<input type="number" name="quantity" value="{{ $item->quantity }}" size="4" class="stash total-update" />
									@if ($item->origorderitemid)
										for<br/>
										<span class="periods_span">{{ $item->timeperiodcount }}</span>
										<input type="number" max="999" name="periods" value="{{ $item->timeperiodcount }}" class="stash total-update" />
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
												<?php echo $item->timeperiodcount; ?>
											</span>
											<input type="number" max="999" name="periods" value="<?php echo $item->timeperiodcount; ?>" class="stash total-update" />
										</span>
									@endif
								</td>
								<td class="text-right">
									{{ config('orders.currency', '$') }} <span name="price">{{ $item->formattedPrice }}</span><br/>
									per {{ $item->product->unit }}
								</td>
								<td class="text-right text-nowrap">
									{{ config('orders.currency', '$') }} <span name="itemtotal">{{ $item->formattedTotal }}</span>
									@if ($item->origorderitemid)
										<a href="/order/recur/{{ $order->id }}" class="tip" title="This is a recurring item.">
											<i class="fa fa-undo"></i><span class="sr-only">This is a recurring item.</span>
										</a>
									@endif
									@if (auth()->user()->can('manage orders'))
										<input type="text" size="8" name="linetotal" value="{{ $item->formattedTotal }}" class="stash total-update" data-override="1" />
										<input type="hidden" name="original_total" value="{{ $item->price }}" />
									@endif
									<input type="hidden" name="itemid" id="{{ $item->id }}" value="{{ $item->isFulfilled() ? 'FULFILLED' : 'PENDING_FULFILLMENT' }}" />
								</td>
							</tr>
						<?php } ?>
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="5">
								<strong>{{ trans('orders::orders.order total') }}</strong>
							</td>
							<td class="text-right text-nowrap orderprice">
								{{ config('orders.currency', '$') }} <span id="ordertotal">{{ $order->formattedTotal }}</span>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<div class="panel panel-default card">
			<div class="panel-heading card-header">
				<div class="row">
					<div class="col col-md-6">
						<h3 class="panel-title card-title">
							{{ trans('orders::orders.payment information') }}
							<a href="#help2" class="help icn tip" title="Help">
								<i class="fa fa-question-circle" aria-hidden="true"></i> Help
							</a>
						</h3>
					</div>
					<div class="col col-md-6 text-right">
						<?php if ($order->status == 'pending_payment'
							|| $order->status == 'pending_boassignment'
							|| (($order->status == 'pending_approval' || $order->status == 'pending_fulfillment') && auth()->user()->can('manage orders'))
							|| ($order->status == 'pending_approval' && !$myorder)) { ?>
						<input type="button" value="Edit Accounts" class="btn btn-sm btn-secondary account-edit" id="save_accounts" />
						<input type="button" value="Cancel Changes" class="btn btn-sm btn-danger account-edit-cancel stash" id="cancel_accounts" />
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="panel-body card-body">
				<table class="table">
					<caption class="sr-only">{{ trans('orders::orders.payment information') }}</caption>
					<thead>
						<tr>
							<th scope="col">{{ trans('orders::orders.status') }}</th>
							<th scope="col">{{ trans('orders::orders.account') }}</th>
							<th scope="col">{{ trans('orders::orders.account approver') }}</th>
							<th scope="col" class="text-right text-nowrap">{{ trans('orders::orders.amount') }}</th>
							@if ($canEdit)
								<th></th>
							@endif
						</tr>
					</thead>
					<tbody>
						<?php
						$total = 0;
						foreach ($order->accounts as $account)
						{
							$history = $history->merge($account->history()->orderBy('created_at', 'desc')->get());

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
									@if ($s == 'pending_approval')
										@if ($account->approveruserid == auth()->user()->id)
											<input type="button" name="adbutton" value="Approve" class="btn btn-success account-approve" data-id="{{ $account->id }}" />
											<input type="button" name="adbutton" value="Deny" class="btn btn-danger account-deny" data-id="{{ $account->id }}" />
										@elseif (auth()->user()->can('manage orders') || $myorder)
											<a name="editremove" href="{{ route('site.orders.read', ['id' => $order->id]) }}?remove={{ $account->id }}" title="Remove account" class="account-remove stash btn btn-danger" data-id="{{ $account->id }}">
												<i class="fa fa-trash"></i><span class="sr-only">Remove account</span>
											</a>
										@endif

										@if (auth()->user()->can('manage orders'))
											<input type="button" name="remind" value="Remind" class="btn btn-secondary account-remind" data-id="{{ $account->id }}" />
										@endif
									@elseif ($s == 'pending_collection')
										@if ($order->status == 'pending_collection' && (auth()->user()->can('manage orders') || $myorder))
											<a name="editremove" href="{{ route('site.orders.read', ['id' => $order->id]) }}?remove={{ $account->id }}" title="Remove account" class="account-remove btn btn-danger stash" data-id="{{ $account->id }}" data-api="{{ route('api.orders.accounts.delete', ['id' => $account->id]) }}">
												<i class="fa fa-trash"></i><span class="sr-only">Remove account</span>
											</a>
										@endif
										@if ($order->status == 'pending_collection' && auth()->user()->can('manage orders'))
											<input type="text" class="doc copy-doc form-control" name="docid" id="docid_{{ $account->id }}" placeholder="Doc ID" size="15" maxlength="12" />
											<input type="text" class="doc copy-docdate form-control date-pick" name="docdate" id="docdate_{{ $account->id }}" placeholder="Doc Date" size="15" />
											<input type="button" value="{{ trans('orders::orders.collect') }}" class="btn btn-secondary account-collect" data-id="{{ $account->id }}" />
										@endif
									@endif
									<input type="hidden" name="accountid"  data-api="{{ route('api.orders.accounts.read', ['id' => $account->id]) }}" id="{{ $account->id }}" value="{{ strtoupper($account->status) }}" />
								</td>
								<td>
									<span id="status_{{ $account->id }}" class="{{ $account->status }} order-status">{{ $text }}</span>
								</td>
								<td>
									@if ($account->purchaseio)
										<input type="text" class="stash num8 balance-update form-control" name="account" data-api="{{ route('api.orders.accounts.read', ['id' => $account->id]) }}" maxlength="17" value="{{ $account->purchaseio }}" />
									@else
										<input type="text" class="stash num8 balance-update form-control" name="account" data-api="{{ route('api.orders.accounts.read', ['id' => $account->id]) }}" maxlength="17" value="{{ $account->purchasewbse }}" />
									@endif
									@if ($account->purchaseio)
										Internal order: <span class="account_span">{{ $account->purchaseio }}</span>
									@else
										WBSE: <span class="account_span">$account->purchasewbse }}</span>
									@endif
									<br />
									<label for="justification{{ $account->id }}">Budget justification:</label><br />
									<span class="justification_span">{{ $account->budgetjustification ? $account->budgetjustification : trans('global.none') }}</span>
									<textarea name="justification" id="justification{{ $account->id }}" rows="3" maxlength="2000" cols="68" class="stash form-control balance-update">{{ $account->budgetjustification }}</textarea>
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
											<a href="{{ route('admin.users.edit', ['id' => $account->approver->id]) }}">
												<span class="approver_span">{{ $account->approver->name }} ({{ $account->approver->username }})</span>
											</a>
											<input id="search_{{ $account->id }}" class="stash" name="approver" value="{{ $account->approver->name }} ({{ $account->approver->username }})" />
										@else
											<span class="approver_span">{{ $account->approver->name }}</span>
										@endif
									@else
										<span class="unknown">{{ trans('global.unknown') }}</span>
									@endif
								</td>
								<td class="text-right text-nowrap">
									<a href="#help2" class="help">
										<span class="amount_error stash"><i class="fa fa-exclamation-triangle"></i><span class="sr-only">Required field is missing or invalid format</span></span>
									</a>
									{{ config('orders.currency', '$') }} <span class="account_amount_span">{{ $account->formattedAmount }}</span>
									<input type="text" class="stash balance-update" size="8" name="account_amount" value="{{ $account->formattedAmount }}" />
								</td>
								<?php /*@if ($canEdit && $order->status != 'canceled' && $order->status != 'complete')
									<td>
										<a href="{{ route('site.orders.read', ['id' => $order->id, 'remove' => $account->id]) }}" title="Remove account" class="btn btn-danger account-remove" data-id="{{ $account->id }}">
											<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Remove account</span>
										</a>
									</td>
								@endif*/ ?>
							</tr>
						<?php } ?>
						<!-- <tr>
							<td>
							</td>
							<td>
								<input type="text" maxlength="17" class="form-control num8 balance-update" name="account" />
								<label for="new_justification">Budget justification</label>
								<textarea name="justification" id="new_justification" rows="3" maxlength="2000" cols="35" class="form-control balance-update"></textarea>
							</td>
							<td>
								<input type="text" name="account_approver" class="form-control" />
							</td>
							<td class="text-right text-nowrap">
								<span class="input-group">
									<span class="input-group-addon"><span class="input-group-text">$</span></span>
									<input type="text" size="8" name="account_amount" class="form-control balance-update" value="0.00" />
								</span>
							</td>
							@if ($canEdit)
								<td>
									<a href="{{ route('site.orders.read', ['id' => $order->id]) }}" title="Add account" class="btn btn-success account-add">
										<i class="fa fa-plus" aria-hidden="true"></i><span class="sr-only">Add account</span>
									</a>
								</td>
							@endif
						</tr> -->
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="4">
								<strong>{{ trans('orders::orders.balance remaining') }}</strong>
								<a href="#help2" class="help">
									<i id="balance_error" class="fa fa-exclamation-triange stash"></i><span class="sr-only">Balance should be $0.00 before saving changes.</span>
								</a>
							</td>
							<td class="text-right orderprice">
								{{ config('orders.currency', '$') }} <span id="balance">{{ number_format($order->total - $total) }}</span>
							</td>
							<?php /*@if ($canEdit && $order->status != 'canceled' && $order->status != 'complete')
								<td>
									<a href="{{ route('site.orders.read', ['id' => $order->id]) }}" title="Add account" class="btn btn-success account-add">
										<i class="fa fa-plus" aria-hidden="true"></i><span class="sr-only">Add account</span>
									</a>
								</td>
							@endif*/ ?>
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
		</div>

		<div class="panel panel-default card">
			<div class="panel-heading card-header">
				@if ($canEdit)
					<div class="row">
						<div class="col-md-6">
							<h3 class="panel-title card-title">
								{{ trans('orders::orders.notes') }}
								<a href="#help1" class="help icn tip"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</a>
							</h3>
						</div>
						<div class="col-md-6 text-right">
							<a href="{{ route('site.orders.read', ['id' => $order->id, 'edit' => 'usernotes']) }}" class="edit-property" data-prop="usernotes" data-value="<?php echo $order->id; ?>">
								<i class="fa fa-pencil" id="IMG_<?php echo $order->id; ?>_usernotes"></i><span class="sr-only">Edit</span>
							</a>
							<a href="{{ route('site.orders.read', ['id' => $order->id]) }}" id="CANCEL_<?php echo $order->id; ?>_usernotes" class="stash cancel-edit-property" data-prop="usernotes" data-value="<?php echo $order->id; ?>">
								<i class="fa fa-ban"></i><span class="sr-only">Cancel</span>
							</a>
						</div>
					</div>
				@else
					<h3 class="panel-title card-title">
						{{ trans('orders::orders.notes') }}
						<a href="#help1" class="help icn tip"><i class="fa fa-question-circle" aria-hidden="true"></i> Help</a>
					</h3>
				@endif
			</div>
			<div class="panel-body card-body">
				<div id="help1" title="Order Notes" class="dialog dialog-help">
					<p>Use this section to leave any special instructions, extra contact information, or any other notes for this order. ITaP and your business office staff will be able to view these notes.</p>
				</div>

				<p class="ordernotes">
					<span id="SPAN_<?php echo $order->id; ?>_usernotes">{!! $order->usernotes ? nl2br($order->usernotes) : '<span class="none">' . trans('global.none') . '</span>' !!}</span>

					@if ($canEdit)
						<label for="INPUT_<?php echo $order->id; ?>_usernotes" class="sr-only">{{ trans('orders::orders.user notes') }}:</label>
						<textarea name="fields[usernotes]" maxlength="2000" cols="80" rows="10" class="form-control stash" id="INPUT_<?php echo $order->id; ?>_usernotes">{{ $order->usernotes }}</textarea>
					@endif
				</p>
			</div>
		</div>

		@if (auth()->user()->can('manage orders'))
			<div class="panel panel-default card">
				<div class="panel-heading card-header">
					<div class="row">
						<div class="col-md-6">
							<h3 class="panel-title card-title">{{ trans('orders::orders.staff notes') }}</h3>
						</div>
						<div class="col-md-6 text-right">
							<a href="{{ route('site.orders.read', ['id' => $order->id, 'edit' => 'usernotes']) }}" class="edit-property" data-prop="staffnotes" data-value="<?php echo $order->id; ?>">
								<i class="fa fa-pencil" id="IMG_<?php echo $order->id; ?>_staffnotes"></i><span class="sr-only">Edit</span>
							</a>
							<a href="{{ route('site.orders.read', ['id' => $order->id]) }}" id="CANCEL_<?php echo $order->id; ?>_staffnotes" class="stash cancel-edit-property" data-prop="staffnotes" data-value="<?php echo $order->id; ?>">
								<i class="fa fa-ban"></i><span class="sr-only">Cancel</span>
							</a>
						</div>
					</div>
				</div>
				<div class="panel-body card-body">
					<p class="ordernotes">
						<span id="SPAN_<?php echo $order->id; ?>_staffnotes">{!! $order->staffnotes ? nl2br($order->staffnotes) : '<span class="none">' . trans('global.none') . '</span>' !!}</span>

						<label for="INPUT_<?php echo $order->id; ?>_staffnotes" class="sr-only">{{ trans('orders::orders.staff notes') }}:</label>
						<textarea name="fields[staffnotes]" maxlength="2000" cols="80" rows="10" class="form-control stash" id="INPUT_<?php echo $order->id; ?>_staffnotes">{{ $order->staffnotes }}</textarea>
					</p>
				</div>
			</div>
		@endif

		@if ($order->id)
			<div id="order-history">
				<div class="data-wrap">
					<h3>{{ trans('history::history.history') }}</h3>
					<ul class="entry-log">
						<?php
						if (count($history)):
							$history->sortByDesc('created_at');

							foreach ($history as $action):
								$actor = trans('global.unknown');

								if ($action->user):
									$actor = e($action->user->name);
								endif;

								$created = $action->created_at && $action->created_at != '0000-00-00 00:00:00' ? $action->created_at : trans('global.unknown');

								$fields = array_keys(get_object_vars($action->new));
								foreach ($fields as $i => $k)
								{
									if (in_array($k, ['created_at', 'updated_at', 'deleted_at']))
									{
										unset($fields[$i]);
									}
								}
								$old = Carbon\Carbon::now()->subDays(2); //->toDateTimeString();
								?>
								<li>
									<span class="entry-log-action">{{ trans('history::history.action ' . $action->action, ['user' => $actor, 'entity' => 'menu']) }}</span><br />
									<time datetime="{{ $action->created_at }}" class="entry-log-date">
										@if ($action->created_at < $old)
											{{ $action->created_at->format('d M Y') }}
										@else
											{{ $action->created_at->diffForHumans() }}
										@endif
									</time><br />
									@if ($action->action == 'updated')
										<span class="entry-diff">Changed fields: <?php echo implode(', ', $fields); ?></span>
									@endif
								</li>
								<?php
							endforeach;
						else:
							?>
							<li>
								<span class="entry-diff">{{ trans('history::history.none found') }}</span>
							</li>
							<?php
						endif;
						?>
					</ul>
				</div>
			</div><!-- / #order-history -->
		@endif

		@csrf
	</form>
</div>
@stop