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

	var charts = new Array;
	$('.sparkline-chart').each(function (i, el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse($(el).attr('data-labels')),
				datasets: [
					{
						fill: true,
						data: JSON.parse($(el).attr('data-values'))
					}
				]
			},
			options: {
				//responsive: false,
				bezierCurve: false,
				animation: {
					duration: 0
				},
				legend: {
					display: false
				},
				elements: {
					line: {
						borderColor: 'rgb(54, 162, 235)', //'#0091EB',
						backgroundColor: 'rgb(54, 162, 235)',
						borderWidth: 1,
						tension: 0
					},
					point: {
						borderColor: 'rgb(54, 162, 235)'//'#0091EB'
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
		charts.push(chart);
	});

	$('.pie-chart').each(function (i, el) {
		const ctx = el.getContext('2d');
		const pchart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: JSON.parse($(el).attr('data-labels')),
				datasets: [
					{
						data: JSON.parse($(el).attr('data-values')),
						backgroundColor: [
							'rgb(255, 99, 132)', // red
							'rgb(54, 162, 235)', // blue
							'rgb(255, 205, 86)', // yellow
							'rgb(201, 203, 207)', // grey
							'rgb(75, 192, 192)', // blue green
							'rgb(255, 159, 64)', // orange
							'rgb(153, 102, 255)' // purple
						]
					}
				]
			},
			options: {
				animation: {
					duration: 0
				}/*,
				legend: {
					display: false
				}*/
			}
		});
		charts.push(pchart);
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

@section('content')

@component('orders::admin.submenu')
	stats
@endcomponent

<form action="{{ route('admin.orders.stats') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 text-right">
				<label class="sr-only" for="filter_start">{{ trans('orders::orders.start date') }}</label>
				<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />
				to
				<label class="sr-only" for="filter_end">{{ trans('orders::orders.end date') }}</label>
				<input type="text" name="end" id="filter_end" class="form-control date filter filter-submit" value="{{ $filters['end'] }}" placeholder="End date" />

				<button type="submit" class="btn btn-secondary">Filter</button>
			</div>
		</div>
	</fieldset>

	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<div class="stat-block">
						<div class="text-info">
							<strong class="float-right">{{ config('orders.currency', '$') }} {{ $stats['sold'] }}</strong>
							<span class="fa fa-shopping-cart display-4 float-left" aria-hidden="true"></span>
							<span class="value">{{ number_format($stats['submitted']) }}</span><br />
							<span class="key">{{ trans('orders::orders.submitted') }}</span>
						</div>
						@if ($stats['submitted_prev'] > $stats['submitted'])
							<div><span class="text-danger" aria-hidden="true">&darr; {{ number_format(abs(100 - (($stats['submitted'] / $stats['submitted_prev']) * 100))) }}%</span> Down from previous period</div>
						@elseif ($stats['submitted_prev'] == $stats['submitted'])
							<div><span class="text-info" aria-hidden="true">&rarr;</span> Same as previous period</div>
						@else
						{{ $stats['submitted_prev'] }}
							<div><span class="text-success" aria-hidden="true">&uarr; {{ $stats['submitted_prev'] ? number_format(abs(100 - (($stats['submitted'] / $stats['submitted_prev']) * 100))) : 100 }}%</span> Up from previous period</div>
						@endif
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<div class="stat-block">
						<div class="text-danger">
							<strong class="float-right">{{ config('orders.currency', '$') }} {{ $stats['uncharged'] }}</strong>
							<span class="icon-alert-triangle display-4 float-left" aria-hidden="true"></span>
							<span class="value">{{ number_format($stats['canceled']) }}</span><br />
							<span class="key">{{ trans('orders::orders.canceled') }}</span>
						</div>
						@if ($stats['canceled_prev'] > $stats['canceled'])
							<div><span class="text-success" aria-hidden="true">&darr; {{ number_format(abs(100 - (($stats['canceled'] / $stats['canceled_prev']) * 100))) }}%</span> Down from previous period</div>
						@elseif ($stats['canceled_prev'] == $stats['canceled'])
							<div><span class="text-info" aria-hidden="true">&rarr;</span> Same as previous period</div>
						@else
							<div><span class="text-danger" aria-hidden="true">&uarr; {{ $stats['canceled_prev'] ? number_format(abs(100 - (($stats['canceled'] / $stats['canceled_prev']) * 100))) : 100 }}%</span> Up from previous period</div>
						@endif
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<div class="stat-block">
						<div class="text-success">
							<strong class="float-right">{{ config('orders.currency', '$') }} {{ $stats['collected'] }}</strong>
							<span class="fa fa-check display-4 float-left" aria-hidden="true"></span>
							<span class="value">{{ number_format($stats['fulfilled']) }}</span><br />
							<span class="key">{{ trans('orders::orders.fulfilled') }}</span>
						</div>
						@if ($stats['fulfilled_prev'] > $stats['fulfilled'])
							<div><span class="text-danger" aria-hidden="true">&darr; {{ number_format(abs(100 - (($stats['fulfilled'] / $stats['fulfilled_prev']) * 100))) }}%</span> Down from previous period</div>
						@elseif ($stats['fulfilled_prev'] == $stats['fulfilled'])
							<div><span class="text-info" aria-hidden="true">&rarr;</span> Same as previous period</div>
						@else
							<div><span class="text-success" aria-hidden="true">&uarr; {{ $stats['fulfilled_prev'] ? number_format(abs(100 - (($stats['fulfilled'] / $stats['fulfilled_prev']) * 100))) : 100 }}%</span> Up from previous period</div>
						@endif
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card mb-3">
				<div class="card-body">
					<h4>Avg. Time From Order Submission</h4>
					<div class="order-process">
						<ol>
							<li>
								<strong>Payment information</strong><br />
								<span class="text-muted">{{ $stats['steps']['payment']['average'] ? $stats['steps']['payment']['average'] : '-' }}</span>
							</li>
							<li>
								<strong>Approval by business office</strong><br />
								<span class="text-muted">{{ $stats['steps']['approval']['average'] ? $stats['steps']['approval']['average'] : '-' }}</span>
							</li>
							<li>
								<strong>Fulfillment</strong><br />
								<span class="text-muted">{{ $stats['steps']['fulfilled']['average'] }}</span>
								<span class="text-muted float-right">{{ $stats['steps']['completed']['average'] }}</span>
							</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-8">
			<div class="card mb-3">
				<div class="card-body">
					<h4>New orders</h4>
					<div>
					<canvas id="sparkline" class="sparkline-chart" width="500" height="110" data-labels="{{ json_encode(array_keys($stats['daily'])) }}" data-values="{{ json_encode(array_values($stats['daily'])) }}">
						@foreach ($stats['daily'] as $day => $val)
							{{ $day }}: $val<br />
						@endforeach
					</canvas>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card mb-3">
				<div class="card-body">
					<h4>Most Ordered Products</h4>
					<table class="table">
						<caption class="sr-only">Most Ordered Products</caption>
						<thead>
							<tr>
								<th scope="col">Product</th>
								<th scope="col">Orders</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($stats['products'] as $name => $val)
							<tr>
								<td>
									{{ $name }}
								</td>
								<td class="text-right">
									{{ number_format($val) }}
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card mb-3">
				<div class="card-body">
					<h4>Orders By Category</h4>
					<?php
					$p = (new App\Modules\Orders\Models\Product)->getTable();
					$i = (new App\Modules\Orders\Models\Item)->getTable();
					$o = (new App\Modules\Orders\Models\Order)->getTable();

					$cats = array();
					foreach ($categories as $category):
						$val = App\Modules\Orders\Models\Order::query()
							->join($i, $i . '.orderid', $o . '.id')
							->join($p, $p . '.id', $i . '.orderproductid')
							->whereNull($p . '.datetimeremoved')
							->whereNull($i . '.datetimeremoved')
							->whereNull($o . '.datetimeremoved')
							->where($p . '.ordercategoryid', '=', $category->id)
							->where($o . '.datetimecreated', '>=', $filters['start'] . ' 00:00:00')
							->where($o . '.datetimecreated', '<', $filters['end'] . ' 00:00:00')
							->count();

						$cats[$category->name] = $val;
					endforeach;

					$cats = array_filter($cats);
					?>
					<div>
						<canvas id="orders-categories" class="pie-chart" width="275" height="275" data-labels="{{ json_encode(array_keys($cats)) }}" data-values="{{ json_encode(array_values($cats)) }}">
							<table class="table">
								<caption class="sr-only">Orders By Category</caption>
								<thead>
									<tr>
										<th scope="col">Category</th>
										<th scope="col" class="text-right">Total</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($cats as $name => $val)
									<tr>
										<td>
											<span class="legend-key"></span> {{ $name }}
										</td>
										<td class="text-right">
											{{ number_format($val) }}
										</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</canvas>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card mb-3">
				<div class="card-body">
					<h4>Revenue By Category</h4>
					<?php
					$cats = array();
					foreach ($categories as $category):
						$val = App\Modules\Orders\Models\Order::query()
							->select(Illuminate\Support\Facades\DB::raw('SUM(' . $i . '.price) AS revenue'))
							->join($i, $i . '.orderid', $o . '.id')
							->join($p, $p . '.id', $i . '.orderproductid')
							->whereNull($p . '.datetimeremoved')
							->whereNull($i . '.datetimeremoved')
							->whereNull($o . '.datetimeremoved')
							->where($p . '.ordercategoryid', '=', $category->id)
							->where($o . '.datetimecreated', '>=', $filters['start'] . ' 00:00:00')
							->where($o . '.datetimecreated', '<', $filters['end'] . ' 00:00:00')
							->get()
							->first()->revenue;

						$cats[$category->name] = $val;
					endforeach;

					$cats = array_filter($cats);
					foreach ($cats as $key => $val):
						$cats[$key] = ($val / 100);
					endforeach;
					?>
					<div>
						<canvas id="revenue-categories" class="pie-chart" width="275" height="275" data-labels="{{ json_encode(array_keys($cats)) }}" data-values="{{ json_encode(array_values($cats)) }}">
							<table class="table">
								<caption class="sr-only">Revenue By Category</caption>
								<thead>
									<tr>
										<th scope="col">Category</th>
										<th scope="col" class="text-right">Total</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($cats as $name => $val)
									<tr>
										<td>
											<span class="legend-key"></span> {{ $name }}
										</td>
										<td class="text-right">
											{{ config('orders.currency', '$') }} {{ App\Modules\Orders\Helpers\Currency::formatNumber($val) }}
										</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</canvas>
					</div>
				</div>
			</div>
		</div>
	</div>

	@csrf
</form>

@stop