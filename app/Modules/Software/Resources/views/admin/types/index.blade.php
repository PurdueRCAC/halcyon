@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('software::software.module name'),
		route('admin.software.index')
	)
	->append(
		trans('software::software.types')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete software.types'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.software.types.delete')) !!}
	@endif

	@if (auth()->user()->can('create software.types'))
		{!! Toolbar::addNew(route('admin.software.types.create')) !!}
	@endif

	@if (auth()->user()->can('admin software.types'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('software')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('software::software.module name') }}: {{ trans('software::software.types') }}
@stop

@section('content')

@component('software::admin.submenu')
	types
@endcomponent

<form action="{{ route('admin.software.types') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-12 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('software::software.types') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete software.types'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col">
					{!! Html::grid('sort', trans('software::software.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('software::software.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('software::software.alias'), 'alias', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-right">
					{!! Html::grid('sort', trans('software::software.applications'), 'applications_count', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete software.types'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					@if (auth()->user()->can('edit software.types'))
						<a href="{{ route('admin.software.types.edit', ['id' => $row->id]) }}">
							{{ $row->id }}
						</a>
					@else
						{{ $row->id }}
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit software.types'))
						<a href="{{ route('admin.software.types.edit', ['id' => $row->id]) }}">
							{{ $row->title }}
						</a>
					@else
						{{ $row->title }}
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit software.types'))
						<a href="{{ route('admin.software.types.edit', ['id' => $row->id]) }}">
							{{ $row->alias }}
						</a>
					@else
						{{ $row->alias }}
					@endif
				</td>
				<td class="text-right">
					{{ $row->applications_count }}
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
</form>

@stop