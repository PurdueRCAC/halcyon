@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.registration fields'),
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
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('users::registration.field options')}}</legend>
				<div class="form-group">
					<label for="field-type">{{ trans('users::registration.type') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[type]" id="field-type" class="form-control required">
						<option value="text" {{ $row->type == 'text' ? 'selected' : '' }}>Text</option>
						<option value="textarea" {{ $row->type == 'textarea' ? 'selected' : '' }}>Text Area</option>
						<option value="select" {{ $row->type == 'select' ? 'selected' : '' }}>Select</option>
					</select>
				</div>
				<div class="selectOptions {{ $row->type != 'select' ? 'hide' : '' }}" data-options="{{ json_encode($row->options) }}">
					<button type="button" class="btn btn-sm btn-success text-white" id="add-option">Add Option</button>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
<script type="text/javascript">
	function generateOptionField(id, value) {
		const valueText = value ? value : ''
		return `
			<label for="field-options-${id}">{{ trans('users::registration.option') }} ${id}: <span class="required">{{ trans('global.required') }}</span></label>
			<div class="d-flex">
				<input type="text" name="fields[options][]" id="field-options-${id}" class="form-control required mr-3" maxlength="100" value="${valueText}" />
				<button type="button" class="delete-option text-white btn btn-sm btn-danger" data-id="field-options-${id}">Delete</button>
			</div>
		`;
	} 
	function deleteOption(element) {
		const inputBox = document.getElementById(element);
		inputBox.closest('.form-group').remove();
		resortOptions();
	}
	function resortOptions() {
		const optionsWindow = document.querySelector('.selectOptions');
		const existingOptions = optionsWindow.querySelectorAll('input');
		const values = [];
		for (const option of existingOptions) {
			values.push(option.value ? option.value : '');
			option.closest('.form-group').remove();
		}
		if (values.length > 0) {
			values.forEach((value) => {
				addOption(value);
			})
		} else {
			addOption();
		}
	}
	function addOption(value) {
		const optionsWindow = document.querySelector('.selectOptions');
		const existingOptions = optionsWindow.querySelectorAll('input');
		const optionNumber = existingOptions.length + 1;
		const formGroup = document.createElement('div');
		formGroup.classList.add('form-group');
		formGroup.insertAdjacentHTML('beforeend', generateOptionField(optionNumber, value));
		const deleteButton = formGroup.querySelector('.delete-option');
		deleteButton.addEventListener('click', (event) => deleteOption(event.currentTarget.dataset.id));
		optionsWindow.insertAdjacentElement('beforeend', formGroup);
	}
	function handleShowOptionsWindow() {
		const typeSelect = document.getElementById('field-type');
		const option = typeSelect.value;
		const optionsWindow = document.querySelector('.selectOptions');
		if (option === 'select') {
			optionsWindow.classList.remove('hide');
		} else {
			optionsWindow.classList.add('hide');
		}
		const existingOptions = optionsWindow.querySelectorAll('input');
		if (existingOptions.length === 0) {
			const optionValues = JSON.parse(optionsWindow.dataset.options);
			if (optionValues && optionValues.length > 0) {
				for (const option of optionValues) {
					addOption(option);
				}
			} else {
				addOption();
			}
		}
	}
	window.onload = () => {
		handleShowOptionsWindow();
		const typeSelect = document.getElementById('field-type');
		const addButton = document.getElementById('add-option');
		addButton.addEventListener('click', () => addOption());
		typeSelect.addEventListener('change', () => handleShowOptionsWindow());
	}
</script>
@stop
