@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@stop

@php
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
{!! config('orders.name') !!}: {{ $row->id ? '#' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.orders.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">

@if ($row->id)
	<div class="tabs">
		<ul>
			<li><a href="#order-info">Order</a></li>
			<li><a href="#order-history">History</a></li>
		</ul>
		<div id="order-info">
@endif
	<div class="row">
		<div class="col col-md-9">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<!-- <div class="form-group{{ $errors->has('id') ? ' has-error' : '' }}">
					<label for="field-id">{{ trans('orders::orders.id') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[id]" id="field-id" class="form-control required" value="{{ $row->id }}" />
				</div> -->
				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('userid') ? ' has-error' : '' }}">
							<label for="field-userid">{{ trans('orders::orders.userid') }}:</label>
							<span class="input-group input-user">
								<input type="text" name="fields[userid]" id="field-userid" class="form-control" value="{{ $row->userid }}" />
								<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
							</span>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('groupid') ? ' has-error' : '' }}">
							<label for="field-groupid">{{ trans('orders::orders.groupid') }}:</label>
							<span class="input-group input-user">
								<input type="text" name="fields[groupid]" id="field-groupid" class="form-control" value="{{ $row->groupid }}" />
								<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
							</span>
						</div>
					</div>
				</div>

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

				<div class="form-group{{ $errors->has('usernotes') ? ' has-error' : '' }}">
					<label for="field-usernotes">{{ trans('orders::orders.user notes') }}:</label>
					<textarea name="fields[usernotes]" id="field-usernotes" class="form-control" cols="30" rows="5">{{ $row->usernotes }}</textarea>
				</div>

				<div class="form-group{{ $errors->has('staffnotes') ? ' has-error' : '' }}">
					<label for="field-staffnotes">{{ trans('orders::orders.staff notes') }}:</label>
					<textarea name="fields[staffnotes]" id="field-staffnotes" class="form-control" cols="30" rows="5">{{ $row->staffnotes }}</textarea>
				</div>
			</fieldset>

			<?php $history = $row->history()->orderBy('created_at', 'desc')->get(); ?>

			<fieldset class="adminform">
				<legend>{{ trans('orders::orders.items') }}</legend>

				<table>
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
						<?php foreach ($row->items as $item) {
							$history = $history->merge($item->history()->orderBy('created_at', 'desc')->get());
							?>
							<tr>
								<td>
									@if (!$item->fulfilled || $item->fulfilled == '0000-00-00 00:00:00')
										@if ($row->status != 'canceled' && $row->status == 'pending_fulfillment')
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
								<td class="text-right">{{ config('orders.currency', '$') }} {{ number_format($item->price * $item->quantity) }}</td>
							</tr>
						<?php } ?>
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="5">{{ trans('orders::orders.order total') }}</td>
							<td class="text-right orderprice">{{ config('orders.currency', '$') }} <span id="ordertotal">{{ number_format($row->total) }}</span></td>
						</tr>
					</tfoot>
				</table>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('orders::orders.payment information') }}</legend>

				<table>
					<thead>
						<tr>
							<th scope="col">{{ trans('orders::orders.status') }}</th>
							<th scope="col">{{ trans('orders::orders.account') }}</th>
							<th scope="col">{{ trans('orders::orders.account approver') }}</th>
							<th scope="col" class="text-right">{{ trans('orders::orders.quantity') }}</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total = 0;
						foreach ($row->accounts as $account) {
							$history = $history->merge($account->history()->orderBy('created_at', 'desc')->get());

							$s = $account->status;

							$text = trans('global.unknown');

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
								<td><span class="order-status {{ $s }}">{{ $text }}</span></td>
								<td>{{ $account->purchaseio ? 'Internal order:' . $account->purchaseio : 'WBSE: ' . $account->purchasewbse }} {{ $account->budgetjustification }}</td>
								<td>{{ $account->approver ? $account->approver->name : trans('global.unknown') }}</td>
								<td class="text-right">{{ config('orders.currency', '$') }} {{ number_format($account->amount) }}</td>
							</tr>
						<?php } ?>
					</tbody>
					<tfoot>
						<tr>
							<td class="text-right" colspan="3">{{ trans('orders::orders.balance remaining') }}</td>
							<td class="text-right orderprice">{{ config('orders.currency', '$') }} <span id="ordertotal">{{ number_format($row->total - $total) }}</span></td>
						</tr>
					</tfoot>
				</table>
			</fieldset>
		</div>
		<div class="col col-md-3">
			<table class="meta">
				<tbody>
					<tr>
						<th scope="row"><?php echo trans('orders::orders.id'); ?>:</th>
						<td>
							<?php echo e($row->id); ?>
							<input type="hidden" name="id" id="field-id" value="<?php echo e($row->id); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo trans('orders::orders.created'); ?>:</th>
						<td>
							<?php if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00'): ?>
								<?php echo e($row->datetimecreated); ?>
							<?php else: ?>
								<?php echo trans('global.unknown'); ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php if ($row->getOriginal('datetimeremoved') && $row->getOriginal('datetimeremoved') != '0000-00-00 00:00:00'): ?>
						<tr>
							<th scope="row"><?php echo trans('orders::orders.canceled'); ?>:</th>
							<td>
								<?php echo e($row->datetimeremoved); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
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
@stop