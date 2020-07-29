@extends('layouts.master')

@section('title')
{!! config('dashboard.name') !!}
@stop

@section('content')

<div class="hero width-100">
	@widget('cpanelhero')
</div>
<div class="cpanel-wrap">
	<div class="cpanel col width-48 fltlft">
		@widget('icon')
	</div>
	<div class="cpanel col width-48 fltrt">
		@widget('cpanel')
	</div>
</div>

@stop