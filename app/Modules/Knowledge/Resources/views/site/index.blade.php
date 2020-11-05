@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/prism/prism.css') }}?v={{ filemtime(public_path('modules/core/vendor/prism/prism.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/knowledge/css/knowledge.css') }}?v={{ filemtime(public_path('modules/knowledge/css/knowledge.css')) }}" />
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@php
	$children = $root->publishedChildren();

	$p = implode('/', $path);
	$page = $node->page;
	@endphp
	@include('knowledge::site.list', ['nodes' => $children, 'path' => '', 'current' => $path, 'variables' => $root->page->variables])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="row">
		<div class="col-md-9">
			<form method="get" action="{{ route('site.knowledge.search') }}">
				<div class="form-group">
					<label class="sr-only" for="knowledge_search">{{ trans('knowledge::knowledge.search') }}</label>
					<input type="search" name="search" id="knowledge_search" class="form-control" placeholder="{{ trans('knowledge::knowledge.search placeholder') }}" value="" />
					<input type="hidden" name="id" value="" />
				</div>
			</form>
		</div>
		<div class="col-md-3 text-right">
			<a class="btn btn-default btn-secondary" href="<?php if ($p) { echo route('site.knowledge.page', ['uri' => $p, 'all' => 'true']); } else { echo route('site.knowledge.index', ['all' => 'true']); } ?>">{{ trans('knowledge::knowledge.expand topics') }}</a>
		</div>
	</div>

	@if ($page->params->get('show_title', 1))
		<h2>{{ $page->headline }}</h2>
	@endif

	@if ($page->content)
		{!! $page->body !!}
	@endif

	@if (!$page->content || $page->params->get('show_toc', 1) || request('all'))
		@php
		$childs = $node->publishedChildren();
		@endphp
		@if (count($childs))
			@if (request('all'))
				@foreach ($childs as $n)
					@php
						$n->page->variables->merge($page->variables);
						$pa = $p ? $p . '/' . $n->page->alias : $n->page->alias;
					@endphp
					<h3>{{ $node->headline }}</h3>

					{!! $node->body ||}
				@endforeach
			@else
				<ul class="kb-toc">
				@foreach ($childs as $n)
					@php
						$n->page->variables->merge($page->variables);
						$pa = $p ? $p . '/' . $n->page->alias : $n->page->alias;
					@endphp
					<li>
						<a href="{{ route('site.knowledge.page', ['uri' => $pa]) }}">{{ $n->page->headline }}</a>
						@if ($n->page->params->get('expandtoc'))
							@include('knowledge::site.list', ['nodes' => $n->publishedChildren(), 'path' => $pa, 'current' => $path, 'variables' => $n->page->variables])
						@endif
					</li>
				@endforeach
				</ul>
			@endif
		@endif
	@endif
</div>

@stop