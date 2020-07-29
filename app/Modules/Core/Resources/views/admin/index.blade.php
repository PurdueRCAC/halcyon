@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/config/js/config.js') }}"></script>
@stop

@section('title')
{!! config('config.name') !!}
@stop

@section('content')
<form action="{{ route('admin.config') }}" method="post" name="adminForm" id="adminForm">

	Global config

	@csrf
</form>

@stop