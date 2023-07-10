@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/contactreports/css/contactreports.css') }}" />
@endpush

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="nav flex-column">
		<li class="nav-item"><a class="nav-link" href="{{ route('site.news.search') }}">Search ContactReports</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ route('site.news.rss') }}">RSS Feeds</a></li>
		<li class="nav-item"><div class="separator"></div></li>
		<?php foreach ($types as $type): ?>
			<li class="nav-item">
				<a class="nav-link" href="{{ route('site.news.type', ['name' => $type->name]) }}">
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
	<script type="text/javascript" src="{{ timestamped_asset('modules/contactreports/js/app.js') }}"></script>
</div>
</div>
@stop