@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/messages/js/admin.js?v=' . filemtime(public_path() . '/modules/messages/js/admin.js')) }}"></script>
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
{{ trans('messages::messages.module name') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.messages.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

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
					<label for="field-newstypeid">{{ trans('messages::messages.type') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[newstypeid]" id="field-newstypeid" class="form-control required">
						<?php foreach ($types as $type): ?>
							<option value="{{ $type->id }}"<?php if ($row->messagequeuetypeid == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-targetobjectid">{{ trans('messages::messages.target object id') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="number" name="fields[targetobjectid]" id="field-targetobjectid" class="form-control required" value="{{ $row->targetobjectid }}" />
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

			<table class="meta">
				<caption class="sr-only">{{ trans('global.metadata') }}</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('messages::messages.submitted') }}:</th>
						<td>
							{{ $row->datetimesubmitted }}
						</td>
					</tr>
				</tbody>
			</table>
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