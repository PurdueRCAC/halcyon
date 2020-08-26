@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/config/js/config.js') }}"></script>
@stop

@section('title')
{{ trans($module->element . '::system.name') . ': ' . trans('config::config.module configuration') }}
@stop

@section('content')
<form action="{{ route('admin.config.module.update', ['module' => $module->element]) }}" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">
	<fieldset>
		<div class="configuration">
			<div class="configuration-options">
				<button class="btn btn-secondary" type="submit" id="btn-save">{{ trans('global.button.save') }}</button>
				<button class="btn btn-outline-secondary" type="button" id="btn-cancel"<?php echo request('refresh', 0) ? ' data-refresh="1"' : ''; ?>>{{ trans('global.button.cancel') }}</button>
			</div>
		</div>
	</fieldset>

	<?php
	echo Html::tabs('start', 'config-tabs-' . $module->element . '_configuration', array('useCookie' => 1));

		if ($form) :
			$fieldSets = $form->getFieldsets();

			?>
			<ul class="config-option-list">
				<?php foreach ($fieldSets as $name => $fieldSet) :
					$label = empty($fieldSet->label) ? 'config::modules.' . $name . ' fieldset label' : $fieldSet->label;
					?>
					<li><a href="#tab-{{ $name }}">{{ trans($label) }}</a></li>
				<?php endforeach; ?>
			</ul>
			<?php
			foreach ($fieldSets as $name => $fieldSet) :
				//$label = empty($fieldSet->label) ? 'config::modules.' . $name . ' fieldset label' : $fieldSet->label;

				//echo Html::tabs('panel', trans($label), 'publishing-details');
				echo '<div id="tab-' . $name . '"><fieldset>';
				if (isset($fieldSet->description) && !empty($fieldSet->description)) :
					echo '<p class="tab-description">' . trans($fieldSet->description) . '</p>';
				endif;
				?>

					<?php foreach ($form->getFieldset($name) as $field): ?>
						<div class="form-group">
							<?php if (!$field->hidden) : ?>
								<?php echo $field->label; ?>
							<?php endif; ?>
							<?php echo $field->input; ?>
						</div>
					<?php endforeach; ?>

				<?php
				echo '</div>';
			endforeach;
		else :
			echo '<p class="warning">' . trans('config::modules.not found', ['module' => $module->element]) . '</p>';
		endif;

	echo Html::tabs('end');
	?>

	<input type="hidden" name="id" value="{{ $module->id }}" />
	<input type="hidden" name="module" value="{{ $module->element }}" />

	@csrf
</form>
@stop