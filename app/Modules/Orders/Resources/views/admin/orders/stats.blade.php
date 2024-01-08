@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/orders/js/orders.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.items-toggle').forEach(function (el) {
		el.addEventListener('click', function(e){
			e.preventDefault();
			document.getElementById(this.getAttribute('href').replace('#', '')).classList.toggle('collapse');
		});
	});

	var charts = new Array;
	document.querySelectorAll('.sparkline-chart').forEach(function (el) {
		const ctx = el.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						fill: true,
						data: JSON.parse(el.getAttribute('data-values'))
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

	document.querySelectorAll('.pie-chart').forEach(function (el) {
		const ctx = el.getContext('2d');
		const pchart = new Chart(ctx, {
			type: 'doughnut',
			data: {
				labels: JSON.parse(el.getAttribute('data-labels')),
				datasets: [
					{
						data: JSON.parse(el.getAttribute('data-values')),
						backgroundColor: [
							'rgb(255, 99, 132)', // red
							'rgb(54, 162, 235)', // blue
							'rgb(255, 205, 86)', // yellow
							'rgb(201, 203, 207)', // grey
							'rgb(75, 192, 192)', // blue green
							'rgb(255, 159, 64)', // orange
							'rgb(153, 102, 255)' // purple
						],
						borderColor: <?php echo (auth()->user()->facet('theme.admin.mode') == 'dark' ? '"rgba(0, 0, 0, 0.6)"' : '"#fff"'); ?>
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
	)
	->append(
		trans('orders::orders.stats'),
		route('admin.orders.stats')
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
{{ trans('orders::orders.module name') }}: {{ trans('orders::orders.stats') }}
@stop

@section('content')

@component('orders::admin.submenu')
	stats
@endcomponent

<form action="{{ route('admin.orders.stats') }}" method="get" name="adminForm" id="adminForm" class="form-inlin">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-6">
				<div class="btn-group" role="group" aria-label="Recurrence filter">
					<a href="{{ route('admin.orders.stats') }}" class="btn btn-secondary{{ $filters['recurring'] < 0 ? ' active' : '' }}">All</a>
					<a href="{{ route('admin.orders.stats', ['recurring' => 0]) }}" class="btn btn-secondary{{ $filters['recurring'] == 0 ? ' active' : '' }}">One-time</a>
					<a href="{{ route('admin.orders.stats', ['recurring' => 1]) }}" class="btn btn-secondary{{ $filters['recurring'] == 1 ? ' active' : '' }}">Recurring</a>
				</div>
				<input type="hidden" name="recurring" value="{{ $filters['recurring'] }}" />
			</div>
			<div class="col col-md-6 text-right">
				<div class="btn-group position-static" role="group" aria-label="Specific date range">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						@if ($filters['start'] || $filters['end'])
							@if ($filters['start'])
								{{ $filters['start'] }}
							@else
								All past
							@endif
							-
							@if ($filters['end'])
								{{ $filters['end'] }}
							@else
								Now
							@endif
						@else
							Date range
						@endif
					</button>
					<div class="dropdown-menu dropdown-menu-right dropdown-dates">
						<div class="row">
							<div class="col-md-3">
								<p class="mt-0 mx-4"><strong>To-Date</strong></p>
								<a href="{{ route('admin.orders.stats', ['start' => '', 'end' => '']) }}" class="dropdown-item{{ !$filters['start'] && !$filters['end'] ? ' active' : '' }}">All Time</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 week')->format('Y-m-d');
								$end = Carbon\Carbon::now()->modify('+1 day')->format('Y-m-d');
								?>
								<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Week</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 month')->format('Y-m-d');
								?>
								<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Month</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-6 months')->format('Y-m-d');
								?>
								<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">6 Months</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 year')->format('Y-m-d');
								?>
								<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Year</a>
							</div>
							<div class="col-md-3">
								<p class="mt-0 mx-4"><strong>Fiscal</strong></p>
								<?php
								$fs = config('fiscal_start', 7);
								$now = Carbon\Carbon::now();
								$y = $now->format('Y');
								if ((int)$now->format('m') < $fs)
								{
									$y = $now->modify('-1 year')->format('Y');
								}
								$fiscal_start = Carbon\Carbon::parse($y . '-' . Illuminate\Support\Str::padLeft($fs, 2, '0') . '-01');
								$start = $fiscal_start->format('Y-m-d');
								?>
								<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Year-to-date</a>
								<?php
								$now = Carbon\Carbon::now();
								$q1 = clone $fiscal_start;
								$end = $q1->modify('+3 months')->format('Y-m-d');
								?>
								<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">2023 quarter 1</a>
								<?php
								$q2 = clone $fiscal_start;
								$start = $q2->modify('+3 months');
								if ($start->timestamp < $now->timestamp):
									$start = $q2->format('Y-m-d');
									$end   = $q2->modify('+3 months')->format('Y-m-d');
									?>
									<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">2023 quarter 2</a>
									<?php
								endif;

								$q3 = clone $fiscal_start;
								$start = $q3->modify('+6 months')->format('Y-m-d');
								if ($start < $now->format('Y-m-d')):
									$end   = $q3->modify('+3 months')->format('Y-m-d');
									?>
									<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">2023 quarter 3</a>
									<?php
								endif;

								$q4 = clone $fiscal_start;
								$start = $q4->modify('+9 months')->format('Y-m-d');
								if ($start < $now->format('Y-m-d')):
									$end   = $q4->modify('+3 months')->format('Y-m-d');
									?>
									<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">2023 quarter 4</a>
									<?php
								endif;

								$lastyear = clone $fiscal_start;
								$end = $lastyear->format('Y-m-d');
								$start = $lastyear->modify('-1 year')->format('Y-m-d');
								?>
								<a href="{{ route('admin.orders.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">2022</a>
							</div>
							<div class="col-md-6">
								<p class="mt-0 mx-4"><strong>Specific</strong></p>
								<div class="px-4 py-3">
									<div class="form-group mb-3">
										<label for="filter_start">{{ trans('orders::orders.start date') }}</label>
										<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />
									</div>
									<div class="form-group">
										<label for="filter_end">{{ trans('orders::orders.end date') }}</label>
										<input type="text" name="end" id="filter_end" class="form-control date filter filter-submit" value="{{ $filters['end'] }}" placeholder="End date" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
	
				<button type="submit" class="sr-only btn btn-secondary">Filter</button>
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
							<span class="fa fa-exclamation-triangle display-4 float-left" aria-hidden="true"></span>
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
						$query = App\Modules\Orders\Models\Order::query()
							->join($i, $i . '.orderid', $o . '.id')
							->join($p, $p . '.id', $i . '.orderproductid')
							->whereNull($p . '.datetimeremoved')
							->whereNull($i . '.datetimeremoved')
							->whereNull($o . '.datetimeremoved')
							->where($p . '.ordercategoryid', '=', $category->id)
							->where($o . '.datetimecreated', '>=', $filters['start'] . ' 00:00:00')
							->where($o . '.datetimecreated', '<', $filters['end'] . ' 00:00:00');
						if ($filters['recurring'] >= 0)
						{
							$query->where($i . '.origorderitemid', ($filters['recurring'] ? '>' : '='), 0);
						}
						$val = $query
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
						$query = App\Modules\Orders\Models\Order::query()
							->select(Illuminate\Support\Facades\DB::raw('SUM(' . $i . '.price) AS revenue'))
							->join($i, $i . '.orderid', $o . '.id')
							->join($p, $p . '.id', $i . '.orderproductid')
							->whereNull($p . '.datetimeremoved')
							->whereNull($i . '.datetimeremoved')
							->whereNull($o . '.datetimeremoved')
							->where($p . '.ordercategoryid', '=', $category->id)
							->where($o . '.datetimecreated', '>=', $filters['start'] . ' 00:00:00')
							->where($o . '.datetimecreated', '<', $filters['end'] . ' 00:00:00');
						if ($filters['recurring'] >= 0)
						{
							$query->where($i . '.origorderitemid', ($filters['recurring'] ? '>' : '='), 0);
						}

						$val = $query
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
</form>

@stop