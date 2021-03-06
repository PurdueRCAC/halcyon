@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/fancytree/skin-xp/ui.fancytree.css?v=' . filemtime(public_path() . '/modules/core/vendor/fancytree/skin-xp/ui.fancytree.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/fancytree/jquery.fancytree-all.js?v=' . filemtime(public_path() . '/modules/core/vendor/fancytree/jquery.fancytree-all.js')) }}"></script>
<script src="{{ asset('modules/storage/js/admin.js?v=' . filemtime(public_path() . '/modules/storage/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('storage::storage.module name'),
		route('admin.storage.index')
	)
	->append(
		trans('storage::storage.directories'),
		route('admin.storage.directories')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit storage'))
		{!! Toolbar::save(route('admin.storage.directories.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.storage.directories.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('storage::storage.module name') }}: {{ trans('storage::storage.directories') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.storage.directories.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	<nav class="container-fluid">
		<ul id="dir-tabs" class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation">
				<a class="nav-link active" href="#dir-details" data-toggle="tab" role="tab" id="dir-details-tab" aria-controls="dir-details" aria-selected="true">{{ trans('global.details') }}</a>
			</li>
			<li class="nav-item" role="presentation">
				<a class="nav-link" href="#dir-messages" data-toggle="tab" role="tab" id="dir-messages-tab" aria-controls="dir-messages" aria-selected="false">{{ trans('storage::storage.messages') }}</a>
			</li>
		</ul>
	</nav>

	<div class="tab-content">
		<div class="tab-pane show active" role="tabpanel" aria-labelledby="dir-details-tab" id="dir-details">
			<div class="row">
				<div class="col-md-7">
					<fieldset class="adminform">
						<legend>{{ trans('global.details') }}</legend>

						<div class="form-group">
							<label for="storageresourceid">{{ trans('storage::storage.parent') }} <span class="required">{{ trans('global.required') }}</span></label>
							<select name="fields[storageresourceid]" id="storageresourceid" class="form-control required" required>
								<option value="0" data-path="">{{ trans('global.none') }}</option>
								<?php foreach ($storageresources as $s): ?>
									<?php $selected = ($s->id == $row->storageresourceid ? ' selected="selected"' : ''); ?>
									<option value="{{ $s->id }}" data-path="{{ rtrim($s->path, '/') }}"<?php echo $selected; ?>>{{ $s->name }}</option>
								<?php endforeach; ?>
							</select>
							<span class="invalid-feedback">{{ trans('storage::storage.error.invalid parent') }}</span>
						</div>

						<div class="form-group">
							<label for="field-name">{{ trans('storage::storage.name') }} <span class="required">{{ trans('global.required') }}</span></label>
							@if ($row->storageResource && $row->storageResource->path)
							<span class="input-group">
								<span class="input-group-prepend"><span class="input-group-text"><span id="storageresourceid_path">{{ $row->storageResource ? rtrim($row->storageResource->path, '/') : '' }}</span>{{ $row->parent ? '/' . trim($row->parent->path, '/') : '' }}/<span id="new_dir_path"></span></span></span>
							@endif
								<input type="text" name="fields[name]" id="field-name" class="form-control required" pattern="^([a-zA-Z0-9]+\.?[\-_ ]*)*[a-zA-Z0-9]$" required maxlength="32" value="{{ $row->name }}" />
							@if ($row->storageResource && $row->storageResource->path)
							</span>
							@endif
							<span class="form-text text-muted">{{ trans('storage::storage.name desc') }}</span>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="groupid">{{ trans('storage::storage.group') }}</label>
									<span class="input-group">
										<input type="text" name="fields[groupid]" id="groupid" class="form-control form-groups" data-uri="{{ url('/') }}/api/groups/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ ($row->group ? $row->group->name . ':' . $row->groupid : '') }}" />
										<span class="input-group-append"><span class="input-group-text icon-users" aria-hidden="true"></span></span>
									</span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="field-owneruserid">{{ trans('storage::storage.owner') }}</label>
									<span class="input-group">
										<input type="text" name="fields[owneruserid]" id="field-owneruserid" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ ($row->owner ? $row->owner->name . ':' . $row->owneruserid : '') }}" />
										<span class="input-group-append"><span class="input-group-text icon-user" aria-hidden="true"></span></span>
									</span>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="field-bytes">{{ trans('storage::storage.quota') }}</label>
							<input type="text" name="fields[bytes]" id="field-bytes" class="form-control" value="{{ $row->formattedBytes }}" />
							<span class="form-text text-muted">{{ trans('storage::storage.quota desc') }}</span>
						</div>
					</fieldset>
				</div>
				<div class="col-md-5">
					<fieldset class="adminform">
						<legend>{{ trans('storage::storage.permissions') }}</legend>

						<div class="form-group">
							<label for="field-unixgroupid">{{ trans('storage::storage.access unix group') }}</label>
							<select id="field-unixgroupid" name="fields[unixgroupid]" class="form-control">
								<option value="">{{ trans('storage::storage.select unix group') }}</option>
								@if ($row->group)
									@foreach ($row->group->unixgroups as $unixgroup)
										<option value="{{ $unixgroup->id }}" <?php if ($row->unixgroupid == $unixgroup->id) { echo ' selected="selected"'; } ?> data-api="{{ route('api.unixgroups.read', ['id' => $unixgroup->id]) }}">{{ $unixgroup->longname }}</option>
									@endforeach
								@endif
							</select>
						</div>

						<div class="form-group">
							<label for="field-autouser">{{ trans('storage::storage.type') }}</label>
							<select id="field-autouser" name="fields[autouser]" data-update="#autouserunixgroupid" class="form-control">
								<option value="0">{{ trans('storage::storage.permissions type.group shared') }}</option>
								<option value="1" data-read="1" data-write="0"<?php if ($row->autouser == 1) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.auto user group readable') }}</option>
								<option value="3" data-read="1" data-write="1"<?php if ($row->autouser == 3) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.auto user group writeable') }}</option>
								<option value="2" data-read="0" data-write="0"<?php if ($row->autouser == 2) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.auto user private') }}</option>
								<option value="0" data-read="1" data-write="0"<?php if (!$row->autouser && $row->groupread && !$row->groupwrite) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.user owned readable') }}</option>
								<option value="0" data-read="1" data-write="1"<?php if (!$row->autouser && $row->groupread && $row->groupwrite) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.user owned writeable') }}</option>
								<option value="0" data-read="0" data-write="0"<?php if (!$row->autouser && !$row->groupread && !$row->groupwrite) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.user owned private') }}</option>
							</select>
							<span class="form-text text-muted">{{ trans('storage::storage.permissions type desc') }}</span>
						</div>

						<div id="autouserunixgroupid" class="form-group<?php if (!$row->autouser) { echo ' hidden'; } ?>">
							<label for="field-autouserunixgroupid">{{ trans('storage::storage.populating unix group') }}</label>
							<select id="field-autouserunixgroupid" name="fields[autouserunixgroupid]" class="form-control">
								<option value="">{{ trans('storage::storage.select unix group') }}</option>
								@if ($row->group)
									@foreach ($row->group->unixgroups as $unixgroup)
										<option value="{{ $unixgroup->id }}"<?php if ($row->autouserunixgroupid == $unixgroup->id) { echo ' selected="selected"'; } ?>>{{ $unixgroup->longname }}</option>
									@endforeach
								@endif
							</select>
						</div>

						<table class="table table-hover table-bordered mb-3">
							<caption class="sr-only">{{ trans('storage::storage.permissions') }}</caption>
							<thead>
								<tr>
									<th scope="col">{{ trans('storage::storage.permission.level') }}</th>
									<th scope="col" class="text-center">{{ trans('storage::storage.permission.read') }}</th>
									<th scope="col" class="text-center">{{ trans('storage::storage.permission.write') }}</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<th scope="row">{{ trans('storage::storage.permission.owner') }}</th>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[ownerread]" id="field-ownerread" <?php if ($row->ownerread) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-ownerread" class="form-check-label"><span class="sr-only">{{ trans('storage::storage.permission.read') }}</span></label>
										</div>
									</td>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[ownerwrite]" id="field-ownerwrite" <?php if ($row->ownerwrite) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-ownerwrite" class="form-check-label"><span class="sr-only">{{ trans('storage::storage.permission.write') }}</span></label>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ trans('storage::storage.permission.group') }}</th>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[groupread]" id="field-groupread" <?php if ($row->groupread) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-groupread" class="form-check-label"><span class="sr-only">{{ trans('storage::storage.permission.read') }}</span></label>
										</div>
									</td>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[groupwrite]" id="field-groupwrite" <?php if ($row->groupwrite) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-groupwrite" class="form-check-label"><span class="sr-only">{{ trans('storage::storage.permission.write') }}</span></label>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ trans('storage::storage.permission.public') }}</th>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[publicread]" id="field-publicread" <?php if ($row->publicread) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-publicread" class="form-check-label"><span class="sr-only">{{ trans('storage::storage.permission.read') }}</span></label>
										</div>
									</td>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[publicwrite]" id="field-publicwrite" <?php if ($row->publicwrite) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-publicwrite" class="form-check-label"><span class="sr-only">{{ trans('storage::storage.permission.write') }}</span></label>
										</div>
									</td>
								</tr>
							</tbody>
						</table>

					</fieldset>
				</div>
			</div>
		</div><!-- / #dir-details -->
		<div class="tab-pane" role="tabpanel" aria-labelledby="dir-details-tab"  id="dir-messages">
			<fieldset class="adminform">
				<legend>{{ trans('storage::storage.messages') }}</legend>

				<table class="table table-hover">
					<caption class="sr-only">{{ trans('storage::storage.messages') }}</caption>
					<thead>
						<tr>
							<th scope="col">{{ trans('storage::storage.status') }}</th>
							<th scope="col">{{ trans('storage::storage.path') }}</th>
							<th scope="col">{{ trans('storage::storage.action') }}</th>
							<th scope="col">{{ trans('storage::storage.submitted') }}</th>
							<th scope="col">{{ trans('storage::storage.completed') }}</th>
							<th scope="col">{{ trans('storage::storage.runtime') }}</th>
						</tr>
					</thead>
					<tbody>
					@if (count($row->messages))
						@foreach ($row->messages as $message)
							<tr>
								<td><span class="badge badge-{{ $message->status == 'completed' ? 'success' : 'warning' }}">{{ trans('messages::messages.' . $message->status) }}</span></td>
								<td>{{ $message->target }}</td>
								<td>{{ $message->type->name }}</td>
								<td>{{ $message->datetimesubmitted->format('Y-m-d') }}</td>
								<td>
									@if ($message->completed())
										{{ $message->datetimecompleted->format('Y-m-d') }}
									@else
										-
									@endif
								</td>
								<td>
									@if (strtotime($message->datetimesubmitted) <= date("U"))
										{{ $message->runtime }}
									@else
										-
									@endif
								</td>
							</tr>
						@endforeach
					@else
						<tr>
							<td colspan="6" class="text-center">
								<span class="none">{{ trans('global.none') }}</span>
							</td>
						</tr>
					@endif
					</tbody>
				</table>
			</fieldset>
		</div><!-- / #dir-messages -->
	</div><!-- / .tabs -->

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
	<input type="hidden" name="resourceid" id="resourceid" value="{{ $row->storageResource ? $row->storageResource->parentresourceid : '' }}" />

	@csrf
</form>
@stop