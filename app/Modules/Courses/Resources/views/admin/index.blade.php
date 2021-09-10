@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/courses/js/admin.js?v=' . filemtime(public_path() . '/modules/courses/js/admin.js')) }}"></script>
<script>
$(document).ready(function() {
	var searchusers = $('#filter_userid');
	if (searchusers.length) {
		searchusers.each(function(i, el){
			$(el).select2({
				ajax: {
					url: $(el).data('api'),
					dataType: 'json',
					maximumSelectionLength: 1,
					data: function (params) {
						var query = {
							search: params.term,
							order: 'name',
							order_dir: 'asc'
						}

						return query;
					},
					processResults: function (data) {
						for (var i = 0; i < data.data.length; i++) {
							if (data.data[i].id) {
								data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
							} else {
								data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
								data.data[i].id = data.data[i].username;
							}
						}

						return {
							results: data.data
						};
					}
				},
				templateResult: function (state) {
					if (isNaN(state.id) && typeof state.name != 'undefined') {
						return $('<span>' + state.text + ' <span class="text-warning ml-1"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span> No local account</span></span>');
					}
					return state.text;
				}
			});
		});
		searchusers.on('select2:select', function (e) {
			var data = e.params.data;
			window.location = $(this).data('url') + "?userid=" + data.id;
		});
		searchusers.on('select2:unselect', function (e) {
			var data = e.params.data;
			window.location = $(this).data('url') + "?userid=";
		});
	}
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
			<div class="col col-md-3 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-3 text-right">
				<!-- <div class="form-group">
					<label for="member-userid" class="sr-only">{{ trans('courses::courses.member') }}:</label>
					<span class="input-group">
						<input type="text" name="userid" id="filter-userid" class="form-control form-users submit" data-uri="{{ route('api.users.index') }}?search=%s" placeholder="{{ trans('courses::courses.filter by user') }}" value="{{ $filters['userid'] ? App\Modules\Users\Models\User::find($filters['userid'])->name . ':' . $filters['userid'] : '' }}" />
						<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
					</span>
				</div> -->
				<div class="form-group">
					<label class="sr-only" for="filter_userid">{{ trans('courses::courses.member') }}</label>
					<select name="userid" id="filter_userid" class="form-control filter_search filter" multiple="multiple" data-placeholder="Select users..." data-api="{{ route('api.users.index') }}" data-url="{{ request()->url() }}">
						<option value="">Select users...</option>
						@if ($filters['userid'])
							@php
							$s = $filters['userid'];
							if (is_numeric($filters['userid'])):
								$u = App\Modules\Users\Models\User::find($filters['userid']);
								if ($u && $u->id):
									$s = $u->name . ' (' . $u->username . ')';
								endif;
							endif;
							@endphp
							<option value="{{ $filters['userid'] }}" selected="selected">{{ $s }}</option>
						@endif
					</select>
					<!-- <span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span> -->
				</div>
			</div>
			<div class="col col-md-6 text-right filter-select">
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
					{!! Html::grid('sort', trans('courses::courses.owner'), 'userid', $filters['order_dir'], $filters['order']) !!}
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
		<?php
		$now = Carbon\Carbon::now()->toDateTimeString();
		?>
		@foreach ($rows as $i => $row)
			<?php
			$row->accounts = 0;

			/*if ($row->semester != 'Workshop')
			{
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
			}*/
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
					@if ($row->user)
						{{ $row->user->name }}
					@endif
				</td>
				<td class="priority-5">
					@if ($row->department)
						{{ $row->department }}
					@endif
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
					@if (auth()->user()->can('edit courses'))
						<a href="{{ route('admin.courses.members', ['account' => $row->id]) }}">
							{{ $row->studentcount ? $row->studentcount : $row->members()->withTrashed()->whereIsActive()->where('datetimestop', '>', $now)->count() }}
						</a>
					@else
						{{ $row->studentcount ? $row->studentcount : $row->members()->withTrashed()->whereIsActive()->where('datetimestop', '>', $now)->count() }}
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

	<div id="sync" class="hide" title="{{ trans('courses::courses.sync') }}">
		<h2 class="modal-title sr-only">{{ trans('courses::courses.sync') }}</h2>

		<div class="row">
			<div class="col-md-12">
				<a href="{{ route('admin.knowledge.create') }}" class="btn bt-primary">
					<span class="fa fa-undo" aria-hidden="true"></span>
					{{ trans('courses::courses.sync') }}
				</a>
			</div>
		</div>
	</div>

	@csrf
</form>

@stop