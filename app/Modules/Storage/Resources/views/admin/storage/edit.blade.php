@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('storage::storage.module name'),
		route('admin.storage.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit storage'))
		{!! Toolbar::save(route('admin.storage.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.storage.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('storage.name') !!}
@stop

@section('content')
<form action="{{ route('admin.storage.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-parentresourceid">{{ trans('storage::storage.resource type') }}:</label>
					<select name="fields[parentresourceid]" id="field-parentresourceid" class="form-control">
						<option value="0">{{ trans('global.none') }}</option>
						<?php foreach ($resources as $resource): ?>
							<?php $selected = ($resource->id == $row->parentresourceid ? ' selected="selected"' : ''); ?>
							<option value="{{ $resource->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $resource->level) . $resource->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('storage::storage.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" value="{{ $row->name }}" />
				</div>

				<div class="form-group">
					<label for="field-path">{{ trans('storage::storage.path') }}:</label>
					<input type="text" name="fields[path]" id="field-path" class="form-control" value="{{ $row->path }}" />
				</div>

				<div class="form-group">
					<label for="field-importhostname">{{ trans('storage::storage.import hostname') }}:</label>
					<input type="text" name="fields[importhostname]" id="field-listname" class="form-control" value="{{ $row->importhostname ? $row->importhostname : '' }}" />
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('storage::storage.quota') }}</legend>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-defaultquotaspace">{{ trans('storage::storage.quota space') }}:</label>
							<input type="text" name="fields[defaultquotaspace]" id="field-defaultquotaspace" class="form-control" value="{{ App\Halcyon\Utility\Number::formatBytes($row->defaultquotaspace) }}" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-defaultquotafile">{{ trans('storage::storage.quota file') }}:</label>
							<input type="number" name="fields[defaultquotafile]" id="field-defaultquotafile" class="form-control" value="{{ $row->defaultquotafile }}" />
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('storage::storage.message queue') }}</legend>

				<!-- <div class="row">
					<div class="col-md-6"> -->
						<div class="form-group">
							<label for="field-getquotatypeid">{{ trans('storage::storage.get quota type') }}:</label>
							<select name="fields[getquotatypeid]" id="field-getquotatypeid" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								<?php foreach ($messagetypes as $messagetype): ?>
									<?php $selected = ($messagetype->id == $row->getquotatypeid ? ' selected="selected"' : ''); ?>
									<option value="{{ $messagetype->id }}"<?php echo $selected; ?>>{{ $messagetype->name }}</option>
								<?php endforeach; ?>
							</select>
						</div>
					<!-- </div>
					<div class="col-md-6"> -->
						<div class="form-group">
							<label for="field-createtypeid">{{ trans('storage::storage.create type') }}:</label>
							<select name="fields[createtypeid]" id="field-createtypeid" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								<?php foreach ($messagetypes as $messagetype): ?>
									<?php $selected = ($messagetype->id == $row->createtypeid ? ' selected="selected"' : ''); ?>
									<option value="{{ $messagetype->id }}"<?php echo $selected; ?>>{{ $messagetype->name }}</option>
								<?php endforeach; ?>
							</select>
						</div>
					<!-- </div>
				</div> -->
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop