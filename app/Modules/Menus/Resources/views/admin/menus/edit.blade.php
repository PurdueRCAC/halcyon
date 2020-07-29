@extends('layouts.master')

@push('scripts')
<script src="{{ asset('js/validate.js?v=' . filemtime(public_path() . '/js/validate.js')) }}"></script>
<script src="{{ asset('modules/menus/js/menus.js?v=' . filemtime(public_path() . '/modules/menus/js/menus.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('menus::menus.module name'),
		route('admin.menus.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit menus'))
		{!! Toolbar::save(route('admin.menus.store')) !!}
	@endif
	{!! Toolbar::cancel(route('admin.menus.cancel')) !!}
	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('menus.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.menus.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-title">{{ trans('menus::menus.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[title]" id="field-title" class="form-control required" maxlength="250" value="{{ $row->title }}" />
				</div>

				<div class="form-group">
					<label for="field-menutype">{{ trans('menus::menus.item type') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[menutype]" id="field-menutype" class="form-control" maxlength="250" value="{{ $row->menutype }}" />
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('menus::menus.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="form-control" rows="5" cols="40">{{ $row->description }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			@include('history::admin.history')
		</div>
	</div>

	<input type="hidden" name="id" value="{{ $row->id }}" />
	@csrf
</form>
@stop