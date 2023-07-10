@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/publications/js/publications.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('publications::publications.module name'),
		route('admin.publications.index')
	)
	->append(
		trans('publications::publications.items'),
		route('admin.publications.items')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit publications'))
		{!! Toolbar::save(route('admin.publications.items.store')) !!}
	@endif
	{!! Toolbar::cancel(route('admin.publications.items.cancel')) !!}
	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('publications::publications.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.publications.items.store') }}" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
					<?php echo $form->getLabel('type'); ?>
					<?php echo $form->getInput('type'); ?>
					{!! $errors->first('type', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<div class="form-group menutype-dependant menutype-url menutype-module{{ $errors->has('title') ? ' has-error' : '' }}">
					<?php echo $form->getLabel('title'); ?>
					<?php echo $form->getInput('title'); ?>
					{!! $errors->first('title', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<?php if ($row->type == 'url'): ?>
					<?php $form->setFieldAttribute('link', 'readonly', 'false');?>
				<?php endif; ?>
				<div class="form-group menutype-dependant menutype-url{{ $errors->has('link') ? ' has-error' : '' }}">
					<?php echo $form->getLabel('link'); ?>
					<!-- <div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
							<div class="input-group-text">{{ url('/') }}</div>
						</div> -->
						<?php echo $form->getInput('link'); ?>
					<!-- </div> -->
					{!! $errors->first('link', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<div class="form-group menutype-dependant menutype-module">
					<?php echo $form->getLabel('page_id'); ?>
					<?php echo $form->getInput('page_id'); ?>
				</div>

				<?php /*if ($row->type == 'alias'): ?>
					<div class="form-group">
						<?php echo $form->getLabel('aliastip'); ?>
					</div>
				<?php endif; ?>

				<?php if ($row->type != 'url'): ?>
					<div class="form-group">
						<?php echo $form->getLabel('alias'); ?>
						<?php echo $form->getInput('alias'); ?>
					</div>
				<?php endif;*/ ?>

				<div class="form-group">
					<?php echo $form->getLabel('note'); ?>
					<?php echo $form->getInput('note'); ?>
				</div>

				<?php /*if ($row->type != 'url'): ?>
					<div class="form-group">
						<?php echo $form->getLabel('link'); ?>
						<?php echo $form->getInput('link'); ?>
					</div>
				<?php endif*/ ?>

				<div class="form-group">
					<?php echo $form->getLabel('menutype'); ?>
					<?php echo $form->getInput('menutype'); ?>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('parent_id'); ?>
					<?php echo $form->getInput('parent_id'); ?>
				</div>

				<?php /*<div class="form-group">
					<?php echo $form->getLabel('menuordering'); ?>
					<?php echo $form->getInput('menuordering'); ?>
				</div>*/ ?>

				<div class="row menutype-dependant menutype-url menutype-module">
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('target'); ?>
							<?php echo $form->getInput('target'); ?>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('class') ? ' has-error' : '' }}">
							<?php echo $form->getLabel('class'); ?>
							<?php echo $form->getInput('class'); ?>
							{!! $errors->first('class', '<span class="form-text text-danger">:message</span>') !!}
						</div>
					</div>
				</div>

				<?php /*if ($row->type == 'module') : ?>
					<div class="form-group">
						<?php echo $form->getLabel('home'); ?>
						<?php echo $form->getInput('home'); ?>
					</div>
				<?php endif; ?>

				<?php <div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('language'); ?>
							<?php echo $form->getInput('language'); ?>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('template_style_id'); ?>
							<?php echo $form->getInput('template_style_id'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $form->getLabel('id'); ?>
					<?php echo $form->getInput('id'); ?>
				</div>*/ ?>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('access'); ?>
							<span class="input-group input-access"><?php echo $form->getInput('access'); ?><span class="input-group-append"><span class="input-group-text icon-lock"></span></span></span>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<?php echo $form->getLabel('published'); ?>
							<span class="input-group input-state"><?php echo $form->getInput('published'); ?><span class="input-group-append"><span class="input-group-text icon-check"></span></span></span>
						</div>
					</div>
				</div>
			</fieldset>

			<?php /*@sliders('start', 'menu-sliders')
				<?php
				$fieldSets = $form->getFieldsets('request');

				foreach ($fieldSets as $name => $fieldSet) :
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'publications::publications.' . $name . ' fieldset';
					echo app('html.builder')->sliders('panel', trans($label), 'request-options');
						if (isset($fieldSet->description) && trim($fieldSet->description)) :
							echo '<p>' . trans($fieldSet->description) . '</p>';
						endif;
						?>
					<fieldset class="card-body panelform">
						<?php $hidden_fields = ''; ?>

						<?php foreach ($form->getFieldset($name) as $field) : ?>
							<?php if (!$field->hidden) : ?>
								<div class="form-group">
									<?php echo $field->label; ?><br />
									<?php echo $field->input; ?>
								</div>
							<?php else : $hidden_fields .= $field->input; ?>
							<?php endif; ?>
						<?php endforeach; ?>

						<?php echo $hidden_fields; ?>
					</fieldset>
				<?php endforeach; ?>

				<?php
				$fieldSets = $form->getFieldsets('params');

				foreach ($fieldSets as $name => $fieldSet) :
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'publications::publications.' . $name . ' fieldset';
					echo app('html.builder')->sliders('panel', trans($label), $name . '-options');
						if (isset($fieldSet->description) && trim($fieldSet->description)) :
							echo '<p>' . trans($fieldSet->description) . '</p>';
						endif;
						?>
					<fieldset class="card-body panelform">
						<?php $hidden_fields = ''; ?>

						<?php foreach ($form->getFieldset($name) as $field) : ?>
							<?php if (!$field->hidden) : ?>
								<div class="form-group">
									<?php echo $field->label; ?><br />
									<?php echo $field->input; ?>
								</div>
							<?php else : $hidden_fields .= $field->input; ?>
							<?php endif; ?>
						<?php endforeach; ?>

						<?php echo $hidden_fields; ?>
					</fieldset>
				<?php endforeach; ?>
			@sliders('end') */ ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="fields[language]" value="*" />
			<?php echo $form->getInput('module_id'); ?>
			<?php echo $form->getInput('id'); ?>

			<input type="hidden" id="fieldtype" name="fieldtype" value="" />
		</div>
	</div>

	@csrf
</form>
@stop