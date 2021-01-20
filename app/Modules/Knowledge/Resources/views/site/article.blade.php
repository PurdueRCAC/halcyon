@php
$path .= $path ? '/' . $node->page->alias : $node->page->alias;

$node->page->variables->merge($variables);

$children = $node->publishedChildren();
$hasChildren = count($children);
@endphp

<section class="all-section" id="{{ str_replace('/', '_', $path) }}">
	@if ($node->page->params->get('show_title', 1))
		<h3>{{ $node->page->headline }}</h3>
	@endif

	{!! $node->page->body !!}
</section>
@include('knowledge::site.articles', ['nodes' => $node->publishedChildren(), 'path' => $path, 'variables' => $node->page->variables])