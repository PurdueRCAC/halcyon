@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script>
$(document).ready(function() {
	$('.searchable-select').select2({
		//placeholder: $(this).data('placeholder')
		})
		.on('select2:select', function (e) {
			if ($(this).hasClass('filter-submit')) {
				$(this).closest('form').submit();
			}
		});

	var dialog = $(".dialog").dialog({
		autoOpen: false,
		height: 'auto',
		width: 500,
		modal: true
	});

	$('#toolbar-plus').on('click', function(e){
		e.preventDefault();

		dialog.dialog("open");
	});

	$('#add-group').on('click', function(e){
		e.preventDefault();

		var url = $(this).data('api');
		var route = $(this).data('route');
		var name = document.getElementById("field-name").value;

		$.ajax({
			url: url,
			type: 'post',
			data: {
				name: name
			},
			dataType: 'json',
			async: false,
			success: function(data) {
				Halcyon.message('success', 'Created group ' + name);
				window.location = route.replace('-id-', data.data.id);
				//location.reload;
			},
			error: function(xhr, ajaxOptions, thrownError) {
				Halcyon.message('danger', 'Failed to create group ' + name);
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
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.groups.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-9 text-right">
				<label class="sr-only" for="filter_fieldofscience">{{ trans('groups::groups.field of science') }}</label>
				<select name="fieldofscience" id="filter_fieldofscience" class="form-control filter-submit searchable-select">
					<option value="0">{{ trans('groups::groups.all fields of science') }}</option>
					@foreach ($fields as $field)
						@php
						if ($field->level == 0):
							continue;
						endif;
						@endphp
						<option value="{{ $field->id }}"<?php if ($filters['fieldofscience'] == $field->id) { echo ' selected="selected"'; } ?>>{{ str_repeat('|- ', ($field->level - 1)) . $field->name }} (<?php echo App\Modules\Groups\Models\GroupFieldOfScience::where('fieldofscienceid', '=', $field->id)->count(); ?>)</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_department">{{ trans('groups::groups.department') }}</label>
				<select name="department" id="filter_department" class="form-control filter-submit searchable-select">
					<option value="0">{{ trans('groups::groups.all departments') }}</option>
					@foreach ($departments as $department)
						@php
						if ($department->level == 0):
							continue;
						endif;
						@endphp
						<option value="{{ $department->id }}"<?php if ($filters['department'] == $department->id) { echo ' selected="selected"'; } ?>>{{ str_repeat('|- ', ($department->level - 1)) . $department->name }} (<?php echo App\Modules\Groups\Models\GroupDepartment::where('collegedeptid', '=', $department->id)->count(); ?>)</option>
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
		<caption class="sr-only">{{ trans('groups::groups.groups') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete groups'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('groups::groups.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.unix group'), 'unixgroup', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{!! Html::grid('sort', trans('groups::groups.members'), 'members_count', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('groups::groups.department') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete groups'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit groups'))
						<a href="{{ route('admin.groups.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit groups'))
						</a>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit groups'))
						<a href="{{ route('admin.groups.edit', ['id' => $row->id]) }}">
					@endif
						<?php echo $row->unixgroup ? e($row->unixgroup) : '<span class="unknown">' . trans('global.none') . '</span>'; ?>
					@if (auth()->user()->can('edit groups'))
						</a>
					@endif
				</td>
				<td class="priority-4 text-right">
					<a href="{{ route('admin.groups.members', ['group' => $row->id]) }}">
						{{ $row->members_count }}
					</a>
				</td>
				<td class="priority-4">
					<?php
					$departments = array();
					foreach ($row->departmentList as $d)
					{
						$departments[] = $d->name;
					}
					echo !empty($departments) ? e(implode(', ', $departments)) : '<span class="unknown">' . trans('global.none') . '</span>';
					?>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<div class="dialog ui-front hide" title="{{ trans('groups::groups.create group') }}">
		<h3 class="sr-only">{{ trans('groups::groups.create group') }}</h3>

		<div class="form-group">
			<label for="field-name">{{ trans('groups::groups.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
			<input type="text" name="fields[name]" id="field-name" class="form-control required" maxlength="250" value="" />
		</div>

		<div class="form-group text-center">
			<button class="btn btn-primary" id="add-group" data-api="{{ route('api.groups.create') }}" data-route="{{ route('admin.groups.edit', ['id' => '-id-']) }}"><span class="icon-plus"></span> Add</button>
		</div>
	</div>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop
