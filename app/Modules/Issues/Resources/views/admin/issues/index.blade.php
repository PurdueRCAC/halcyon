@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/issues/js/admin.js?v=' . filemtime(public_path() . '/modules/issues/js/admin.js')) }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('issues::issues.module name'),
		route('admin.issues.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete issues'))
		{!! Toolbar::deleteList('', route('admin.issues.delete')) !!}
	@endif

	@if (auth()->user()->can('create issues'))
		{!! Toolbar::addNew(route('admin.issues.create')) !!}
	@endif

	@if (auth()->user()->can('admin issues'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('issues')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('issues.name') !!}
@stop

@section('content')
@component('issues::admin.submenu')
	issues
@endcomponent

<form action="{{ route('admin.issues.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<div class="card mb-3 tab-search">
		<div class="card-header">
			<div class="row">
				<div class="col-md-6">
					<div class="card-title">{{ trans('issues::issues.checklist') }}</div>
				</div>
				<div class="col-md-6 text-right">
					<label for="checklist_status" class="sr-only">Show</label>
					<select name="checklist_status" id="checklist_status" class="form-control">
						<option value="all">{{ trans('issues::issues.all') }}</option>
						<option value="incomplete" selected="selected">{{ trans('issues::issues.incomplete') }}</option>
						<option value="complete">{{ trans('issues::issues.complete') }}</option>
					</select>
				</div>
			</div>
		</div>
		<?php
		foreach ($todos as $i => $todo)
		{
			$now = new DateTime('now');

			// Check for completed todos in the recurring time period
			switch ($todo->timeperiod->name)
			{
				case 'hourly':
					$period = $now->format('Y-m-d h') . ':00:00';
					$badge = 'danger';
				break;

				case 'daily':
					$period = $now->format('Y-m-d') . ' 00:00:00';
					$badge = 'warning';
				break;

				case 'weekly':
					$day = date('w');
					$period = $now->modify('-' . $day . ' days')->format('Y-m-d') . ' 00:00:00';
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
			}

			$issues = $todo->issues()
				->withTrashed()
				->whereIsActive()
				->where('datetimecreated', '>=', $period)
				->first();

			$todos[$i]->status = 'incomplete';
			// We found an item for this time period
			if ($issues)
			{
				$todos[$i]->status = 'complete';
				$todos[$i]->issue = $issues->id;
				//unset($todos[$i]);
			}
		}
		?>
		@if (count($todos))
			<ul class="list-group checklist">
				@foreach ($todos as $todo)
					<li class="list-group-item {{ $todo->status == 'complete' ? 'hide complete' : 'incomplete' }}">
						<div class="form-group">
							<div class="form-check">
								<input type="checkbox"
									class="form-check-input issue-todo"
									data-name="{{ $todo->name }}"
									data-id="{{ $todo->id }}"
									data-api="{{ route('api.issues.create') }}"
									data-issue="{{ $todo->issue }}"
									name="todo{{ $todo->id }}"
									id="todo{{ $todo->id }}"
									value="1"
									{{ $todo->status == 'complete' ? 'checked="checked"' : '' }} />
								<label class="form-check-label" for="todo{{ $todo->id }}"><span class="badge badge-{{ $badge }}">{{ $todo->timeperiod->name }}</span> {{ $todo->name }}</label>
								@if ($todo->description)
									<div class="form-text text-muted">{{ $todo->formattedDescription }}</div>
								@endif
							</div>
							<span class="issue-todo-alert tip"><i class="fa" aria-hidden="true"></i></span>
						</div>
					</li>
				@endforeach
			</ul>
		@else
			<ul class="list-group checklist">
				<li class="list-group-item text-center">All caught up!</li>
			</ul>
		@endif
	</div>

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-6">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-3">
				<label class="sr-only" for="filter_start">{{ trans('issues::issues.start') }}</label>
				<span class="input-group">
					<input type="text" name="start" id="filter_start" class="form-control filter filter-submit date" value="{{ $filters['start'] }}" placeholder="Start date" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span>
				</span>
			</div>
			<div class="col col-md-3">
				<label class="sr-only" for="filter_stop">{{ trans('issues::issues.stop') }}</label>
				<span class="input-group">
					<input type="text" name="stop" id="filter_stop" class="form-control filter filter-submit date" value="{{ $filters['stop'] }}" placeholder="End date" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
				</span>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('issues::issues.issues') }}</caption>
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
					{!! Html::grid('sort', trans('issues::issues.report'), 'report', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('issues::issues.resources') }}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('issues::issues.created'), 'datetimecreated', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-right">
					{{ trans('issues::issues.comments') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete issues'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"><span class="sr-only">{{ trans('global.admin.record id', ['id' => $row->id]) }}</span></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit issues'))
						<a href="{{ route('admin.issues.edit', ['id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit($row->report, 70) }}
						</a>
					@else
						<span>
							{{ Illuminate\Support\Str::limit($row->report, 70) }}
						</span>
					@endif
				</td>
				<td class="priority-4">
					@if ($r = $row->resourcesString)
						{{ $r }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datetimecreated)
							<time datetime="{{ $row->datetimecreated }}">
								@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->datetimecreated->diffForHumans() }}
								@else
									{{ $row->datetimecreated->format('Y-m-d') }}
								@endif
							</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4 text-right">
					@if ($row->comments_count)
						{{ $row->comments_count }}
					@else
						<span class="none">{{ $row->comments_count }}</span>
					@endif
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