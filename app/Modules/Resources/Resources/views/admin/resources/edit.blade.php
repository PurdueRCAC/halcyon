@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/resources/js/admin.js?v=' . filemtime(public_path() . '/modules/resources/js/admin.js')) }}"></script>
@stop

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
{!! config('resources.name') !!}: <?php echo $row->id ? trans('resources::assets.edit') . ': #' . $row->id : trans('resources::assets.create'); ?>
@stop

@section('content')
<form action="{{ route('admin.resources.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">
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
					<label for="field-parentid">{{ trans('resources::assets.FIELD_PARENT') }}: <span class="required">{{ trans('global.required') }}</span></label>
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
					<input type="text" name="fields[name]" id="field-name" class="form-control required" maxlength="32" value="{{ $row->name }}" />
				</div>

				<div class="form-group">
					<label for="field-rolename">{{ trans('resources::assets.role name') }}:</label>
					<input type="text" name="fields[rolename]" id="field-rolename" class="form-control" maxlength="32" value="{{ $row->rolename }}" />
				</div>

				<div class="form-group">
					<label for="field-importhostname">{{ trans('resources::assets.list name') }}:</label>
					<input type="text" name="fields[listname]" id="field-listname" class="form-control" maxlength="32" value="{{ $row->listname }}" />
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			@include('history::admin.history')
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop