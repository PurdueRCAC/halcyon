@extends('layouts.master')

@section('toolbar')
	@if (auth()->user()->can('edit groups'))
		{!! Toolbar::save(route('admin.courses.members.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.courses.members.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! trans('courses::courses.module name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.courses.members.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<fieldset class="adminform">
		<legend><span>{{ trans('global.details') }}</span></legend>

		<div class="form-group" data-hint="{{ trans('courses::courses.name hint') }}">
			<label for="field-name">{{ trans('courses::courses.name') }}:</label>
			<input type="text" name="fields[name]" id="field-user" class="form-control disabled" disabled="disabled" readonly="readonly" value="{{ $row->user ? $row->user->username : '' }}" />
		</div>

		<div class="form-group">
			<select name="membertype[{{ $row->id }}]" class="form-control"<?php if ($row->user && $row->user->isTrashed()) { echo ' disabled'; } ?>>
				<option valie="1"<?php if ($row->membertype != 2) { echo ' selected="selected"'; } ?>>Student</option>
				<option valie="2"<?php if ($row->membertype == 2) { echo ' selected="selected"'; } ?>>Instructor</option>
			</select>
		</div>
	</fieldset>

	<input type="hidden" name="userid" value="{{ $row->userid }}" />

	@csrf
</form>
@stop