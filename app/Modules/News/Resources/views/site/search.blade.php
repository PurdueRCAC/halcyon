@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css') }}" />
@endpush

@push('scripts')
<!-- <script src="{{ asset('modules/news/js/site.js') }}"></script> -->
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => 'search'])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div id="app">
		<example-component></example-component>
	</div>
	<script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
</div>
@stop