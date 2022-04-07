@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/publications/js/admin.js?v=' . filemtime(public_path() . '/modules/publications/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('publications::publications.module name'),
		route('admin.publications.index')
	)
	->append(
		trans('publications::publications.types'),
		route('admin.publications.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit publications'))
		{!! Toolbar::save(route('admin.publications.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.publications.types.cancel'))
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('publications::publications.module name') }}: {{ trans('publications::publications.types') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.publications.types.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col-md-12">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('publications::publications.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" required maxlength="50" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('publications::publications.errors.invalid name') }}</span>
				</div>

				<div class="form-group">
					<label for="field-alias">{{ trans('publications::publications.alias') }}:</label>
					<input type="text" name="fields[alias]" id="field-alias" class="form-control" maxlength="50" pattern="[a-zA-Z0-9_\-]{1,50}" value="{{ $row->alias }}" />
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop