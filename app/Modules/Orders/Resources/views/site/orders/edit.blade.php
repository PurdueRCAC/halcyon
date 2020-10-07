@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/orders/js/orders.js') }}"></script>
<script>
$(document).ready(function() {
	/*$('.edit-property').on('click', function(e){
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
	});*/

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
				response($.map(data.groups, function (el) {
					return {
						label: el.name,
						id: el.id
					};
				}));
			});
		};
	};
	$("#search_group").autocomplete({
		source: autocompleteGroup('{{ route("api.groups.index") }}?search=%s'),
		dataName: 'groups',
		height: 150,
		delay: 100,
		minLength: 2,
		filter: /^[a-z0-9\-_ \.,\'\(\)]+$/i
	});

	var autocompleteName = function(url) {
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
	$("#search_user").on("autocompleteselect", SearchEventHandler);
});
</script>
@stop

@php
$canEdit = (auth()->user()->can('edit orders') || (auth()->user()->can('edit.own orders') && auth()->user()->id == $order->submitteruserid));
$myorder = (auth()->user()->id == $order->submitteruserid);
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="dropdown-menu">
		<li class="item-10"><a href="orders">Orders</a></li>
		<li class="item-19"><a href="orders/products">Products</a></li>
		<li class="item-15"><a href="orders/categories">Categories</a></li>
	</ul>
