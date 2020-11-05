@php
$path .= $path ? '/' . $node->page->alias : $node->page->alias;

$node->page->variables->merge($variables);

$isActive = (count($current) == 1 && $current[0] == $node->page->alias);
$hasChildren = $node->publishedChildren();

$cls = '';
if ($hasChildren)
{
	$cls .= 'parent';
}
if ($isActive)
{
	$cls .= ' active';
}
if (!empty($current) && $current[0] == $node->page->alias)
{
	$children = $node->publishedChildren();

	if (count($children))
	{
		$cls .= ' active';
	}
}
@endphp
<li<?php if ($cls) { echo ' class="' . trim($cls) . '"'; } ?>>
	@if ($node->page->access > 1)
		<i class="fa fa-lock" aria-hidden="true"></i>
	@endif
	@if ($isActive)
		<span>{{ $node->page->headline }}</span>
	@else
		<a href="{{ route('site.knowledge.page', ['uri' => $path]) }}">{{ $node->page->headline }}</a>
	@endif
	@if (!empty($current) && $current[0] == $node->page->alias)
		@php
		array_shift($current);
		@endphp
		@if (count($children))
			@include('knowledge::site.list', ['nodes' => $children, 'path' => $path, 'current' => $current, 'variables' => $node->page->variables])
		@endif
	@endif
</li>