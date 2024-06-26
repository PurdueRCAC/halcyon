@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

	var sels = document.querySelectorAll(".searchable-select");
	if (sels.length) {
		sels.forEach(function (sel) {
			new TomSelect(sel, {
				plugins: ['dropdown_input']
			});
		});
	}

	var btnnew = document.getElementById('toolbar-plus');
	if (btnnew) {
		btnnew.setAttribute('data-toggle', 'modal');
		btnnew.setAttribute('data-target', '#new-group');
		btnnew.setAttribute('data-bs-toggle', 'modal');
		btnnew.setAttribute('data-bs-target', '#new-group');

		btnnew.addEventListener('click', function (e) {
			e.preventDefault();
		});
	}

	document.getElementById('add-group').addEventListener('click', function(e){
		e.preventDefault();

		var url = this.getAttribute('data-api');
		var route = this.getAttribute('data-route');
		var name = document.getElementById('field-name').value;

		fetch(url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
			},
			body: JSON.stringify({
				name: name
			})
		})
		.then(function (response) {
			if (response.ok) {
				return response.json();
			}
			return response.json().then(function (data) {
				var msg = data.message;
				if (typeof msg === 'object') {
					msg = Object.values(msg).join('<br />');
				}
				throw msg;
			});
		})
		.then(function (data) {
			Halcyon.message('success', 'Created group ' + name);
			window.location = route.replace('-id-', data.id);
		})
		.catch(function (err) {
			if (err) {
				Halcyon.message('danger', err);
			}
		});
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('groups::groups.module name'),
		route('admin.groups.index')
	)
	->append(
		trans('groups::groups.groups'),
		route('admin.groups.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete groups'))
		{!! Toolbar::deleteList('', route('admin.groups.delete')) !!}
	@endif

	@if (auth()->user()->can('create groups'))
		{!! Toolbar::addNew(route('admin.groups.create')) !!}
	@endif

	@if (auth()->user()->can('admin groups'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('groups')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('groups.name') !!}
@stop

@section('content')
@component('groups::admin.submenu')
	groups
@endcomponent

<form action="{{ route('admin.groups.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3 filter-search">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col col-md-9 text-right text-end">
				<label class="sr-only visually-hidden" for="filter-state">{{ trans('groups::groups.state') }}</label>
				<select name="state" id="filter-state" class="form-control filter filter-submit">
					<option value="*">{{ trans('groups::groups.all states') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled') { echo ' selected="selected"'; } ?>>{{ trans('global.enabled') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active') { echo ' selected="selected"'; } ?>>&nbsp; &nbsp; {{ trans('groups::groups.active allocation') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only visually-hidden" for="filter_fieldofscience">{{ trans('groups::groups.field of science') }}</label>
				<select name="fieldofscience" id="filter_fieldofscience" class="form-control filter-submit searchable-select">
					<option value="0">{{ trans('groups::groups.all fields of science') }}</option>
					@foreach ($fields as $field)
						@php
						if ($field->level == 0):
							continue;
						endif;
						@endphp
						<option value="{{ $field->id }}"<?php if ($filters['fieldofscience'] == $field->id) { echo ' selected="selected"'; } ?>>{{ str_repeat('|- ', ($field->level - 1)) . $field->name }} ({{ $field->groups_count }})</option>
					@endforeach
				</select>

				<label class="sr-only visually-hidden" for="filter_department">{{ trans('groups::groups.department') }}</label>
				<select name="department" id="filter_department" class="form-control filter-submit searchable-select">
					<option value="0">{{ trans('groups::groups.all departments') }}</option>
					@foreach ($departments as $department)
						@php
						if ($department->level == 0):
							continue;
						endif;
						@endphp
						<option value="{{ $department->id }}"<?php if ($filters['department'] == $department->id) { echo ' selected="selected"'; } ?>>{{ str_repeat('|- ', ($department->level - 1)) . $department->name }} ({{ $department->groups_count }})</option>
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
		<caption class="sr-only visually-hidden">{{ trans('groups::groups.groups') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete groups'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('groups::groups.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.unix group'), 'unixgroup', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right text-end">
					{!! Html::grid('sort', trans('groups::groups.members'), 'members_count', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-6">
					{{ trans('groups::groups.department') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete groups'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-6">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit groups'))
						<a href="{{ route('admin.groups.show', ['id' => $row->id]) }}">
					@endif
						{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
					@if (auth()->user()->can('edit groups'))
						</a>
					@endif
				</td>
				<td>
					@if ($row->unixgroup)
						@if (auth()->user()->can('edit groups'))
							<a href="{{ route('admin.groups.show', ['id' => $row->id]) }}">
						@endif
								{{ $row->unixgroup }}
						@if (auth()->user()->can('edit groups'))
							</a>
						@endif
					@endif
				</td>
				<td class="priority-4 text-right text-end">
					{{ number_format($row->members_count) }}
				</td>
				<td class="priority-6">
					@php
					$deps = array();
					foreach ($row->departmentList as $dep):
						$name  = $dep->parentid > 1 ? $dep->parent->name . ' &rsaquo; ' : '';
						$name .= $dep->name;

						$deps[] = $name;
					endforeach;
					//$row->departmentList->pluck('name')->toArray()
					@endphp
					{!! implode('<br />', $deps) !!}
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

	@csrf
</form>

<form id="new-group" class="modal fade" tabindex="-1" aria-labelledby="new-group-title" aria-hidden="true" method="get" action="{{ route('admin.groups.create') }}">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="new-group-title">{{ trans('groups::groups.create group') }}</h3>
				<button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
					<span class="visually-hidden" aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="field-name">{{ trans('groups::groups.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" required maxlength="48" value="" />
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" id="add-group" data-api="{{ route('api.groups.create') }}" data-route="{{ route('admin.groups.edit', ['id' => '-id-']) }}">
					{{ trans('global.button.create') }}
				</button>
			</div>
		</div>
	</div>

	@csrf
</form>
@stop
