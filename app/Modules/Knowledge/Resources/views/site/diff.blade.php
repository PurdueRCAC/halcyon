@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/prism/prism.css') }}?v={{ filemtime(public_path('modules/core/vendor/prism/prism.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/knowledge/css/knowledge.css') }}?v={{ filemtime(public_path('modules/knowledge/css/knowledge.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/prism/prism.js?v=' . filemtime(public_path() . '/modules/core/vendor/prism/prism.js')) }}"></script>
<script src="{{ asset('modules/knowledge/js/site.js?v=' . filemtime(public_path() . '/modules/knowledge/js/site.js')) }}"></script>
@endpush

@section('title'){{ trans('knowledge::knowledge.module name') }}: {{ trans('knowledge::knowledge.diff') }}@stop

@section('content')

<h2>{{ trans('knowledge::knowledge.diff') }}</h2>
@foreach ($results as $result)
	{!! $result !!}
@endforeach

@stop
