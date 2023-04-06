@extends('layouts.master')

@php
	app('pathway')
		->append(
			trans('core::modules.module name'),
			route('admin.modules.index')
		);

	Toolbar::link('search', trans('core::modules.scan'), route('admin.modules.scan'), false);

	Toolbar::publishList(route('admin.modules.enable'), trans('global.toolbar.enable'));
	Toolbar::unpublishList(route('admin.modules.disable'), trans('global.toolbar.disable'));
@endphp

@section('toolbar')
	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('core::modules.module name') }}
@stop

@section('content')

<form action="{{ route('admin.modules.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col-md-8 text-right">
				<label class="sr-only" for="filter_state">{{ trans('core::modules.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('core::modules.all states') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled'): echo ' selected="selected"'; endif;?>>{{ trans('global.enabled') }}</option>
					<option value="disabled"<?php if ($filters['state'] == 'disabled'): echo ' selected="selected"'; endif;?>>{{ trans('global.disabled') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('core::modules.module manager') }}</caption>
		<thead>
			<tr>
				<th class="text-center">
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('core::modules.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('core::modules.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('core::modules.state'), 'state', $filters['order_dir'], $filters['order']) !!}
				</th>
				<!--<th scope="col" class="priority-6 text-center">
					{!! Html::grid('sort', trans('core::modules.admin'), 'client_id', $filters['order_dir'], $filters['order']) !!}
				</th>-->
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('core::modules.folder'), 'folder', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-5 text-center">
					{!! Html::grid('sort', trans('core::modules.ordering'), 'ordering', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr{!! $row->protected ? ' class="locked"' : '' !!}>
				<td class="text-center">
					@if ($row->protected)
						<span class="fa fa-lock" aria-hidden="true"></span>
						<span class="sr-only">{{ trans('global.yes') }}</span>
					@else
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-6">
					{{ $row->id }}
				</td>
				<td>
					<a href="{{ route('admin.modules.edit', ['id' => $row->id]) }}">
						{{ ($row->name == $row->element ? trans($row->name . '::' . $row->name . '.module name') : $row->name) }}
					</a>
				</td>
				<td class="priority-4">
					@if ($row->protected)
						@if ($row->enabled)
							<span class="badge badge-success">
								{{ trans('core::modules.enabled') }}
							</span>
						@else
							<span class="badge badge-secondary">
								{{ trans('core::modules.disabled') }}
							</span>
						@endif
					@else
						@if ($row->enabled)
							<a class="badge badge-success btn-state" href="{{ route('admin.modules.disable', ['id' => $row->id]) }}" title="{{ trans('core::modules.set state to', ['state' => trans('global.unpublished')]) }}">
								{{ trans('core::modules.enabled') }}
							</a>
						@else
							<a class="badge badge-secondary btn-state" href="{{ route('admin.modules.enable', ['id' => $row->id]) }}" title="{{ trans('core::modules.set state to', ['state' => trans('global.published')]) }}">
								{{ trans('core::modules.disabled') }}
							</a>
						@endif
					@endif
				</td>
				<td class="priority-6">
					<a href="{{ route('admin.modules.edit', ['id' => $row->id]) }}">
						{{ $row->folder ? $row->folder : 'extensions' }}
					</a>
				</td>
				<!-- <td class="priority-6 text-center">
					@if ($row->client_id)
						<span class="badge badge-success">{{ trans('global.yes') }}</span>
					@else
						<span class="badge badge-secondary">{{ trans('global.no') }}</span>
					@endif
				</td> -->
				<td class="priority-5 text-center">
					{{ $row->ordering }}
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
