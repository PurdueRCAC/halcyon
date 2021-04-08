@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/fancytree/skin-xp/ui.fancytree.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/fancytree/jquery.fancytree-all.js') }}"></script>
<script src="{{ asset('modules/storage/js/admin.js?v=' . filemtime(public_path() . '/modules/storage/js/admin.js')) }}"></script>
<script>
jQuery(document).ready(function ($) {
	$('#storageresourceid')
		.on('change', function (){
			$('#storageresourceid_path').html($(this).children("option:selected").data('path'));
		});
});
</script>
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
{!! config('storage.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.storage.directories.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	<div class="tabs">
		<ul>
			<li>
				<a href="#dir-details">{{ trans('global.details') }}</a>
			</li>
			<li>
				<a href="#dir-messages">{{ trans('storage::storage.messages') }}</a>
			</li>
		</ul>

		<div id="dir-details">

	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="storageresourceid">{{ trans('storage::storage.parent') }}: <span class="required">{{ trans('global.required') }}</span></label>
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
					<label for="field-name">{{ trans('storage::storage.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					@if ($row->storageResource && $row->storageResource->path)
					<span class="input-group">
						<span class="input-group-prepend"><span class="input-group-text"><span id="storageresourceid_path">{{ $row->storageResource ? rtrim($row->storageResource->path, '/') : '' }}</span>{{ $row->parent ? '/' . trim($row->parent->path, '/') : '' }}/<span id="new_dir_path"></span></span></span>
					@endif
						<input type="text" name="fields[name]" id="field-name" class="form-control required" pattern="^([a-zA-Z0-9]+\.?[\-_ ]*)*[a-zA-Z0-9]$" required value="{{ $row->name }}" />
					@if ($row->storageResource && $row->storageResource->path)
					</span>
					@endif
					<span class="form-text text-muted">{{ trans('storage::storage.name desc') }}</span>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="groupid">{{ trans('storage::storage.group') }}:</label>
							<span class="input-group">
								<input type="text" name="fields[groupid]" id="groupid" class="form-control form-groups" data-uri="{{ url('/') }}/api/groups/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ ($row->group ? $row->group->name . ':' . $row->groupid : '') }}" />
								<span class="input-group-append"><span class="input-group-text icon-users"></span></span>
							</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-owneruserid">{{ trans('storage::storage.owner') }}:</label>
							<span class="input-group">
								<input type="text" name="fields[owneruserid]" id="field-owneruserid" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ ($row->owner ? $row->owner->name . ':' . $row->owneruserid : '') }}" />
								<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
							</span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-bytes">{{ trans('storage::storage.quota') }}:</label>
					<input type="text" name="fields[bytes]" id="field-bytes" class="form-control" value="{{ App\Halcyon\Utility\Number::formatBytes($row->bytes) }}" />
					<span class="form-text text-muted">{{ trans('storage::storage.quota desc') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('storage::storage.permissions') }}</legend>
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
		<div id="dir-messages">
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