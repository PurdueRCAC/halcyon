@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.registration fields')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete users.registration'))
		{!! Toolbar::deleteList('', route('admin.users.registration.delete')) !!}
	@endif

	@if (auth()->user()->can('create users.registration'))
		{!! Toolbar::addNew(route('admin.users.registration.create')) !!}
	@endif

	@if (auth()->user()->can('admin users'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('users')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::users.module name') }}: {{ trans('users::access.levels') }}
@stop

@section('content')

@component('users::admin.submenu')
	registration
@endcomponent

<form action="{{ route('admin.users.registration') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-xs-12 col-sm-12 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card md-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('users::users.users') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('users::access.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('users::registration.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('users::registration.type'), 'type', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-center">
					{!! Html::grid('sort', trans('users::registration.required'), 'required', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-center">
					{!! Html::grid('sort', trans('users::registration.include admin'), 'include_admin', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$ordering  = ($filters['order'] == 'ordering');
		$n = $rows->count();
		?>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit users.registration'))
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-4">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit users.registration'))
						<a href="{{ route('admin.users.registration.edit', ['id' => $row->id]) }}">
							{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
						</a>
					@else
						{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
					@endif
				</td>
				<td>
					{{ trans('users::registration.fieldtype.' . $row->type) }}
				</td>
				<td class="text-center">
					<span class="badge badge-{{$row->required ? 'success' : 'danger' }}">
						{{ $row->required ? 'Yes' : 'No' }}
					</span>
				</td>
				<td class="text-center">
					<span class="badge badge-{{$row->include_admin ? 'success' : 'danger' }}">
						{{ $row->include_admin ? 'Yes' : 'No' }}
					</span>
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