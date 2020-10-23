@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('messages::messages.module name'),
		route('admin.messages.index')
	)
	->append(
		trans('messages::messages.types')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete messages.types'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.messages.types.delete')) !!}
	@endif

	@if (auth()->user()->can('create messages.types'))
		{!! Toolbar::addNew(route('admin.messages.types.create')) !!}
	@endif

	@if (auth()->user()->can('admin messages.types'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('messages')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('messages::messages.module name') }}: {{ trans('messages::messages.types') }}
@stop

@section('content')

@component('messages::admin.submenu')
	types
@endcomponent

<form action="{{ route('admin.messages.types') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-12 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('messages::messages.types') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('messages::messages.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('messages::messages.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('messages::messages.classname'), 'classname', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('messages::messages.resource'), 'resourceid', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit messages.types'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					@if (auth()->user()->can('edit messages.types'))
						<a href="{{ route('admin.messages.types.edit', ['id' => $row->id]) }}">
							{{ $row->id }}
						</a>
					@else
						{{ $row->id }}
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit messages.types'))
						<a href="{{ route('admin.messages.types.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit messages.types'))
						<a href="{{ route('admin.messages.types.edit', ['id' => $row->id]) }}">
							{{ $row->classname }}
						</a>
					@else
						{{ $row->classname }}
					@endif
				</td>
				<td class="priority-4">
					@if ($row->resource)
						@if ($row->resource->isTrashed())
							<span class="icon-trash trash">
						@endif
						{{ $row->resource->name }}
						@if ($row->resource->isTrashed())
							</span>
						@endif
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
	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>

@stop