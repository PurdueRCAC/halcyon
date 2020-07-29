@extends('layouts.master')

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ $article->headline }}</h2>

	<div class="wrapper-news">
		{!! $article->formattedBody !!}
	</div>
</div>

@stop