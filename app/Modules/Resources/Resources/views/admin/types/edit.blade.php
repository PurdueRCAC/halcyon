@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/resources/vendor/formbuilder/formbuilder.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/resources/vendor/formbuilder/vendor.js') }}"></script>
<script src="{{ timestamped_asset('modules/resources/vendor/formbuilder/formbuilder.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('input[required],textarea[required]').forEach(function (item) {
		if (item.value) {
			if (item.validity.valid) {
				item.classList.remove('is-invalid');
				item.classList.add('is-valid');
			}
		}

		item.addEventListener('blur', function(e){
			if (this.value) {
				if (this.validity.valid) {
					this.classList.remove('is-invalid');
					this.classList.add('is-valid');
				} else {
					this.classList.remove('is-valid');
					this.classList.add('is-invalid');
				}
			} else {
				this.classList.remove('is-valid');
				this.classList.add('is-invalid');
			}
		});
	});
});
</script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.types'),
		route('admin.resources.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit resources.types'))
		{!! Toolbar::save(route('admin.resources.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.resources.types.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('resources::resources.module name') }}: {{ trans('resources::assets.types') }}: <?php echo $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create'); ?>
@stop

@section('content')
<form action="{{ route('admin.resources.types.store') }}" method="post" name="adminForm" id="item-form" class="editform">
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
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('resources::assets.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" required maxlength="20" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('resources::assets.invalid.name') }}</span>
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('resources::assets.description') }}</label>
					{!! editor('fields[description]', $row->description, ['rows' => 7, 'maxlength' => 2000, 'id' => 'field-description']) !!}
				</div>
			</fieldset>
		</div>
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('resources::assets.asset options') }}</legend>

				<p class="form-text">{{ trans('resources::assets.asset options desc') }}</p>

				<div class="fb-main"></div>

				<?php
				$elements = array();

				foreach ($row->facetTypes as $field)
				{
					$element = new stdClass;
					$element->label      = (string)$field->label;
					$element->name       = (string)$field->name;
					$element->field_type = (string)$field->type;

					//$element->required = (bool)$field->required;
					$element->field_id = (int)$field->id;

					$element->field_options = new stdClass;
					$element->field_options->description = (string)$field->description;
					$element->field_options->placeholder = (string)$field->placeholder;
					$element->field_options->min = (int)$field->min;
					$element->field_options->max = (int)$field->max;
					$element->field_options->value = (string)$field->default_value;

					$options = $field->options;

					if (count($options))
					{
						$element->field_options->options = array();
						foreach ($options as $option)
						{
							$opt = new stdClass;
							$opt->field_id = (int)$option->id;
							$opt->label    = (string)$option->label;
							$opt->value    = (!is_null($option->value) && $option->value !== '' ? $option->value : $option->label);
							$opt->checked  = (bool)$option->checked;

							$element->field_options->options[] = $opt;
						}
					}

					$elements[] = $element;
				}

				$json = new stdClass;
				$json->fields = $elements;
				$json = json_encode($json);
				?>
				<input type="hidden" name="facets" id="facet-schema" value="<?php echo e($json); ?>" />
				<script type="text/javascript">
					var fb = null;

					jQuery(document).ready(function($){
						fb = new Formbuilder({
							selector: '.fb-main',
							bootstrapData: <?php echo json_encode($elements); ?>
						});

						fb.on('save', function(payload){
							$('#facet-schema').val(payload);
						});

						form = document.getElementById('item-form');
						form.addEventListener('submit', function(e){
							fb.mainView.saveForm();
						});
					});
				</script>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop