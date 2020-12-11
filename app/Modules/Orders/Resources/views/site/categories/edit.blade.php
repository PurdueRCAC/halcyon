@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('site.orders.index')
	)
	->append(
		trans('orders::orders.categories'),
		route('site.orders.categories')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('title')
{!! config('orders.name') !!}: {{ trans('orders::orders.categories') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
@component('orders::site.submenu')
	categories
@endcomponent
</div>
<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
<h2>{{ trans('orders::orders.categories') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}</h2>


	@if ($errors->any())
		<div class="alert alert-error">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

		<form action="{{ route('site.orders.categories.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group{{ $errors->has('parentordercategoryid') ? ' has-error' : '' }}">
					<label for="field-parentordercategoryid">{{ trans('orders::orders.parent category') }}</label>
					<select name="fields[parentordercategoryid]" id="field-parentordercategoryid" class="form-control">
						<option value="1"<?php if ($row->parentordercategoryid == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.none') }}</option>
						<?php foreach ($categories as $category) { ?>
							<option value="{{ $category->id }}"<?php if ($row->parentordercategoryid == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
						<?php } ?>
					</select>
				</div>

				<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
					<label for="field-name">{{ trans('orders::orders.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
					<label for="field-description">{{ trans('orders::orders.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="form-control" cols="30" rows="5">{{ $row->description }}</textarea>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-state">{{ trans('global.state') }}:</label>
					<select class="form-control" name="state" id="field-state">
						<option value="published"<?php if (!$row->isTrashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="trashed"<?php if ($row->isTrashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
					</select>
				</div>
			</fieldset>
			<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf

				<div class="form-group text-center">
					<input type="submit" class="btn btn-success" value="{{ trans('global.button.save') }}" />
				</div>
			</form>
		</div>
	
</div>
@stop