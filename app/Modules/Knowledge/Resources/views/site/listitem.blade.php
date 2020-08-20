@php
$path .= $path ? '/' . $node->alias : $node->alias;

/*if ($vars = $options->get('variables'))
{
	$node->options->merge(['variables' => $vars]);
}*/
$node->variables->merge($variables);

$isActive = (count($current) == 1 && $current[0] == $node->alias);
$hasChildren = $node->children()
					->where('state', '=', 1)
					->whereIn('access', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
					->count();
$cls = '';
if ($hasChildren)
{
	$cls .= 'parent';
}
if ($isActive)
{
	$cls .= ' active';
}
if (!empty($current) && $current[0] == $node->alias)
{
	$children = $node->children()
		->orderBy('ordering', 'asc')
		->where('state', '=', 1)
		->whereIn('access', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
		->get();

	if (count($children))
	{
		$cls .= ' active';
	}
}
@endphp
<li<?php if ($cls) { echo ' class="' . trim($cls) . '"'; } ?>>
	<!--
	@if ($hasChildren)
		@if (!empty($current) && $current[0] == $node->alias)
			<i class="fa fa-minus-square"></i>
		@else
			<i class="fa fa-plus-square"></i>
		@endif
	@else
		<i class="fa"></i>
	@endif
-->
	@if ($node->access > 1)
		<i class="fa fa-lock" aria-hidden="true"></i>
	@endif
	@if ($isActive)
		<span>{{ $node->headline }}</span>
	@else
		<a href="{{ route('site.knowledge.page', ['uri' => $path]) }}">{{ $node->headline }}</a>
	@endif
	@if (!empty($current) && $current[0] == $node->alias)
		@php
		array_shift($current);
		@endphp
		@if (count($children))
			@include('knowledge::site.list', ['nodes' => $children, 'path' => $path, 'current' => $current, 'variables' => $node->variables])
		@endif
	@endif
</li>