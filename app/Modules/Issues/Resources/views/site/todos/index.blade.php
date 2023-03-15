@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.css?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker/jquery.timepicker.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/issues/css/site.css?v=' . filemtime(public_path() . '/modules/issues/css/site.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.js?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker/jquery.timepicker.js')) }}"></script>
<script src="{{ asset('modules/issues/js/site.js?v=' . filemtime(public_path() . '/modules/issues/js/site.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('issues::issues.issues'),
		route('site.issues.index')
	)
	->append(
		trans('issues::issues.todos'),
		route('site.issues.todos')
	);
@endphp

@section('content')
<form action="{{ route('site.issues.todos') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-6">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-6 text-right">
				<label class="sr-only" for="filter_start">{{ trans('issues::issues.recurrence') }}</label>
				<select class="form-control" name="fields[recurringtimeperiodid]" id="field-recurringtimeperiodid">
					<option value="0"<?php if (!$filters['timeperiod']) { echo ' selected="selected"'; } ?>>{{ trans('issues::issues.all recurrence') }}</option>
					<?php foreach (App\Halcyon\Models\Timeperiod::all() as $period): ?>
						<option value="{{ $period->id }}"<?php if ($filters['timeperiod'] == $period->id) { echo ' selected="selected"'; } ?>>{{ $period->name }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="table-responsive">
		<table class="table table-hover adminlist">
			<caption class="sr-only">{{ trans('issues::issues.todos') }}</caption>
			<thead>
				<tr>
					@if (auth()->user()->can('delete issues'))
						<th>
							{!! Html::grid('checkall') !!}
						</th>
					@endif
					<th scope="col" class="priority-5">
						{!! Html::grid('sort', trans('issues::issues.id'), 'id', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{!! Html::grid('sort', trans('issues::issues.name'), 'report', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-4">
						{!! Html::grid('sort', trans('issues::issues.recurring time period'), 'recurringtimeperiodid', $filters['order_dir'], $filters['order']) !!}
					</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($rows as $i => $row)
				<tr>
					@if (auth()->user()->can('delete issues'))
						<td>
							{!! Html::grid('id', $i, $row->id) !!}
						</td>
					@endif
					<td class="priority-5">
						{{ $row->id }}
					</td>
					<td>
						@if (auth()->user()->can('edit issues'))
							<a href="{{ route('site.issues.todos.edit', ['id' => $row->id]) }}">
								{{ $row->name }}
							</a>
						@else
							{{ $row->name }}
						@endif
					</td>
					<td class="priority-4">
						<?php
						// Check for completed todos in the recurring time period
						switch ($row->timeperiod->name):
							case 'hourly':
								$badge = 'danger';
							break;

							case 'daily':
								$badge = 'warning';
							break;

							case 'weekly':
								$badge = 'info';
							break;

							case 'monthly':
								$period = $now->format('Y-m-01') . ' 00:00:00';
							break;

							case 'annual':
								$period = $now->format('Y-01-01') . ' 00:00:00';
							break;

							default:
								$badge = 'secondary';
							break;
						endswitch;
						?>
						<span class="badge badge-{{ $badge }}">{{ $row->timeperiod->name }}</span>
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop