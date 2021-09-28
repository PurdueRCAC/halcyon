@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/orders/js/admin.js?v=' . filemtime(public_path() . '/modules/orders/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('admin.orders.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit orders'))
		{!! Toolbar::save(route('admin.orders.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.orders.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('orders::orders.module name') }}: {{ $row->id ? '#' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.orders.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

@if ($row->id)
	<div class="tabs">
		<ul>
			<li><a href="#order-info">Order</a></li>
			<li><a href="#order-history">History</a></li>
		</ul>
		<div id="order-info">
@endif
	<div class="row">
		<div class="col col-md-12">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<!-- <div class="form-group{{ $errors->has('id') ? ' has-error' : '' }}">
					<label for="field-id">{{ trans('orders::orders.id') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[id]" id="field-id" class="form-control required" value="{{ $row->id }}" />
				</div> -->
				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('userid') ? ' has-error' : '' }}">
							<label for="field-userid">{{ trans('orders::orders.user') }}:</label>
							<span class="input-group input-user">
								<?php
								$user = ($row->user ? $row->user->name . ' (' . $row->user->username . ')' : trans('global.unknown')) . ':' . $row->userid;
								?>
								<input type="text" name="fields[userid]" id="field-userid" class="form-control form-users" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ $user }}" />
								<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
							</span>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('groupid') ? ' has-error' : '' }}">
							<label for="field-groupid">{{ trans('orders::orders.group') }}:</label>
							<span class="input-group input-user">
								<input type="text" name="fields[groupid]" id="field-groupid" class="form-control form-groups" data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" data-multiple="false" value="{{ $row->group ? $row->group->name . ':' . $row->groupid : '' }}" />
								<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
							</span>
						</div>
					</div>
				</div>

			@if ($row->id)
				<div class="form-group">
					<label for="field-state">{{ trans('global.state') }}:</label>
					<select class="form-control" name="state" id="field-state">
						<!-- <option value="pending_payment"<?php if ($row->status == 'pending payment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending payment') }}</option>
						<option value="pending_boassignment"<?php if ($row->status == 'pending boassignment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending boassignment') }}</option>
						<option value="pending_approval"<?php if ($row->status == 'pending approval'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending approval') }}</option>
						<option value="pending_collection"<?php if ($row->status == 'pending collection'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending collection') }}</option>
						<option value="pending_fulfillment"<?php if ($row->status == 'pending fulfillment'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending fulfillment') }}</option> -->
						<option value="pending"<?php if ($row->status != 'complete' && $row->status != 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.pending') }}</option>
						<option value="complete"<?php if ($row->status == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.complete') }}</option>
						<option value="canceled"<?php if ($row->status == 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.canceled') }}</option>
					</select>
				</div>
			@endif

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('usernotes') ? ' has-error' : '' }}">
							<label for="field-usernotes">{{ trans('orders::orders.user notes') }}:</label>
							<textarea name="fields[usernotes]" id="field-usernotes" class="form-control" cols="30" rows="5">{{ $row->usernotes }}</textarea>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('staffnotes') ? ' has-error' : '' }}">
							<label for="field-staffnotes">{{ trans('orders::orders.staff notes') }}:</label>
							<textarea name="fields[staffnotes]" id="field-staffnotes" class="form-control" cols="30" rows="5">{{ $row->staffnotes }}</textarea>
						</div>
					</div>
				</div>
			</fieldset>

			<?php $history = $row->history()->orderBy('created_at', 'desc')->get(); ?>

			<fieldset class="adminform">
				<legend>{{ trans('orders::orders.items') }}</legend>

				<table class="table table-hover">
					<thead>
						<tr>
							<th scope="col" colspan="2">{{ trans('orders::orders.status') }}</th>
							<th scope="col">{{ trans('orders::orders.item') }}</th>
							<th scope="col" class="text-right">{{ trans('orders::orders.quantity') }}</th>
							<th scope="col" class="text-right">{{ trans('orders::orders.price') }}</th>
							<th scope="col" class="text-right">{{ trans('orders::orders.total') }}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($row->items as $item) {
							$history = $history->merge($item->history()->orderBy('created_at', 'desc')->get());
							?>
							<tr>
								<td>
									@if (!$item->fulfilled)
										@if ($row->status == 'pending_fulfillment')
											<input type="button" value="Fulfill" class="btn btn-sm btn-secondary order-fulfill" id="button_<?php echo $item->id; ?>" data-id="<?php echo $item->id; ?>" />
										</td>
										<td>
											<span class="order-status {{ $row->status }}" id="status_<?php echo $item->id; ?>">{{ trans('orders:orders.pending_fulfillment') }}</span>
										@else
												<span class="order-status {{ $row->status }}">
													@if ($row->status == 'pending_fulfillment')
														{{ trans('orders::orders.' . $row->status) }}
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
								<td class="text-right">
									@if ($item->origorderitemid)
										@if ($item->timeperiodcount > 1)
											{{ trans('orders::orders.quantity for', ['quantity' => $item->quantity, 'count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->plural]) }}
										@else
											{{ trans('orders::orders.quantity for', ['quantity' => $item->quantity, 'count' => $item->timeperiodcount, 'timeperiod' => $item->product->timeperiod->singular]) }}
										@endif
									@else
										@if (!$item->fulfilled)
											<input type="number" class="form-control quantity-control" data-unitprice="{{ $item->price }}" name="quantity{{ $item->id }}" size="5" min="1" max="999" value="{{ $item->quantity }}" />
										@else
											{{ $item->quantity }}
										@endif
									@endif
								</td>
								<td class="text-right">
									{{ config('orders.currency', '$') }} {{ $row->formatNumber($item->price) }}<br /><span class="text-muted">per&nbsp;{{ $item->product->unit }}</span>
								</td>
								<td class="text-right text-nowrap">
									{{ config('orders.currency', '$') }} <span name="itemtotal">{{ $row->formatNumber($item->price * $item->quantity) }}</span>
								</td>
								<td>
									@if ($row->status != 'canceled' && $row->status != 'complete')
									<a href="#item{{ $row->id }}" class="btn btn-secondary"><span class="glyph icon-edit">{{ trans('global.edit') }}</span></a>
									<div id="item{{ $row->id }}" class="dialog hide" title="Item #{{ $row->id }}">
										<div class="form-group">
										</div>
									</div>
									@endif
								</td>
							</tr>
						<?php } ?>
						@if ($row->status != 'canceled' && $row->status != 'complete')
						<tr>
							<td></td>
							<td></td>
							<td>
								<select name="orderproductid" class="form-control basic-single">
									<option value="">- Select product -</option>
									@foreach ($categories as $category)
										<optgroup label="{{ $category->name }}">
											@foreach ($category->products()->orderBy('sequence', 'asc')->get() as $product)
												<option value="{{ $product->id }}" data-unitprice="{{ $product->unitprice }}" data-unit="{{ $product->unit }}">{{ $product->name }}</option>
											@endforeach
										</optgroup>
									@endforeach
								</select>
							</td>
							<td>
								<input type="number" class="form-control quantity-control" name="quantity" data-unitprice="" size="5" min="1" max="999" value="0" />
							</td>
							<td class="text-right text-nowrap">
								<!-- <input type="text" class="form-control-plaintext unitprice" value="{{ config('orders.currency', '$') }} 0.00" /> -->
								{{ config('orders.currency', '$') }} <span class="unitprice">0.00</span><br /><span class="text-muted">per&nbsp;<span class="unit">--</span></span>
							</td>
							<td class="text-right text-nowrap">
								{{ config('orders.currency', '$') }} <span class="order-total">0.00</span>
							</td>
							<td>
								<a href="#" class="btn btn-success"><span class="glyph icon-plus">{{ trans('global.add') }}</span></a>
							</td>
						</tr>
						@endif
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="5">{{ trans('orders::orders.order total') }}</td>
							<td class="text-right text-nowrap orderprice">{{ config('orders.currency', '$') }} <span id="ordertotal">{{ $row->formatNumber($row->total) }}</span></td>
							<td></td>
						</tr>
					</tfoot>
				</table>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('orders::orders.payment information') }}</legend>

				<table class="table table-hover">
					<caption class="sr-only">{{ trans('orders::orders.payment information') }}</caption>
					<thead>
						<tr>
							<th scope="col">{{ trans('orders::orders.status') }}</th>
							<th scope="col">{{ trans('orders::orders.account') }}</th>
							<th scope="col">{{ trans('orders::orders.account approver') }}</th>
							<th scope="col" class="text-right text-nowrap">{{ trans('orders::orders.amount') }}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total = 0;
						foreach ($row->accounts as $account) {
							$history = $history->merge($account->history()->orderBy('created_at', 'desc')->get());

							$s = $account->status;

							$text = '<span class="unknown">' . trans('global.unknown') . '</span>';

							if ($s == 'canceled')
							{
								$text = trans('orders::orders.' . $row->status);
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
								if ($row->status != 'pending_collection')
								{
									$text = trans('orders::orders.approved on date', ['date' => date("M&\\nb\s\p;j,&\\nb\s\p;Y", strtotime($account->datetimeapproved))]);
								}
								else
								{
									$text = trans('orders::orders.' . $s);
								}
							}
							else if ($s == "PAID")
							{
								$text = trans('orders::orders.paid on date', ['date' => date("M&\\nb\s\p;j,&\\nb\s\p;Y", strtotime($account->docdate)), 'docid' => $account->docid]);
							}

							$total += $account->amount;
							?>
							<tr>
								<td><span class="order-status {{ $s }}">{!! $text !!}</span></td>
								<td>
									@if ($account->purchaseio)
										<strong>Internal order:</strong> {{$account->purchaseio}}
									@else
										<strong>WBSE:</strong> {{ $account->purchasewbse }}
									@endif
									<p class="form-text text-muted">{{ $account->budgetjustification }}</p>
								</td>
								<td>{{ $account->approver ? $account->approver->name : trans('global.unknown') }}</td>
								<td class="text-right text-nowrap">{{ config('orders.currency', '$') }} {{ number_format($account->amount) }}</td>
								<td>
									@if ($row->status != 'canceled' && $row->status != 'complete')
									<a href="#" class="btn btn-danger"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></a>
									@endif
								</td>
							</tr>
						<?php } ?>
						@if ($row->status != 'canceled' && $row->status != 'complete')
						<tr>
							<td></td>
							<td>
								<div class="form-group">
									<label for="" class="sr-only">Account</label>
									<input type="text" class="form-control" />
								</div>
								<div class="form-group">
									<label for="">
									Budget justification:
									<a href="#help2" class="help">
										<span class="glyph icon-help">Budget justification required</span>
									</a>
								</label>
								<textarea name="justification" rows="4" maxlength="2000" cols="40" class="form-control balance-update"></textarea>
								</div>
							</td>
							<td>
								<span class="input-group input-user">
									<input type="text" name="approverid" id="approverid" class="form-control form-users" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" value="" />
									<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
								</span>
							</td>
							<td class="text-right">
								<input type="text" class="form-control" value="0.00" size="9" />
							</td>
							<td>
								<a href="#" class="btn btn-success"><span class="glyph icon-plus">{{ trans('global.add') }}</span></a>
							</td>
						</tr>
						@endif
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="3">{{ trans('orders::orders.balance remaining') }}</td>
							<td class="text-right text-nowrap orderprice">{{ config('orders.currency', '$') }} <span id="ordertotal">{{ $row->formatNumber($row->total - $total) }}</span></td>
							<td></td>
						</tr>
					</tfoot>
				</table>
			</fieldset>
		<!-- </div>
		<div class="col col-md-3">
			<table class="meta">
				<tbody>
					<tr>
						<th scope="row"><?php echo trans('orders::orders.id'); ?>:</th>
						<td>
							<?php echo e($row->id); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo trans('orders::orders.created'); ?>:</th>
						<td>
							<?php if ($row->datetimecreated): ?>
								<?php echo e($row->datetimecreated); ?>
							<?php else: ?>
								<?php echo trans('global.unknown'); ?>
							<?php endif; ?>
						</td>
					</tr>
					@if ($row->trashed())
						<tr>
							<th scope="row">{{ trans('orders::orders.canceled') }}:</th>
							<td>
								{{ $row->datetimeremoved }}
							</td>
						</tr>
					@endif
				</tbody>
			</table> -->
		</div>
	</div>
@if ($row->id)
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

								$created = $action->created_at ? $action->created_at : trans('global.unknown');

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

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
	@csrf
</form>
@stop