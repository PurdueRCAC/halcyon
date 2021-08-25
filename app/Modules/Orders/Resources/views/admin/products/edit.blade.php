@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css?v=' . filemtime(public_path() . '/modules/orders/css/orders.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js?v=' . filemtime(public_path() . '/modules/orders/js/orders.js')) }}"></script>
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
	$('.form-currency')
		.on('keyup', function (e){
			var val = $(this).val();

			val = val.replace(/[^0-9.,]+/g, '');

			$(this).val(val);
		})
		.on('blur', function (e){
			var val = $(this).val();

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

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('admin.orders.index')
	)
	->append(
		trans('orders::orders.products'),
		route('admin.orders.products')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit orders.products'))
		{!! Toolbar::save(route('admin.orders.products.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.orders.products.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('orders.name') !!}: {{ trans('orders::orders.products') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.orders.products.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group{{ $errors->has('fields.ordercategoryid') ? ' has-error' : '' }}">
					<label for="field-ordercategoryid">{{ trans('orders::orders.parent category') }} <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[ordercategoryid]" id="field-ordercategoryid" class="form-control" required>
						<option value="1"<?php if ($row->ordercategoryid == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.none') }}</option>
						<?php foreach ($categories as $category): ?>
							<option value="<?php echo $category->id; ?>"<?php if ($row->ordercategoryid == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group{{ $errors->has('fields.name') ? ' has-error' : '' }}">
					<label for="field-name">{{ trans('orders::orders.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control" required maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="form-group{{ $errors->has('fields.description') ? ' has-error' : '' }}">
					<label for="field-description">{{ trans('orders::orders.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="form-control" maxlength="2000" cols="30" rows="5">{{ $row->description }}</textarea>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('fields.unitprice') ? ' has-error' : '' }}">
							<label for="field-unitprice">{{ trans('orders::orders.price') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<span class="input-group">
								<span class="input-group-prepend"><span class="input-group-text">{{ config('module.orders.currency', '$') }}</span></span>
								<input type="text" name="fields[unitprice]" id="field-unitprice" class="form-control form-currency" required maxlength="12" value="{{ str_replace('$', '', $row->price) }}" />
							</span>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('fields.unit') ? ' has-error' : '' }}">
							<label for="field-unit">{{ trans('orders::orders.unit') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="fields[unit]" id="field-unit" class="form-control" required maxlength="16" value="{{ $row->unit }}" />
							<span class="form-text text-muted">{{ trans('orders::orders.unit hint') }}</span>
						</div>
					</div>
				</div>

				<div class="form-group{{ $errors->has('resourceid') ? ' has-error' : '' }}">
					<label for="field-resourceid">{{ trans('orders::orders.resource') }}:</label>
					<select class="form-control" name="fields[resourceid]" id="field-resourceid">
						<option value="0"<?php if (!$row->resourceid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						<?php
						$resources = (new App\Modules\Resources\Models\Asset)->tree();
						foreach ($resources as $resource): ?>
							<option value="{{ $resource->id }}"<?php if ($row->resourceid == $resource->id) { echo ' selected="selected"'; } ?>>{!! str_repeat('|&mdash;', $resource->level) !!} {{ $resource->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group{{ $errors->has('mou') ? ' has-error' : '' }}">
					<label for="field-mou">{{ trans('orders::orders.mou') }}:</label>
					<input type="text" name="fields[mou]" id="field-mou" class="form-control" maxlength="255" placeholder="http://" value="{{ $row->mou }}" />
				</div>

				<div class="form-group{{ $errors->has('recurringtimeperiodid') ? ' has-error' : '' }}">
					<label for="field-recurringtimeperiodid">{{ trans('orders::orders.recurrence') }}:</label>
					<select class="form-control" name="fields[recurringtimeperiodid]" id="field-recurringtimeperiodid">
						<option value="0"<?php if (!$row->recurringtimeperiodid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						<?php foreach (App\Halcyon\Models\Timeperiod::all() as $period): ?>
							<option value="{{ $period->id }}"<?php if ($row->recurringtimeperiodid == $period->id) { echo ' selected="selected"'; } ?>>{{ $period->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group form-block">
							<div class="form-check{{ $errors->has('restricteddata') ? ' has-error' : '' }}">
								<input class="form-check-input" type="checkbox" id="field-restricteddata" name="fields[restricteddata]" value="1"<?php if ($row->restricteddata) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="field-restricteddata">{{ trans('orders::orders.restricted data') }}</label>
								<span class="form-text">{{ trans('orders::orders.restricted data explanation') }}</span>
							</div>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group form-block">
							<div class="form-check{{ $errors->has('ticket') ? ' has-error' : '' }}">
								<input class="form-check-input" type="checkbox" id="field-ticket" name="fields[ticket]" value="1"<?php if ($row->ticket) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="field-ticket">{{ trans('orders::orders.ticket') }}</label>
								<span class="form-text">{{ trans('orders::orders.ticket explanation') }}</span>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group{{ $errors->has('fields.terms') ? ' has-error' : '' }}">
					<label for="field-terms">{{ trans('orders::orders.terms') }}:</label>
					<textarea name="fields[terms]" id="field-terms" class="form-control" maxlength="2000" cols="30" rows="5">{{ $row->terms }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-access">{{ trans('global.access') }}:</label>
					<select class="form-control" name="fields[public]" id="field-public">
						<option value="1"<?php if ($row->public == 1) { echo ' selected="selected"'; } ?>>Public</option>
						<option value="0"<?php if ($row->public == 0) { echo ' selected="selected"'; } ?>>Hidden</option>
					</select>
				</div>
			</fieldset>

			@include('history::admin.history')
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop