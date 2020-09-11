@extends('layouts.master')

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="dropdown-menu">
		@foreach ($items as $i => $row)
			<li>
				<a href="{{ route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]) }}">{{ $row->name }}</a>
			</li>
		@endforeach
		<li><div class="separator"></div></li>
		<li<?php if ($retired) { echo ' class="active"'; } ?>><a href="{{ route('site.resources.' . $type->alias . '.retired') }}">{{ trans('resources::resources.retired') }}</a></li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ trans('resources::resources.type resources', ['type' => $type->name]) }}</h2>
	@if ($type->description)
		<p>{{ $type->description }}</p>
	@endif

	<div class="row resources">
		@foreach ($rows as $i => $row)
			<div class="col-md-12 card">
				<a href="{{ route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]) }}" class="card-content mb-3">
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
		@endforeach
	</div>
</div>
@stop