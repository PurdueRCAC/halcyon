@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/menus/js/menus.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('menus::menus.module name'),
		route('admin.menus.index')
	)
	->append(
		trans('menus::menus.items'),
		route('admin.menus.items')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit menus'))
		{!! Toolbar::save(route('admin.menus.items.store')) !!}
	@endif
	{!! Toolbar::cancel(route('admin.menus.items.cancel')) !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('menus::menus.menu manager') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.menus.items.store') }}" method="post" name="adminForm" id="item-form" class="form-validate">
	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('menutype'); ?>
							<?php echo $form->getInput('menutype'); ?>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('parent_id'); ?>
							<?php echo $form->getInput('parent_id'); ?>
						</div>
					</div>
				</div>

				<div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
					<label id="fields_type-lbl" for="fields_type" class="hasTip required-field" title="{{ trans('menus::menus.item.type desc') }}">{{ trans('menus::menus.item.type') }} <span class="required star">{{ trans('global.required') }}</span></label>
					<fieldset id="fields_type" class="radio required">
						<ul>
							<li>
								<div class="form-check">
									<input type="radio" id="fields_type0" name="fields[type]" value="module" <?php if ($row->type == 'module') { echo ' checked="checked"'; } ?> class="form-check-input"/>
									<label class="form-check-label" for="fields_type0">{{ trans('menus::menus.item.type page') }}<br /><span class="text-muted">{{ trans('menus::menus.item.type page desc') }}</span></label>
								</div>
							</li>
							<li>
								<div class="form-check">
									<input type="radio" id="fields_type1" name="fields[type]" value="url" <?php if ($row->type == 'url') { echo ' checked="checked"'; } ?> class="form-check-input"/>
									<label class="form-check-label" for="fields_type1">{{ trans('menus::menus.item.type url') }}<br /><span class="text-muted">{{ trans('menus::menus.item.type url desc') }}</span></label>
								</div>
							</li>
							<li>
								<div class="form-check">
									<input type="radio" id="fields_type2" name="fields[type]" value="separator" <?php if ($row->type == 'separator') { echo ' checked="checked"'; } ?> class="form-check-input"/>
									<label class="form-check-label" for="fields_type2">{{ trans('menus::menus.item.type separator') }}<br /><span class="text-muted">{{ trans('menus::menus.item.type separator desc') }}</span></label>
								</div>
							</li>
							<li>
								<div class="form-check">
									<input type="radio" id="fields_type3" name="fields[type]" value="html" <?php if ($row->type == 'html') { echo ' checked="checked"'; } ?> class="form-check-input"/>
									<label class="form-check-label" for="fields_type3">{{ trans('menus::menus.item.type html') }}<br /><span class="text-muted">{{ trans('menus::menus.item.type html desc') }}</span></label>
								</div>
							</li>
						</ul>
					</fieldset>
					{!! $errors->first('type', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<div class="form-group menutype-dependant menutype-url menutype-module menutype-html{{ $errors->has('title') ? ' has-error' : '' }}">
					<?php echo $form->getLabel('title'); ?>
					<?php echo $form->getInput('title'); ?>
					{!! $errors->first('title', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<?php if ($row->type == 'url'): ?>
					<?php $form->setFieldAttribute('link', 'readonly', 'false');?>
				<?php endif; ?>
				<div class="form-group menutype-dependant menutype-url{{ $errors->has('link') ? ' has-error' : '' }}">
					<?php echo $form->getLabel('link'); ?>
					<?php echo $form->getInput('link'); ?>
					{!! $errors->first('link', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<div class="form-group menutype-dependant menutype-module">
					<?php echo $form->getLabel('route_id'); ?>
					<?php echo $form->getInput('route_id'); ?>
				</div>

				<div class="form-group menutype-dependant menutype-module mb-0">
					<?php echo $form->getLabel('page_id'); ?>
					<?php echo $form->getInput('page_id'); ?>
				</div>

				<div class="form-group menutype-dependant menutype-html mb-0">
					<?php echo $form->getLabel('content'); ?>
					<?php echo $form->getInput('content'); ?>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('access'); ?>
							<span class="input-group input-access">
								<?php echo $form->getInput('access'); ?>
								<span class="input-group-append"><span class="input-group-text fa fa-lock"></span></span>
							</span>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('published'); ?>
							<span class="input-group input-state">
								<?php echo $form->getInput('published'); ?>
								<span class="input-group-append"><span class="input-group-text fa fa-check"></span></span>
							</span>
						</div>
					</div>
				</div>
			</fieldset>
			<fieldset class="adminform menutype-dependant menutype-url menutype-module">
				<legend>{{ trans('global.options') }}</legend>

						<div class="form-group">
							<?php
							$field = $form->getField('target');
							$desc = $field->description;

							echo $field->label;
							if ($desc):
								echo ' <span class="fa fa-question-circle text-info" aria-hidden="true" data-tip="' . e(trans($desc)) . '"></span>';
							endif;
							echo $field->input;
							if ($desc):
								echo '<span class="sr-only visually-hidden">' . trans($desc) . '</span>';
							endif;
							?>
						</div>

						<div class="form-group">
							<?php
							$field = $form->getField('class');
							$desc = $field->description;

							echo $field->label;
							if ($desc):
								echo ' <span class="fa fa-question-circle text-info" aria-hidden="true" data-tip="' . e(trans($desc)) . '"></span>';
							endif;
							echo $field->input;
							if ($desc):
								echo '<span class="sr-only visually-hidden">' . trans($desc) . '</span>';
							endif;
							?>
						</div>

				<div class="form-group">
					<label for="params-menu_image">{{ trans('menus::menus.item.image') }} <span class="fa fa-question-circle text-info" aria-hidden="true" data-tip="{{ trans('menus::menus.item.image desc') }}"></span></label>
					<input type="text" name="params[menu_image]" id="params-menu_image" class="form-control" value="{{ $row->params->get('menu_image', '') }}" />
					<span class="sr-only visually-hidden">{{ trans('menus::menus.item.image desc') }}</span>
				</div>

				<div class="form-group mb-0">
					<label for="params-menu_image_position">{{ trans('menus::menus.item.image position') }}</label>
					<select name="params[menu_image_position]" id="params-menu_image_position" class="form-control">
						<option value="prepend">{{ trans('menus::menus.item.image position prepend') }}</option>
						<option value="append">{{ trans('menus::menus.item.image position append') }}</option>
						<option value="replace">{{ trans('menus::menus.item.image position replace') }}</option>
					</select>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="fields[language]" value="{{ $row->language }}" />
	<input type="hidden" name="fields[module_id]" value="{{ $row->module_id }}" />
	<input type="hidden" name="fields[id]" value="{{ $row->id }}" />
	<input type="hidden" name="fieldtype" value="" />
	@csrf
</form>
@stop