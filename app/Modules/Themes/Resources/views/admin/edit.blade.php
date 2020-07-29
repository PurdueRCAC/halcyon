@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('themes::themes.module name'),
		route('admin.themes.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@php
		if (auth()->user()->can('edit themes')):
			Toolbar::save(route('admin.themes.store'));
		endif;

		Toolbar::spacer();
		Toolbar::cancel(route('admin.themes.cancel'));
	@endphp

	{!! Toolbar::render() !!}
@stop

@section('title')
trans('themes::themes.module name'): {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.themes.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col col-xs-12 col-sm-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				@if (auth()->user()->can('edit themes'))
					<div class="form-group">
						<label for="field-name">{{ trans('themes::themes.title') }}: <span class="required">{{ trans('global.required') }}</span></label><br />
						<input type="text" name="fields[name]" id="field-name" class="form-control required" maxlength="250" value="{{ $row->name }}" />
					</div>
				@endif

				@if (auth()->user()->can('edit.state themes'))

						<div class="form-group">
							<fieldset id="fields_home" class="radio inputbox">
								<legend id="fields_home-lbl">{{ trans('themes::themes.home') }}:</legend>
								<ul>
									<li>
										<div class="form-check">
											<input class="form-check-input" type="radio" id="fields_enabled0" name="fields[enabled]" value="0" <?php if ($row->enabled == 0) { echo ' checked="checked"'; } ?> />
											<label class="form-check-label" for="fields_enabled0">{{ trans('global.no') }}</label>
										</div>
									</li>
									<li>
										<div class="form-check">
											<input class="form-check-input" type="radio" id="fields_enabled1" name="fields[enabled]" value="1" <?php if ($row->enabled == 1) { echo ' checked="checked"'; } ?> />
											<label class="form-check-label" for="fields_enabled1">{{ trans('global.yes') }}</label>
										</div>
									</li>
								</ul>
							</fieldset>
							<div class="form-text text-muted">{!! trans('themes::themes.home description') !!}</div>
						</div>

				@endif
			</fieldset>

			<?php /*if (auth()->user()->can('edit menus') && $row->client_id == 0):?>
				<?php if (auth()->user()->can('edit.state themes')) : ?>
					<fieldset class="adminform">
						<legend><?php echo trans('themes::themes.menu assignment'); ?></legend>

						<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo trans('themes::themes.menu selection'); ?></label>

						<button type="button" class="jform-rightbtn">
							<?php echo trans('global.selection invert'); ?>
						</button>
						<div class="clr"></div>

						<div id="menu-assignment">
							<?php $menuTypes = App\Modules\Menus\Helpers\Menus::getMenuLinks(); ?>
							<?php //echo App\Halcyon\Html\Builder\Tabs::start('module-menu-assignment-tabs', array('useCookie' => 1)); ?>
							<div class="tabs">
								<ul>
									<?php foreach ($menuTypes as &$type) : ?>
										<li><a href="#<?php echo $type->menutype; ?>-details"><?php echo $type->title ? $type->title : $type->menutype; ?></a></li>
									<?php endforeach; ?>
								</ul>
							<?php foreach ($menuTypes as &$type) : ?>
								<div id="<?php echo $type->menutype; ?>-details">
									<?php //echo App\Halcyon\Html\Builder\Tabs::panel($type->title ? $type->title : $type->menutype, $type->menutype.'-details'); ?>
									<h3><?php echo $type->title ? $type->title : $type->menutype; ?></h3>
									<ul class="menu-links">
										<?php foreach ($type->links as $link) :?>
											<li class="menu-link">
												<div class="form-check">
													<input type="checkbox" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php if ($link->template_style_id == $row->id):?> checked="checked"<?php endif;?><?php if ($link->checked_out && $link->checked_out != auth()->user()->id):?> disabled="disabled"<?php else:?> class="chk-menulink form-check-input"<?php endif;?> />
													<label for="link<?php echo (int) $link->value;?>" class="form-check-label">
														<?php echo $link->text; ?>
													</label>
												</div>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>
							</div>
							<?php //echo App\Halcyon\Html\Builder\Tabs::end(); ?>
						</div>
					</fieldset>
				<?php endif; ?>
			<?php endif;*/ ?>
		</div>
		<div class="col span5 col-xs-12 col-sm-5">
			@sliders('start', 'template-sliders')
				<?php
				$fieldSets = $form->getFieldsets('params');

				$k = 0;
				foreach ($fieldSets as $name => $fieldSet) :
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'themes::themes.' . $name . ' fieldset';

					echo app('html.builder')->sliders('panel', trans($label), $name . '-options');

					if (isset($fieldSet->description) && trim($fieldSet->description)) :
						echo '<p class="tip">' . trans($fieldSet->description) . '</p>';
					endif;
					$k++;
					?>
					<fieldset class="panelform">
						<?php foreach ($form->getFieldset($name) as $field) : ?>
							<div class="form-group">
								<?php if (!$field->hidden) : ?>
									<?php echo $field->label; ?>
								<?php endif; ?>
								<?php echo $field->input; ?>
							</div>
						<?php endforeach; ?>
					</fieldset>
				<?php endforeach; ?>
				<?php if (!$k) { ?>
					<p class="warning">{{ trans('No options found for this template.') }}</p>
				<?php } ?>
			@sliders('end')
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop
