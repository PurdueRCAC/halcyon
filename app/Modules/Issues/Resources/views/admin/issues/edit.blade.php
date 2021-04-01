@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/issues/js/admin.js?v=' . filemtime(public_path() . '/modules/issues/js/admin.js')) }}"></script>
@stop

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('issues::issues.module name'),
		route('admin.issues.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);

if (auth()->user()->can('edit issues')):
	Toolbar::save(route('admin.issues.store'));
endif;
Toolbar::spacer();
Toolbar::cancel(route('admin.issues.cancel'));

@endphp

@section('toolbar')
	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('issues.name') !!}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.issues.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
					<label for="field-datetimecreated">{{ trans('issues::issues.created') }}: <span class="required">{{ trans('global.required') }}</span></label>
					{!! Html::input('calendar', 'fields[datetimecreated]', $row->datetimecreated ? $row->datetimecreated->format('Y-m-d') : '', ['required' => true, 'time' => false]) !!}
					<span class="invalid-feedback">{{ trans('issues::issues.invalid.contacted') }}</span>
				</div>

				<div class="form-group">
					<?php
					$resources = array();
					foreach ($row->resources as $resource)
					{
						$resources[] = ($resource->resource ? $resource->resource->name : trans('global.unknown')) . ':' . $resource->resourceid;
					}
					?>
					<label for="field-resources">{{ trans('issues::issues.resources') }}:</label>
					<select class="form-control basic-multiple" name="resources[]" multiple="multiple" data-placeholder="">
						<?php
						$r = $row->resources->pluck('resourceid')->toArray();
						$resources = App\Modules\Resources\Models\Asset::orderBy('name', 'asc')->get();
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
					<label for="field-report">{{ trans('issues::issues.report') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<textarea name="fields[report]" id="field-report" class="form-control" required rows="15" cols="40">{{ $row->report }}</textarea>
					<span class="invalid-feedback">{{ trans('issues::issues.invalid.report') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<div class="help">
				<p>{{ trans('issues::issues.formatting help') }}</p>
			</div>

			@if ($row->id)
				<fieldset class="adminform">
					<legend>{{ trans('issues::issues.comments') }}</legend>

					<ul id="comments">
					<?php
					$comments = $row->comments()->whereIsActive()->orderBy('datetimecreated', 'asc')->get();

					if (count($comments) > 0) {
					?>
						@foreach ($comments as $comment)
						<li id="comment_{{ $comment->id }}" data-api="{{ route('api.issues.comments.update', ['comment' => $comment->id]) }}">
							<a href="#comment_{{ $comment->id }}_comment" class="btn btn-secondary comment-edit hide-when-editing">
								<span class="icon-edit"><span class="sr-only">{{ trans('global.button.edit') }}</span></span>
							</a>
							<a href="#comment_{{ $comment->id }}" class="btn btn-danger comment-delete" data-confirm="{{ trans('global.confirm delete') }}">
								<span class="icon-trash"><span class="sr-only">{{ trans('global.button.delete') }}</span></span>
							</a>
							<div id="comment_{{ $comment->id }}_text" class="hide-when-editing">
								{!! $comment->formattedComment !!}
							</div>
							<div id="comment_{{ $comment->id }}_edit" class="show-when-editing">
								<div class="form-group">
									<label for="comment_{{ $comment->id }}_comment" class="sr-only">{{ trans('issues::issues.comment') }}</label>
									<textarea name="comment" id="comment_{{ $comment->id }}_comment" class="form-control" cols="45" rows="3">{{ $comment->comment }}</textarea>
								</div>
								<div class="form-group text-right">
									<button class="btn btn-secondary comment-save" data-parent="#comment_{{ $comment->id }}">{{ trans('global.button.save') }}</button>
									<a href="#comment_{{ $comment->id }}" class="btn btn-link comment-cancel">
										{{ trans('global.button.cancel') }}
									</a>
								</div>
							</div>
							<p>{{ trans('issues::issues.posted by', ['who' => ($comment->creator ? $comment->creator->name : trans('global.unknown')), 'when' => $comment->datetimecreated->toDateTimeString()]) }}</p>
						</li>
						@endforeach
					<?php
					}
					?>
						<li id="comment_<?php echo '{id}'; ?>" class="d-none" data-api="{{ route('api.issues.comments') }}/<?php echo '{id}'; ?>">
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
									<label for="comment_<?php echo '{id}'; ?>_comment" class="sr-only">{{ trans('issues::issues.comment') }}</label>
									<textarea name="comment" id="comment_<?php echo '{id}'; ?>_comment" class="form-control" cols="45" rows="3"></textarea>
								</div>
								<div class="form-group text-right">
									<button class="btn btn-secondary comment-save" data-parent="#comment_<?php echo '{id}'; ?>">{{ trans('global.button.save') }}</button>
									<a href="#comment_<?php echo '{id}'; ?>" class="btn btn-link comment-cancel">
										{{ trans('global.button.cancel') }}
									</a>
								</div>
							</div>
							<p>{{ trans('issues::issues.posted by', ['who' => '{who}', 'when' => '{when}']) }}</p>
						</li>
						<li id="comment_new" data-api="{{ route('api.issues.comments.create') }}">
							<div class="form-group">
								<label for="comment_new_comment" class="sr-only">{{ trans('issues::issues.comment') }}</label>
								<textarea name="comment" id="comment_new_comment" class="form-control" cols="45" rows="3"></textarea>
							</div>
							<div class="form-group text-right">
								<button class="btn btn-secondary comment-add" data-parent="#comment_new">{{ trans('issues::issues.add') }}</button>
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