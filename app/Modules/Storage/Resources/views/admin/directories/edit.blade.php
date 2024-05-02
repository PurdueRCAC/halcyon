@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/storage/js/admin.js') }}"></script>
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
		Toolbar::link('cancel', trans('global.toolbar.cancel'), route('admin.storage.directories'), false);
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('storage::storage.module name') }}: {{ trans('storage::storage.directories') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.storage.directories.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<nav class="container-fluid">
		<ul id="dir-tabs" class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation">
				<a class="nav-link active" href="#dir-details" data-toggle="tab" data-bs-toggle="tab" role="tab" id="dir-details-tab" aria-controls="dir-details" aria-selected="true">{{ trans('global.details') }}</a>
			</li>
			<li class="nav-item" role="presentation">
				<a class="nav-link" href="#dir-messages" data-toggle="tab" data-bs-toggle="tab" role="tab" id="dir-messages-tab" aria-controls="dir-messages" aria-selected="false">{{ trans('storage::storage.messages') }}</a>
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
									<select name="fields[groupid]" id="groupid" class="form-control form-groups" data-api="{{ route('api.groups.index', ['order' => 'name', 'order_by' => 'asc']) }}">
										<option value="0">{{ trans('global.none') }}</option>
										@if ($row->groupid)
										<option value="{{ $row->groupid }}" selected="selected">{{ $row->groupid && $row->group ? $row->group->name : trans('global.unknown') }}</option>
										@endif
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="field-owneruserid">{{ trans('storage::storage.owner') }}</label>
									<select name="fields[owneruserid]" id="field-owneruserid" class="form-control form-users" data-api="{{ route('api.users.index', ['order' => 'name', 'order_by' => 'asc']) }}">
										<option value="0">{{ trans('global.none') }}</option>
										@if ($row->groupid && $row->group)
											@foreach ($row->group->members()->with('user')->get() as $member)
												<option value="{{ $member->userid }}"<?php if ($row->owneruserid == $member->userid) { echo ' selected="selected"'; } ?>>{{ $member->user ? $member->user->name . ' (' . $member->user->username . ')' : trans('global.none') }}</option>
											@endforeach
										@else
											<option value="{{ $row->owneruserid }}" selected="selected">{{ $row->owneruserid && $row->owner ? $row->owner->name . ' (' . $row->owner->username . ')' : trans('global.none') }}</option>
										@endif
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="field-bytes">{{ trans('storage::storage.quota') }}</label>
							<input type="text" name="fields[bytes]" id="field-bytes" class="form-control" value="{{ $row->formattedBytes }}" />
							<span class="form-text text-muted">{{ trans('storage::storage.quota desc') }}</span>
						</div>

						@if ($row->id)
							<div class="row">
								<div class="col-md-6">
									<div class="form-group mb-0">
										{{ trans('storage::storage.created') }}
										<span class="form-text text-muted">{{ $row->datetimecreated }}</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group mb-0">
										{{ trans('storage::storage.removed') }}
										<span class="form-text text-muted">{{ $row->datetimeremoved ? $row->datetimeremoved : '-' }}</span>
									</div>
								</div>
							</div>
						@endif
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
								<option value="0" data-owner="0">{{ trans('storage::storage.permissions type.group shared') }}</option>
								<option value="1" data-read="1" data-write="0" data-owner="0"<?php if ($row->autouser == 1) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.auto user group readable') }}</option>
								<option value="3" data-read="1" data-write="1" data-owner="0"<?php if ($row->autouser == 3) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.auto user group writeable') }}</option>
								<option value="2" data-read="0" data-write="0" data-owner="0"<?php if ($row->autouser == 2) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.auto user private') }}</option>
								<option value="0" data-read="1" data-write="0" data-owner="1"<?php if ($row->owneruserid && !$row->autouser && $row->groupread && !$row->groupwrite) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.user owned readable') }}</option>
								<option value="0" data-read="1" data-write="1" data-owner="1"<?php if ($row->owneruserid && !$row->autouser && $row->groupread && $row->groupwrite) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.user owned writeable') }}</option>
								<option value="0" data-read="0" data-write="0" data-owner="1"<?php if ($row->owneruserid && !$row->autouser && !$row->groupread && !$row->groupwrite) { echo ' selected="selected"'; } ?>>{{ trans('storage::storage.permissions type.user owned private') }}</option>
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
							<caption class="sr-only visually-hidden">{{ trans('storage::storage.permissions') }}</caption>
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
											<label for="field-ownerread" class="form-check-label"><span class="sr-only visually-hidden">{{ trans('storage::storage.permission.read') }}</span></label>
										</div>
									</td>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[ownerwrite]" id="field-ownerwrite" <?php if ($row->ownerwrite) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-ownerwrite" class="form-check-label"><span class="sr-only visually-hidden">{{ trans('storage::storage.permission.write') }}</span></label>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ trans('storage::storage.permission.group') }}</th>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[groupread]" id="field-groupread" <?php if ($row->groupread) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-groupread" class="form-check-label"><span class="sr-only visually-hidden">{{ trans('storage::storage.permission.read') }}</span></label>
										</div>
									</td>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[groupwrite]" id="field-groupwrite" <?php if ($row->groupwrite) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-groupwrite" class="form-check-label"><span class="sr-only visually-hidden">{{ trans('storage::storage.permission.write') }}</span></label>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row">{{ trans('storage::storage.permission.public') }}</th>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[publicread]" id="field-publicread" <?php if ($row->publicread) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-publicread" class="form-check-label"><span class="sr-only visually-hidden">{{ trans('storage::storage.permission.read') }}</span></label>
										</div>
									</td>
									<td class="text-center">
										<div class="form-check">
											<input type="checkbox" name="fields[publicwrite]" id="field-publicwrite" <?php if ($row->publicwrite) { echo 'checked="checked"'; } ?> value="1" class="form-check-input" />
											<label for="field-publicwrite" class="form-check-label"><span class="sr-only visually-hidden">{{ trans('storage::storage.permission.write') }}</span></label>
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
			@php
			$messages = $row->messages()
				->with('type')
				->orderBy('datetimesubmitted', 'desc')
				->paginate(20, ['*'], 'page', request()->input('page', 1));
			@endphp
			<div class="card mb-4">
				<div class="table-responsive">
					<table class="table table-hover">
						<caption class="sr-only visually-hidden">{{ trans('storage::storage.messages') }}</caption>
						<thead>
							<tr>
								<th scope="col">{{ trans('storage::storage.action') }}</th>
								<th scope="col">{{ trans('storage::storage.submitted') }}</th>
								<th scope="col">{{ trans('storage::storage.completed') }}</th>
								<th scope="col">{{ trans('storage::storage.status') }}</th>
							</tr>
						</thead>
						<tbody>
						@if (count($messages))
							@foreach ($messages as $message)
								<tr>
									<td>{{ $message->type->name }}</td>
									<td>
										<time datetime="{{ $message->datetimesubmitted->toDateTimeLocalString() }}">
											@if ($message->datetimesubmitted->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
												{{ $message->datetimesubmitted->diffForHumans() }}
											@else
												{{ $message->datetimesubmitted->format('F j, Y') }}
											@endif
										</time>
									</td>
									<td>
										<?php
										$timetable  = '<div>';
										$timetable .= '<strong>' . trans('messages::messages.started') . '</strong>: ';
										if ($message->started()):
											$timetable .= '<time datetime=\'' . $message->datetimestarted->toDateTimeLocalString() . '\'>' . $message->datetimestarted . '</time>';
										else:
											$timetable .= trans('messages::messages.not started');
										endif;
										$timetable .= '<br />';
										$timetable .= '<strong>' . trans('messages::messages.completed') . '</strong>: ';
										if ($message->completed()):
											$timetable .= '<time datetime=\'' . $message->datetimecompleted->toDateTimeLocalString() . '\'>' . $message->datetimecompleted . '</time>';
										else:
											$timetable .= trans('messages::messages.not completed');
										endif;
										$timetable .= '</div>';
										?>
										@if ($message->completed())
											<span class="badge badge-success" data-tip="{!! $timetable !!}">
												<span class="fa fa-check" aria-hidden="true"></span> {{ $message->elapsed }}
											</span>
										@elseif ($message->started())
											<span class="badge badge-warning" data-tip="{!! $timetable !!}">
												<span class="fa fa-undo" aria-hidden="true"></span> {{ trans('messages::messages.processing') }}
											</span>
										@else
											<span class="badge badge-info" data-tip="{!! $timetable !!}">
												<span class="fa fa-ellipsis-h" aria-hidden="true"></span> {{ trans('messages::messages.pending') }}
											</span>
										@endif
									</td>
									<td>
										@if ($message->completed())
											@if ($message->returnstatus)
												<span class="text-danger fa fa-exclamation-circle" aria-hidden="true"></span>
											@else
												<span class="text-success fa fa-check" aria-hidden="true"></span>
											@endif
											{{ $message->returnstatus }}
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
				</div>
			</div>
			{{ $messages->render() }}
		</div><!-- / #dir-messages -->
	</div><!-- / .tabs -->

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
	<input type="hidden" name="resourceid" id="resourceid" value="{{ $row->storageResource ? $row->storageResource->parentresourceid : '' }}" />

	@csrf
</form>
@stop