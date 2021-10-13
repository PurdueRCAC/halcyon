@extends('layouts.master')

@push('scripts')
<script src="./js/queue.js"></script>
@endpush

@section('content')
<h2>{!! config('queues.name') !!}</h2>

<form action="{{ url('site.queues.index') }}" method="post" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="form-inline">
		<legend>Filter</legend>
		<div class="row">
			<div class="col-sm-6 filter-search span4">
				<div class="form-group">
					<label class="sr-only filter-search-lbl" for="filter_search">{{ trans('search.label') }}</label>
					<input type="text" name="filter_search" id="filter_search" class="form-control filter" value="" placeholder="{{ trans('search.placeholder') }}" />
				</div>

				<button type="submit" class="btn btn-default">{{ trans('search.submit'); ?></button>
			</div>
			<div class="col-sm-6 filter-select span8">
				<div class="form-group">
					<label class="sr-only" for="filter_state">{{ trans('queues::queues.STATE') }}</label>
					<select name="filter_state" class="form-control filter filter-submit">
						<option value="*">{{ trans('queues::queues.all_states') }}</option>
						<option value="active">{{ trans('queues::queues.ACTIVE') }}</option>
						<option value="inactive">{{ trans('queues::queues.INACTIVE') }}</option>
					</select>
				</div>
				<div class="form-group">
					<label class="sr-only" for="filter_type">{{ trans('queues::queues.TYPE') }}</label>
					<select name="filter_type" class="form-control filter filter-submit">
						<option value="0">{{ trans('queues::queues.TYPE_ALL') }}</option>
					</select>
				</div>
			</div>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					<span class="form-check"><input type="checkbox" name="toggle" value="" id="toggle-all" class="form-check-input checkbox-toggle toggle-all" /><label for="toggle-all"></label></span>
				</th>
				<th scope="col" class="priority-5">{{ trans('queues::queues.COL_ID') }}</th>
				<th scope="col">{{ trans('queues::queues.COL_NAME') }}</th>
				<th scope="col">{{ trans('queues::queues.COL_ROLENAME') }}</th>
				<th scope="col" class="priority-4">{{ trans('queues::queues.COL_LISTNAME') }}</th>
				<th scope="col" class="priority-3">{{ trans('queues::queues.COL_TYPE') }}</th>
				<th scope="col" class="priority-4">{{ trans('queues::queues.COL_CREATED') }}</th>
				<th scope="col" class="priority-2">{{ trans('queues::queues.COL_REMOVED') }}</th>
				<th scope="col">{{ trans('queues::queues.COL_RESOURCES') }}</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					<a href="{{ route('admin.queues.edit', ['id' => $row->id]) }}">
						{{ $row->name }}
					</a>
				</td>
				<td>
					<a href="{{ route('admin.queues.edit', ['id' => $row->id]) }}">
						{{ $row->rolename }}
					</a>
				</td>
				<td class="priority-4">
					<a href="{{ route('admin.queues.edit', ['id' => $row->id]) }}">
						{{ $row->listname }}
					</a>
				</td>
				<td class="priority-3">
					{{ $row->type->name }}
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datetimecreated)
							<time datetime="{{ $row->datetimecreated->format('Y-m-d\TH:i:s\Z') }}">
								@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->datetimecreated->diffForHumans() }}
								@else
									{{ $row->datetimecreated->format('Y-m-d') }}
								@endif
							</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->trashed())
							<time datetime="{{ $row->datetimeremoved->format('Y-m-d\TH:i:s\Z') }}">{{ $row->datetimeremoved }}</time>
						@else
							<span class="never">{{ trans('global.never') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<a href="{{ route('admin.queues.index', ['id' => $row->id]) }}">
						0
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop