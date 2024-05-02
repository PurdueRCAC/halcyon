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
{{ trans('contactreports::contactreports.module name') }}: {{ trans('contactreports::contactreports.types') }}
@stop

@section('content')

@component('contactreports::admin.submenu')
	types
@endcomponent

<form action="{{ route('admin.contactreports.types') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 filter-search">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
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
				<th scope="col" class="text-right text-end">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('contactreports::contactreports.reports'), 'reports_count', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('contactreports::contactreports.followup'), 'timeperiodid', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('contactreports::contactreports.pause followups'), 'waitperiodid', $filters['order_dir'], $filters['order']); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete contactreports.types'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit contactreports.types'))
						<a href="{{ route('admin.contactreports.types.edit', ['id' => $row->id]) }}">
							{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
						</a>
					@else
						<span>
							{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
						</span>
					@endif
				</td>
				<td class="text-right text-end">
					<a href="{{ route('admin.contactreports.index', ['type' => $row->id]) }}">
						{{ number_format($row->reports_count) }}
					</a>
				</td>
				<td>
					@if ($row->timeperiodid)
						{{ $row->timeperiodcount }} {{ $row->timeperiod->plural }} after
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td>
					@if ($row->waitperiodid)
						{{ $row->waitperiodcount }} {{ $row->waitperiod->plural }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
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
</form>

@stop