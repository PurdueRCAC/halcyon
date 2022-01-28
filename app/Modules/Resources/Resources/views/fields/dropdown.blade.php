@php
$value = $field->default_value;
$facet = $row->facets->where('facet_type_id', '=', $field->id)->first();
if ($facet):
	$value = $facet->value;
endif;
@endphp
<div class="form-group">
	<label for="facet-{{ $field->name }}">{{ $field->label }}<?php if ($field->required) { ?> <span class="required">{{ trans('global.required') }}</span><?php } ?></label>
	<select class="form-control" name="facets[{{ $field->type_id }}][{{ $field->name }}]" id="facet-{{ $field->name }}">
		@foreach ($field->options as $option)
			<option value="{{ $option->value }}"<?php if ($option->value == $value) { echo ' selected="selected"'; } ?>>{{ $option->label }}</option>
		@endforeach
	</select>
	@if ($field->description)
		<span class="form-text text-muted">{{ $field->description }}</span>
	@endif
</div>
