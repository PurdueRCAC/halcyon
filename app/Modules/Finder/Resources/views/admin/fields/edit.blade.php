@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('finder::finder.module name'),
		route('admin.finder.index')
	)
	->append(
		trans('finder::finder.fields'),
		route('admin.finder.fields')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit groups'))
		{!! Toolbar::save(route('admin.finder.fields.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.finder.fields.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('groups.name') !!}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.finder.fields.store') }}" method="post" name="adminForm" id="item-form" class="editform">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-label">{{ trans('finder::finder.label') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[label]" id="field-label" class="form-control{{ $errors->has('fields.label') ? ' is-invalid' : '' }}" required maxlength="150" value="{{ $row->label }}" />
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('finder::finder.name') }}</label>
					<input type="text" name="fields[name]" id="field-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" maxlength="150" value="{{ $row->name }}" />
				</div>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
			</fieldset>
		</div>
		<div class="col col-md-5">
			@include('history::admin.history')
		</div>
	</div>

	@csrf
</form>
@stop