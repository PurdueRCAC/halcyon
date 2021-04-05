@extends('layouts.master')

@section('title'){{ $type->name }}@stop

@php
app('pathway')
	->append(
		$type->name,
		route('site.resources.' . $type->alias . '.show', ['name' => $type->alias])
	);

	if ($retired):
		app('pathway')->append(
			trans('resources::resources.retired'),
			route('site.resources.' . strtolower($type->name) . '.retired')
		);
	endif;
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="dropdown-menu">
		@foreach ($items as $i => $row)
			<li>
				<a href="{{ route('site.resources.' . $type->alias . '.show', ['name' => ($row->listname ? $row->listname : $row->rolename)]) }}">{{ $row->name }}</a>
			</li>
		@endforeach
		<li><div class="separator"></div></li>
		<li<?php if ($retired) { echo ' class="active"'; } ?>><a href="{{ route('site.resources.' . $type->alias . '.retired') }}">{{ trans('resources::resources.retired') }}</a></li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ $retired ? trans('resources::resources.type retired resources', ['type' => $type->name]) : trans('resources::resources.type resources', ['type' => $type->name]) }}</h2>
	@if ($type->description)
		<p>{{ $type->description }}</p>
	@endif

	<div class="row resources">
		@foreach ($rows as $i => $row)
			<div class="col-md-12">
				<div class="card mb-3">
					<a href="{{ route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]) }}" class="card-content">
						<div class="card-header">
							@if ($thumb = $row->thumb)
								<img src="{{ $thumb }}" class="card-img" alt="{{ $row->name }} thumbnail" />
							@endif
						</div>
						<div class="card-body">
							<h3 class="card-title">
								{{ $row->name }}
							</h3>
							<p class="card-text">
								{{ $row->description }}
							</p>
						</div>
					</a>
				</div>
			</div>
		@endforeach
	</div>
</div>
@stop