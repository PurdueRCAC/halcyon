@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/knowledge/css/knowledge.css') }}" />
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@php
	$children = $root->publishedChildren();
	@endphp
	@include('knowledge::site.list', ['nodes' => $children, 'path' => '', 'current' => $path, 'variables' => $root->page->variables])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="row">
		<div class="col-md-9">
			<form method="get" action="{{ route('site.knowledge.search') }}">
				<div class="form-group">
					<label class="sr-only" for="knowledge_search">{{ trans('knowledge::knowledge.search') }}</label>
					<input type="search" name="search" id="knowledge_search" class="form-control" placeholder="{{ trans('knowledge::knowledge.search placeholder') }}" value="{{ $filters['search'] }}" />
					<input type="hidden" name="parent" value="{{ $filters['parent'] }}" />
				</div>
			</form>
		</div>
		<div class="col-md-3 text-right">
		</div>
	</div>

	<ul class="article-list">
	@foreach ($rows as $row)
		<li>
			<article id="kb_{{ $row->id }}" class="article">
				<h3 class="article-title">
					<a href="{{ route('site.knowledge.page', ['uri' => $row->path]) }}">
						{{ $row->headline }}
					</a>
				</h3>
				<p class="article-metadata text-muted">
					{{ route('site.knowledge.page', ['uri' => $row->path]) }}
				</p>
				<p class="article-body">
					{!! App\Halcyon\Utility\Str::highlight(App\Halcyon\Utility\Str::excerpt(strip_tags($row->body), $filters['search']), $filters['search']) !!}
				</p>
			</article>
		</li>
	@endforeach
	</ul>

	{{ $rows->render() }}
</div>
@stop