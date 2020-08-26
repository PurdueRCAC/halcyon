@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('listeners::listeners.module name'),
		route('admin.listeners.index')
	)
	->append(
		($row->extension_id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit listeners'))
		{!! Toolbar::apply(route('admin.listeners.store')) !!}
	@endif
	{!! Toolbar::cancel(route('admin.listeners.cancel')) !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('listeners.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.listeners.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.VALIDATION_FORM_FAILED') }}">
	<div class="row">
		<div class="col col-xs-12 col-sm-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

				<div class="row">
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('folder'); ?><br />
							<?php echo $form->getInput('folder'); ?>
						</div>
					</div>
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('element'); ?><br />
							<?php echo $form->getInput('element'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('name'); ?><br />
					<?php echo $form->getInput('name'); ?>
					<!-- <input type="text" readonly="readonly" disabled="disabled" class="form-control-plaintext" value="{{ trans('listener.' . $row->folder . '.' . $row->element . '::' . $row->element . '.listener name') }}" />
					<span class="readonly plg-name"><?php echo $row->name; ?></span> -->
				</div>

				<div class="form-group">
					{{ trans('listeners::listeners.description') }}<br />
					{{ trans('listener.' . $row->folder . '.' . $row->element . '::' . $row->element . '.listener desc') }}
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="row">
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('enabled'); ?><br />
							<?php echo $form->getInput('enabled'); ?>
						</div>
					</div>
					<div class="col col-xs-12 col-sm-6">
						<div class="form-group">
							<?php echo $form->getLabel('access'); ?><br />
							<?php echo $form->getInput('access'); ?>
						</div>
					</div>
				</div>

				<!-- <div class="form-group">
					<?php echo $form->getLabel('ordering'); ?><br />
					<?php echo $form->getInput('ordering'); ?>
				</div> -->

				<?php /*if ($row->extension_id) : ?>
					<div class="form-group">
						<?php echo $form->getLabel('id'); ?><br />
						<?php echo $form->getInput('id'); ?>
					</div>
				<?php endif;*/ ?>
			</fieldset>
		</div>
		<div class="col col-xs-12 col-sm-5">
			<?php
			$fieldSets = $form->getFieldsets('params');

			if (count($fieldSets)):
				?>
				@sliders('start', 'module-sliders')
				<?php
				foreach ($fieldSets as $name => $fieldSet) :
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'listeners::listeners.' . $name . ' fieldset';
					echo app('html.builder')->sliders('panel', trans($label), $name . '-options');
						if (isset($fieldSet->description) && trim($fieldSet->description)) :
							echo '<p class="tip">' . trans($fieldSet->description) . '</p>';
						endif;
						?>
					<fieldset class="panelform">
						<?php $hidden_fields = ''; ?>

						<?php foreach ($form->getFieldset($name) as $field) : ?>
							<?php if (!$field->hidden) : ?>
								<div class="form-group">
									<?php echo $field->label; ?>
									<?php echo $field->input; ?>
								</div>
							<?php else : $hidden_fields .= $field->input; ?>
							<?php endif; ?>
						<?php endforeach; ?>

						<?php echo $hidden_fields; ?>
					</fieldset>
				<?php endforeach; ?>
				@sliders('end')
			<?php
			else:
				?>
				<p class="alert alert-info">{{ trans('listeners::listeners.no params') }}</p>
				<?php
			endif;
			?>
		</div>
	</div>

	@csrf
</form>
@stop