@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/history/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('history::history.history manager'),
		route('admin.history.index')
	)
	->append(
		trans('history::history.notifications'),
		route('admin.history.notifications')
	)
	->append(
		'#' . $row->id
	);
@endphp

@section('toolbar')
	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.history.notifications.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('history::history.history manager') }}: {{ trans('history::history.notifications') }}: #{{ $row->id }}
@stop

@section('content')
<form action="{{ route('admin.history.index') }}" method="post" name="adminForm" id="item-form" class="editform">
	<div class="row">
		<div class="col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-historable_table">{{ trans('history::history.type') }}</label>
					<input type="text" name="fields[type]" id="field-type" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->type }}" />
				</div>

				<div class="form-group">
					<label for="field-notifiable_id">{{ trans('history::history.notifiable id') }}</label>
					<input type="text" name="fields[notifiable_id]" id="field-notifiable_id" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->notifiable_id }}" />
				</div>

				<div class="form-group">
					<label for="field-notifiable_type">{{ trans('history::history.notifiable type') }}</label>
					<input type="text" name="fields[notifiable_type]" id="field-notifiable_type" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->notifiable_type }}" />
				</div>
			</fieldset>
		</div>
		<div class="col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-created_at">{{ trans('history::history.created') }}</label>
					<input type="text" name="fields[created_at]" id="field-created_at" class="form-control" disabled="disabled" readonly="readonly" value="{{ $row->created_at }}" />
				</div>

				<div class="form-group">
					<label for="field-read_at">{{ trans('history::history.read') }}</label>
					<input type="text" name="fields[read_at]" id="field-read_at" class="form-control" disabled="disabled" readonly="readonly" value="{{ $row->read_at }}" />
				</div>
			</fieldset>
		</div>
	</div>

	<fieldset class="adminform">
		<legend>{{ trans('history::history.data') }}</legend>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label for="field-data" class="sr-only">{{ trans('history::history.data') }}</label>
					<textarea name="fields[data]" id="field-data" class="form-control" disabled="disabled" readonly="readonly" rows="20" cols="40">{{ json_encode($row->data, JSON_PRETTY_PRINT) }}</textarea>
				</div>
			</div>
		</div>
	</fieldset>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop