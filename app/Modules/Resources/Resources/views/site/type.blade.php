@extends('layouts.master')

@section('title'){{ $type->name }}@stop

@if ($type->metadata)
	@foreach ($type->metadata->all() as $k => $v)
		@if ($v)
			@if ($v == '__comment__')
				@push('meta')
		{!! $k !!}
@endpush
			@else
				@push('meta')
		{!! $v !!}
@endpush
			@endif
		@endif
	@endforeach
@endif

@php
app('pathway')
	->append(
		$type->name,
		($retired ? route('site.resources.type.' . $type->alias) : route('site.resources.' . $type->alias . '.show', ['name' => $type->alias]))
	);

	if ($retired):
		app('pathway')->append(
			trans('resources::resources.retired'),
			route('site.resources.' . $type->alias . '.retired')
		);
	endif;
@endphp

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="nav flex-column">
		@foreach ($items as $i => $row)
			<li class="nav-item">
				@php
				$url = route('site.resources.' . $type->alias . '.show', ['name' => ($row->listname ? $row->listname : $row->rolename)]);
				if ($facet = $row->getFacet('url')):
					$url = $facet->value;
				endif;
				@endphp
				<a class="nav-link" href="{{ $url }}">{{ $row->name }}</a>
			</li>
		@endforeach
		<li class="nav-item"><div class="separator"></div></li>
		<li class="nav-item<?php if ($retired) { echo ' active'; } ?>">
			<a class="nav-link<?php if ($retired) { echo ' active'; } ?>" href="{{ route('site.resources.' . $type->alias . '.retired') }}">{{ trans('resources::resources.retired') }}</a>
		</li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ $type->name }}</h2>
	@if ($type->description)
		{!! $type->description !!}
	@endif

	<div class="row resources">
		@foreach ($rows as $i => $row)
			<div class="col-md-12">
				<div class="card mb-3">
					@if ($url = $row->getFacet('url'))
					<a href="{{ $url->value }}" class="card-content">
					@else
					<a href="{{ route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]) }}" class="card-content">
					@endif
						<div class="card-header">
							@if ($thumb = $row->thumb)
								<img src="{{ $thumb }}" class="card-img" width="80" alt="{{ trans('resources::assets.asset thumbnail', ['asset' => $row->name]) }}" />
							@else
								<img src="{{ asset('/modules/resources/images/resource.png') }}" class="card-img" width="80" alt="{{ trans('resources::assets.asset thumbnail', ['asset' => $row->name]) }}" />
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
</div>
@stop