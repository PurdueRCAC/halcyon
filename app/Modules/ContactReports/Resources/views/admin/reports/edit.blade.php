@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@stop

@section('scripts')
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
<form action="{{ route('admin.contactreports.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-datetimecontact">{{ trans('contactreports::contactreports.contacted') }}: <span class="required">{{ trans('global.required') }}</span></label>
					{!! Html::input('calendar', 'fields[datetimecontact]', $row->datetimecontact ? $row->datetimecontact->format('Y-m-d') : '', ['required' => true, 'time' => false]) !!}
					<span class="invalid-feedback">{{ trans('contactreports::contactreports.invalid.contacted') }}</span>
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
					<select class="form-control basic-multiple" name="resources[]" multiple="multiple" data-placeholder="">
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
					<textarea name="fields[report]" id="field-report" class="form-control" required rows="15" cols="40">{{ $row->report }}</textarea>
					<span class="invalid-feedback">{{ trans('contactreports::contactreports.invalid.report') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<div class="help">
				<p>{{ trans('contactreports::contactreports.formatting help') }}</p>
			</div>

			@if ($row->id)
				<fieldset class="adminform">
					<legend>{{ trans('contactreports::contactreports.comments') }}</legend>

					<ul id="comments">
					<?php
					$comments = $row->comments()->orderBy('datetimecreated', 'asc')->get();

					if (count($comments) > 0) {
					?>
						@foreach ($comments as $comment)
						<li id="comment_{{ $comment->id }}" data-api="{{ route('api.contactreports.comments.update', ['comment' => $comment->id]) }}">
							<a href="#comment_{{ $comment->id }}_comment" class="btn btn-link comment-edit hide-when-editing">
								<span class="icon-edit"><span class="sr-only">{{ trans('global.button.edit') }}</span></span>
							</a>
							<a href="#comment_{{ $comment->id }}" class="btn btn-link comment-delete" data-confirm="{{ trans('global.confirm delete') }}">
								<span class="icon-trash"><span class="sr-only">{{ trans('global.button.delete') }}</span></span>
							</a>
							<div id="comment_{{ $comment->id }}_text">
								{!! $comment->formattedComment !!}
							</div>
							<div id="comment_{{ $comment->id }}_edit" class="show-when-editing">
								<div class="form-group">
									<label for="comment_{{ $comment->id }}_comment" class="sr-only">{{ trans('contactreports::contactreports.comment') }}</label>
									<textarea name="comment" id="comment_{{ $comment->id }}_comment" class="form-control" cols="45" rows="3">{{ $comment->comment }}</textarea>
								</div>
								<div class="form-group text-right">
									<button class="btn btn-secondary comment-save" data-parent="#comment_{{ $comment->id }}">{{ trans('global.button.save') }}</button>
									<a href="#comment_{{ $comment->id }}" class="btn btn-link comment-cancel">
										{{ trans('global.button.cancel') }}
									</a>
								</div>
							</div>
							<p>{{ trans('contactreports::contactreports.posted by', ['who' => ($comment->creator ? $comment->creator->name : trans('global.unknown')), 'when' => $comment->datetimecreated->toDateTimeString()]) }}</p>
						</li>
						@endforeach
					<?php
					}
					?>
						<li id="comment_<?php echo '{id}'; ?>" class="d-none" data-api="{{ route('api.contactreports.comments') }}/<?php echo '{id}'; ?>">
							<a href="#comment_<?php echo '{id}'; ?>_comment" class="btn btn-link comment-edit hide-when-editing">
								<span class="icon-edit"><span class="sr-only">{{ trans('global.button.edit') }}</span></span>
							</a>
							<a href="#comment_<?php echo '{id}'; ?>" class="btn btn-link comment-delete" data-confirm="{{ trans('global.confirm delete') }}">
								<span class="icon-trash"><span class="sr-only">{{ trans('global.button.delete') }}</span></span>
							</a>
							<div id="comment_<?php echo '{id}'; ?>_text">
							</div>
							<div id="comment_<?php echo '{id}'; ?>_edit" class="show-when-editing">
								<div class="form-group">
									<label for="comment_<?php echo '{id}'; ?>_comment" class="sr-only">{{ trans('contactreports::contactreports.comment') }}</label>
									<textarea name="comment" id="comment_<?php echo '{id}'; ?>_comment" class="form-control" cols="45" rows="3"></textarea>
								</div>
								<div class="form-group text-right">
									<button class="btn btn-secondary comment-save" data-parent="#comment_<?php echo '{id}'; ?>">{{ trans('global.button.save') }}</button>
									<a href="#comment_<?php echo '{id}'; ?>" class="btn btn-link comment-cancel">
										{{ trans('global.button.cancel') }}
									</a>
								</div>
							</div>
							<p>{{ trans('contactreports::contactreports.posted by', ['who' => '{who}', 'when' => '{when}']) }}</p>
						</li>
						<li id="comment_new" data-api="{{ route('api.contactreports.comments.create') }}">
							<div class="form-group">
								<label for="comment_new_comment" class="sr-only">{{ trans('contactreports::contactreports.comment') }}</label>
								<textarea name="comment" id="comment_new_comment" class="form-control" cols="45" rows="3"></textarea>
							</div>
							<div class="form-group text-right">
								<button class="btn btn-secondary comment-add" data-parent="#comment_new">{{ trans('contactreports::contactreports.add') }}</button>
							</div>
						</li>
					</ul>
				</fieldset>
			@endif
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop