@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/courses/js/admin.js?v=' . filemtime(public_path() . '/modules/courses/js/admin.js')) }}"></script>
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
			<div class="col col-md-6 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-3 text-right">
				<div class="form-group">
					<label for="member-userid" class="sr-only">{{ trans('courses::courses.member') }}:</label>
					<span class="input-group">
						<input type="text" name="userid" id="filter-userid" class="form-control form-users submit" data-uri="{{ route('api.users.index') }}?search=%s" placeholder="{{ trans('courses::courses.filter by user') }}" value="{{ $filters['userid'] ? App\Modules\Users\Models\User::find($filters['userid'])->name . ':' . $filters['userid'] : '' }}" />
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

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
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
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('courses::courses.department'), 'department', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-5">
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
					{{ trans('courses::courses.enrolled') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<?php
			$row->accounts = 0;

			event($e = new App\Modules\Courses\Events\AccountEnrollment($row));

			$row->enrollment = $e->enrollments;

			if (is_array($row->enrollment))
			{
				foreach ($row->enrollment as $student)
				{
					// Attempt to look up student in our records
					$u = App\Modules\Users\Models\User::findByOrganizationId($student->externalId);

					if ($u)
					{
						//$username = $u->username;

						// See if the they have host entry yet
						event($e = new App\Modules\Users\Events\UserLookup(['username' => $u->username, 'host' => $row->resource->rolename . '.rcac.purdue.edu']));

						if (count($e->results) > 0)
						{
							$row->accounts++;
						}
					}
				}
			}
			?>
			<tr>
				@if (auth()->user()->can('delete courses'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
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
							@endif
						</a>
					@else
						<span>
							@if ($row->classname)
								{{ $row->classname }}
							@endif
						</span>
					@endif
				</td>
				<td class="priority-4">
					@if ($row->department)
						{{ $row->department }}
					@endif
				</td>
				<td class="priority-5">
					@if ($row->coursenumber)
						{{ $row->coursenumber }}
					@endif
				</td>
				<td class="priority-2">
					@if ($row->semester)
						{{ $row->semester }}
					@endif
				</td>
				<td class="priority-4">
					@if ($row->resource)
						{{ $row->resource->name }}
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->hasStart())
							<time datetime="{{ $row->datetimestart->format('Y-m-d\TH:i:s\Z') }}">{{ $row->datetimestart->format('Y-m-d') }}</time>
						@endif
					</span>
				</td>
				<td class="priority-2">
					<span class="datetime">
						@if ($row->hasEnd())
							<time datetime="{{ $row->datetimestop->format('Y-m-d\TH:i:s\Z') }}">{{ $row->datetimestop->format('Y-m-d') }}</time>
						@endif
					</span>
				</td>
				<td class="priority-2 text-right">
					{{ $row->accounts }} /
					@if (auth()->user()->can('edit courses'))
						<a href="{{ route('admin.courses.members', ['account' => $row->id]) }}">
							{{ $row->studentcount ? $row->studentcount : $row->members()->withTrashed()->whereIsActive()->count() }}
						</a>
					@else
						{{ $row->studentcount ? $row->studentcount : $row->members()->withTrashed()->whereIsActive()->count() }}
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

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