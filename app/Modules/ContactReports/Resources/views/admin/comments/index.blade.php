@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/contactreports/js/admin.js') }}"></script>
@endpush

@section('toolbar')
	@if (auth()->user()->can('delete contactreports'))
		{!! Toolbar::deleteList('', route('admin.contactreports.comments.delete', ['report' => $report->id])) !!}
	@endif

	@if (auth()->user()->can('create contactreports'))
		{!! Toolbar::addNew(route('admin.contactreports.comments.create', ['report' => $report->id])) !!}
	@endif

	@if (auth()->user()->can('admin contactreports'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('contactreports')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('contactreports::contactreports.module name') }}: #{{ $report->id }}: {{ trans('contactreports::contactreports.comments') }}
@stop

@section('content')

<form action="{{ route('admin.contactreports.comments', ['report' => $report->id]) }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar">
		<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
		<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption>#{{ $report->id }} - {{ Illuminate\Support\Str::limit($report->report, 70) }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete contactreports.comments'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('contactreports::contactreports.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('contactreports::contactreports.comment'), 'comment', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('contactreports::contactreports.created'), 'datetimecreated', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('contactreports::contactreports.creator'), 'userid', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete contactreports.comments'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit contactreports'))
						<a href="{{ route('admin.contactreports.comments.edit', ['report' => $report->id, 'id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit(strip_tags($row->comment), 70) }}
						</a>
					@else
						<span>
							{{ Illuminate\Support\Str::limit(strip_tags($row->comment), 70) }}
						</span>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datetimecreated)
							<time datetime="{{ $row->datetimecreated->toDateTimeLocalString() }}">{{ $row->datetimecreated }}</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					{{ ($row->creator ? $row->creator->name : trans('global.unknown')) }}
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