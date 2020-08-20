@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.batchsystems'),
		route('admin.resources.batchsystems')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('create resources.batchsystems'))
		{!! Toolbar::addNew(route('admin.resources.batchsystems.create')) !!}
	@endif

	@if (auth()->user()->can('delete resources'))
		{!! Toolbar::deleteList('', route('admin.resources.batchsystems.delete')) !!}
	@endif

	@if (auth()->user()->can('admin resources.batchsystems'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('resources');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('resources.name') !!}: {{ trans('resources::resources.batchsystems') }}
@stop

@section('content')
@component('resources::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.resources.batchsystems') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar">
			<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
			<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

			<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('resources::assets.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('resources::assets.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{{ trans('resources::assets.resources') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit resources.batchsystems'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit resources.batchsystems'))
					<a href="{{ route('admin.resources.batchsystems.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit resources.batchsystems'))
					</a>
					@endif
				</td>
				<td class="priority-4 text-right">
					<a href="{{ route('admin.resources.index') }}?batchsystems={{ $row->id }}">
						{{ $row->resources_count }}
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>

@stop
