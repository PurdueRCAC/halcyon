@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/software/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('software::software.module name'),
		route('admin.software.index')
	)
	->append(
		trans('software::software.types'),
		route('admin.software.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit software'))
		{!! Toolbar::save(route('admin.software.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.software.types.cancel'))
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('software::software.module name') }}: {{ trans('software::software.types') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.software.types.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col-md-6 mx-auto">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-title">{{ trans('software::software.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="title" id="field-title" class="form-control required" required maxlength="255" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('software::software.errors.invalid name') }}</span>
				</div>

				<div class="form-group">
					<label for="field-alias">{{ trans('software::software.alias') }}:</label>
					<input type="text" name="alias" id="field-alias" class="form-control" maxlength="255" pattern="[a-zA-Z0-9_\-]{1,50}" value="{{ $row->alias }}" />
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop