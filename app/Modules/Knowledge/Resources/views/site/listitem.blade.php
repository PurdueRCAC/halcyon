@php
$path .= $path ? '/' . $node->page->alias : $node->page->alias;

$node->page->mergeVariables($variables);

$isActive = (count($current) == 1 && $current[0] == $node->page->alias);
$children = $node->publishedChildren();
$hasChildren = count($children);

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
	if ($hasChildren)
	{
		$cls .= ' active';
	}
}
if ($node->isSeparator())
{
	$cls = 'separator';
}
@endphp
<li<?php if ($cls) { echo ' class="' . trim($cls) . '"'; } ?>>
	<div>
	@if ($node->access > 1)
		<span class="fa fa-lock" aria-hidden="true"></span>
	@endif
	@if ($node->isSeparator())
		<span class="page-title">{{ $node->page->headline }}</span>
	@else
		@if ($isActive)
			<span class="page-title">{{ $node->page->headline }}</span>
		@else
			<a class="page-title" href="{{ route('site.knowledge.page', ['uri' => $path]) }}">{{ $node->page->headline }}</a>
		@endif
	@endif
	</div>
	@if (!empty($current) && ($current[0] == $node->page->alias || $current[0] == '__all__'))
		@php
		if ($current[0] != '__all__')
		{
			array_shift($current);
		}
		@endphp
		@if (count($children))
			@include('knowledge::site.list', ['nodes' => $children, 'path' => $path, 'current' => $current, 'variables' => $node->page->variables->toArray()])
		@endif
	@endif
</li>