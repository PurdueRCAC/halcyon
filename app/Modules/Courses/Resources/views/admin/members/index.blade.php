@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/courses/js/admin.js?v=' . filemtime(public_path() . '/modules/courses/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('courses::courses.module name'),
		route('admin.courses.index')
	)
	->append(
		$account->classname . ' ' . $account->coursenumber . ' (' . $account->crn . ')',
		route('admin.courses.edit', ['id' => $account->id])
	)
	->append(
		trans('courses::courses.members'),
		route('admin.courses.members')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit courses'))
		{!! Toolbar::deleteList('', route('admin.courses.members.delete')) !!}
	@endif

	@if (auth()->user()->can('edit courses'))
		{!! Toolbar::addNew(route('admin.courses.members.create')) !!}
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
{!! trans('courses::courses.module name') !!}: {{ $account->name }}: Members
@stop

@section('content')
<form action="{{ route('admin.courses.members', ['account' => $account->id]) }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only" for="filter-state">{{ trans('courses::courses.state') }}</label>
				<select name="state" id="filter-state" class="form-control filter filter-submit">
					<option value="*">{{ trans('courses::courses.all states') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active') { echo ' selected="selected"'; } ?>>{{ trans('global.active') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-type">{{ trans('courses::courses.membership type') }}</label>
				<select name="type" id="filter-type" class="form-control filter filter-submit">
					<option value="0">{{ trans('courses::courses.select membership type') }}</option>
					<option value="1"<?php if ($filters['type'] == 1) { echo ' selected="selected"'; } ?>>{{ trans('courses::courses.student') }}</option>
					<option value="2"<?php if ($filters['type'] == 2) { echo ' selected="selected"'; } ?>>{{ trans('courses::courses.instructor') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="account" value="{{ $account->id }}" autocomplete="off" />
		<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button type="submit" class="btn btn-secondary sr-only">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<div class="card mb-4">
			<table class="table table-hover adminlist">
				<caption>{{ $account->classname . ' ' . $account->coursenumber . ' (' . $account->crn . ')' }}</caption>
				<thead>
					<tr>
						<th>
							{!! Html::grid('checkall') !!}
						</th>
						<th scope="col" class="priority-5">
							{!! Html::grid('sort', trans('courses::courses.id'), 'id', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('courses::courses.name'), 'name', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col" class="priority-4">
							{!! Html::grid('sort', trans('courses::courses.start'), 'datetimestart', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col" class="priority-4">
							{!! Html::grid('sort', trans('courses::courses.stop'), 'datetimestop', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col" class="priority-4">
							{!! Html::grid('sort', trans('courses::courses.type'), 'membertype', $filters['order_dir'], $filters['order']) !!}
						</th>
					</tr>
				</thead>
				<tbody>
				@foreach ($rows as $i => $row)
					<tr<?php if ($row->user && $row->user->isTrashed()) { echo ' class="trashed"'; } ?>>
						<td>
							@if (auth()->user()->can('edit courses'))
								{!! Html::grid('id', $i, $row->id) !!}
							@endif
						</td>
						<td class="priority-5">
							@if (auth()->user()->can('edit courses'))
								<a href="{{ route('admin.courses.members.edit', ['id' => $row->id]) }}">
							@endif
									{{ $row->id }}
							@if (auth()->user()->can('edit courses'))
								</a>
							@endif
						</td>
						<td>
							@if ($row->user && $row->user->isTrashed())
								<span class="icon-alert-triangle glyph warning has-tip" title="{{ trans('courses::courses.user account removed') }}">{{ trans('courses::courses.user account removed') }}</span>
							@endif
							@if (auth()->user()->can('edit users'))
								<a href="{{ route('admin.users.edit', ['id' => $row->userid]) }}">
							@endif
									{{ $row->user ? $row->user->name : trans('global.unknown') . ': ' . $row->userid }}
							@if (auth()->user()->can('edit users'))
								</a>
							@endif
						</td>
						<td class="priority-4">
							<time datetime="{{ $row->datetimestart->format('Y-m-d\TH:i:s\Z') }}">{{ $row->datetimestart->toDateTimeString() }}</time>
						</td>
						<td class="priority-4">
							<time datetime="{{ $row->datetimestop->format('Y-m-d\TH:i:s\Z') }}">{{ $row->datetimestop->toDateTimeString() }}</time>
						</td>
						<td>
							@if ($row->membertype == 2)
								<span class="badge badge-success">{{ trans('courses::courses.instructor') }}</span>
							@else
								<span class="badge badge-info">{{ trans('courses::courses.student') }}</span>
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
	@csrf
</form>

<div id="new-account" class="modal dialog hide" title="{{ trans('courses::courses.choose member') }}">
	<form action="{{ route('admin.courses.members', ['account' => $account->id]) }}" method="post">
		<div class="modal-body">
			<h2 class="modal-title sr-only">{{ trans('courses::courses.add member') }}</h2>

			<div class="form-group">
				<label for="field-userid">{{ trans('courses::courses.member') }}: <span class="required">{{ trans('global.required') }}</span></label>
				<span class="input-group">
					<input type="text" name="userid" id="field-userid" data-classaccountid="{{ $account->id }}" class="form-control form-users" data-uri="{{ route('api.users.index') }}?search=%s" required value="" />
					<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
				</span>
			</div>

			<div class="form-group">
				<label for="field-membertype">{{ trans('courses::courses.type') }}:</label>
				<select name="membertype" id="field-membertype" class="form-control">
					<option value="1">{{ trans('courses::courses.student') }}</option>
					<option value="2">{{ trans('courses::courses.instructor') }}</option>
				</select>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-success add-member" data-api="{{ route('api.courses.members.create') }}" data-account="{{ $account->id }}" data-field="#field-userid" data-type="#field-membertype" data-success="{{ trans('courses::courses.user added') }}">
				<span class="icon-plus" aria-hidden="true"></span> {{ trans('global.button.add') }}
			</button>
		</div>

		<input type="hidden" name="classaccountid" value="{{ $account->id }}" autocomplete="off" />

		@csrf
	</form>
</div>

@stop
