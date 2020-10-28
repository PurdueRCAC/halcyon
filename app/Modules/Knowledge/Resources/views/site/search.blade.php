@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/knowledge/css/knowledge.css') }}" />
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="dropdown-menu">
		<li><a href="{{ route('site.news.search') }}">Search Knowledge</a></li>
		<li><a href="{{ route('site.news.rss') }}">RSS Feeds</a></li>
		<li><div class="separator"></div></li>
		<?php foreach ($types as $type): ?>
			<li>
				<a href="{{ route('site.news.type', ['name' => $type->name]) }}">
					{{ $type->name }}
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div id="app">
		<example-component></example-component>
	</div>
	<script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
</div>
@stop