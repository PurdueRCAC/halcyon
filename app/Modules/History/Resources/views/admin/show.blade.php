@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
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
		'#' . $row->id
	);
@endphp

@section('toolbar')
	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.history.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('history::history.history manager') }}: View: #{{ $row->id }}
@stop

@section('content')
<form action="{{ route('admin.history.store') }}" method="post" name="adminForm" id="item-form" class="editform">
	<div class="grid row">
		<div class="col col-md-6 span6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-historable_id">{{ trans('history::history.item id') }}:</label>
					<input type="text" name="fields[historable_id]" id="field-historable_id" class="form-control" disabled="disabled" size="30" maxlength="250" value="{{ $row->historable_id }}" />
				</div>

				<div class="form-group">
					<label for="field-historable_type">{{ trans('history::history.item type') }}:</label>
					<input type="text" name="fields[historable_type]" id="field-historable_type" class="form-control" disabled="disabled" size="30" maxlength="250" value="{{ $row->historable_type }}" />
				</div>

				<div class="form-group">
					<label for="field-historable_table">{{ trans('history::history.item table') }}:</label>
					<input type="text" name="fields[historable_table]" id="field-historable_table" class="form-control" disabled="disabled" size="30" maxlength="250" value="{{ $row->historable_table }}" />
				</div>
			</fieldset>
		</div>
		<div class="col col-md-6 span6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-actor">{{ trans('history::history.actor') }}:</label>
					<input type="text" name="fields[actor]" id="field-actor" class="form-control" disabled="disabled" value="{{ $row->user ? $row->user->name : trans('global.unknown') }}" />
				</div>

				<div class="form-group">
					<label for="field-action">{{ trans('history::history.action') }}:</label>
					<input type="text" name="fields[action]" id="field-action" class="form-control" disabled="disabled" value="{{ $row->action }}" />
				</div>

				<div class="form-group">
					<label for="field-created_at">{{ trans('history::history.created') }}:</label>
					<input type="text" name="fields[created_at]" id="field-created_at" class="form-control" disabled="disabled" value="{{ $row->created_at }}" />
				</div>
			</fieldset>
		</div>
	</div>

	<fieldset class="adminform">
		<legend>{{ trans('history::history.changes') }}</legend>
		<div class="grid row">
			<div class="col col-md-6 span6">
				<div class="form-group">
					<label for="field-old"><?php echo trans('history::history.old'); ?>:</label>
					<textarea name="fields[old]" id="field-old" class="form-control" rows="20" cols="40">{{ json_encode($row->getOriginal('old'), JSON_PRETTY_PRINT) }}</textarea>
				</div>
			</div>
			<div class="col col-md-6 span6">
				<div class="form-group">
					<label for="field-new"><?php echo trans('history::history.new'); ?>:</label>
					<textarea name="fields[new]" id="field-new" class="form-control" rows="20" cols="40">{{ json_encode($row->getOriginal('new'), JSON_PRETTY_PRINT) }}</textarea>
				</div>
			</div>
		</div>
	</fieldset>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop