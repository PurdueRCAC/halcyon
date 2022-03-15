@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ Module::asset('core:vendor/chartjs/Chart.css') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ Module::asset('core:vendor/chartjs/Chart.min.js') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
$(document).ready(function () {
	$('.items-toggle').on('click', function(e){
		e.preventDefault();
		$($(this).attr('href')).toggle('collapse');
	});

	$('.sparkline-chart').each(function (i, el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse($(el).attr('data-labels')),
				datasets: [
					{
						fill: false,
						data: JSON.parse($(el).attr('data-values'))
					}
				]
			},
			options: {
				responsive: false,
				animation: {
					duration: 0
				},
				legend: {
					display: false
				},
				elements: {
					line: {
						borderColor: '#0091EB',
						borderWidth: 1
					},
					point: {
						borderColor: '#0091EB'
					}
				},
				scales: {
					/*yAxes: [
						{
							display: false
						}
					],*/
					xAxes: [
						{
							display: false
						}
					]
				}
			}
		});
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('admin.orders.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete orders'))
		{!! Toolbar::deleteList('', route('admin.orders.delete')) !!}
	@endif

	{!!
		Toolbar::dropdown('export', trans('orders::orders.export'), [
			route('admin.orders.index', ['export' => 'only_main']) => trans('orders::orders.export summary'),
			route('admin.orders.index', ['export' => 'items']) => trans('orders::orders.export items'),
			route('admin.orders.index', ['export' => 'accounts']) => trans('orders::orders.export accounts')
		]);
		Toolbar::spacer();
	!!}

	@if (auth()->user()->can('create orders'))
		{!! Toolbar::addNew(route('admin.orders.create')) !!}
	@endif

	@if (auth()->user()->can('admin orders'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('orders');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('orders.name') !!}
@stop

@section('panel')
	<h2 class="sr-only">Order Stats</h2>

	<div class="car mb-3">
		<form action="{{ route('admin.orders.index') }}" method="post" name="statsForm">
			<label for="timeframe" class="sr-only">Stats for</label>
			<select name="timeframe" id="timeframe" class="form-control filter-submit">
				<option value="7"<?php if ($filters['timeframe'] == 7) { echo ' selected="slected"'; } ?>>Past 7 days</option>
				<option value="14"<?php if ($filters['timeframe'] == 14) { echo ' selected="slected"'; } ?>>Past 14 days</option>
				<option value="30"<?php if ($filters['timeframe'] == 30) { echo ' selected="slected"'; } ?>>Past 30 days</option>
			</select>

			@csrf
		</form>
	</div>

	<?php
	$start = Carbon\Carbon::now()->modify('-' . $filters['timeframe'] . ' days');
	$stop  = Carbon\Carbon::now()->modify('+1 day');
	$stats = App\Modules\Orders\Models\Order::stats($start, $stop);
	?>
	<h3 class="sr-only">Overview</h3>

	<div class="card">
		<div class="card-body">
			<div class="stat-block text-info">
				<span class="fa fa-shopping-cart display-4 float-left" aria-hidden="true"></span>
				<span class="value">{{ number_format($stats['submitted']) }}</span><br />
				<span class="key">{{ trans('orders::orders.submitted') }}</span>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<a href="{{ route('admin.orders.index', ['status' => 'canceled', 'start' => $start->format('Y-m-d')]) }}" class="stat-block text-danger">
				<span class="icon-alert-triangle display-4 float-left" aria-hidden="true"></span>
				<span class="value">{{ number_format($stats['canceled']) }}</span><br />
				<span class="key">{{ trans('orders::orders.canceled') }}</span>
			</a>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<a href="{{ route('admin.orders.index', ['status' => 'complete', 'start' => $start->format('Y-m-d')]) }}" class="stat-block text-success">
				<span class="fa fa-check display-4 float-left" aria-hidden="true"></span>
				<span class="value">{{ number_format($stats['fulfilled']) }}</span><br />
				<span class="key">{{ trans('orders::orders.fulfilled') }}</span>
			</a>
		</div>
	</div>

	<div class="car mb-3">
		<h4>New orders</h4>
		<canvas id="sparkline" class="sparkline-chart" width="275" height="150" data-labels="{{ json_encode(array_keys($stats['daily'])) }}" data-values="{{ json_encode(array_values($stats['daily'])) }}">
			@foreach ($stats['daily'] as $day => $val)
				{{ $day }}: $val<br />
			@endforeach
		</canvas>
	</div>

	<div class="car mb-3">
		<h4>Avg. Time From Order Submission</h4>
		<div class="order-process">
			<ol>
				<li>
					<strong>Payment information</strong><br />
					<span class="text-muted">{{ $stats['steps']['payment']['average'] }}</span>
				</li>
				<li>
					<strong>Approval by business office</strong><br />
					<span class="text-muted">{{ $stats['steps']['approval']['average'] }}</span>
				</li>
				<li>
					<strong>Fulfillment</strong><br />
					<span class="text-muted">{{ $stats['steps']['fulfilled']['average'] }}</span>
					<span class="text-muted float-right">{{ $stats['steps']['completed']['average'] }}</span>
				</li>
			</ol>
		</div>
	</div>
@stop

@section('content')

@component('orders::admin.submenu')
	orders
@endcomponent

<form action="{{ route('admin.orders.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-3 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col col-md-9 text-right">
				<label class="sr-only" for="filter_status">{{ trans('orders::orders.status') }}</label>
				<select name="status" id="filter_status" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all statuses') }}</option>
					<option value="active"<?php if ($filters['status'] == 'active'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.active') }}</option>
					<option value="pending_payment"<?php if ($filters['status'] == 'pending_payment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_payment') }}</option>
					<option value="pending_boassignment"<?php if ($filters['status'] == 'pending_boassignment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_boassignment') }}</option>
					<option value="pending_approval"<?php if ($filters['status'] == 'pending_approval'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_approval') }}</option>
					<option value="pending_fulfillment"<?php if ($filters['status'] == 'pending_fulfillment'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_fulfillment') }}</option>
					<option value="pending_collection"<?php if ($filters['status'] == 'pending_collection'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('orders::orders.pending_collection') }}</option>
					<option value="complete"<?php if ($filters['status'] == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.complete') }}</option>
					<option value="canceled"<?php if ($filters['status'] == 'canceled'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.canceled') }}</option>
				</select>

				<div class="btn-group">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="fa fa-filter" aria-hidden="true"></span><span class="sr-only">Filters</span>
					</button>
					<div class="dropdown-menu dropdown-menu-right">
						<div class="px-4 py-3">
							<div class="form-group mb-3">
								<label class="sr-only" for="filter_category">{{ trans('orders::orders.category') }}</label>
								<select name="category" id="filter_category" class="form-control filter filter-submit">
									<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
									<?php foreach ($categories as $category): ?>
										<option value="<?php echo $category->id; ?>"<?php if ($filters['category'] == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="form-group mb-3">
								<label class="sr-only" for="filter_start">{{ trans('orders::orders.start date') }}</label>
								<span class="input-group">
									<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />
									<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
								</span>
							</div>

							<div class="form-group">
								<label class="sr-only" for="filter_end">{{ trans('orders::orders.end date') }}</label>
								<span class="input-group">
									<input type="text" name="end" id="filter_end" class="form-control date filter filter-submit" value="{{ $filters['end'] }}" placeholder="End date" />
									<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<thead>
			<tr>
				@if (auth()->user()->can('delete orders'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col">
					{!! Html::grid('sort', trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('orders::orders.created'), 'datetimecreated', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">{{ trans('orders::orders.status') }}</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('orders::orders.submitter'), 'userid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 numeric">
					{!! Html::grid('sort', trans('orders::orders.total'), 'ordertotal', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 numeric">
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete orders'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td>
					@if (auth()->user()->can('edit orders'))
						<a href="{{ route('admin.orders.edit', ['id' => $row->id]) }}">
							{{ $row->id }}
						</a>
					@else
						{{ $row->id }}
					@endif
				</td>
				<td class="priority-6">
					@if (auth()->user()->can('edit orders'))
						<a href="{{ route('admin.orders.edit', ['id' => $row->id]) }}">
					@endif
					@if ($row->datetimecreated)
						<time datetime="{{ $row->datetimecreated->format('Y-m-d\TH:i:s\Z') }}">
							@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->datetimecreated->diffForHumans() }}
							@else
								{{ $row->datetimecreated->format('Y-m-d') }}
							@endif
						</time>
					@else
						<span class="unknown">{{ trans('global.unknown') }}</span>
					@endif
					@if (auth()->user()->can('edit orders'))
						</a>
					@endif
				</td>
				<td>
					<span class="badge badge-sm order-status {{ str_replace(' ', '-', $row->status) }}" data-tip="Accounts: {{ $row->accounts }}<br />Assigned: {{ $row->accountsassigned }}<br />Approved: {{ $row->accountsapproved }}<br />Denied: {{ $row->accountsdenied }}<br />Paid: {{ $row->accountspaid }}<br />---<br />Items: {{ $row->items_count }}<br />Fulfilled: {{ $row->itemsfulfilled }}">
						{{ trans('orders::orders.' . $row->status) }}
					</span>
				</td>
				<td class="priority-5">
					@if ($row->groupid)
						@if (auth()->user()->can('manage groups'))
							<a href="{{ route('admin.groups.edit', ['id' => $row->groupid]) }}">
								{{ $row->group ? $row->group->name : 'Group ID #' . $row->groupid }}
							</a>
						@else
							{{ $row->group ? $row->group->name : 'Group ID #' . $row->groupid }}
						@endif
					@else
						@if (auth()->user()->can('manage users'))
							<a href="{{ route('admin.users.edit', ['id' => $row->userid]) }}">
								{{ $row->name ? $row->name : 'User ID #' . $row->userid }}
							</a>
						@else
							{{ $row->name ? $row->name : 'User ID #' . $row->userid }}
						@endif
					@endif
				</td>
				<td class="priority-2 numeric text-nowrap">
					{{ config('orders.currency', '$') }} {{ $row->formatNumber($row->ordertotal) }}
				</td>
				<td class="priority-2 numeric">
					<a class="items-toggle" data-toggle="collapse" data-parent="#queues" href="#row{{ $row->id }}" title="Items in this order">
						<span class="icon-list" aria-hidden="true"></span><span class="sr-only">Items</span>
					</a>
				</td>
			</tr>
			<tr class="details-row collapse" id="row{{ $row->id }}">
				<td colspan="<?php echo (auth()->user()->can('delete orders') ? 8 : 7); ?>">
					<table class="table">
						<caption class="sr-only">{{ trans('orders::orders.items') }}</caption>
						<thead>
							<tr>
								<th scope="col">{{ trans('orders::orders.status') }}</th>
								<th scope="col">{{ trans('orders::orders.item') }}</th>
								<th scope="col" class="text-right">{{ trans('orders::orders.quantity') }}</th>
								<th scope="col" class="text-right">{{ trans('orders::orders.price') }}</th>
								<th scope="col" class="text-right">{{ trans('orders::orders.total') }}</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($row->items as $item)
								<tr>
									@if (!$item->isFulfilled())
										@if ($row->status != 'canceled' && $row->status == 'pending_fulfillment')
											<td>
												<div class="badge order-status {{ str_replace(' ', '-', $row->status) }}" id="status_{{ $item->id }}">{{ trans('orders::orders.pending_fulfillment') }}</div>
											</td>
										@else
											<td>
												<div class="badge order-status {{ str_replace(' ', '-', $row->status) }}">
													@if ($row->status == 'pending_fulfillment' || $row->status == 'canceled')
														{{ trans('orders::orders.' . $row->status) }}
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
										<span class="item-edit-hide quantity_span">{{ $item->quantity }}</span>
										@if ($item->product->timeperiod && $item->origorderitemid)
											for<br/>
											<span class="item-edit-hide periods_span">{{ $item->timeperiodcount }}</span>
											@if ($item->timeperiodcount > 1)
												{{ $item->product->timeperiod->plural }}
											@else
												{{ $item->product->timeperiod->singular }}
											@endif
										@endif
									</td>
									<td class="text-right">
										{{ config('orders.currency', '$') }} <span name="price">{{ $item->formattedPrice }}</span><br/>
										<span class="text-nowrap">per {{ $item->product->unit }}</span>
									</td>
									<td class="text-right text-nowrap">
										<span class="item-edit-hide">{{ config('orders.currency', '$') }} <span name="itemtotal">{{ $item->formattedTotal }}</span></span>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>

	{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop