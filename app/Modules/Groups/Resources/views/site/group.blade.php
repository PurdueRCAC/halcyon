@php
$canManage = auth()->user()->can('manage groups') || ((auth()->user()->can('edit groups') || auth()->user()->can('edit.own groups')) && $group->isManager(auth()->user()));
$subsection = request()->segment(4);
$subsection = $subsection ?: 'overview';
@endphp

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css?v=' . filemtime(public_path('/modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css'))) }}" />
@if ($subsection == 'members')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/media/vendor/dropzone-5.7.0/dist/min/dropzone.min.css') . '?v=' . filemtime(public_path() . '/modules/media/vendor/dropzone-5.7.0/dist/min/dropzone.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.css')) }}" />
@endif
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js?v=' . filemtime(public_path('/modules/core/vendor/tom-select/js/tom-select.complete.min.js'))) }}"></script>
@if ($subsection == 'motd')
<script src="{{ asset('modules/groups/js/motd.js?v=' . filemtime(public_path() . '/modules/groups/js/motd.js')) }}"></script>
@endif
@if ($subsection == 'members')
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/datatables.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.js')) }}"></script>
<script src="{{ asset('modules/groups/js/userrequests.js?v=' . filemtime(public_path() . '/modules/groups/js/userrequests.js')) }}"></script>
@endif
<script src="{{ asset('modules/groups/js/site.js?v=' . filemtime(public_path() . '/modules/groups/js/site.js')) }}"></script>
@endpush

	<div class="contentInner">
		<div class="row mb-3">
			<div class="col-md-9">
				<h2>{{ $group->name }}</h2>
			</div>
			<div class="col-md-3 text-right">
				@if ($membership)
					@if ($membership->trashed())
						<span class="badge badge-danger">{{ trans('users::users.removed') }}</span>
					@elseif ($membership->membertype == 4)
						<span class="badge badge-warning">{{ $membership->type->name }}</span>
					@else
						<span class="badge {{ $membership->isManager() ? 'badge-success' : 'badge-secondary' }}">{{ $membership->type->name }}</span>
					@endif
				@endif
			</div>
		</div>

		<div id="everything">
			<ul class="nav nav-tabs mb-3">
				<li class="nav-item">
					<a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $group->id, 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}" id="group-overview" class="nav-link tab<?php if ($subsection == 'overview') { echo ' active activeTab'; } ?>">
						Overview
					</a>
				</li>
			@if ($canManage)
				@php
				$pending = $group->pendingMembersCount;
				@endphp
				<li class="nav-item">
					<a href="{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'members', 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}" id="group-members" class="nav-link tab<?php if ($subsection == 'members') { echo ' active activeTab'; } ?>">
						Members
						@if ($pending)
							<span class="badge badge-warning tip" title="Pending membership requests">{{ $pending }}</span>
						@endif
					</a>
				</li>
			@endif
			@foreach ($sections as $section)
				<li class="nav-item">
					<a href="{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => $section['route'], 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}" id="group-{{ $section['route'] }}" class="nav-link tab<?php if ($subsection == $section['route']) { echo ' active activeTab'; } ?>">{{ $section['name'] }}</a>
				</li>
			@endforeach
			@if ($canManage)
				<li class="nav-item">
					<a href="{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'motd', 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}" id="group-motd" class="nav-link tab<?php if ($subsection == 'motd') { echo ' active activeTab'; } ?>">
						Notices
					</a>
				</li>
			@endif
			</ul>

			<input type="hidden" id="groupid" value="{{ $group->id }}" />
			<input type="hidden" id="HIDDEN_property_{{ $group->id }}" value="{{ $group->id }}" />

			@if ($subsection == 'overview')
			<div id="DIV_group-overview">
				@include('groups::site.group.overview', ['group' => $group])
			</div><!-- / #group-overview -->
			@endif

			@if ($subsection == 'members' && $canManage)
			<div id="DIV_group-members">
				@include('groups::site.group.members', ['group' => $group])
			</div><!-- / #group-members -->
			@endif

			@foreach ($sections as $section)
				@if ($subsection == $section['route'])
				<div id="DIV_group-{{ $section['route'] }}">
					{{ $section['content'] }}
				</div>
				@endif
			@endforeach

			@if ($subsection == 'motd' && $canManage)
			<div id="DIV_group-motd">
				@include('groups::site.group.motd', ['group' => $group])
			</div><!-- / #group-motd -->
			@endif
		</div><!-- / #everything -->
	</div><!-- / .contentInner -->
