@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('finder::finder.module name'),
		route('admin.finder.index')
	)
	->append(
		trans('finder::finder.facets'),
		route('admin.finder.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit finder'))
		{!! Toolbar::save(route('admin.finder.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.finder.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('finder.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.finder.store') }}" method="post" name="adminForm" id="item-form" class="editform">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('finder::finder.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('finder::finder.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="form-control{{ $errors->has('fields.description') ? ' is-invalid' : '' }}" rows="5" cols="50">{{ $row->description }}</textarea>
				</div>

				<fieldset>
					<legend>{{ trans('finder::finder.control type') }}</legend>

					<div class="form-group">
						<div class="form-check">
							<input type="radio" name="fields[control_type]" id="field-control_type-radio" class="form-check-input" value="radio"<?php if ($row->control_type == 'radio') { echo ' checked="checked"'; } ?> />
							<label for="field-control_type-radio" class="form-check-label">{{ trans('finder::finder.radio') }}</label>
						</div>
					</div>

					<div class="form-group">
						<div class="form-check">
							<input type="radio" name="fields[control_type]" id="field-control_type-checkbox" class="form-check-input" value="checkbox"<?php if ($row->control_type == 'radio') { echo ' checked="checked"'; } ?> />
							<label for="field-control_type-checkbox" class="form-check-label">{{ trans('finder::finder.radio') }}</label>
						</div>
					</div>
				</fieldset>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('finder::finder.choices') }}</legend>

				@foreach ($row->choices()->orderBy('weight', 'asc')->get() as $choice)
					<?php
					$matches = $choice->services->pluck('service_id')->toArray();
					?>
					<div class="form-group">
						<label for="choice-{{ $choice->id }}-name">{{ trans('finder::finder.choice') }}:</label>
						<input type="text" name="choice[{{ $choice->id }}][name]" id="choice-{{ $choice->id }}-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $choice->name }}" />
					</div>
					<select name="choice[{{ $choice->id }}][matches]" size="7" multiple="multiple" class="form-control">
					@foreach ($services as $service)
						<option value="{{ $service->id }}"<?php if (in_array($service->id, $matches)) { echo ' selected="selected"'; }?>>{{ $service->title }}</option>
					@endforeach
					</select>
				@endforeach
			</fieldset>
		</div>
		<div class="col col-md-5">
		</div>
	</div>

	@csrf
</form>
@stop