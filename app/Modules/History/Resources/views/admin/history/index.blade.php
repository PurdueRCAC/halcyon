@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/history/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('history::history.module name'),
		route('admin.history.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin history'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('history');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('history::history.history manager') }}
@stop

@section('content')

@component('history::admin.submenu')
	history
@endcomponent

<form action="{{ route('admin.history.index') }}" method="get" name="adminForm" id="adminForm">
	<fieldset id="filter-bar" class="container-fluid mb-3">
		<div class="row">
			<div class="col filter-search col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
				</span>
			</div>
			<div class="col col-md-3">
				<div class="btn-group position-static" role="group" aria-label="Specific date range">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						@if ($filters['start'] || $filters['end'])
							@if ($filters['start'])
								{{ $filters['start'] }}
							@else
								All past
							@endif
							-
							@if ($filters['end'])
								{{ $filters['end'] }}
							@else
								Now
							@endif
						@else
							Date range
						@endif
					</button>
					<div class="dropdown-menu dropdown-menu-right dropdown-dates">
						<div class="row">
							<div class="col-md-5">
								<p class="mt-0 mx-4"><strong>To-Date</strong></p>
								<a href="{{ route('admin.history.index', ['start' => '', 'end' => '']) }}" class="dropdown-item{{ !$filters['start'] && !$filters['end'] ? ' active' : '' }}">All Time</a>
								<?php
								$start = Carbon\Carbon::now()->format('Y-m-d');
								$end = Carbon\Carbon::now()->modify('+1 day')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Past Day</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 week')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Week</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 month')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Month</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-6 months')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">6 Months</a>
								<?php
								$start = Carbon\Carbon::now()->modify('-1 year')->format('Y-m-d');
								?>
								<a href="{{ route('admin.history.index', ['start' => $start, 'end' => $end]) }}" class="dropdown-item{{ $filters['start'] == $start && $filters['end'] == $end ? ' active' : '' }}">Year</a>
							</div>
							<div class="col-md-7">
								<p class="mt-0 mx-4"><strong>Specific</strong></p>
								<div class="px-4 py-3">
									<div class="form-group mb-3">
										<label for="filter_start">{{ trans('history::history.start date') }}</label>
										<input type="text" name="start" id="filter_start" class="form-control date filter filter-submit" value="{{ $filters['start'] }}" placeholder="YYYY-MM-DD" />
									</div>
									<div class="form-group">
										<label for="filter_end">{{ trans('history::history.end date') }}</label>
										<input type="text" name="end" id="filter_end" class="form-control date filter filter-submit" value="{{ $filters['end'] }}" placeholder="YYYY-MM-DD" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_action">{{ trans('history::history.action') }}</label>
				<select name="action" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['action'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all actions') }}</option>
					<option value="created"<?php if ($filters['action'] == 'created'): echo ' selected="selected"'; endif;?>>{{ trans('history::history.created') }}</option>
					<option value="updated"<?php if ($filters['action'] == 'updated'): echo ' selected="selected"'; endif;?>>{{ trans('history::history.updated') }}</option>
					<option value="deleted"<?php if ($filters['action'] == 'deleted'): echo ' selected="selected"'; endif;?>>{{ trans('history::history.deleted') }}</option>
				</select>
			</div>
			<div class="col col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_type">{{ trans('history::history.type') }}</label>
				<select name="type" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['type'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all types') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="created"<?php if ($filters['type'] == $type->historable_type): echo ' selected="selected"'; endif;?>>{{ $type->historable_type }}</option>
					<?php endforeach; ?>
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
					<caption class="sr-only visually-hidden">{{ trans('history::history.history manager') }}</caption>
					<thead>
						<tr>
							@if (auth()->user()->can('delete history'))
								<th>
									<span class="form-check"><input type="checkbox" name="toggle" value="" id="toggle-all" class="form-check-input checkbox-toggle toggle-all" /><label for="toggle-all"></label></span>
								</th>
							@endif
							<th scope="col">{{ trans('history::history.id') }}</th>
							<th scope="col">{{ trans('history::history.item id') }}</th>
							<th scope="col">{{ trans('history::history.item type') }}</th>
							<th scope="col">{{ trans('history::history.item table') }}</th>
							<th scope="col">{{ trans('history::history.action') }}</th>
							<th scope="col">{{ trans('history::history.actor') }}</th>
							<th scope="col" class="priority-4">{{ trans('history::history.timestamp') }}</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($rows as $i => $row)
						<tr>
							@if (auth()->user()->can('delete history'))
								<td>
									<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
								</td>
							@endif
							<td class="priority-5">
								{{ $row->id }}
							</td>
							<td>
								<a href="{{ route('admin.history.show', ['id' => $row->id]) }}">
									{{ $row->historable_id }}
								</a>
							</td>
							<td>
								<a href="{{ route('admin.history.show', ['id' => $row->id]) }}">
									{{ $row->type }}
								</a>
							</td>
							<td>
								{{ $row->historable_table }}
							</td>
							<td>
								@if ($row->action == 'deleted')
									<span class="badge badge-danger">{{ $row->action }}</span>
								@elseif ($row->action == 'created')
									<span class="badge badge-success">{{ $row->action }}</span>
								@elseif ($row->action == 'updated')
									<span class="badge badge-info">{{ $row->action }}</span>
								@elseif ($row->action == 'emailed')
									<span class="badge badge-info">{{ $row->action }}</span>
								@endif
							</td>
							<td>
								@if ($row->user)
									<a href="{{ route('admin.users.edit', ['id' => $row->user->id]) }}">
										{{ $row->user->name }}
									</a>
								@else
									<span class="text-muted unknown">{{ trans('global.unknown') }}</span>
								@endif
							</td>
							<td class="priority-4">
								<span class="datetime">
									@if ($row->updated_at)
										<time datetime="{{ $row->updated_at->toDateTimeLocalString() }}">{{ $row->updated_at }}</time>
									@else
										@if ($row->created_at)
											<time datetime="{{ $row->created_at->toDateTimeLocalString() }}">{{ $row->created_at }}</time>
										@else
											<span class="never">{{ trans('global.unknown') }}</span>
										@endif
									@endif
								</span>
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