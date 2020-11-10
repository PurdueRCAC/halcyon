@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/courses/js/admin.js') }}"></script>
<script>
$(document).ready(function() {
	var dialog = $("#new-account").dialog({
		autoOpen: false,
		height: 200,
		width: 500,
		modal: true
	});

	$('#toolbar-plus').on('click', function(e){
		e.preventDefault();

		dialog.dialog("open");
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('courses::courses.module name'),
		route('admin.courses.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete courses'))
		{!! Toolbar::deleteList('', route('admin.courses.delete')) !!}
	@endif

	@if (auth()->user()->can('create courses'))
		{!! Toolbar::addNew(route('admin.courses.create')) !!}
	@endif

	@if (auth()->user()->can('admin courses'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('courses')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('courses.name') !!}
@stop

@section('content')
<form action="{{ route('admin.courses.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-7 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-2 text-right">
				<div class="form-group">
					<label for="member-userid" class="sr-only">{{ trans('courses::courses.member') }}:</label>
					<span class="input-group">
						<input type="text" name="userid" id="filter-userid" class="form-control form-users submit" data-uri="{{ route('api.users.index') }}?search=%s" value="{{ $filters['userid'] ? App\Modules\Users\Models\User::find($filters['userid'])->name . ':' . $filters['userid'] : '' }}" />
						<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-3 text-right filter-select">
				<label class="sr-only" for="filter_semester">{{ trans('courses::courses.semester') }}</label>
				<select name="semester" id="filter_semester" class="form-control filter filter-submit">
					<option value=""<?php if (!$filters['semester']): echo ' selected="selected"'; endif;?>>{{ trans('courses::courses.all semesters') }}</option>
					@foreach ($semesters as $semester)
						<option value="{{ $semester->semester }}"<?php if ($filters['semester'] == $semester->semester): echo ' selected="selected"'; endif;?>>{{ $semester->semester }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value=""<?php if (!$filters['state']): echo ' selected="selected"'; endif;?>>{{ trans('courses::courses.all states') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active'): echo ' selected="selected"'; endif;?>>{{ trans('global.active') }}</option>
					<option value="inactive"<?php if ($filters['state'] == 'inactive'): echo ' selected="selected"'; endif;?>>{{ trans('global.inactive') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('courses::courses.courses') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete courses'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('courses::courses.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('courses::courses.course name'), 'classname', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('courses::courses.department'), 'department', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3">
					{!! Html::grid('sort', trans('courses::courses.course number'), 'coursenumber', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('courses::courses.semester'), 'semester', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('courses::courses.resource') }}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('courses::courses.date start'), 'datetimestart', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('courses::courses.date stop'), 'datetimestop', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-right">
					{!! Html::grid('sort', trans('courses::courses.students'), 'studentcount', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<?php
			switch ($row->state)
			{
				case '2': // Deleted
					$task = 'publish';
					$alt  = trans('global.trashed');
					$cls  = 'trash';
				break;
				case '1': // Published
					$task = 'unpublish';
					$alt  = trans('global.published');
					$cls  = 'publish';
				break;
				case '0': // Unpublished
				default:
					$task = 'publish';
					$alt  = trans('global.unpublished');
					$cls  = 'unpublish';
				break;
			}

			switch ($row->active)
			{
				case '1': // Published
					$alt2 = trans('courses::courses.active');
					$cls2 = 'publish';
				break;
				case '0': // Unpublished
				default:
					$alt2 = trans('courses::courses.inactive');
					$cls2 = 'unpublish';
				break;
			}
			?>
			<tr>
				@if (auth()->user()->can('delete courses'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit courses'))
						<a href="{{ route('admin.courses.edit', ['id' => $row->id]) }}">
							@if ($row->classname)
								{{ $row->classname }}
							@else
								<span class="none">{{ trans('global.none') }}</span>
							@endif
						</a>
					@else
						<span>
							@if ($row->classname)
								{{ $row->classname }}
							@else
								<span class="none">{{ trans('global.none') }}</span>
							@endif
						</span>
					@endif
				</td>
				<td>
					@if ($row->department)
						{{ $row->department }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-3">
					@if ($row->coursenumber)
						{{ $row->coursenumber }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-2">
					@if ($row->semester)
						{{ $row->semester }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-2">
					@if ($row->resource)
						{{ $row->resource->name }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datetimestart && $row->datetimestart != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimestart }}">{{ $row->datetimestart->format('Y-m-d') }}</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-2">
					<span class="datetime">
						@if ($row->datetimestop && $row->datetimestop != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimestop }}">{{ $row->datetimestop->format('Y-m-d') }}</time>
						@else
							<span class="never">{{ trans('global.never') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-2 text-right">
					@if (auth()->user()->can('edit courses'))
						<a href="{{ route('admin.courses.members', ['id' => $row->id]) }}">
							{{ $row->studentcount }}
						</a>
					@else
						{{ $row->studentcount }}
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

	<div id="new-account" class="hide" title="{{ trans('courses::courses.choose user') }}">
		<h2 class="modal-title sr-only">{{ trans('courses::courses.choose user') }}</h2>

		<div class="form-group">
			<label for="field-userid">{{ trans('courses::courses.owner') }}:</label>
			<span class="input-group">
				<input type="text" name="userid" id="field-userid" class="form-control form-users redirect" data-uri="{{ route('api.users.index') }}?search=%s" data-location="{{ route('admin.courses.create') }}?userid=%s" value="" />
				<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
			</span>
		</div>
	</div>

	@csrf
</form>

@stop