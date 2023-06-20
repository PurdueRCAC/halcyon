@php
$value = $field->default_value;
$facet = $row->facets->where('facet_type_id', '=', $field->id)->first();
if ($facet):
	$value = $facet->value;
endif;

$options = \App\Halcyon\Access\Role::query()
	->select(['a.id', 'a.title', 'a.parent_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT b.id) AS level')])
	->from($none->getTable() . ' AS a')
	->leftJoin($none->getTable() . ' AS b', function ($join)
		{
			$join->on('a.lft', '>', 'b.lft')
				->on('a.rgt', '<', 'b.rgt');
		})
	->groupBy(['a.id', 'a.title', 'a.lft', 'a.rgt', 'a.parent_id'])
	->orderBy('a.lft', 'asc')
	->get();

$options->each(function($item)
{
	$item->value = $item->id;
	$item->label = str_repeat('|&mdash;', $item->level) . $item->title;
});
@endphp
<div class="form-group">
	<label for="facet-{{ $field->name }}">{{ $field->label }}<?php if ($field->required) { ?> <span class="required">{{ trans('global.required') }}</span><?php } ?></label>
	<select class="form-control" name="facets[{{ $field->type_id }}][{{ $field->name }}]" id="facet-{{ $field->name }}">
		@foreach ($options as $option)
			<option value="{{ $option->value }}"<?php if ($option->value == $value) { echo ' selected="selected"'; } ?>>{{ $option->label }}</option>
		@endforeach
	</select>
	@if ($field->description)
		<span class="form-text text-muted">{{ $field->description }}</span>
	@endif
</div>
