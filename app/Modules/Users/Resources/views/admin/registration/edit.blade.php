@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.registration'),
		route('admin.users.registration')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit users.registration'))
		{!! Toolbar::save(route('admin.users.registration.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.users.registration.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::users.module name') }}: {{ trans('users::users.registration_fields') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.users.registration.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">
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
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('users::registration.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" maxlength="100" value="{{ $row->name }}" />
					<span class="form-text text-muted">{{ trans('users::registration.name desc') }}</span>
				</div>
				<div class="row justify-content-around">
					<div class="form-check">
						<input type="checkbox" name="fields[required]" id="field-required" class="form-check-input" {{ $row->required ? 'checked' : ''  }} value="1" />
						<label class="form-check-label" for="field-required">{{ trans('users::registration.required') }}</label>
					</div>
					<div class="form-check">
						<input type="checkbox" name="fields[include_admin]" id="field-include_admin" class="form-check-input" {{ $row->include_admin ? 'checked' : ''  }} value="1" />
						<label class="form-check-label" for="field-include_admin">{{ trans('users::registration.include admin') }}</label>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop