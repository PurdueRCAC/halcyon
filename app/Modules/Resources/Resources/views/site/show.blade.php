@extends('layouts.master')

@section('scripts')
<script src="./js/resource.js"></script>
@stop

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="dropdown-menu">
		@foreach ($rows as $i => $row)
			<li>
				<a href="{{ route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]) }}">{{ $row->name }}</a>
			</li>
		@endforeach
		<li><div class="separator"></div></li>
		<li><a href="{{ route('site.resources.' . $type->alias . '.retired') }}">{{ trans('resources::resources.retired') }}</a></li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ $resource->name }}</h2>

	Info here
</div>
@stop