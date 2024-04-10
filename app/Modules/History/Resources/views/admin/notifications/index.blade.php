@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/history/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('history::history.module name'),
		route('admin.history.index')
	)
	->append(
		trans('history::history.notifications'),
		route('admin.history.notifications')
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
{{ trans('history::history.history manager') }}: {{ trans('history::history.notifictions') }}
@stop

@section('content')

@component('history::admin.submenu')
	notifications
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
			<div class="col col-md-6">
			</div>
			<div class="col col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_type">{{ trans('history::history.type') }}</label>
				<select name="type" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['type'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all types') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="created"<?php if ($filters['type'] == $type->type): echo ' selected="selected"'; endif;?>>{{ $type->type }}</option>
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
							<th scope="col">{{ trans('history::history.type') }}</th>
							<th scope="col">{{ trans('history::history.recipient') }}</th>
							<th scope="col">{{ trans('history::history.sent') }}</th>
							<th scope="col">{{ trans('history::history.read') }}</th>
						</tr>
					</thead>
					<tbody>
					@php
					$past = Carbon\Carbon::now()->modify('-1 week');
					@endphp
					@foreach ($rows as $i => $row)
						<tr>
							@if (auth()->user()->can('delete history'))
								<td>
									<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
								</td>
							@endif
							<td>
								<a href="{{ route('admin.history.notifications.show', ['id' => $row->id]) }}">
									{{ $row->type }}
								</a>
							</td>
							<td>
								<a href="{{ route('admin.history.notifications.show', ['id' => $row->id]) }}">
								<?php
								$cls = $row->notifiable_type;
								$recipient = $cls::find($row->notifiable_id);
								?>
								@if ($recipient)
									{{ $recipient->name }}
								@else
									{{ $row->notifiable_type }}:{{ $row->notifiable_id }}
								@endif
								</a>
							</td>
							<td class="priority-4">
								@if ($row->created_at)
									<time datetime="{{ $row->created_at->toDateTimeLocalString() }}">
									@if ($row->created_at->getTimestamp() > $past->getTimestamp())
										{{ $row->created_at->diffForHumans() }}
									@else
										{{ $row->created_at->format('F j, Y') }}
									@endif
									</time>
								@else
									<span class="never">{{ trans('global.unknown') }}</span>
								@endif
							</td>
							<td class="priority-4">
								@if ($row->read_at)
									<time datetime="{{ $row->read_at->toDateTimeLocalString() }}">{{ $row->read_at->toDateTimeString() }}</time>
								@else
									<span class="never">{{ trans('global.never') }}</span>
								@endif
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