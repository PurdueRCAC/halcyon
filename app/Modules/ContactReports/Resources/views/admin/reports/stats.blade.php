@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ Module::asset('core:vendor/chartjs/Chart.css') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.css') }}" />
@endpush

@push('scripts')
<script src="{{ Module::asset('core:vendor/chartjs/Chart.min.js') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ asset('modules/contactreports/js/stats.js?v=' . filemtime(public_path() . '/modules/contactreports/js/stats.js')) }}"></script>
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
{{ trans('contactreports::contactreports.module name') }}
@stop

@section('content')

@component('contactreports::admin.submenu')
	stats
@endcomponent

<form action="{{ route('admin.news.stats') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 text-right">
				<label class="sr-only" for="filter_start">Start date</label>
				<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="Start date" />
				to
				<label class="sr-only" for="filter_end">End date</label>
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
							<canvas id="sparkline" class="sparkline-chart" width="275" height="30" data-labels="{{ json_encode(array_keys($stats['daily'])) }}" data-values="{{ json_encode(array_values($stats['daily'])) }}" data-border="<?php echo(auth() -> user() -> facet('theme.admin.mode') == 'dark' ? 'rgba(0, 0, 0, 0.6)' : '#fff'); ?>">
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
</form>

@stop