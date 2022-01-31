@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
<script>
jQuery(document).ready(function ($) {
	$('.btn-success').on('click', function () {
		//e.preventDefault();

		var btn = this,
			frm = $(this).closest('form'),
			invalid = false;

		if (frm.length) {
			var elms = frm[0].querySelectorAll('input[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			elms = frm[0].querySelectorAll('select[required]');
			elms.forEach(function (el) {
				if (!el.value || el.value <= 0) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			elms = frm[0].querySelectorAll('textarea[required]');
			elms.forEach(function (el) {
				if (!el.value || !el.validity.valid) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});

			if (invalid) {
				return false;
			}
		}

		return true;
	});

	$('[maxlength]').each(function (i, el) {
		var container = $('<span class="char-counter-wrap"></span>');
		var counter = $('<span class="char-counter"></span>');
		var input = $(this);

		if (input.attr('id') != '') {
			counter.attr('id', input.attr('id') + '-counter');
		}
		
		if (input.parent().hasClass('input-group')) {
			input.parent().wrap(container);
			counter.insertAfter(input.parent());
		} else {
			input.wrap(container);
			counter.insertAfter(input);
		}
		counter.text(input.val().length + ' / ' + input.attr('maxlength'));

		input
			.on('focus', function () {
				var container = $(this).closest('.char-counter-wrap');
				if (container.length) {
					container.addClass('char-counter-focus');
				}
			})
			.on('blur', function () {
				var container = $(this).closest('.char-counter-wrap');
				if (container.length) {
					container.removeClass('char-counter-focus');
				}
			})
			.on('keyup', function () {
				var chars = $(this).val().length;
				var counter = $('#' + $(this).attr('id') + '-counter');
				if (counter.length) {
					counter.text(chars + ' / ' + $(this).attr('maxlength'));
				}
			});
	});
});
</script>
@endpush

@section('title')
{!! config('orders.name') !!}: {{ trans('orders::orders.products') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@php
	app('pathway')
		->append(
			trans('orders::orders.orders'),
			route('site.orders.index')
		)
		->append(
			trans('orders::orders.products'),
			route('site.orders.products')
		)
		->append(
			$row->id ? $row->name : trans('global.create'),
			$row->id ? route('site.orders.products.edit', ['id' => $row->id]) : route('site.orders.products.create')
		);
@endphp

@section('content')
<div class="contentInner col-md-12">
	<h2>{{ trans('orders::orders.products') }}: {{ $row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create') }}</h2>

<form action="{{ route('site.orders.products.store') }}" method="post" name="adminForm" class="editform">
	<div class="row">
		<div class="col col-md-8">
			<fieldset>
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-ordercategoryid">{{ trans('orders::orders.parent category') }}: <span class="required" title="{{ trans('global.required') }}">*</span></label>
					<select name="fields[ordercategoryid]" id="field-ordercategoryid" class="form-control" required>
						<option value="1"<?php if ($row->parentordercategoryid == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.none') }}</option>
						<?php foreach ($categories as $category) { ?>
							<option value="<?php echo $category->id; ?>"<?php if ($row->ordercategoryid == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
						<?php } ?>
					</select>
				</div>

				<div class="form-group{{ $errors->has('fields.name') ? ' has-error' : '' }}">
					<label for="field-name">{{ trans('orders::orders.name') }}: <span class="required" title="{{ trans('global.required') }}">*</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control" required maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="form-group{{ $errors->has('fields.description') ? ' has-error' : '' }}">
					<label for="field-description">{{ trans('orders::orders.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="form-control" maxlength="2000" cols="30" rows="5">{{ $row->description }}</textarea>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('fields.unitprice') ? ' has-error' : '' }}">
							<label for="field-unitprice">{{ trans('orders::orders.price') }}: <span class="required" title="{{ trans('global.required') }}">*</span></label>
							<span class="input-group">
								<span class="input-group-addon"><span class="input-group-text">{{ config('module.orders.currency', '$') }}</span></span>
								<input type="text" name="fields[unitprice]" id="field-unitprice" class="form-control form-currency" required maxlength="250" value="{{ str_replace('$', '', $row->price) }}" />
							</span>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('fields.unit') ? ' has-error' : '' }}">
							<label for="field-unit">{{ trans('orders::orders.unit') }}: <span class="required" title="{{ trans('global.required') }}">*</span></label>
							<input type="text" name="fields[unit]" id="field-unit" class="form-control" required maxlength="16" value="{{ $row->unit }}" />
							<span class="form-text text-muted">{{ trans('orders::orders.unit hint') }}</span>
						</div>
					</div>
				</div>

				<div class="form-group{{ $errors->has('fields.resourceid') ? ' has-error' : '' }}">
					<label for="field-resourceid">{{ trans('orders::orders.resource') }}:</label>
					<select class="form-control" name="fields[resourceid]" id="field-resourceid">
						<option value="0"<?php if (!$row->resourceid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						<?php foreach (App\Modules\Resources\Models\Asset::query()->orderBy('name', 'asc')->get() as $resource): ?>
							<option value="{{ $resource->id }}"<?php if ($row->resourceid == $resource->id) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group{{ $errors->has('fields.mou') ? ' has-error' : '' }}">
					<label for="field-mou">{{ trans('orders::orders.mou') }}:</label>
					<input type="url" name="fields[mou]" id="field-mou" class="form-control" maxlength="255" placeholder="https://" value="{{ $row->mou }}" />
					<span class="form-text text-muted">{{ trans('orders::orders.mou hint') }}</span>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('fields.recurringtimeperiodid') ? ' has-error' : '' }}">
							<label for="field-recurringtimeperiodid">{{ trans('orders::orders.recurrence') }}:</label>
							<select class="form-control" name="fields[recurringtimeperiodid]" id="field-recurringtimeperiod">
								<option value="0"<?php if (!$row->recurringtimeperiodid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
								<?php foreach (App\Halcyon\Models\Timeperiod::all() as $period): ?>
									<option value="{{ $period->id }}"<?php if ($row->recurringtimeperiodid == $period->id) { echo ' selected="selected"'; } ?>>{{ $period->name }}</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-check{{ $errors->has('fields.restricteddata') ? ' has-error' : '' }}">
							<input class="form-check-input" type="checkbox" id="field-restricteddata" name="fields[restricteddata]" value="1"<?php if ($row->restricteddata) { echo ' checked="checked"'; } ?> />
							<label class="form-check-label" for="field-restricteddata">{{ trans('orders::orders.restricted data') }}</label>
							<span class="form-text text-muted">{{ trans('orders::orders.restricted data explanation') }}</span>
						</div>
					</div>
				</div>

				<div class="form-group{{ $errors->has('fields.terms') ? ' has-error' : '' }}">
					<label for="field-terms">{{ trans('orders::orders.terms') }}:</label>
					<textarea name="fields[terms]" id="field-terms" class="form-control" maxlength="2000" cols="30" rows="5">{{ $row->terms }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-4">
			<fieldset>
				<legend>{{ trans('global.publishing') }}</legend>

				<!-- <div class="form-group">
					<label for="field-state">{{ trans('global.state') }}:</label>
					<select class="form-control" name="fields[state]" id="field-state">
						<option value="0"<?php if (!$row->trashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="1"<?php if ($row->trashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
					</select>
				</div> -->

				<div class="form-group">
					<label for="field-access">{{ trans('global.access') }}:</label>
					<select class="form-control" name="fields[public]" id="field-public">
						<option value="1"<?php if ($row->public == 1) { echo ' selected="selected"'; } ?>>Public</option>
						<option value="0"<?php if ($row->public == 0) { echo ' selected="selected"'; } ?>>Hidden</option>
					</select>
				</div>
			</fieldset>
		</div>
	</div>

	<p class="text-center">
		<input type="submit" class="btn btn-success" value="{{ trans('global.save') }}" />
		<a class="btn btn-outline" href="{{ route('site.orders.products') }}">{{ trans('global.cancel') }}</a>
	</p>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
</div>
@stop