@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/contactreports/js/admin.js?v=' . filemtime(public_path() . '/modules/contactreports/js/admin.js')) }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('contactreports::contactreports.module name'),
		route('admin.contactreports.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit contactreports'))
		{!! Toolbar::save(route('admin.contactreports.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.contactreports.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('contactreports.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.contactreports.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-datetimecontact">{{ trans('contactreports::contactreports.contacted') }}:</label>
					{!! Html::input('calendar', 'fields[datetimecontact]', $row->datetimecontact) !!}
				</div>

				<div class="form-group">
					<?php
					$resources = array();
					foreach ($row->resources as $resource)
					{
						$resources[] = ($resource->resource ? $resource->resource->name : trans('global.unknown')) . ':' . $resource->resourceid;
					}
					?>
					<label for="field-resources">{{ trans('contactreports::contactreports.resources') }}:</label>
					<!-- <input type="text" name="resources" id="field-resources" class="form-control form-resources" data-uri="{{ url('/') }}/api/resources/?api_token={{ auth()->user()->api_token }}&search=%s" size="30" maxlength="250" value="{{ implode(',', $resources) }}" />-->
					<select class="form-control basic-multiple" name="resources[]" multiple="multiple" data-placeholder="Select resource...">
						<?php
						$r = $row->resources->pluck('resourceid')->toArray();
						$resources = App\Modules\Resources\Entities\Asset::orderBy('name', 'asc')->get();
						foreach ($resources as $resource)
						{
							?>
							<option value="{{ $resource->id }}"<?php if (in_array($resource->id, $r)) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
							<?php
						}
						?>
					</select>
				</div>

				<div class="form-group">
					<?php
					$users = array();
					foreach ($row->users as $user)
					{
						$users[] = ($user->user ? $user->user->name : trans('global.unknown')) . ':' . $user->userid;
					}
					?>
					<label for="field-people">{{ trans('contactreports::contactreports.users') }}:</label>
					<input type="text" name="people" id="field-people" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" size="30" maxlength="250" value="{{ implode(',', $users) }}" />
				</div>

				<div class="form-group">
					<label for="field-groupid">{{ trans('contactreports::contactreports.group') }}:</label>
					<select name="fields[groupid]" id="field-groupid" class="form-control searchable-select">
						<option value="0"<?php if (!$row->groupid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						@foreach ($groups as $group)
							<option value="{{ $group->id }}"<?php if ($row->groupid == $group->id) { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<label for="field-report">{{ trans('contactreports::contactreports.report') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<textarea name="fields[report]" id="field-report" class="form-control" rows="15" cols="40">{{ $row->report }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			<table class="meta">
				<caption class="sr-only">Metadata</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('contactreports::contactreports.created') }}:</th>
						<td>
							<?php if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00'): ?>
								<?php echo e($row->datetimecreated); ?>
							<?php else: ?>
								<?php echo trans('global.unknown'); ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php if ($row->getOriginal('datetimeremoved') && $row->getOriginal('datetimeremoved') != '0000-00-00 00:00:00'): ?>
						<tr>
							<th scope="row">{{ trans('contactreports::contactreports.removed') }}:</th>
							<td>
								{{ $row->datetimeremoved }}
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	@if ($row->id)
		<div class="row">
			<div class="col col-md-7">
				<fieldset class="adminform">
					<legend>{{ trans('contactreports::contactreports.comments') }}</legend>
					<?php
					$comments = $row->comments()->orderBy('datetimecreated', 'asc')->get();

					if (count($comments) > 0) {
					?>
					<ul>
						<?php foreach ($comments as $comment) { ?>
						<li>
							{!! $comment->formattedComment() !!}
							<p>Posted by {{ $comment->creator ? $comment->creator->name : trans('global.unknown') }} on {{ $comment->datetimecreated->toDateTimeString() }}</p>
						</li>
						<?php } ?>
					</ul>
					<?php
					}
					else
					{
						?>
						<p>No comments found.</p>
						<ul>
							<li>
								<div class="form-group">
									<label for="comment">Comment</label>
									<textarea name="comment" id="comment" class="form-control" cols="45" rows="1"></textarea>
								</div>
							</li>
						</ul>
						<?php
					}
					?>
				</fieldset>
			</div>
		</div>
	@endif

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop