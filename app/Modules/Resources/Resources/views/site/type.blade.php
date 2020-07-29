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
	<h2>{{ trans('resources::resources.type resources', ['type' => $type->name]) }}</h2>

	<ul class="resource-list">
		@foreach ($rows as $i => $row)
			<li class="resource_list">
			<!-- <div class="card panel panel-default">
				<div class="card-heading panel-heading"> -->
					<h3>
						<a href="{{ route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]) }}">
							{{ $row->name }}
							@if (is_file(storage_path('app/public/resources/' . $row->listname . '/thumb.png')))
								<img src="{{ url(Storage::url('resources/' . $row->listname . '/thumb.png')) }}" alt="{{ $row->name }} thumbnail" />
							@endif
						</a>
					</h3>
				<!-- </div>
				<div class="card-body panel-body">
					<?php
					//$sub = $row->subresources()->where('cluster', '=', '')->orWhereNull('cluster')->limit(1)->first();
					//echo $sub->description;
					?>
				</div>
			</div> -->
			</li>
		@endforeach
	</ul>
</div>
@stop