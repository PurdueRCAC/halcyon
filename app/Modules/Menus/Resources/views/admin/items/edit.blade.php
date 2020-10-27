@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/menus/js/menus.js?v=' . filemtime(public_path() . '/modules/menus/js/menus.js')) }}"></script>
<script>
$( document ).ready(function() {
	$('.menutype-dependant').hide();
	//$('.menu-page').fadeIn();
	$('[name="fields[type]"]')
		.on('change', function(){
			$('.menutype-dependant').hide();
			$('.menutype-'+$(this).val()).show();

			/*if ($(this).val() == 'separator') {
				if (!$('#fields_title').val()) {
					$('#fields_title').val('[ separator ]');
				}
			}*/
		})
		.each(function(i, el){
			if ($(el).prop('checked')) {
				$('.menutype-'+$(el).val()).show();
			}
		});
	$('#fields_page_id').on('change', function(e){
		if ($('#fields_title').val() == '') {
			$('#fields_title').val($(this).children("option:selected").text().replace(/\|\— /g, ''));
		}
	});
	/*$('input[type="checkbox"].flat-blue, input[type="radio"].flat-blue').iCheck({
		checkboxClass: 'icheckbox_flat-blue',
		radioClass: 'iradio_flat-blue'
	});*/

	var data = $('#menutypes');
	if (data.length) {
		menus = JSON.parse(data.html());

		$('#' + data.data('field')).on('change', function(e){
			var val = $(this).val();

			if (typeof(menus[val]) !== 'undefined') {
				$('#fields_parent_id')
					.find('option')
					.remove()
					.end();

				for (var i = 0; i < menus[val].length; i++) {
					$('#fields_parent_id').append('<option value="' + menus[val][i].value + '">' + menus[val][i].text + '</option>');
				}
			}
		});
		/*var html = '\n	<select name="' + modorders.name + '" id="' + modorders.id + '"' + modorders.attr + '>';
		var i = 0,
			key = modorders.originalPos,
			orig_key = modorders.originalPos,
			orig_val = modorders.originalOrder;
		for (x in modorders.orders) {
			if (modorders.orders[x][0] == key) {
				var selected = '';
				if ((orig_key == key && orig_val == modorders.orders[x][1])
				 || (i == 0 && orig_key != key)) {
					selected = 'selected="selected"';
				}
				html += '\n		<option value="' + modorders.orders[x][1] + '" ' + selected + '>' + modorders.orders[x][2] + '</option>';
			}
			i++;
		}
		html += '\n	</select>';

		$('#moduleorder').after(html);*/
	}
});
</script>
@stop

@php
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
{!! config('pages.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.menus.items.store') }}" method="post" name="adminForm" id="item-form" class="form-validate">
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
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'menus::menus.' . $name . ' fieldset';
					echo app('html.builder')->sliders('panel', trans($label), 'request-options');
						if (isset($fieldSet->description) && trim($fieldSet->description)) :
							echo '<p class="tip">' . trans($fieldSet->description) . '</p>';
						endif;
						?>
					<fieldset class="panelform">
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
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'menus::menus.' . $name . ' fieldset';
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
				$fieldSets = $form->getFieldsets('associations');

				foreach ($fieldSets as $name => $fieldSet) :
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'menus::menus.' . $name . ' fieldset';
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