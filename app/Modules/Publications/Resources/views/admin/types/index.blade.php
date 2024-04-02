@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('publications::publications.module name'),
		route('admin.publications.index')
	)
	->append(
		trans('publications::publications.types')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete publications.types'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.publications.types.delete')) !!}
	@endif

	@if (auth()->user()->can('create publications.types'))
		{!! Toolbar::addNew(route('admin.publications.types.create')) !!}
	@endif

	@if (auth()->user()->can('admin publications.types'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('publications')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('publications::publications.module name') }}: {{ trans('publications::publications.types') }}
@stop

@section('content')

@component('publications::admin.submenu')
	types
@endcomponent

<form action="{{ route('admin.publications.types') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-12 filter-search">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
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
		<caption class="sr-only visually-hidden">{{ trans('publications::publications.types') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete publications.types'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('publications::publications.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('publications::publications.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('publications::publications.alias'), 'alias', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('publications::publications.publications'), 'publications_count', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete publications.types'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					@if (auth()->user()->can('edit publications.types'))
						<a href="{{ route('admin.publications.types.edit', ['id' => $row->id]) }}">
							{{ $row->id }}
						</a>
					@else
						{{ $row->id }}
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit publications.types'))
						<a href="{{ route('admin.publications.types.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit publications.types'))
						<a href="{{ route('admin.publications.types.edit', ['id' => $row->id]) }}">
							{{ $row->alias }}
						</a>
					@else
						{{ $row->alias }}
					@endif
				</td>
				<td class="priority-4">
					{{ $row->publications_count }}
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