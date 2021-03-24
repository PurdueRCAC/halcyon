@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/issues/js/admin.js?v=' . filemtime(public_path() . '/modules/issues/js/admin.js')) }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('issues::issues.module name'),
		route('admin.issues.index')
	)
	->append(
		trans('issues::issues.todos'),
		route('admin.issues.todos')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete issues'))
		{!! Toolbar::deleteList('', route('admin.issues.todos.delete')) !!}
	@endif

	@if (auth()->user()->can('create issues'))
		{!! Toolbar::addNew(route('admin.issues.todos.create')) !!}
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
{{ trans('issues::issues.module name') }}: {{ trans('issues::issues.todos') }}
@stop

@section('content')
@component('issues::admin.submenu')
	todos
@endcomponent

<form action="{{ route('admin.issues.todos') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

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

	<div class="card mb-4">
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
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"><span class="sr-only">{{ trans('global.admin.record id', ['id' => $row->id]) }}</span></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit issues'))
						<a href="{{ route('admin.issues.todos.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
				</td>
				<td class="priority-4">
					{{ $row->timeperiod->name }}
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