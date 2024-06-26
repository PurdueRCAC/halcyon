@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/issues/js/admin.js') }}"></script>
@endpush

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
{{ trans('issues::issues.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
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
					<label for="field-datetimecreated">{{ trans('issues::issues.created') }} <span class="required">{{ trans('global.required') }}</span></label>
					<span class="input-group input-datetime">
						<input type="date" name="fields[datetimecreated]" id="field-datetimecreated" class="form-control date" required value="{{ $row->datetimecreated->format('Y-m-d') }}" />
						<span class="input-group-append"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
					</span>
					<span class="invalid-feedback">{{ trans('issues::issues.invalid.date') }}</span>
				</div>

				<div class="form-group">
					<?php
					$resources = array();
					foreach ($row->resources as $resource):
						$resources[] = ($resource->resource ? $resource->resource->name : trans('global.unknown')) . ':' . $resource->resourceid;
					endforeach;
					?>
					<label for="field-resources">{{ trans('issues::issues.resources') }}</label>
					<select class="form-control basic-multiple" name="resources[]" multiple="multiple" data-placeholder="">
						<?php
						$r = $row->resources->pluck('resourceid')->toArray();
						$resources = App\Modules\Resources\Models\Asset::orderBy('name', 'asc')->get();
						foreach ($resources as $resource):
							?>
							<option value="{{ $resource->id }}"<?php if (in_array($resource->id, $r)) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
							<?php
						endforeach;
						?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-report">{{ trans('issues::issues.report') }} <span class="required">{{ trans('global.required') }}</span></label>
					{!! markdown_editor('fields[report]', $row->report, ['rows' => 15, 'required' => 'required']) !!}
					<span class="invalid-feedback">{{ trans('issues::issues.invalid.report') }}</span>
					<span class="form-text">{{ trans('issues::issues.formatting help') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			@if ($row->id)
				<fieldset class="adminform">
					<legend>{{ trans('issues::issues.comments') }}</legend>

					<ul id="comments" class="comments">
					<?php
					$comments = $row->comments()->orderBy('datetimecreated', 'asc')->get();

					if (count($comments) > 0):
						?>
						@foreach ($comments as $comment)
						<li id="comment_{{ $comment->id }}" data-api="{{ route('api.issues.comments.update', ['comment' => $comment->id]) }}">
							<div class="row">
								<div class="col-md-6">
									<span class="badge badge-success<?php if (!$comment->resolution) { echo ' hide'; } ?>">{{ trans('issues::issues.resolution') }}</span>
								</div>
								<div class="col-md-6 text-right text-end">
									<a href="#comment_{{ $comment->id }}_comment" class="btn comment-edit hide-when-editing">
										<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.button.edit') }}</span>
									</a>
									<a href="#comment_{{ $comment->id }}" class="btn text-danger comment-delete" data-confirm="{{ trans('global.confirm delete') }}">
										<span class="fa fa-trash" aria-hidden="true"><span class="sr-only visually-hidden">{{ trans('global.button.delete') }}</span>
									</a>
								</div>
							</div>
							<div id="comment_{{ $comment->id }}_text" class="hide-when-editing">
								{!! $comment->formattedComment !!}
							</div>
							<div id="comment_{{ $comment->id }}_edit" class="show-when-editing">
								<div class="form-group">
									<label for="comment_{{ $comment->id }}_comment" class="sr-only visually-hidden">{{ trans('issues::issues.comment') }}</label>
									<textarea name="comment" id="comment_{{ $comment->id }}_comment" class="form-control" cols="45" rows="3">{{ $comment->comment }}</textarea>
									<span class="form-text text-muted">{{ trans('issues::issues.formatting help') }}</span>
								</div>
								<div class="row">
									<div class="col-md-6">
										<div class="form-group form-check">
											<input type="checkbox" name="resolution" id="comment_{{ $comment->id }}_resolution" class="form-check-input" value="1" <?php if ($comment->resolution) { echo ' checked="checked"'; } ?> />
											<label for="comment_{{ $comment->id }}_resolution" class="form-check-label">{{ trans('issues::issues.mark as resolution') }}</label>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group text-right text-end">
											<button class="btn btn-secondary comment-save" data-parent="#comment_{{ $comment->id }}">{{ trans('global.button.save') }}</button>
											<a href="#comment_{{ $comment->id }}" class="btn btn-link comment-cancel">
												{{ trans('global.button.cancel') }}
											</a>
										</div>
									</div>
								</div>
							</div>
							<p class="text-muted">{{ trans('issues::issues.posted by', ['who' => ($comment->creator ? $comment->creator->name : trans('global.unknown')), 'when' => $comment->datetimecreated->toDateTimeString()]) }}</p>
						</li>
						@endforeach
						<?php
					endif;
					?>
						<li id="comment_<?php echo '{id}'; ?>" class="d-none" data-api="{{ route('api.issues.comments') }}/<?php echo '{id}'; ?>">
							<div class="row">
								<div class="col-md-6">
									<span class="badge badge-success hide">{{ trans('issues::issues.resolution') }}</span>
								</div>
								<div class="col-md-6 text-right text-end">
									<a href="#comment_<?php echo '{id}'; ?>_comment" class="btn btn-link comment-edit hide-when-editing">
										<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.button.edit') }}</span>
									</a>
									<a href="#comment_<?php echo '{id}'; ?>" class="btn btn-link comment-delete" data-confirm="{{ trans('global.confirm delete') }}">
										<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.button.delete') }}</span><
									</a>
								</div>
							</div>
							<div id="comment_<?php echo '{id}'; ?>_text">
							</div>
							<div id="comment_<?php echo '{id}'; ?>_edit" class="show-when-editing">
								<div class="form-group">
									<label for="comment_<?php echo '{id}'; ?>_comment" class="sr-only visually-hidden">{{ trans('issues::issues.comment') }}</label>
									<textarea name="comment" id="comment_<?php echo '{id}'; ?>_comment" class="form-control" cols="45" rows="3"></textarea>
									<span class="form-text text-muted">{{ trans('issues::issues.formatting help') }}</span>
								</div>
								<div class="row">
									<div class="col-md-6">
										<div class="form-group form-check">
											<input type="checkbox" name="resolution" id="comment_<?php echo '{id}'; ?>_resolution" class="form-check-input" value="1" />
											<label for="comment_<?php echo '{id}'; ?>_resolution" class="form-check-label">{{ trans('issues::issues.resolution') }}</label>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group text-right text-end">
											<button class="btn btn-secondary comment-save" data-parent="#comment_<?php echo '{id}'; ?>">{{ trans('global.button.save') }}</button>
											<a href="#comment_<?php echo '{id}'; ?>" class="btn btn-link comment-cancel">
												{{ trans('global.button.cancel') }}
											</a>
										</div>
									</div>
								</div>
							</div>
							<p class="text-muted">{{ trans('issues::issues.posted by', ['who' => '{who}', 'when' => '{when}']) }}</p>
						</li>
						<li id="comment_new" data-api="{{ route('api.issues.comments.create') }}">
							<div class="form-group">
								<label for="comment_new_comment" class="sr-only visually-hidden">{{ trans('issues::issues.comment') }}</label>
								<textarea name="comment" id="comment_new_comment" class="form-control" cols="45" rows="3"></textarea>
								<span class="form-text text-muted">{{ trans('issues::issues.formatting help') }}</span>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group form-check">
										<input type="checkbox" name="resolution" id="comment_new_resolution" class="form-check-input" value="1" />
										<label for="comment_new_resolution" class="form-check-label">{{ trans('issues::issues.mark as resolution') }}</label>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group text-right text-end">
										<button class="btn btn-secondary comment-add" data-parent="#comment_new">{{ trans('issues::issues.add') }}</button>
									</div>
								</div>
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
