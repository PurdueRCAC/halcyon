@extends('layouts.master')

@push('scripts')
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
		trans('resources::resources.subresources'),
		route('admin.resources.subresources')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit resources'))
		{!! Toolbar::save(route('admin.resources.subresources.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.resources.subresources.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('resources.name') !!}: <?php echo $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create'); ?>
@stop

@section('content')
<form action="{{ route('admin.resources.subresources.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col-sm-12 col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="assoc-resourceid">{{ trans('resources::assets.resource') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="assoc[resourceid]" id="assoc-resourceid" class="form-control" required>
						<option value="0">{{ trans('global.none') }}</option>
						<?php foreach ($parents as $parent): ?>
							<?php
							$selected = ($parent->id == $resourceid ? ' selected="selected"' : '');
							?>
							<option value="{{ $parent->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $parent->level) . $parent->name }}</option>
						<?php endforeach; ?>
					</select>
					<span class="invalid-feedback">{{ trans('resources::assets.invalid.resource') }}</span>
				</div>

				<div class="row">
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<div class="form-group">
								<label for="field-cluster">{{ trans('resources::assets.cluster') }}: <span class="required">{{ trans('global.required') }}</span></label>
								<input type="text" name="fields[cluster]" id="field-cluster" class="form-control" maxlength="12" value="{{ $row->cluster }}" />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<div class="form-group">
								<label for="field-name">{{ trans('resources::assets.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
								<input type="text" name="fields[name]" id="field-name" class="form-control" required maxlength="32" value="{{ $row->name }}" />
								<span class="invalid-feedback">{{ trans('resources::assets.invalid.name') }}</span>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('resources::assets.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="form-control" rows="3" cols="35">{{ $row->description }}</textarea>
				</div>

				<div class="row">
					<div class="col-xs-12 col-md-4">
						<div class="form-group">
							<div class="form-group">
								<label for="field-nodecores">{{ trans('resources::assets.node cores') }}: <span class="required">{{ trans('global.required') }}</span></label>
								<input type="number" name="fields[nodecores]" id="field-nodecores" class="form-control" value="{{ $row->nodecores }}" />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-md-4">
						<div class="form-group">
							<div class="form-group">
								<label for="field-nodemem">{{ trans('resources::assets.node mem') }}: <span class="required">{{ trans('global.required') }}</span></label>
								<input type="text" name="fields[nodemem]" id="field-nodemem" class="form-control" pattern="[0-9]{1,4}[PTGMKB]" value="{{ $row->nodemem }}" />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-md-4">
						<div class="form-group">
							<div class="form-group">
								<label for="field-nodegpus">{{ trans('resources::assets.node gpus') }}:</label>
								<input type="number" name="fields[nodegpus]" id="field-nodegpus" class="form-control" value="{{ $row->nodegpus }}" />
							</div>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-nodeattributes">{{ trans('resources::assets.node attributes') }}:</label>
					<input type="text" name="fields[nodeattributes]" id="field-nodeattributes" class="form-control" maxlength="16" value="{{ $row->nodeattributes }}" />
				</div>

			</fieldset>
		</div>
		<div class="col-sm-12 col-md-5">
			@include('history::admin.history')
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop