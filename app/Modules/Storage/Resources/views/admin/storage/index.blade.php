@extends('layouts.master')

@section('scripts')
<script src="{{ Module::asset('storage:js/admin.js') . '?v=' . filemtime(public_path() . '/modules/storage/js/admin.js') }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('storage::storage.module name'),
		route('admin.storage.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete storage'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.storage.delete')) !!}
	@endif

	@if (auth()->user()->can('create storage'))
		{!! Toolbar::addNew(route('admin.storage.create')) !!}
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
{!! config('storage.name') !!}
@stop

@section('content')
@component('storage::admin.submenu')
	@if (request()->segment(3) == 'types')
		types
	@else
		storage
	@endif
@endcomponent

<form action="{{ route('admin.storage.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

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
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('storage::storage.module name') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
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
					{!! Html::grid('sort', trans('storage::storage.quota space'), 'defaultquotaspace', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3 text-right">
					{!! Html::grid('sort', trans('storage::storage.quota file'), 'defaultquotafile', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('storage::storage.resource'), 'storageresourceid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{{ trans('storage::storage.directories') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit storage'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit storage'))
					<a href="{{ route('admin.storage.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit storage'))
					</a>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit storage'))
					<a href="{{ route('admin.storage.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->path }}
					@if (auth()->user()->can('edit storage'))
					</a>
					@endif
				</td>
				<td class="priority-4 text-right">
					@if ($row->defaultquotaspace)
						{{ App\Halcyon\Utility\Number::formatBytes($row->defaultquotaspace) }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-3 text-right">
					@if ($row->defaultquotafile)
						{{ number_format($row->defaultquotafile, 2) }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<!-- <td class="priority-4">
					<span class="datetime">
						@if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimecreated }}">{{ $row->datetimecreated }}</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->getOriginal('datetimeremoved') && $row->getOriginal('datetimeremoved') != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimeremoved }}">{{ $row->datetimeremoved }}</time>
						@else
							<span class="never">{{ trans('global.never') }}</span>
						@endif
					</span>
				</td> -->
				<td class="priority-4">
					@if ($row->parentresourceid)
						@if ($row->resource)
							<a href="{{ route('admin.resources.edit', ['id' => $row->parentresourceid]) }}">
								{{ $row->resource->name }}
							</a>
						@else
							<span class="unknown">{{ trans('global.unknown') }}</span>
						@endif
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-4 text-right">
					@if ($row->directories_count)
						<a href="{{ route('admin.storage.directories', ['resource' => $row->id]) }}">
							{{ number_format($row->directories_count) }}
						</a>
					@else
						<a class="btn btn-sm btn-success" href="{{ route('admin.storage.directories.create', ['resource' => $row->id, 'parent' => 0]) }}">
							<span class="icon-plus"></span><span class="sr-only">{{ trans('global.add') }}</span>
						</a>
						<span class="none">0</span>
					@endif
				</td>
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