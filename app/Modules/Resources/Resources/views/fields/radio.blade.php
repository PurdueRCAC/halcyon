@php
$value = $row->facets->where('facet_type_id', '=', $field->id)
	->get()
	->pluck('value')
	->toArray();

if (empty($value) && $field->default_value):
	$value[] = $field->default_value;
endif;
@endphp
<fieldset>
	<legend>{{ $field->label }}<?php if ($field->required) { ?> <span class="required">{{ trans('global.required') }}</span><?php } ?></legend>

	@if ($field->description)
		<p class="form-text text-muted">{{ $field->description }}</p>
	@endif

	@foreach ($field->options as $i => $option)
		<div class="form-check">
			<input type="radio" class="form-check-input" name="facets[{{ $field->type_id }}][{{ $field->name }}]" id="facet-{{ $field->name }}-{{ $i }}"<?php if (in_array($option->value, $value)) { echo ' selected="selected"'; } ?> value="{{ $option->value }}" />
			<label for="facet-{{ $field->name }}-{{ $i }}" class="form-check-label">{{ $option->label }}</label>
		</div>
	@endforeach
</fieldset>
