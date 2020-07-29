@extends('layouts.master')

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="dropdown-menu">
		<li><a href="{{ route('site.news.search') }}">Search ContactReports</a></li>
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
	<h2>{{ $article->headline }}</h2>

	<div class="wrapper-news">
	{!! $article->body !!}
</div>

@stop