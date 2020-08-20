@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/prism/prism.css') }}?v={{ filemtime(public_path('modules/core/vendor/prism/prism.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/knowledge/css/knowledge.css') }}?v={{ filemtime(public_path('modules/knowledge/css/knowledge.css')) }}" />
@stop

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@php
	$children = $root->children()
				->orderBy('ordering', 'asc')
				->where('state', '=', 1)
				->whereIn('access', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
				->get();

	$p = implode('/', $path);
	@endphp
	@include('knowledge::site.list', ['nodes' => $children, 'path' => '', 'current' => $path, 'variables' => $root->variables])
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

	@if ($page->options->get('show_title', 1))
		<h2>{{ $page->headline }}</h2>
	@endif

	@if ($page->content)
		{!! $page->body !!}
	@endif

	@if (!$page->content || $page->options->get('show_toc', 1) || request('all'))
		@php
		$childs = $page->children()
			->orderBy('ordering', 'asc')
			->where('state', '=', 1)
			->whereIn('access', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
			->get();
		@endphp
		@if (count($childs))
			@if (request('all'))
				@foreach ($childs as $node)
					@php
						$node->variables->merge($page->variables);
						$pa = $p ? $p . '/' . $node->alias : $node->alias;
					@endphp
					<h3>{{ $node->headline }}</h3>

					{!! $node->body ||}
				@endforeach
			@else
				<ul class="kb-toc">
				@foreach ($childs as $node)
					@php
						$node->variables->merge($page->variables);
						$pa = $p ? $p . '/' . $node->alias : $node->alias;
					@endphp
					<li>
						<a href="{{ route('site.knowledge.page', ['uri' => $pa]) }}">{{ $node->headline }}</a>
						@if ($node->options->get('expandtoc'))
							@php
							$children = $node->children()
										->orderBy('ordering', 'asc')
										->where('state', '=', 1)
										->whereIn('access', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
										->get();

							@endphp
							@include('knowledge::site.list', ['nodes' => $children, 'path' => $pa, 'current' => $path, 'variables' => $node->variables])
						@endif
					</li>
				@endforeach
				</ul>
			@endif
		@endif
	@endif
</div>

@stop