</div>
<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

	<h2>{{ trans('orders::orders.orders') }}: {{ $order->id ? '#' . $order->id : 'Create' }}</h2>

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

	<div class="orderstatusblocks">
		<div class="orderstatus">
			<span class="orderstatus {{ $order->status }}">{{ trans('orders::orders.' . $order->status) }}</span>
			<a href="#orderheaderpopup" class="order-status icn tip" title="Help">
				<i class="fa fa-question-circle" aria-hidden="true"></i> Help
			</a>
		</div><!-- / .orderstatus -->

		<div class="orderheaderitem">
			{{ $order->datetimecreated->format('F j, Y g:ia') }}
		</div><!-- / .orderheaderitem -->

		<div>
			Submitted by 
			<?php
			if (auth()->user()->can('manage orders'))
			{
				echo '<a style="font-weight: bold;" href="/admin/user/?u=' . $order->submitteruserid . '">' . $order->submitter->name . '</a>';
			}
			else
			{
				echo $order->submitter->name;
			}
			?>
		</div>

		<?php
		if (($order->status == 'PENDING_PAYMENT'
			 || $order->status == 'PENDING_BOASSIGNMENT'
			 || (
				($order->status == 'PENDING_APPROVAL'
				|| $order->status == 'PENDING_FULFILLMENT'
				|| $order->status == 'PENDING_COLLECTION'
				) && $superuser)
			|| ($order->status == 'PENDING_APPROVAL' && !$myorder))
		 && (auth()->user()->can('manage orders') || $myorder)) { ?>
			<div style="clear:right">
				<?php if ($order->status == "PENDING_PAYMENT" && auth()->user()->can('manage orders')) { ?>
					<input type="button" id="remindorder" data-id="<?php echo $order->id; ?>" value="Remind Customer" />
					<span id="remindorderspan"></span>
				<?php } ?>

				<input type="button" id="cancelorder" value="Cancel Order" />
			</div>
		<?php } ?>
	</div><!-- / .orderstatusblock -->

	<form action="{{ route('site.orders.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

		<input type="hidden" name="id" id="order" value="{{ $order->id }}" />

		<div class="panel panel-default card">
			<div class="panel-heading card-header">
				<h3 class="panel-title card-title">{{ trans('global.details') }}</h3>
			</div>
			<div class="panel-body card-body">

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('userid') ? ' has-error' : '' }}">
							<label for="field-userid">{{ trans('orders::orders.user') }}:</label>
							@if (auth()->user()->can('manage orders'))
								<span class="input-group input-user">
									<input type="text" name="userid" id="userid" class="form-control" value="{{ ($order->user ? $order->user->name : trans('global.unknown')) . ':' . $order->userid }}" placeholder="{{ trans('global.none') }}" />
									<span class="input-group-addon"><span class="input-group-text fa fa-user" aria-hidden="true"></span></span>
								</span>
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
							<label for="field-groupid">{{ trans('orders::orders.group') }}:</label>
							<span class="input-group input-user">
								<input type="text" name="fields[groupid]" id="field-groupid" class="form-control" value="{{ $order->group ? $order->group->name . ':' . $order->groupid : '' }}" placeholder="{{ trans('global.none') }}" />
								<span class="input-group-addon"><span class="input-group-text fa fa-users" aria-hidden="true"></span></span>
							</span>
							@if ($order->groupid)
								{{ $order->group->name }}
							@else
								<span class="none">{{ trans('global.none') }}</span>
							@endif
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-state">{{ trans('global.state') }}:</label>
					<select class="form-control" name="state" id="field-state">
						<option value="pending_payment"<?php if ($order->status == 'pending payment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending payment') }}</option>
						<option value="pending_boassignment"<?php if ($order->status == 'pending boassignment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending boassignment') }}</option>
						<option value="pending_approval"<?php if ($order->status == 'pending approval'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending approval') }}</option>
						<option value="pending_collection"<?php if ($order->status == 'pending collection'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending collection') }}</option>
						<option value="pending_fulfillment"<?php if ($order->status == 'pending fulfillment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending fulfillment') }}</option>
						<!-- <option value="pending"<?php if ($order->status != 'complete' && $order->status != 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending') }}</option> -->
						<option value="complete"<?php if ($order->status == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.complete') }}</option>
						<option value="canceled"<?php if ($order->status == 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.canceled') }}</option>
					</select>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('usernotes') ? ' has-error' : '' }}">
							<label for="field-usernotes">{{ trans('orders::orders.user notes') }}:</label>
							<textarea name="fields[usernotes]" id="field-usernotes" class="form-control" cols="30" rows="5">{{ $order->usernotes }}</textarea>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('staffnotes') ? ' has-error' : '' }}">
							<label for="field-staffnotes">{{ trans('orders::orders.staff notes') }}:</label>
							<textarea name="fields[staffnotes]" id="field-staffnotes" class="form-control" cols="30" rows="5">{{ $order->staffnotes }}</textarea>
						</div>
					</div>
				</div>
			</div>
		</div>

			<?php $history = $order->history()->orderBy('created_at', 'desc')->get(); ?>

			<div class="panel panel-default card">
				<div class="panel-heading card-header">
					<h3 class="panel-title card-title">{{ trans('orders::orders.items') }}</h3>
				</div>
				<div class="panel-body card-body">
				<table class="table">
					<thead>
						<tr>
							<th scope="col" colspan="2">{{ trans('orders::orders.status') }}</th>
							<th scope="col">{{ trans('orders::orders.item') }}</th>
							<th scope="col" class="text-right">{{ trans('orders::orders.quantity') }}</th>
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
									@if (!$item->fulfilled || $item->fulfilled == '0000-00-00 00:00:00')
										@if ($order->status != 'canceled' && $order->status == 'pending_fulfillment')
											<input type="button" value="Fulfill" class="btn btn-sm btn-secondary order-fulfill" id="button_<?php echo $item->id; ?>" data-id="<?php echo $item->id; ?>" />
										</td>
										<td>
											<span class="order-status {{ $order->status }}" id="status_<?php echo $item->id; ?>">{{ trans('orders:orders.pending_fulfillment') }}</span>
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
											<span class="order-status complete">{{ trans('orders:orders.complete') }}</span>
										</td>
										<td>
											{{ Carbon\Carbon::parse($item->fulfilled)->format('M j, Y') }}
									@endif
								</td>
								<td>
									{{ $item->product->name }}
									<p class="form-text">
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
								<td class="text-right">
									@if ($item->origorderitemid)
										@if ($item->timeperiodcount > 1)
											{{ trans('orders::orders.quantity for', ['quantity' => $item->quantity, 'count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->plural]) }}
										@else
											{{ trans('orders::orders.quantity for', ['quantity' => $item->quantity, 'count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->singular]) }}
										@endif
									@else
										{{ $item->quantity }}
									@endif
								</td>
								<td class="text-right">{{ config('orders.currency', '$') }} {{ number_format($item->price) }} / {{ $item->product->unit }}</td>
								<td class="text-right text-nowrap">{{ config('orders.currency', '$') }} {{ number_format($item->price * $item->quantity) }}</td>
							</tr>
						<?php } ?>
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="5">{{ trans('orders::orders.order total') }}</td>
							<td class="text-right text-nowrap orderprice">{{ config('orders.currency', '$') }} <span id="ordertotal">{{ number_format($order->total) }}</span></td>
						</tr>
					</tfoot>
				</table>
				</div>
			</div>

			<!-- <div class="panel panel-default card">
				<div class="panel-heading card-header">
					<h3 class="panel-title card-title">{{ trans('orders::orders.payment information') }}</h3>
				</div>
				<div class="panel-body card-body"> -->
				<h3>{{ trans('orders::orders.payment information') }}</h3>

				<table class="table">
					<caption class="sr-only">{{ trans('orders::orders.payment information') }}</caption>
					<thead>
						<tr>
							<th scope="col">{{ trans('orders::orders.status') }}</th>
							<th scope="col">{{ trans('orders::orders.account') }}</th>
							<th scope="col">{{ trans('orders::orders.account approver') }}</th>
							<th scope="col" class="text-right">{{ trans('orders::orders.amount') }}</th>
							@if ($canEdit)
								<th></th>
							@endif
						</tr>
					</thead>
					<tbody>
						<?php
						$total = 0;
						foreach ($order->accounts as $account) {
							$history = $history->merge($account->history()->orderBy('created_at', 'desc')->get());

							$s = $account->status;

							$text = trans('global.unknown');

							if ($s == 'canceled')
							{
								$text = trans('orders::orders.' . $order->status);
							}
							else if ($s == 'denied')
							{
								$text = trans('orders::orders.' . $s);
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
							else if ($s == "PAID")
							{
								$text = trans('orders::orders.paid on date', ['date' => date("M j, Y", strtotime($account->docdate)), 'docid' => $account->docid]);
							}

							$total += $account->amount;
							?>
							<tr>
								<td>
									<span class="order-status {{ $s }}">{{ $text }}</span>
								</td>
								<td>
									{{ $account->purchaseio ? 'Internal order:' . $account->purchaseio : 'WBSE: ' . $account->purchasewbse }}
									@if ($account->budgetjustification)
										<p class="text-muted">{{ $account->budgetjustification }}</p>
									@endif
								</td>
								<td>
									@if ($account->approver)
										@if (auth()->user()->can('manage users'))
											<a href="{{ route('admin.users.edit', ['id' => $account->approver->id]) }}">
												<span name="approver_span">{{ $account->approver->name }} ({{ $account->approver->username }})</span>
											</a>
										@else
											<span name="approver_span">{{ $account->approver->name }}</span>
										@endif
									@else
										<span class="unknown">{{ trans('global.unknown') }}</span>
									@endif
								</td>
								<td class="text-right">
									{{ config('orders.currency', '$') }} <span name="account_amount_span">{{ number_format($account->amount) }}</span>
									<input type="text" class="stash balance-update" size="8" name="account_amount" value="{{ $account->amount }}" />
								</td>
								@if ($canEdit)
									<td>
										<a href="{{ route('site.orders.read', ['id' => $order->id, 'remove' => $account->id]) }}" title="Remove account" class="account-remove" data-id="{{ $account->id }}">
											<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Remove account</span>
										</a>
									</td>
								@endif
							</tr>
						<?php } ?>
						<tr>
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
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="3">{{ trans('orders::orders.balance remaining') }}</td>
							<td class="text-right orderprice">{{ config('orders.currency', '$') }} <span id="ordertotal">{{ number_format($order->total - $total) }}</span></td>
							@if ($canEdit)
								<td>
									<a href="{{ route('site.orders.read', ['id' => $order->id]) }}" title="Add account" class="btn btn-success account-add">
										<i class="fa fa-plus" aria-hidden="true"></i><span class="sr-only">Add account</span>
									</a>
								</td>
							@endif
						</tr>
					</tfoot>
				</table>
		<!-- </div>
	</div> -->
	</div>
@if ($order->id)
		</div>
		<div id="order-history">
			
				<div class="data-wrap">
					<h4>{{ trans('history::history.history') }}</h4>
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
		</div>
	</div><!-- / .tabs -->
	@endif

	@csrf
</form>
</div>
@stop