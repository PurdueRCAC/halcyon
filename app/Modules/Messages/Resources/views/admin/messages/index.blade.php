@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/messages/js/admin.js?v=' . filemtime(public_path() . '/modules/messages/js/admin.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('messages::messages.module name'),
		route('admin.messages.index')
	)
	->append(
		trans('messages::messages.messages')
	);
@endphp

@section('toolbar')
	{!!
		Toolbar::custom(route('admin.messages.rerun'), 'refresh', 'refresh', 'messages::messages.rerun', true);
		Toolbar::spacer();
	!!}

	@if (auth()->user()->can('delete messages'))
		{!! Toolbar::deleteList('', route('admin.messages.delete')) !!}
	@endif

	@if (auth()->user()->can('create messages'))
		{!! Toolbar::addNew(route('admin.messages.create')) !!}
	@endif

	@if (auth()->user()->can('admin messages'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('messages')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
	{{ trans('messages::messages.module name') }}
@stop

@section('panel')
	<div class="card">
		<div class="card-body">
			<a href="{{ route('admin.messages.index', ['state' => 'pending', 'type' => '']) }}" class="stat-block text-info">
				<span class="fa fa-ellipsis-h display-4 float-left" aria-hidden="true"></span>
				<span class="value">{{ number_format($stats->pending) }}</span><br />
				<span class="key">{{ trans('messages::messages.pending') }}</span>
			</a>
		</div>
		@if (count($stats->pendingtypes))
			<table class="table">
				<caption class="sr-only visually-hidden">{{ trans('messages::messages.message types') }}</caption>
				<tbody>
					@foreach ($stats->pendingtypes as $t)
						<tr>
							<th scope="row"><a href="{{ route('admin.messages.index', ['state' => 'pending', 'type' => $t->id]) }}">{{ $t->name }}</a></th>
							<td class="text-right">{{ $t->total }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</div>

	<div class="card">
		<div class="card-body">
			<a href="{{ route('admin.messages.index', ['status' => 'failure', 'type' => '']) }}" class="stat-block text-danger">
				<span class="fa fa-exclamation-triangle display-4 float-left" aria-hidden="true"></span>
				<span class="value">{{ number_format($stats->failed) }}</span><br />
				<span class="key">{{ trans('messages::messages.failure') }}</span>
			</a>
		</div>
		@if (count($stats->failedtypes))
			<table class="table">
				<caption class="sr-only visually-hidden">{{ trans('messages::messages.message types') }}</caption>
				<tbody>
					@foreach ($stats->failedtypes as $t)
						<tr>
							<th scope="row"><a href="{{ route('admin.messages.index', ['status' => 'failure', 'type' => $t->id]) }}">{{ $t->name }}</a></th>
							<td class="text-right">{{ number_format($t->total) }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</div>

	<div class="card">
		<div class="card-body">
			<a href="{{ route('admin.messages.index', ['status' => 'success', 'type' => '']) }}" class="stat-block text-success">
				<span class="fa fa-check display-4 float-left" aria-hidden="true"></span>
				<span class="value">{{ number_format($stats->succeeded) }}</span><br />
				<span class="key">{{ trans('messages::messages.success') }}</span>
			</a>
		</div>
		@if (count($stats->succeededtypes))
			<table class="table">
				<caption class="sr-only visually-hidden">{{ trans('messages::messages.message types') }}</caption>
				<tbody>
					@foreach ($stats->succeededtypes as $t)
						<tr>
							<th scope="row"><a href="{{ route('admin.messages.index', ['status' => 'success', 'type' => $t->id]) }}">{{ $t->name }}</a></th>
							<td class="text-right">{{ number_format($t->total) }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</div>
@stop

@section('content')

@component('messages::admin.submenu')
	messages
@endcomponent

<form action="{{ route('admin.messages.index') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3 filter-select">
				<div class="input-group">
					<label class="form-label sr-only visually-hidden" for="filter_start">{{ trans('messages::messages.start') }}</label>
					<input type="text" name="start" id="filter_start" class="form-control filter filter-submit date" value="{{ $filters['start'] }}" placeholder="{{ trans('messages::messages.start placeholder') }}" />
					<span class="input-group-prepend input-group-append">
						<span class="input-group-text">&rarr;</span>
					</span>
					<label class="form-label sr-only visually-hidden" for="filter_stop">{{ trans('messages::messages.stop') }}</label>
					<input type="text" name="stop" id="filter_stop" class="form-control filter filter-submit date" value="{{ $filters['stop'] }}" placeholder="{{ trans('messages::messages.stop placeholder') }}" />
				</div>
			</div>
			<div class="col-md-3">
				<label class="form-label sr-only visually-hidden" for="filter_state">{{ trans('messages::messages.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.all states') }}</option>
					<option value="pending"<?php if ($filters['state'] == 'pending'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.pending') }}</option>
					<option value="incomplete"<?php if ($filters['state'] == 'incomplete'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.incomplete') }}</option>
					<option value="complete"<?php if ($filters['state'] == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.complete') }}</option>
				</select>
			</div>
			<div class="col-md-3">
				<label class="form-label sr-only visually-hidden" for="filter_type">{{ trans('messages::messages.type') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['type'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.all types') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-3">
				<label class="form-label sr-only visually-hidden" for="filter_status">{{ trans('messages::messages.status') }}</label>
				<select name="status" id="filter_status" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['status'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.all statuses') }}</option>
					<option value="success"<?php if ($filters['status'] == 'success'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.success') }}</option>
					<option value="failure"<?php if ($filters['status'] == 'failure'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.failure') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	<div class="card mb-4">
		<div class="table-responsive">
		@if (count($rows) > 0)
			<table class="table table-hover adminlist">
				<caption class="sr-only visually-hidden">{{ trans('messages::messages.messages') }}</caption>
				<thead>
					<tr>
						@if (auth()->user()->can('delete messages'))
							<th>
								{!! Html::grid('checkall') !!}
							</th>
						@endif
						<th scope="col">
							{!! Html::grid('sort', trans('messages::messages.id'), 'id', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('messages::messages.type'), 'messagequeuetypeid', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('messages::messages.target object id'), 'targetobjectid', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('messages::messages.submitted'), 'datetimesubmitted', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{{ trans('messages::messages.processed') }}
						</th>
						<th scope="col" class="text-right text-end">
							{!! Html::grid('sort', trans('messages::messages.return status'), 'returnstatus', $filters['order_dir'], $filters['order']) !!}
						</th>
					</tr>
				</thead>
				<tbody>
				@foreach ($rows as $i => $row)
					<?php
					$cls = '';
					if ($row->completed() && $row->returnstatus):
						$cls = ' class="error-danger"';
					endif;
					?>
					<tr{!! $cls !!}>
						@if (auth()->user()->can('delete messages'))
							<td>
								{!! Html::grid('id', $i, $row->id) !!}
							</td>
						@endif
						<td>
							@if (auth()->user()->can('edit messages'))
								<a href="{{ route('admin.messages.edit', ['id' => $row->id]) }}">
									{{ $row->id }}
								</a>
							@else
								{{ $row->id }}
							@endif
						</td>
						<td>
							@if ($row->type)
								@if (auth()->user()->can('edit messages'))
									<a href="{{ route('admin.messages.edit', ['id' => $row->id]) }}">
										{{ $row->type->name }}
									</a>
								@else
									{{ $row->type->name }}
								@endif
							@else
								<span class="unknown">{{ trans('global.unknown') }}</span>
							@endif
						</td>
						<td>
							@if ($target = $row->target)
								<span data-id="{{ $row->targetobjectid }}">{{ $target }}</span>
							@else
								<span data-id="{{ $row->targetobjectid }}" class="text-muted">{{ trans('global.unknown') }}</span>
							@endif
						</td>
						<td>
							@if (auth()->user()->can('edit messages'))
								<a href="{{ route('admin.messages.edit', ['id' => $row->id]) }}">
									<time datetime="{{ $row->datetimesubmitted->toDateTimeLocalString() }}">
										@if ($row->datetimesubmitted->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
											{{ $row->datetimesubmitted->diffForHumans() }}
										@else
											{{ $row->datetimesubmitted->format('F j, Y') }}
										@endif
									</time>
								</a>
							@else
								<time datetime="{{ $row->datetimesubmitted->toDateTimeLocalString() }}">
									@if ($row->datetimesubmitted->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
										{{ $row->datetimesubmitted->diffForHumans() }}
									@else
										{{ $row->datetimesubmitted->format('F j, Y') }}
									@endif
								</time>
							@endif
						</td>
						<td>
							<?php
							$timetable  = '<div>';
							$timetable .= '<strong>' . trans('messages::messages.started') . '</strong>: ';
							if ($row->started()):
								$timetable .= '<time datetime=\'' . $row->datetimestarted->toDateTimeLocalString() . '\'>' . $row->datetimestarted . '</time>';
							else:
								$timetable .= trans('messages::messages.not started');
							endif;
							$timetable .= '<br />';
							$timetable .= '<strong>' . trans('messages::messages.completed') . '</strong>: ';
							if ($row->completed()):
								$timetable .= '<time datetime=\'' . $row->datetimecompleted->toDateTimeLocalString() . '\'>' . $row->datetimecompleted . '</time>';
							else:
								$timetable .= trans('messages::messages.not completed');
							endif;
							$timetable .= '</div>';
							?>
							@if ($row->completed())
								<span class="badge badge-success has-tip" data-tip="{!! $timetable !!}">
									<span class="fa fa-check" aria-hidden="true"></span> {{ $row->elapsed }}
								</span>
							@elseif ($row->started())
								<span class="badge badge-warning has-tip" data-tip="{!! $timetable !!}">
									<span class="fa fa-undo" aria-hidden="true"></span> {{ trans('messages::messages.processing') }}
								</span>
							@else
								<span class="badge badge-info has-tip" data-tip="{!! $timetable !!}">
									<span class="fa fa-ellipsis-h" aria-hidden="true"></span> {{ trans('messages::messages.pending') }}
								</span>
							@endif
						</td>
						<td class="text-right text-end">
							@if ($row->completed())
								@if ($row->returnstatus)
									<span class="text-danger fa fa-exclamation-circle" aria-hidden="true"></span>
								@else
									<span class="text-success fa fa-check" aria-hidden="true"></span>
								@endif
								{{ $row->returnstatus }}
							@endif
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		@else
			<div class="card-body text-center">
				<div>{{ trans('global.no records found') }}</div>
			</div>
		@endif
		</div>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />
</form>

@stop