@php
$value = $field->default_value;
$facet = $row->facets->where('facet_type_id', '=', $field->id)->first();
if ($facet):
	$value = $facet->value;
endif;
@endphp
<div class="form-group">
	<label for="facet-{{ $field->name }}">{{ $field->label }}<?php if ($field->required) { ?> <span class="required">{{ trans('global.required') }}</span><?php } ?></label>
	<input type="text" class="form-control" name="facets[{{ $field->type_id }}][{{ $field->name }}]" id="facet-{{ $field->name }}" maxlength="255"<?php if ($field->placeholder) { echo ' placeholder="' . e($field->placeholder) . '"'; } ?> value="{{ $value }}" />
	@if ($field->description)
		<span class="form-text text-muted">{{ $field->description }}</span>
	@endif
</div>
