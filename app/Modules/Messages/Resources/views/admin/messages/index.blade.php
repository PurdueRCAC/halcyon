@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/messages/js/admin.js') }}"></script>
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

@section('content')

@component('messages::admin.submenu')
	messages
@endcomponent

<form action="{{ route('admin.messages.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-2">
				<label class="sr-only" for="filter_start">{{ trans('messages::messages.start') }}</label>
				<span class="input-group">
					<input type="text" name="start" id="filter_start" class="form-control filter filter-submit date" value="{{ $filters['start'] }}" placeholder="Submitted from" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
				</span>
			</div>
			<div class="col col-md-2">
				<label class="sr-only" for="filter_stop">{{ trans('messages::messages.stop') }}</label>
				<span class="input-group">
					<input type="text" name="stop" id="filter_stop" class="form-control filter filter-submit date" value="{{ $filters['stop'] }}" placeholder="Submitted to" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
				</span>
			</div>
			<div class="col col-md-8 text-right filter-select">
				<label class="sr-only" for="filter_state">{{ trans('messages::messages.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.all states') }}</option>
					<option value="pending"<?php if ($filters['state'] == 'pending'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.pending') }}</option>
					<option value="incomplete"<?php if ($filters['state'] == 'incomplete'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.incomplete') }}</option>
					<option value="complete"<?php if ($filters['state'] == 'complete'): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.complete') }}</option>
				</select>

				<label class="sr-only" for="filter_type">{{ trans('messages::messages.types') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['type'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('messages::messages.all types') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_status">{{ trans('messages::messages.status') }}</label>
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
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('messages::messages.messages') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete messages'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('messages::messages.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('messages::messages.type'), 'messagequeuetypeid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('messages::messages.target object id'), 'targetobjectid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('messages::messages.submitted'), 'datetimesubmitted', $filters['order_dir'], $filters['order']) !!}
				</th>
				<!-- <th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('messages::messages.started'), 'datetimestarted', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('messages::messages.completed'), 'datetimecompleted', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('messages::messages.elapsed') }}
				</th> -->
				<th scope="col" class="priority-4">
					{{ trans('messages::messages.processed') }}
				</th>
				<th scope="col" class="text-right">
					{!! Html::grid('sort', trans('messages::messages.pid'), 'pid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
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
			<tr{{ $cls }}>
				@if (auth()->user()->can('delete messages'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					</td>
				@endif
				<td class="priority-5">
					@if (auth()->user()->can('edit messages'))
						<a href="{{ route('admin.messages.edit', ['id' => $row->id]) }}">
							{{ $row->id }}
						</a>
					@else
						{{ $row->id }}
					@endif
				</td>
				<td class="priority-4">
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
				<td class="priority-4">
					@if ($target = $row->target)
						<span data-id="{{ $row->targetobjectid }}">{{ $target }}</span>
					@else
						<span data-id="{{ $row->targetobjectid }}" class="unknown">{{ trans('global.unknown') }}</span>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit messages'))
						<a href="{{ route('admin.messages.edit', ['id' => $row->id]) }}">
							<time datetime="{{ $row->datetimesubmitted }}">{{ $row->datetimesubmitted }}</time>
						</a>
					@else
						<time datetime="{{ $row->datetimesubmitted }}">{{ $row->datetimesubmitted }}</time>
					@endif
				</td>
				<!--
				<td class="priority-4">
					@if ($row->datetimestarted && $row->datetimestarted != '0000-00-00 00:00:00' && $row->datetimestarted != '-0001-11-30 00:00:00')
						<time datetime="{{ $row->datetimestarted }}">{{ $row->datetimestarted }}</time>
					@else
						<span class="none">{{ trans('messages::messages.not started') }}</span>
					@endif
				</td>
				<td class="priority-4">
					@if ($row->datetimecompleted && $row->datetimecompleted != '0000-00-00 00:00:00' && $row->datetimecompleted != '-0001-11-30 00:00:00')
						<time datetime="{{ $row->datetimecompleted }}">{{ $row->datetimecompleted }}</time>
					@else
						<span class="none">{{ trans('messages::messages.not completed') }}</span>
					@endif
				</td>
				<td>
					{{ $row->elapsed }}
				</td>
				-->
				<td>
					<?php
					$timetable  = '<table><tbody>';
					$timetable .= '<tr><th scope=\'row\'>' . trans('messages::messages.started') . '</th><td>';
					if ($row->datetimestarted && $row->datetimestarted != '0000-00-00 00:00:00' && $row->datetimestarted != '-0001-11-30 00:00:00'):
						$timetable .= '<time datetime=\''. $row->datetimestarted .'\'>' . $row->datetimestarted . '</time>';
					else:
						$timetable .= trans('messages::messages.not started');
					endif;
					$timetable .= '</td></tr>';
					$timetable .= '<tr><th scope=\'row\'>' . trans('messages::messages.completed') . '</th><td>';
					if ($row->datetimecompleted && $row->datetimecompleted != '0000-00-00 00:00:00' && $row->datetimecompleted != '-0001-11-30 00:00:00'):
						$timetable .= '<time datetime=\''. $row->datetimecompleted .'\'>' . $row->datetimecompleted . '</time>';
					else:
						$timetable .= trans('messages::messages.not completed');
					endif;
					$timetable .= '</td></tr>';
					$timetable .= '</tbody></table>';
					?>
					@if ($row->started())
						@if ($row->completed())
							<span class="badge badge-success has-tip" data-tip="{!! $timetable !!}"><span class="glyph icon-check"></span> {{ $row->elapsed }}</span>
						@else
							<span class="badge badge-warning has-tip" data-tip="{!! $timetable !!}"><span class="glyph icon-rotate-ccw"></span> {{ trans('messages::messages.processing') }}</span>
						@endif
					@else
						<span class="badge badge-info has-tip" data-tip="{!! $timetable !!}"><span class="glyph icon-more-horizontal"></span> {{ trans('messages::messages.pending') }}</span>
					@endif
				</td>
				<td class="text-right">
					@if (auth()->user()->can('edit messages'))
						<a href="{{ route('admin.messages.edit', ['id' => $row->id]) }}">
							{{ $row->pid }}
						</a>
					@else
						{{ $row->pid }}
					@endif
				</td>
				<td class="priority-4 text-right">
					<?php /*@if ($row->completed())
						@if ($row->returnstatus)
							<span class="text-danger icon-alert-octagon" aria-hidden="true"></span>
						@endif
					@endif*/ ?>
					{{ $row->returnstatus }}
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop