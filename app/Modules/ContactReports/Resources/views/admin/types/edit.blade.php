@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('contactreports::contactreports.module name'),
		route('admin.contactreports.index')
	)
	->append(
		trans('contactreports::contactreports.types'),
		route('admin.contactreports.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit contactreports.types'))
		{!! Toolbar::save(route('admin.contactreports.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.contactreports.types.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('contactreports::contactreports.module name') }}: {{ trans('contactreports::contactreports.types') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.contactreports.types.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
		<div class="col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('contactreports::contactreports.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('contactreports::contactreports.error.invalid name') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('contactreports::contactreports.followup') }}</legend>

				<p class="form-text">{{ trans('contactreports::contactreports.followup desc') }}</p>

				<div class="form-group">
					<label for="field-timeperiodid">{{ trans('contactreports::contactreports.timeperiod') }}</label>
					<select class="form-control" name="fields[timeperiodid]" id="field-timeperiodid">
						<option value="0"<?php if (!$row->timeperiodid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						<?php foreach (App\Halcyon\Models\Timeperiod::all() as $period): ?>
							<option value="{{ $period->id }}"<?php if ($row->timeperiodid == $period->id) { echo ' selected="selected"'; } ?>>{{ $period->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-timeperiodcount">{{ trans('contactreports::contactreports.timeperiod count') }}</label>
					<input type="number" name="fields[timeperiodcount]" id="field-timeperiodcount" class="form-control" value="{{ $row->timeperiodcount ? $row->timeperiodcount : 0 }}" />
				</div>

				<div class="form-group">
					<label for="field-timeperiodlimit">{{ trans('contactreports::contactreports.timeperiod limit') }}</label>
					<input type="number" name="fields[timeperiodlimit]" id="field-timeperiodlimit" class="form-control" value="{{ $row->timeperiodlimit ? $row->timeperiodlimit : 0 }}" />
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('contactreports::contactreports.wait period') }}</legend>

				<p class="form-text">{{ trans('contactreports::contactreports.wait period desc') }}</p>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group mb-0">
							<label for="field-waitperiodid">{{ trans('contactreports::contactreports.waitperiod') }}</label>
							<select class="form-control" name="fields[waitperiodid]" id="field-waitperiodid">
								<option value="0"<?php if (!$row->waitperiodid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
								<?php foreach (App\Halcyon\Models\Timeperiod::all() as $period): ?>
									<option value="{{ $period->id }}"<?php if ($row->waitperiodid == $period->id) { echo ' selected="selected"'; } ?>>{{ $period->plural }}</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group mb-0">
							<label for="field-waitperiodcount">{{ trans('contactreports::contactreports.waitperiod count') }}</label>
							<input type="number" name="fields[waitperiodcount]" id="field-waitperiodcount" class="form-control" value="{{ $row->waitperiodcount ? $row->waitperiodcount : 0 }}" />
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
	@csrf
</form>
@stop