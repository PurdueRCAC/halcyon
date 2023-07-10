@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/messages/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('messages::messages.module name'),
		route('admin.messages.index')
	)
	->append(
		trans('messages::messages.messages'),
		route('admin.messages.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit messages'))
		{!! Toolbar::save(route('admin.messages.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.messages.cancel'))
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('messages::messages.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.messages.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-messagequeuetypeid">{{ trans('messages::messages.type') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[messagequeuetypeid]" id="field-messagequeuetypeid" class="form-control required" required>
						<?php foreach ($types as $type): ?>
							<option value="{{ $type->id }}"<?php if ($row->messagequeuetypeid == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
						<?php endforeach; ?>
					</select>
					<span class="invalid-feedback">{{ trans('messages::messages.errors.invalid message queue type id') }}</span>
				</div>

				<div class="form-group">
					<label for="field-targetobjectid">{{ trans('messages::messages.target object id') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="number" name="fields[targetobjectid]" id="field-targetobjectid" class="form-control required" required value="{{ $row->targetobjectid }}" />
					<span class="invalid-feedback">{{ trans('messages::messages.errors.invalid target object id') }}</span>
				</div>

				<div class="form-group">
					<label for="field-userid">{{ trans('messages::messages.user') }}:</label>
					<span class="input-group">
						<input type="text" name="fields[userid]" id="field-userid" class="form-control form-users" data-uri="{{ route('api.users.index') }}?search=%s" value="{{ ($row->user ? $row->user->name . ':' . $row->userid : '') }}" />
						<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
					</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				@if ($row->id)
				<div class="input-wrap form-group">
					<label for="field-datetimesubmitted">{{ trans('messages::messages.submitted') }}:</label>
					<input type="text" name="datetimesubmitted" id="field-datetimesubmitted" readonly class="form-control-plaintext" value="{{ $row->datetimesubmitted }}" />
				</div>

				<div class="input-wrap form-group">
					<label for="field-datetimestarted">{{ trans('messages::messages.started') }}:</label>
					<span class="input-group">
						<input type="text" name="fields[datetimestarted]" id="field-datetimestarted" class="form-control date" value="{{ $row->started() ? $row->datetimestarted : '' }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
					</span>
				</div>

				<div class="input-wrap form-group">
					<label for="field-datetimecompleted">{{ trans('messages::messages.completed') }}:</label>
					<span class="input-group">
						<input type="text" name="fields[datetimecompleted]" id="field-datetimecompleted" class="form-control date" value="{{ $row->completed() ? $row->datetimecompleted : '' }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</fieldset>
				@else
				<div class="input-wrap form-group">
					<label for="field-datetimesubmitted">{{ trans('messages::messages.submitted') }}:</label>
					<span class="input-group">
						<input type="text" name="fields[datetimesubmitted]" id="field-datetimesubmitted" class="form-control date" placeholder="{{ trans('messages::messages.now') }}" value="" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</fieldset>
				@endif
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop