@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.levels')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete users.levels'))
		{!! Toolbar::deleteList('', route('admin.users.levels.delete')) !!}
	@endif

	@if (auth()->user()->can('create users.levels'))
		{!! Toolbar::addNew(route('admin.users.levels.create')) !!}
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
	levels
@endcomponent

<form action="{{ route('admin.users.levels') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-xs-12 col-sm-6 col-md-3 filter-search">
				<div class="form-group mb-0">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<caption class="sr-only visually-hidden">{{ trans('users::access.levels') }}</caption>
					<thead>
						<tr>
							<th>
								{!! Html::grid('checkall') !!}
							</th>
							<th scope="col" class="priority-4">
								{!! Html::grid('sort', trans('users::access.id'), 'id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('users::access.title'), 'title', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{{ trans('users::users.visible for roles') }}
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
							<td class="center">
								@if (auth()->user()->can('edit users.levels'))
									{!! Html::grid('id', $i, $row->id) !!}
								@endif
							</td>
							<td class="center priority-4">
								{{ $row->id }}
							</td>
							<td>
								@if (auth()->user()->can('edit users.levels'))
									<a href="{{ route('admin.users.levels.edit', ['id' => $row->id]) }}">
										{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
									</a>
								@else
									{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
								@endif
							</td>
							<td>
								{{ $row->visibleByRoles() }}
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