@extends('layouts.master')

@push('scripts')
<script>
$(document).ready(function() {
	var frm = document.getElementById('category-form'),
		invalid = false;

	if (frm) {
		frm.addEventListener('submit', function(e){
			e.preventDefault();

			var elms = frm.querySelectorAll('input[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			elms = frm.querySelectorAll('select[required]');
			elms.forEach(function (el) {
				if (!el.value || el.value <= 0) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			elms = frm.querySelectorAll('textarea[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});

			if (!invalid) {
				return true;
			}

			return false;
		});
	}
});
</script>
@endpush

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
<div class="row">
<div class="contentInner col-lg-12 col-md-12 col-sm-12 col-xs-12">
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

	<form action="{{ route('site.orders.categories.store') }}" method="post" name="categoryform" id="category-form" class="editform">
		<div class="row">
			<div class="col-md-7">
				<fieldset class="adminform">
					<legend>{{ trans('global.details') }}</legend>

					<div class="form-group{{ $errors->has('parentordercategoryid') ? ' has-error' : '' }}">
						<label for="field-parentordercategoryid">{{ trans('orders::orders.parent category') }}</label>
						<select name="fields[parentordercategoryid]" id="field-parentordercategoryid" class="form-control">
							<option value="1"<?php if ($row->parentordercategoryid == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.none') }}</option>
							@foreach ($categories as $category)
								<option value="{{ $category->id }}"<?php if ($row->parentordercategoryid == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
						<label for="field-name">{{ trans('orders::orders.name') }}: <span class="required" title="{{ trans('global.required') }}">*</span></label>
						<input type="text" name="fields[name]" id="field-name" class="form-control" required maxlength="250" value="{{ $row->name }}" />
					</div>

					<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
						<label for="field-description">{{ trans('orders::orders.description') }}:</label>
						<textarea name="fields[description]" id="field-description" class="form-control" maxlength="2000" cols="30" rows="5">{{ $row->description }}</textarea>
					</div>
				</fieldset>
			</div>
			<div class="col-md-5">
				<fieldset class="adminform">
					<legend>{{ trans('global.publishing') }}</legend>

					<div class="form-group">
						<label for="field-state">{{ trans('global.state') }}:</label>
						<select class="form-control" name="state" id="field-state">
							<option value="published"<?php if (!$row->trashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
							<option value="trashed"<?php if ($row->trashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
						</select>
					</div>
				</fieldset>
			</div>
		</div>

		<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
		@csrf

		<div class="form-group text-center">
			<input type="submit" class="btn btn-success" value="{{ trans('global.button.save') }}" />
			<a href="{{ route('site.orders.categories') }}" class="btn btn-link">
				{{ trans('global.button.cancel') }}
			</a>
		</div>
	</form>
</div>
</div>
@stop