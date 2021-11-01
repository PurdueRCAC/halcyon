@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/history/js/admin.js?v=' . filemtime(public_path() . '/modules/history/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('history::history.history manager'),
		route('admin.history.index')
	)
	->append(
		trans('history::history.activity'),
		route('admin.history.activity')
	)
	->append(
		'#' . $row->id
	);
@endphp

@section('toolbar')
	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.history.activity.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('history::history.history manager') }}: {{ trans('history::history.activity') }}: {{ trans('history::history.view') }}: #{{ $row->id }}
@stop

@section('content')
<form action="{{ route('admin.history.activity') }}" method="post" name="adminForm" id="item-form" class="editform">
	<div class="row">
		<div class="col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-objectid">{{ trans('history::history.objectid') }}:</label>
					<input type="text" name="fields[objectid]" id="field-objectid" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->objectid }}" />
				</div>

				<div class="form-group">
					<label for="field-app">{{ trans('history::history.app') }}:</label>
					<input type="text" name="fields[app]" id="field-app" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->app }}" />
				</div>

				<div class="form-group">
					<label for="field-classmethod">{{ trans('history::history.method') }}:</label>
					<input type="text" name="fields[classmethod]" id="field-classmethod" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->classname }}::{{ $row->classmethod }}" />
				</div>

				<div class="form-group">
					<label for="field-status">{{ trans('history::history.status') }}:</label>
					<input type="text" name="fields[status]" id="field-status" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->status }}" />
				</div>
			</fieldset>
		</div>
		<div class="col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-ip">{{ trans('history::history.ip') }}:</label>
					<input type="text" name="fields[ip]" id="field-ip" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->ip }}" />
				</div>

				<div class="form-group">
					<label for="field-userid">{{ trans('history::history.actor') }}:</label>
					<input type="text" name="fields[userid]" id="field-userid" class="form-control" disabled="disabled" readonly="readonly" value="{{ $row->user ? $row->user->name : trans('global.unknown') }}" />
				</div>

				<div class="form-group">
					<label for="field-uri">{{ trans('history::history.uri') }}:</label>
					<span class="input-group">
						<span class="input-group-prepend"><span class="input-group-text">{{ $row->transportmethod }}</span></span>
						<input type="text" name="fields[uri]" id="field-uri" class="form-control" disabled="disabled" readonly="readonly" maxlength="250" value="{{ $row->uri }}" />
					</span>
				</div>

				<div class="form-group">
					<label for="field-datetime">{{ trans('history::history.datetime') }}:</label>
					<input type="text" name="fields[datetime]" id="field-datetime" class="form-control" disabled="disabled" readonly="readonly" value="{{ $row->datetime }}" />
				</div>
			</fieldset>
		</div>
	</div>

	<fieldset class="adminform">
		<legend>{{ trans('history::history.changes') }}</legend>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label for="field-payload">{{ trans('history::history.payload') }}:</label>
					<textarea name="fields[payload]" id="field-payload" class="form-control" disabled="disabled" readonly="readonly" rows="20" cols="40">{{ $row->payload }}</textarea>
				</div>
			</div>
		</div>
	</fieldset>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop