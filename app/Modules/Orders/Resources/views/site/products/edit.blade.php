@extends('layouts.master')

@push('scripts')
<script>
function formatCurrency(number, decPlaces, decSep, thouSep) {
	decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
	decSep = typeof decSep === "undefined" ? "." : decSep;
	thouSep = typeof thouSep === "undefined" ? "," : thouSep;
	var sign = number < 0 ? "-" : "";
	var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
	var j = (j = i.length) > 3 ? j % 3 : 0;

	return sign +
		(j ? i.substr(0, j) + thouSep : "") +
		i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
		(decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
}

jQuery(document).ready(function ($) {
	$('.btn-success').on('click', function (e) {
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
			var elms = frm[0].querySelectorAll('select[required]');
			elms.forEach(function (el) {
				if (!el.value || el.value <= 0) {
					el.classList.add('is-invalid');
					invalid = true;
				} else {
					el.classList.remove('is-invalid');
				}
			});
			var elms = frm[0].querySelectorAll('textarea[required]');
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

	$('.form-currency')
		.on('keyup', function (e){
			var val = $(this).val();

			val = val.replace(/[^0-9.,]+/g, '');

			$(this).val(val);
		})
		.on('blur', function (e){
			var val = $(this).val();

			val = val.replace(/[^0-9.]+/g, '');

			// Create our number formatter.
			var formatter = new Intl.NumberFormat('en-US', {
				style: 'currency',
				currency: 'USD',
				// These options are needed to round to whole numbers if that's what you want.
				//minimumFractionDigits: 0,
				//maximumFractionDigits: 0,
			});

			$(this).val(formatter.format(val).replace('$', '')); /* $2,500.00 */
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
					<select name="fields[ordercategoryid]" id="field-ordercategoryid" class="form-control filter filter-submit" required>
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
					<textarea name="fields[description]" id="field-description" class="form-control" cols="30" rows="5">{{ $row->description }}</textarea>
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
						<?php foreach (App\Modules\Resources\Models\Asset::query()->withTrashed()->whereIsactive()->orderBy('name', 'asc')->get() as $resource): ?>
							<option value="{{ $resource->id }}"<?php if ($row->resourceid == $resource->id) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group{{ $errors->has('fields.mou') ? ' has-error' : '' }}">
					<label for="field-mou">{{ trans('orders::orders.mou') }}:</label>
					<input type="text" name="fields[mou]" id="field-mou" class="form-control" placeholder="http://" value="{{ $row->mou }}" />
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
					<textarea name="fields[terms]" id="field-terms" class="form-control" cols="30" rows="5">{{ $row->terms }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-4">
			<fieldset>
				<legend>{{ trans('global.publishing') }}</legend>

				<!-- <div class="form-group">
					<label for="field-state">{{ trans('global.state') }}:</label>
					<select class="form-control" name="fields[state]" id="field-state">
						<option value="0"<?php if (!$row->isTrashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="1"<?php if ($row->isTrashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
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