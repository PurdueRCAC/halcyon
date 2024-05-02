@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/history/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('history::history.module name'),
		route('admin.history.index')
	)
	->append(
		trans('history::history.activity'),
		route('admin.history.activity')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin history'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('history');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('history::history.activity') }}
@stop

@section('content')

@component('history::admin.submenu')
	activity
@endcomponent

<form action="{{ route('admin.history.activity') }}" method="get" name="adminForm" id="adminForm">
	<fieldset id="filter-bar" class="container-fluid mb-3">
		<div class="row">
			<div class="col filter-search col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
				</span>
			</div>
			<div class="col col-md-2">
				<div class="btn-group position-static" role="group" aria-label="Specific date range">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
							<div class="col-md-5">
								<p class="mt-0 mx-4"><strong>To-Date</strong></p>
								<a href="{{ route('admin.history.index', ['start' => '', 'end' => '']) }}" class="dropdown-item{{ !$filters['start'] && !$filters['end'] ? ' active' : '' }}">All Time</a>
								<?php
								$start = Carbon\Carbon::now()->format('Y-m-d');
								$end = Carbon\Carbon::now()->modify('+1 day')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Past Day</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 week')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Week</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 month')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Month</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-6 months')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">6 Months</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 year')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Year</a>
							</div>
							<div class="col-md-7">
								<p class="mt-0 mx-4"><strong>Specific</strong></p>
								<div class="px-4 py-3">
									<div class="form-group mb-3">
										<label for="filter_start">{{ trans('history::history.start date') }}</label>
										<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="YYYY-MM-DD" />
									</div>
									<div class="form-group">
										<label for="filter_end">{{ trans('history::history.end date') }}</label>
										<input type="text" name="end" id="filter_end" class="form-control date filter filter-submit" value="{{ $filters['end'] }}" placeholder="YYYY-MM-DD" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_app">{{ trans('history::history.app') }}</label>
				<select name="app" id="filter_app" class="form-control filter filter-submit">
					<option value=""<?php if (!$filters['app']): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all apps') }}</option>
					@foreach ($apps as $app)
						<option value="{{ $app->app }}"<?php if ($filters['app'] == $app->app): echo ' selected="selected"'; endif;?>>{{ $app->app }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-2 mb-2">
				<label class="sr-only visually-hidden" for="filter_status">{{ trans('history::history.status') }}</label>
				<select name="status" id="filter_status" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['status'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all status') }}</option>
					<option value="200"<?php if ($filters['status'] == '200'): echo ' selected="selected"'; endif;?>>200</option>
					<option value="201"<?php if ($filters['status'] == '201'): echo ' selected="selected"'; endif;?>>201</option>
					<option value="400"<?php if ($filters['status'] == '400'): echo ' selected="selected"'; endif;?>>400</option>
					<option value="403"<?php if ($filters['status'] == '403'): echo ' selected="selected"'; endif;?>>403</option>
					<option value="404"<?php if ($filters['status'] == '404'): echo ' selected="selected"'; endif;?>>404</option>
					<option value="409"<?php if ($filters['status'] == '409'): echo ' selected="selected"'; endif;?>>409</option>
					<option value="412"<?php if ($filters['status'] == '412'): echo ' selected="selected"'; endif;?>>412</option>
					<option value="415"<?php if ($filters['status'] == '415'): echo ' selected="selected"'; endif;?>>415</option>
					<option value="500"<?php if ($filters['status'] == '500'): echo ' selected="selected"'; endif;?>>500</option>
				</select>
			</div>
			<div class="col-md-2 mb-2">
				<label class="sr-only visually-hidden" for="filter_transport">{{ trans('history::history.transport') }}</label>
				<select name="transportmethod" id="filter_transport" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['transportmethod'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all transports') }}</option>
					<option value="GET"<?php if ($filters['transportmethod'] == 'GET'): echo ' selected="selected"'; endif;?>>GET</option>
					<option value="POST"<?php if ($filters['transportmethod'] == 'POST'): echo ' selected="selected"'; endif;?>>POST</option>
					<option value="PUT"<?php if ($filters['transportmethod'] == 'PUT'): echo ' selected="selected"'; endif;?>>PUT</option>
					<option value="DELETE"<?php if ($filters['transportmethod'] == 'DELETE'): echo ' selected="selected"'; endif;?>>DELETE</option>
					<option value="HEAD"<?php if ($filters['transportmethod'] == 'HEAD'): echo ' selected="selected"'; endif;?>>HEAD</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<caption class="sr-only visually-hidden">{{ trans('history::history.activity') }}</caption>
					<thead>
						<tr>
							<th scope="col">
								{!! Html::grid('sort', trans('history::history.id'), 'id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('history::history.app'), 'app', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('history::history.ip'), 'ip', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('history::history.uri'), 'uri', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('history::history.transport'), 'transportmethod', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('history::history.status'), 'status', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{{ trans('history::history.actor') }}
							</th>
							<th scope="col" class="priority-4">
								{!! Html::grid('sort', trans('history::history.timestamp'), 'datetime', $filters['order_dir'], $filters['order']) !!}
							</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($rows as $i => $row)
						<?php
						$cls = '';
						if ($row->status >= 400):
							$cls = ' class="error-warning"';
						endif;
						if ($row->status >= 500):
							$cls = ' class="error-danger"';
						endif;
						?>
						<tr{!! $cls !!}>
							<td class="priority-5">
								{{ $row->id }}
							</td>
							<td>
								<a href="{{ route('admin.history.activity.show', ['id' => $row->id]) }}">
									{{ $row->app }}
								</a>
							</td>
							<td>
								@if (!$row->ip || $row->ip == '::1')
									loalhost
								@else
									{{ $row->ip }}
								@endif
							</td>
							<td>
								{{ $row->uri }}
							</td>
							<td>
								@if ($row->transportmethod == 'DELETE')
									<span class="badge badge-danger">{{ $row->transportmethod }}</span>
								@elseif ($row->transportmethod == 'POST')
									<span class="badge badge-success">{{ $row->transportmethod }}</span>
								@elseif ($row->transportmethod == 'PUT')
									<span class="badge badge-info">{{ $row->transportmethod }}</span>
								@elseif ($row->transportmethod == 'GET')
									<span class="badge badge-info">{{ $row->transportmethod }}</span>
								@else
									<span class="badge badge-secondary">{{ $row->transportmethod }}</span>
								@endif
							</td>
							<td class="priority-4">
								{{ $row->status }}
							</td>
							<td>
								@if ($row->user)
									<a href="{{ route('admin.users.edit', ['id' => $row->user->id]) }}">
										{{ $row->user->name }}
									</a>
								@elseif (!$row->userid)
									<span class="text-muted unknown">{{ trans('global.unknown') }}</span>
								@else
									<span class="text-muted none">{{ trans('global.none') }}</span>
								@endif
							</td>
							<td class="priority-4">
								@if ($row->datetime)
									<time datetime="{{ $row->datetime->toDateTimeLocalString() }}">{{ $row->datetime }}</time>
								@else
									<span class="text-muted never">{{ trans('global.unknown') }}</span>
								@endif
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

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop