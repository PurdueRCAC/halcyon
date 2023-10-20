@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('software::software.module name'),
		route('admin.software.index')
	)
	->append(
		trans('software::software.applications')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit.state software'))
		{!!
			Toolbar::publishList(route('admin.software.publish'));
			Toolbar::unpublishList(route('admin.software.unpublish'));
			Toolbar::spacer();
		!!}
	@endif
	@if (auth()->user()->can('delete software'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.software.delete')) !!}
	@endif
	@if (auth()->user()->can('create software'))
		{!! Toolbar::addNew(route('admin.software.create')) !!}
	@endif
	@if (auth()->user()->can('admin software'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('software')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('software::software.module name') }}
@stop

@section('content')

@component('software::admin.submenu')
	applications
@endcomponent

<form action="{{ route('admin.software.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col filter-search col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col filter-select col-md-8 text-right">
				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_type">{{ trans('software::software.type') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value="0">{{ trans('software::software.all types') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->title }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_resource">{{ trans('software::software.resources') }}</label>
				<select class="filter_resource" name="resource" class="form-control filter filter-submit">
					<option value="0">{{ trans('software::software.all resources') }}</option>
					@foreach ($resources as $resource)
						<option value="{{ $resource->id }}"<?php if ($filters['resource'] == $resource->id): echo ' selected="selected"'; endif;?>>{{ $resource->name }}</option>
					@endforeach
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
		<caption class="sr-only">{{ trans('software::software.software') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('software::software.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('software::software.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('software::software.type'), 'type_id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('software::software.state'), 'state', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-righ">
					{{ trans('software::software.versions') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit software'))
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-6">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit software'))
						<a href="{{ route('admin.software.edit', ['id' => $row->id]) }}">
							{{ $row->title }}
						</a>
					@else
						{{ $row->title }}
					@endif
				</td>
				<td class="priority-3">
					@if ($row->type)
						@if (auth()->user()->can('edit software'))
							<a href="{{ route('admin.software.edit', ['id' => $row->id]) }}">
								{{ $row->type->title }}
							</a>
						@else
							{{ $row->type->title }}
						@endif
					@endif
				</td>
				<td>
					@if ($row->trashed())
						@if (auth()->user()->can('edit software'))
							<a class="badge badge-secondary state trashed" href="{{ route('admin.software.restore', ['id' => $row->id]) }}" data-tip="{{ trans('software::software.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('software::software.trashed') }}
						@if (auth()->user()->can('edit software'))
							</a>
						@endif
					@elseif ($row->isPublished())
						@if (auth()->user()->can('edit software'))
							<a class="badge badge-success" href="{{ route('admin.software.unpublish', ['id' => $row->id]) }}" data-tip="{{ trans('software::software.set state to', ['state' => trans('global.unpublished')]) }}">
						@endif
							{{ trans('software::software.published') }}
						@if (auth()->user()->can('edit software'))
							</a>
						@endif
					@else
						@if (auth()->user()->can('edit software'))
							<a class="badge badge-secondary" href="{{ route('admin.software.publish', ['id' => $row->id]) }}" data-tip="{{ trans('software::software.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('software::software.unpublished') }}
						@if (auth()->user()->can('edit software'))
							</a>
						@endif
					@endif
				</td>
				<td class="text-righ">
					@foreach ($row->versionsByResource() as $resource => $versions)
						<div>
							{{ $resource }}:
							@foreach ($versions as $version)
								<span class="badge badge-secondary">{{ $version->title }}</span>
							@endforeach
						</div>
					@endforeach
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