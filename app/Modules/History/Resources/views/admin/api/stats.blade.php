@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/history/css/history.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.min.js') }}"></script>
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
					],
					xAxes: [
						{
							display: false
						}
					]*/
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
		trans('history::history.history manager'),
		route('admin.history.index')
	)
	->append(
		trans('history::history.api'),
		route('admin.history.api')
	)
	->append(
		trans('history::history.stats'),
		route('admin.history.api.stats')
	);
@endphp

@section('title')
{{ trans('history::history.history manager') }}: {{ trans('history::history.api') }}: {{ trans('history::history.stats') }}
@stop

@section('content')

@component('history::admin.submenu')
	api
@endcomponent

<nav class="container-fluid" aria-label="{{ trans('history::history.module sub sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link" href="{{ route('admin.history.api') }}">{{ trans('history::history.requests') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link active" href="{{ route('admin.history.api.stats') }}">{{ trans('history::history.stats') }}</a>
		</li>
	</ul>
</nav>

<form action="{{ route('admin.history.api.stats') }}" method="get" name="adminForm" id="adminForm" class="form-inlin">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 text-right">
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
							<div class="col-md-6">
								<p class="mt-0 mx-4"><strong>To-Date</strong></p>
								<a href="{{ route('admin.history.api.stats', ['start' => '', 'end' => '']) }}" class="dropdown-item{{ !$filters['start'] && !$filters['end'] ? ' active' : '' }}">All Time</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 week')->format('Y-m-d');
								$end = Carbon\Carbon::now()->modify('+1 day')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.api.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Week</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 month')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.api.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Month</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-6 months')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.api.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">6 Months</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 year')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.api.stats', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Year</a>
							</div>
							<div class="col-md-6">
								<p class="mt-0 mx-4"><strong>Specific</strong></p>
								<div class="px-4 py-3">
									<div class="form-group mb-3">
										<label for="filter_start">{{ trans('history::history.start date') }}</label>
										<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />
									</div>
									<div class="form-group">
										<label for="filter_end">{{ trans('history::history.end date') }}</label>
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
							<span class="fa fa-server display-4 float-left" aria-hidden="true"></span>
							<span class="value" style="font-size: 2em; line-height: 1.2;">{{ number_format($stats['made']) }}</span><br />
							<span class="key">{{ trans('history::history.requests') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<div class="stat-block">
						<div class="text-danger">
							<span class="fa fa-exclamation-triangle display-4 float-left" aria-hidden="true"></span>
							<span class="value" style="font-size: 2em; line-height: 1.2;">{{ number_format($stats['errors']) }}</span><br />
							<span class="key">{{ trans('history::history.errors') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<div class="stat-block">
						<div class="text-success">
							<span class="fa fa-check display-4 float-left" aria-hidden="true"></span>
							<span class="value" style="font-size: 2em; line-height: 1.2;">{{ number_format($stats['made'] - $stats['errors']) }}</span><br />
							<span class="key">{{ trans('history::history.success') }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card mb-3">
				<div class="card-body">
					<h4>Requests by Transport Methods</h4>

					<div>
						<canvas id="methods" class="pie-chart" width="200" height="200" data-labels="{{ json_encode(array_keys($stats['methods'])) }}" data-values="{{ json_encode(array_values($stats['methods'])) }}">
							<table class="table">
								<caption class="sr-only">Trasnport Methods</caption>
								<thead>
									<tr>
										<th scope="col">Transport Method</th>
										<th scope="col" class="text-right">Requests</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($stats['methods'] as $name => $val)
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

		<div class="col-md-8">
			<div class="card mb-3">
				<div class="card-body">
					<h4>Daily Requests</h4>
					<div>
					<canvas id="sparkline" class="sparkline-chart" width="500" height="220" data-labels="{{ json_encode(array_keys($stats['daily'])) }}" data-values="{{ json_encode(array_values($stats['daily'])) }}">
						@foreach ($stats['daily'] as $day => $val)
							{{ $day }}: $val<br />
						@endforeach
					</canvas>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-7">
			<div class="card mb-3">
				<div class="card-body">
					<h4>Top Requested URIs</h4>
					<table class="table">
						<caption class="sr-only">URIs</caption>
						<thead>
							<tr>
								<th scope="col">URI</th>
								<th scope="col" class="text-right">Requests</th>
								<th scope="col" class="text-right">% of all</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($stats['uris'] as $row)
							<tr>
								<td>
									<?php
									if (preg_match('/api_token=([a-zA-Z0-9]+)/', $row->uri, $matches))
									{
										$row->uri = str_replace($matches[1], '<TOKEN>', $row->uri);
									}
									?>
									{{ \Illuminate\Support\Str::limit($row->uri, 100) }}
									<?php
									$per = round(($row->requests / $stats['made']) * 100);
									?>
									<div class="progress bg-transparent" style="height: 3px">
										<div class="progress-bar" role="progressbar" style="width: <?php echo $per; ?>%;" aria-valuenow="<?php echo $per; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo $per; ?>% of requests">
											<span class="sr-only"><?php echo $per; ?>%</span>
										</div>
									</div>
								</td>
								<td class="text-right">
									{{ number_format($row->requests) }}
								</td>
								<td class="text-right">
									{{ $per . '%' }}
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-md-5">
			<div class="card mb-3">
				<div class="card-body">
					<h4>Top Requestor IPs</h4>
					<table class="table">
						<caption class="sr-only">IPs</caption>
						<thead>
							<tr>
								<th scope="col">IP</th>
								<th scope="col" class="text-right">Requests</th>
								<th scope="col" class="text-right">% of all</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($stats['ips'] as $row)
							<tr>
								<td>
									{{ $row->ip }}
									<?php
									$per = round(($row->requests / $stats['made']) * 100);
									?>
									<div class="progress bg-transparent" style="height: 3px">
										<div class="progress-bar" role="progressbar" style="width: <?php echo $per; ?>%;" aria-valuenow="<?php echo $per; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo $per; ?>% of requests">
											<span class="sr-only"><?php echo $per; ?>%</span>
										</div>
									</div>
								</td>
								<td class="text-right">
									{{ number_format($row->requests) }}
								</td>
								<td class="text-right">
									{{ $per . '%' }}
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</form>

@stop