@extends('layouts.master')

@section('title'){{ trans('resources::resources.resources') }}@stop

@php
app('pathway')
	->append(
		trans('resources::resources.resources'),
		route('site.resources.index')
	);
@endphp

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<ul class="nav flex-column">
		@foreach ($types as $i => $type)
			<li class="nav-item">
				<a class="nav-link" href="{{ route('site.resources.type.' . $type->alias) }}">{{ $type->name }}</a>
			</li>
		@endforeach
		<li class="nav-item"><div class="separator"></div></li>
		@foreach ($types as $i => $type)
			<li class="nav-item">
				<a class="nav-link" href="{{ route('site.resources.' . $type->alias . '.retired') }}">{{ $type->name }}: {{ trans('resources::resources.retired') }}</a>
			</li>
		@endforeach
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ trans('resources::resources.resources') }}</h2>

	<div class="row resources">
		@foreach ($rows as $i => $row)
			<div class="col-md-12">
				<div class="card mb-3">
					@if ($url = $row->getFacet('url'))
					<a href="{{ $url->value }}" class="card-content">
					@elseif ($row->type->id)
					<a href="{{ route('site.resources.' . $row->type->alias . '.show', ['name' => $row->listname]) }}" class="card-content">
					@else
					<div class="card-content">
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
					@if ($url = $row->getFacet('url') || $row->type->id)
					</a>
					@else
					</div>
					@endif
				</div>
			</div>
		@endforeach
	</div>

	{!! $rows->render() !!}
</div>
</div>
@stop