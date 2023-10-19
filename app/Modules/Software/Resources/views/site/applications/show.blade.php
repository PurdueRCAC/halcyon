@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/prism/prism.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/software/css/software.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/prism/prism.js') }}"></script>
<script src="{{ timestamped_asset('modules/software/js/site.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('software::software.software'),
		route('site.software.index')
	)
	->append(
		$row->title,
		route('site.software.show', ['alias' => $row->title])
	);
@endphp

@section('title') {{ trans('software::software.software') }}: {{ $row->title }} @stop

@section('content')
<div class="pull-right">
	<a href="{{ route('site.software.index') }}" class="btn btn-secondary">Back to Catalog</a>
</div>

<h2 class="mt-0">{{ trans('software::software.software') }}: {{ $row->title }}</h2>

<div class="row">
	<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
		<form action="{{ route('site.software.index') }}" method="get">
			<fieldset class="filters mt-0">
				<div class="form-group">
					<label for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="Find by title" value="" />
						<span class="input-group-append">
							<button class="btn input-group-text" type="submit">
								{{ trans('software::software.filter') }}
							</button>
						</span>
					</span>
				</div>

				<div class="form-group">
					<label for="filter_type">{{ trans('software::software.type') }}</label>
					<ul class="na flex-column">
						@foreach ($types as $type)
							<li class="nav-ite<?php if ($row->type_id == $type->alias) { echo ' active'; } ?>">
								<a class="nav-lin<?php if ($row->type_id == $type->alias) { echo ' active'; } ?>" href="{{ route('site.software.index', ['type' => $type->alias]) }}">{{ $type->title }}</a>
							</li>
						@endforeach
					</ul>
				</div>

				<div class="form-group">
					<label for="filter_resource">{{ trans('software::software.resource') }}</label>

					<ul class="mb-0">
						@foreach ($resources as $resource)
							<li class="nav-ite">
								<a class="nav-lin" href="{{ route('site.software.index', ['resource' => $resource->id]) }}">{{ $resource->name }}</a>
							</li>
						@endforeach
					</ul>
				</div>
			</fieldset>
		</form>
	</div>
	<div class="col col-md-9">
		<h3 class="mt-0">Description</h3>

		<p>{{ $row->description }}</p>

		<h3>Available Versions</h3>

		<table class="table table-bordered">
			<caption class="sr-only">Available Versions</caption>
			<tbody>
				@foreach ($row->versionsByResource() as $resource => $versions)
					<tr>
						<th scope="row">{{ $resource }}:</th>
						<td>
							@foreach ($versions as $version)
								<span class="badge badge-secondary">{{ $version->title }}</span>
							@endforeach
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		{!! $row->content !!}
	</div>
</div>
@stop