@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ Module::asset('core:vendor/chartjs/Chart.css') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.css') }}" />
@endpush

@push('scripts')
<script src="{{ Module::asset('core:vendor/chartjs/Chart.min.js') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<!-- script src="{{ asset('modules/contactreports/js/admin.js?v=' . filemtime(public_path() . '/modules/contactreports/js/admin.js')) }}"></script> -->
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
						//backgroundColor: 'rgb(54, 162, 235)',
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
		trans('contactreports::contactreports.module name'),
		route('admin.contactreports.index')
	)
	->append(
		trans('contactreports::contactreports.stats'),
		route('admin.contactreports.stats')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin contactreports'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('contactreports')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('news.name') !!}
@stop

@section('content')

@component('contactreports::admin.submenu')
	stats
@endcomponent

<form action="{{ route('admin.news.stats') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 text-right">
				<label class="sr-only" for="filter_start">{{ trans('news::news.start date') }}</label>
				<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />
				to
				<label class="sr-only" for="filter_end">{{ trans('news::news.end date') }}</label>
				<input type="text" name="end" id="filter_end" class="form-control date filter filter-submit" value="{{ $filters['end'] }}" placeholder="End date" />

				<button type="submit" class="btn btn-secondary">Filter</button>
			</div>
		</div>
	</fieldset>

	<div class="container-fluid">
	<div class="row">
		<div class="col-md-4">
			<div class="card mb-3">
				<div class="card-body">
					<h4 class="mt-0 pt-0 card-title">By Type</h4>
					<?php
					$tstats = array();
					foreach ($types as $type):
						$val = $type->reports()
							->where('datetimecontact', '>=', Carbon\Carbon::parse($filters['start'])->toDateTimeString())
							->where('datetimecontact', '<', Carbon\Carbon::parse($filters['end'])->toDateTimeString())
							->count();

						if ($val):
							$tstats[$type->name] = $val;
						endif;
					endforeach;
					?>
					<div>
						<canvas id="breakdown-types" class="pie-chart" width="300" height="300" data-labels="{{ json_encode(array_keys($tstats)) }}" data-values="{{ json_encode(array_values($tstats)) }}">
							@foreach ($tstats as $name => $val)
								{{ $name }}: $val<br />
							@endforeach
						</canvas>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="row">
				<div class="col-md-6">
					<?php
					$r = (new App\Modules\ContactReports\Models\Reportresource)->getTable();
					$c = (new App\Modules\ContactReports\Models\Report)->getTable();

					$resources = App\Modules\ContactReports\Models\Reportresource::query()
						->select($r . '.resourceid', DB::raw('COUNT(*) as total'))
						->join($c, $c . '.id', $r . '.contactreportid')
						->where($c . '.datetimecontact', '>=', Carbon\Carbon::parse($filters['start'])->toDateTimeString())
						->where($c . '.datetimecontact', '<', Carbon\Carbon::parse($filters['end'])->toDateTimeString())
						->groupBy($r . '.resourceid')
						->orderBy('total', 'desc')
						->limit(5)
						->get();
					?>
					<div class="card mb-3">
						<table class="table">
							<caption>Top Resources Referenced</caption>
							<tbody>
								@foreach ($resources as $i => $res)
									<tr>
										<th scope="row"><span class="badge badge-info">{{ $res->resource->name }}</span></th>
										<td class="text-right">{{ $res->total }}</td>
									</tr>
								@endforeach
								@if ($i < 4)
									@php
									$i++;
									@endphp
									@for ($i; $i < 5; $i++)
										<tr>
											<th scope="row"><span class="text-muted">-</span></th>
											<td class="text-right"></td>
										</tr>
									@endfor
								@endif
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-md-6">
					<?php
					$r = (new App\Modules\Tags\Models\Tagged)->getTable();
					$c = (new App\Modules\ContactReports\Models\Report)->getTable();

					$tags = App\Modules\Tags\Models\Tagged::query()
						->select($r . '.tag_id', DB::raw('COUNT(*) as total'))
						->join($c, $c . '.id', $r . '.taggable_id')
						->where($r . '.taggable_type', '=', App\Modules\ContactReports\Models\Report::class)
						->where($c . '.datetimecontact', '>=', Carbon\Carbon::parse($filters['start'])->toDateTimeString())
						->where($c . '.datetimecontact', '<', Carbon\Carbon::parse($filters['end'])->toDateTimeString())
						->groupBy($r . '.tag_id')
						->orderBy('total', 'desc')
						->limit(5)
						->get();
					?>
					<div class="card mb-3">
						<table class="table">
							<caption>Top Tags Referenced</caption>
							<tbody>
								@foreach ($tags as $i => $tag)
									<tr>
										<th scope="row"><span class="badge badge-secondary">{{ $tag->tag->name }}</span></th>
										<td class="text-right">{{ $tag->total }}</td>
									</tr>
								@endforeach
								@if ($i < 4)
									@php
									$i++;
									@endphp
									@for ($i; $i < 5; $i++)
										<tr>
											<th scope="row"><span class="text-muted">-</span></th>
											<td class="text-right"></td>
										</tr>
									@endfor
								@endif
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-body">
						<h4 class="mt-0 pt-0 card-title">Daily</h4>
						<canvas id="sparkline" class="sparkline-chart" width="275" height="30" data-labels="{{ json_encode(array_keys($stats['daily'])) }}" data-values="{{ json_encode(array_values($stats['daily'])) }}">
							@foreach ($stats['daily'] as $day => $val)
								{{ $day }}: {{ $val }}<br />
							@endforeach
						</canvas>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	@csrf
</form>

@stop