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

<form action="{{ route('admin.messages.types') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3 filter-search">
				<div class="form-group">
					<label class="form-label sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
		<div class="table-responsive">
			<table class="table table-hover adminlist">
				<caption class="sr-only visually-hidden">{{ trans('messages::messages.types') }}</caption>
				<thead>
					<tr>
						@if (auth()->user()->can('delete messages.types'))
							<th>
								{!! Html::grid('checkall') !!}
							</th>
						@endif
						<th scope="col">
							{!! Html::grid('sort', trans('messages::messages.id'), 'id', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('messages::messages.name'), 'name', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('messages::messages.classname'), 'classname', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('messages::messages.resource'), 'resourceid', $filters['order_dir'], $filters['order']) !!}
						</th>
					</tr>
				</thead>
				<tbody>
				@foreach ($rows as $i => $row)
					<tr>
						@if (auth()->user()->can('delete messages.types'))
							<td>
								{!! Html::grid('id', $i, $row->id) !!}
							</td>
						@endif
						<td>
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
						<td>
							@if ($row->resource)
								@if ($row->resource->trashed())
									<del class="text-danger" data-tip="{{ trans('messages::messages.resource is trashed') }}">
								@endif
								{{ $row->resource->name }}
								@if ($row->resource->trashed())
									</del>
								@endif
							@endif
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