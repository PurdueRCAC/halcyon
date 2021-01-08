@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/resources/js/admin.js?v=' . filemtime(public_path() . '/modules/resources/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.resources'),
		route('admin.resources.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit resources'))
		{!! Toolbar::save(route('admin.resources.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.resources.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('resources.name') !!}: <?php echo $row->id ? trans('globak.edit') . ': #' . $row->id : trans('global.create'); ?>
@stop

@section('content')
<form action="{{ route('admin.resources.store') }}" method="post" name="adminForm" id="item-form" class="editform">

	<div class="tabs">
		<ul>
			<li>
				<a href="#resource-details">{{ trans('global.details') }}</a>
			</li>
			@if ($row->id)
				@foreach ($sections as $section)
					<li>
						<a href="#resource-{{ $section['route'] }}">{{ $section['name'] }}</a>
					</li>
				@endforeach
			@endif
		</ul>

		<div id="resource-details">

	<div class="grid row">
		<div class="col col-md-7 span7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="grid row">
					<div class="col col-md-6 span6">
						<div class="form-group">
							<label for="field-resourcetype">{{ trans('resources::assets.type') }}:</label>
							<select name="fields[resourcetype]" id="field-resourcetype" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								<?php foreach ($types as $type): ?>
									<option value="{{ $type->id }}"<?php if ($row->resourcetype == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="col col-md-6 span6">
						<div class="form-group">
							<label for="field-producttype">{{ trans('resources::assets.product type') }}:</label>
							<select name="fields[producttype]" id="field-producttype" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								<option value="1"<?php if ($row->producttype == 1): echo ' selected="selected"'; endif;?>>{{ trans('resources::assets.cluster') }}</option>
								<option value="2"<?php if ($row->producttype == 2): echo ' selected="selected"'; endif;?>>{{ trans('resources::assets.hardware') }}</option>
								<option value="3"<?php if ($row->producttype == 3): echo ' selected="selected"'; endif;?>>{{ trans('resources::assets.service') }}</option>
							</select>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-parentid">{{ trans('resources::assets.parent') }}:</label>
					<select name="fields[parentid]" class="form-control">
						<option value="0">{{ trans('global.none') }}</option>
						<?php foreach ($parents as $parent): ?>
							<?php $selected = ($parent->id == $row->parentid ? ' selected="selected"' : ''); ?>
							<option value="{{ $parent->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $parent->level) . $parent->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-batchsystem">{{ trans('resources::assets.batchsystem') }}:</label>
					<select name="fields[batchsystem]" class="form-control">
						<option value="0">{{ trans('global.none') }}</option>
						<?php foreach ($batchsystems as $batchsystem): ?>
							<?php $selected = ($batchsystem->id == $row->batchsystem ? ' selected="selected"' : ''); ?>
							<option value="{{ $batchsystem->id }}"<?php echo $selected; ?>>{{ $batchsystem->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('resources::assets.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" required maxlength="32" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('resources::assets.invalid.name') }}</span>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-rolename">{{ trans('resources::assets.role name') }}:</label>
							<input type="text" name="fields[rolename]" id="field-rolename" class="form-control" maxlength="32" value="{{ $row->rolename }}" />
							<span class="form-text text-muted">{{ trans('resources::assets.role name desc') }}</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-importhostname">{{ trans('resources::assets.list name') }}:</label>
							<input type="text" name="fields[listname]" id="field-listname" class="form-control" maxlength="32" value="{{ $row->listname }}" />
							<span class="form-text text-muted">{{ trans('resources::assets.list name desc') }}</span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('resources::assets.description') }}:</label>
					<textarea name="fields[description]" id="field-description" cols="35" rows="5" class="form-control">{{ $row->description }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			@sliders('start', 'asset-sliders')
				@sliders('panel', trans('resources::assets.options'), 'params-options')
					<fieldset class="panelform">
						<div class="form-group">
							<label for="params-desktop">{{ trans('resources::assets.params.desktop') }}:</label>
							<input type="text" class="form-control" name="params[desktop]" id="params-desktop" value="{{ $row->params->get('desktop') }}" />
							<span class="form-text text-muted">{{ trans('resources::assets.params.desktop desc') }}</span>
						</div>

						<div class="form-group">
							<label for="params-gateway">{{ trans('resources::assets.params.gateway') }}:</label>
							<input type="text" class="form-control" name="params[gateway]" id="params-gateway" value="{{ $row->params->get('gateway') }}" />
							<span class="form-text text-muted">{{ trans('resources::assets.params.gateway desc') }}</span>
						</div>

						<div class="form-group">
							<label for="params-notebook">{{ trans('resources::assets.params.notebook') }}:</label>
							<input type="text" class="form-control" name="params[notebook]" id="params-notebook" value="{{ $row->params->get('notebook') }}" />
							<span class="form-text text-muted">{{ trans('resources::assets.params.notebook desc') }}</span>
						</div>
					</fieldset>
			@sliders('end')

			@include('history::admin.history')
		</div>
	</div>

		</div><!-- / #resource-details -->
		@if ($row->id)
			@foreach ($sections as $section)
				<div id="resource-{{ $section['route'] }}">
					{!! $section['content'] !!}
				</div><!-- / resource-{{ $section['route'] }} -->
			@endforeach
		@endif
	</div><!-- / .tabs -->

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop