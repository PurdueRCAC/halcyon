@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js?v=' . filemtime(public_path() . '/modules/core/vendor/handlebars/handlebars.min-v4.7.6.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/datatables.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/groups/js/motd.js?v=' . filemtime(public_path() . '/modules/groups/js/motd.js')) }}"></script>
<script src="{{ asset('modules/groups/js/admin.js?v=' . filemtime(public_path() . '/modules/groups/js/admin.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('groups::groups.module name'),
		route('admin.groups.index')
	)
	->append(
		'#' . $row->id . ' - ' . $row->name
	);
@endphp

@section('toolbar')
	{!! Toolbar::link('back', trans('groups::groups.back'), route('admin.groups.index'), false) !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('groups::groups.module name') }}: #{{ $row->id }} - {{ $row->name }}
@stop

@section('content')
	<nav class="container-fluid">
		<ul id="group-tabs" class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation">
				<a href="{{ route('admin.groups.show', ['id' => $row->id, 'section' => 'overview']) }}" id="group-overview-tab" class="nav-link<?php echo (!$active || $active == 'overview' ? ' active" aria-selected="true' : '" aria-selected="false'); ?>" role="tab" aria-controls="group-overview">
					{{ trans('groups::groups.overview') }}
				</a>
			</li>
			<li class="nav-item" role="presentation">
				<a href="{{ route('admin.groups.show', ['id' => $row->id, 'section' => 'members']) }}" id="group-members-tab" class="nav-link<?php echo ($active == 'members' ? ' active" aria-selected="true' : '" aria-selected="false'); ?>" role="tab" aria-controls="group-members">
					{{ trans('groups::groups.members') }}
				</a>
			</li>
			@foreach ($sections as $section)
				<li class="nav-item" role="presentation">
					<a href="{{ route('admin.groups.show', ['id' => $row->id, 'section' => $section['route']]) }}" id="group-{{ $section['route'] }}-tab" class="nav-link<?php echo ($active == $section['route'] ? ' active" aria-selected="true' : '" aria-selected="false'); ?>" role="tab" aria-controls="group-{{ $section['route'] }}">
						{{ $section['name'] }}
					</a>
				</li>
			@endforeach
			<li class="nav-item" role="presentation">
				<a href="{{ route('admin.groups.show', ['id' => $row->id, 'section' => 'motd']) }}" id="group-motd-tab" class="nav-link<?php echo ($active == 'motd' ? ' active" aria-selected="true' : '" aria-selected="false'); ?>" role="tab" aria-controls="group-motd">
					{{ trans('groups::groups.motd') }}
				</a>
			</li>
		</ul>
	</nav>
	<div class="tab-content" id="queue-tabs-contant">
		@if (!$active || $active == 'overview')
		<div class="tab-pane<?php echo (!$active || $active == 'overview' ? ' show active' : ''); ?>" id="group-overview" role="tabpanel" aria-labelledby="group-overview-tab">
			@include('groups::admin.groups.overview', ['row' => $row])
		</div>
		@endif

		@if ($active == 'members')
		<div class="tab-pane<?php echo ($active == 'members' ? ' show active' : ''); ?>" id="group-members" role="tabpanel" aria-labelledby="group-members-tab">
			@include('groups::admin.groups.members', ['group' => $row])
		</div>
		@endif

		@foreach ($sections as $section)
			@if ($active == $section['route'])
			<div class="tab-pane<?php echo ($active == $section['route'] ? ' show active' : ''); ?>" id="group-{{ $section['route'] }}" role="tabpanel" aria-labelledby="group-{{ $section['route'] }}-tab">
				{!! $section['content'] !!}
			</div>
			@endif
		@endforeach

		@if ($active == 'motd')
		<div class="tab-pane" id="group-motd" role="tabpanel" aria-labelledby="group-motd-tab">
			@include('groups::admin.groups.motd', ['group' => $row])
		</div>
		@endif
	</div>

	<input type="hidden" name="id" id="groupid" value="{{ $row->id }}" />
@stop