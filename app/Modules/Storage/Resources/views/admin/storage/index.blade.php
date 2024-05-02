@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/storage/js/admin.js') }}"></script>
@endpush

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
{{ trans('storage::storage.module name') }}
@stop

@section('content')
@component('storage::admin.submenu')
	storage
@endcomponent

<form action="{{ route('admin.storage.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col filter-search col-md-4">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col filter-select col-md-8 text-right text-end">
				<!-- <div class="btn-group" role="group" aria-label="{{ trans('global.state') }}">
					<a class="btn btn-outline-secondary<?php if ($filters['state'] == '*'): echo ' active"'; endif; ?>" href="{{ route('admin.storage.index', ['state' => '*']) }}">{{ trans('global.option.all states') }}</a>
					<a class="btn btn-outline-secondary<?php if ($filters['state'] == 'active'): echo ' active"'; endif; ?>" href="{{ route('admin.storage.index', ['state' => 'active']) }}">{{ trans('global.active') }}</a>
					<a class="btn btn-outline-secondary<?php if ($filters['state'] == 'inactive'): echo ' active"'; endif; ?>" href="{{ route('admin.storage.index', ['state' => 'inactive']) }}">{{ trans('global.inactive') }}</a>
				</div> -->

				<label class="sr-only visually-hidden" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all states') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active'): echo ' selected="selected"'; endif;?>>{{ trans('global.active') }}</option>
					<option value="inactive"<?php if ($filters['state'] == 'inactive'): echo ' selected="selected"'; endif;?>>{{ trans('global.inactive') }}</option>
				</select>

				<label class="sr-only visually-hidden" for="filter_resource">{{ trans('storage::storage.resource') }}</label>
				<select name="resource" id="filter_resource" class="form-control filter filter-submit searchable-select">
					<option value="0">{{ trans('storage::storage.all resources') }}</option>
					@foreach ($resources as $resource)
						<option value="{{ $resource->id }}"<?php if ($filters['resource'] == $resource->id): echo ' selected="selected"'; endif;?>>{{ str_repeat('- ', $resource->level) . $resource->name }}</option>
					@endforeach
				</select>
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
			<caption class="sr-only visually-hidden">{{ trans('storage::storage.module name') }}</caption>
			<thead>
				<tr>
					@if (auth()->user()->can('delete storage'))
						<th>
							{!! Html::grid('checkall') !!}
						</th>
					@endif
					<th scope="col" class="priority-6">
						{!! Html::grid('sort', trans('storage::storage.id'), 'id', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{!! Html::grid('sort', trans('storage::storage.name'), 'name', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{!! Html::grid('sort', trans('storage::storage.path'), 'path', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-5 text-right text-end">
						{!! Html::grid('sort', trans('storage::storage.quota space'), 'defaultquotaspace', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-5 text-right text-end">
						{!! Html::grid('sort', trans('storage::storage.quota file'), 'defaultquotafile', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-4">
						{!! Html::grid('sort', trans('storage::storage.resource'), 'storageresourceid', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="text-right text-end">
						{{ trans('storage::storage.directories') }}
					</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($rows as $i => $row)
					<tr<?php if ($row->trashed()) { echo ' class="trashed"'; } ?>>
						@if (auth()->user()->can('delete storage'))
							<td>
								{!! Html::grid('id', $i, $row->id) !!}
							</td>
						@endif
						<td class="priority-6">
							{{ $row->id }}
						</td>
						<td>
							@if ($row->trashed())
								<span class="text-danger" data-tip="{{ trans('global.trashed') }}: {{ $row->datetimeremoved->format('Y-m-d') }}">
									<span class="fa fa-trash" aria-hidden="true"></span>
									<span class="sr-only visually-hidden">{{ trans('global.trashed') }}: <time datetime="{{ $row->datetimeremoved->toDateTimeString() }}">{{ $row->datetimeremoved->format('Y-m-d') }}</time></span>
								</span>
							@endif
							@if (auth()->user()->can('edit storage'))
							<a href="{{ route('admin.storage.edit', ['id' => $row->id]) }}">
							@endif
								{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
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
						<td class="priority-5 text-right text-end">
							@if ($row->defaultquotaspace)
								{{ App\Halcyon\Utility\Number::formatBytes($row->defaultquotaspace, 0) }}
							@endif
						</td>
						<td class="priority-5 text-right text-end">
							@if ($row->defaultquotafile)
								{{ number_format($row->defaultquotafile, 2) }}
							@endif
						</td>
						<td class="priority-4">
							@if ($row->parentresourceid)
								@if ($row->resource)
									@if ($row->resource->trashed())
										<span class="fa fa-trash text-danger" aria-hidden="true"></span>
									@endif
									<a href="{{ route('admin.resources.edit', ['id' => $row->parentresourceid]) }}">
										{{ $row->resource->name }}
									</a>
								@else
									<span class="unknown">{{ trans('global.unknown') }}</span>
								@endif
							@endif
						</td>
						<td class="text-right text-end">
							@if ($row->directories_count)
								<a href="{{ route('admin.storage.directories', ['resource' => $row->id]) }}">
									{{ number_format($row->directories_count) }}
								</a>
							@else
								@if (!$row->trashed())
									<a class="btn btn-sm btn-success" href="{{ route('admin.storage.directories.create', ['resource' => $row->id, 'parent' => 0]) }}">
										<span class="fa fa-plus" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.add') }}</span>
									</a>
								@endif
								<span class="text-muted">0</span>
							@endif
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

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
@stop