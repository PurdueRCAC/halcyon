@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('finder::finder.module name'),
		route('admin.finder.index')
	)
	->append(
		trans('finder::finder.services'),
		route('admin.finder.services')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit finder'))
		{!! Toolbar::save(route('admin.finder.services.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.finder.services.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('finder.name') !!}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.finder.services.store') }}" method="post" name="adminForm" id="item-form" class="editform">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-title">{{ trans('finder::finder.title') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[title]" id="field-title" class="form-control{{ $errors->has('fields.title') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->title }}" />
				</div>

				<div class="form-group">
					<label for="field-summary">{{ trans('finder::finder.summary') }}</label>
					<textarea name="fields[summary]" id="field-summary" class="form-control{{ $errors->has('fields.summary') ? ' is-invalid' : '' }}" rows="5" cols="50">{{ $row->summary }}</textarea>
				</div>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('finder::finder.fields') }}</legend>
			@foreach ($fields as $field)
				<?php
				$value = '';
				foreach ($row->fields as $sfield):
					if ($sfield->field_id == $field->id):
						$value = $sfield->value;
					endif;
				endforeach;
				?>
				<div class="form-group">
					<label for="sfield-{{ $field->name }}">{{ $field->label }}</label>
					<textarea name="sfields[{{ $field->name }}]" id="sfield-{{ $field->name }}" class="form-control{{ $errors->has('sfields.' . $field->name) ? ' is-invalid' : '' }}" cols="50" rows="3">{{ $value }}</textarea>
				</div>
			@endforeach
			</fieldset>
		</div>
		<div class="col col-md-5">
		</div>
	</div>

	@csrf
</form>
@stop