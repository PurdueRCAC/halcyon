@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('contactreports::contactreports.module name'),
		route('admin.contactreports.index')
	)
	->append(
		trans('contactreports::contactreports.types')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete contactreports.types'))
		{!! Toolbar::deleteList('', route('admin.contactreports.types.delete')) !!}
	@endif

	@if (auth()->user()->can('create contactreports.types'))
		{!! Toolbar::addNew(route('admin.contactreports.types.create')) !!}
	@endif

	@if (auth()->user()->can('admin contactreports'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('contactreports')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('contactreports.name') !!}: {{ trans('contactreports::contactreports.types') }}
@stop

@section('content')

@component('contactreports::admin.submenu')
	types
@endcomponent

<form action="{{ route('admin.contactreports.types') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<thead>
			<tr>
				@if (auth()->user()->can('delete contactreports.types'))
					<th>
						<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
					</th>
				@endif
				<th scope="col" class="priority-5">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('contactreports::contactreports.id'), 'id', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('contactreports::contactreports.name'), 'name', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="text-right">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('contactreports::contactreports.reports'), 'reports_count', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('contactreports::contactreports.followup'), 'timeperiodid', $filters['order_dir'], $filters['order']); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete contactreports.types'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit contactreports.types'))
						<a href="{{ route('admin.contactreports.types.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						<span>
							{{ $row->name }}
						</span>
					@endif
				</td>
				<td class="text-right">
					<a href="{{ route('admin.contactreports.index', ['type' => $row->id]) }}">
						{{ $row->reports_count }}
					</a>
				</td>
				<td>
					@if ($row->timeperiodid)
						{{ $row->timeperiodcount }} {{ $row->timeperiod->plural }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
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