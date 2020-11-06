@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('storage::storage.module name'),
		route('admin.storage.index')
	)
	->append(
		trans('storage::storage.directories'),
		route('admin.storage.directories', ['parent' => $parent ? $parent->parentstoragedirid : 0])
	);
if ($parent)
{
	app('pathway')->append(
		$parent->storageResource->path . '/' . $parent->path
	);
}
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete storage.directories'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.storage.directories.delete')) !!}
	@endif

	@if (auth()->user()->can('create storage.directories'))
		{!! Toolbar::addNew(route('admin.storage.directories.create', ['parent' => $filters['parent']])) !!}
	@endif

	@if (auth()->user()->can('admin storage'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('storage')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('storage.name') !!}{{ $storage ? ': ' . $storage->name : '' }}
@stop

@section('content')
@component('storage::admin.submenu')
	@if (request()->segment(3) == 'types')
		types
	@else
		storage
	@endif
@endcomponent

<form action="{{ route('admin.storage.directories') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col filter-search col-md-4">
				<div class="form-group">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
				</span>
			</div>
			</div>
			<div class="col filter-select col-md-8 text-right">
				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all states') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active'): echo ' selected="selected"'; endif;?>>{{ trans('global.active') }}</option>
					<option value="inactive"<?php if ($filters['state'] == 'inactive'): echo ' selected="selected"'; endif;?>>{{ trans('global.inactive') }}</option>
				</select>

				<label class="sr-only" for="filter_state">{{ trans('storage::storage.resource') }}</label>
				<select name="resource" class="form-control filter filter-submit">
					<option value="0"<?php if (!$filters['resource']): echo ' selected="selected"'; endif;?>>{{ trans('global.all') }}</option>
					@foreach ($storages as $s)
						<option value="{{ $s->id }}"<?php if ($filters['resource'] == $s->id): echo ' selected="selected"'; endif;?>>{{ $s->name }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('storage::storage.directories') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete storage'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('storage::storage.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('storage::storage.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('storage::storage.path'), 'path', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{!! Html::grid('sort', trans('storage::storage.quota'), 'bytes', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3">
					{{ trans('storage::storage.owner') }}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('storage::storage.storage'), 'storageresourceid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{{ trans('storage::storage.directories') }}
				</th>
				<!-- <th scope="col" class="priority-4">{{ trans('storage::storage.created') }}</th>
				<th scope="col" class="priority-4">{{ trans('storage::storage.removed') }}</th>
				<th scope="col" colspan="4">{{ trans('storage::storage.permissions') }} r/w/x</th> -->
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete storage'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if ($row->isTrashed())
						<span class="glyph icon-trash">{{ trans('global.trashed') }}</span>
					@endif
					@if (auth()->user()->can('edit storage'))
					<a href="{{ route('admin.storage.directories.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit storage'))
					</a>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit storage'))
					<a href="{{ route('admin.storage.directories.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->storageResource->path . '/' . $row->path }}
					@if (auth()->user()->can('edit storage'))
					</a>
					@endif
				</td>
				<td class="priority-4 text-right">
					@if ($row->bytes)
						{{ App\Halcyon\Utility\Number::formatBytes($row->bytes) }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-3">
					@if ($row->group)
						@if (auth()->user()->can('manage groups'))
							<a href="{{ route('admin.groups.edit', ['id' => $row->groupid]) }}">
								{{ $row->group->name }}
							</a>
						@else
							{{ $row->group->name }}
						@endif
					@else
						{!! $row->owner ? e($row->owner->name) : '<span class="unknown">' . trans('global.unknown') . '</span>' !!}
					@endif
				</td>
				<td class="priority-4">
					@if ($row->storageResource)
						{{ $row->storageResource->name }}
					@else
						<span class="unknown">{{ trans('global.unknown') }}</span>
					@endif
				</td>
				<td class="priority-4 text-right">
					@if ($c = $row->children()->count())
						<a href="{{ route('admin.storage.directories', ['parent' => $row->id]) }}">
							{{ number_format($c) }}
						</a>
					@else
						<a class="btn btn-sm btn-success" href="{{ route('admin.storage.directories.create', ['parent' => $row->id, 'resource' => $row->storageresourceid]) }}">
							<span class="icon-plus"></span><span class="sr-only">{{ trans('global.add') }}</span>
						</a>
						<span class="none">0</span>
					@endif
				</td>
				<!--<td>
					@if ($row->ownerread)
						<span class="state yes">{{ trans('global.yes') }}</span>
					@else
						<span class="state no">{{ trans('global.no') }}</span>
					@endif
				</td>
				<td>
					@if ($row->ownerwrite)
						<span class="state yes">{{ trans('global.yes') }}</span>
					@else
						<span class="state no">{{ trans('global.no') }}</span>
					@endif
				</td>
				<td>
					@if ($row->groupread)
						<span class="state yes">{{ trans('global.yes') }}</span>
					@else
						<span class="state no">{{ trans('global.no') }}</span>
					@endif
				</td>
				<td>
					@if ($row->groupwrite)
						<span class="state yes">{{ trans('global.yes') }}</span>
					@else
						<span class="state no">{{ trans('global.no') }}</span>
					@endif
				</td> -->
			</tr>
		@endforeach
		</tbody>
	</table>
</div>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop