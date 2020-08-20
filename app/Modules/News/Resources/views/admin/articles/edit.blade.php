@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@stop

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/news/js/admin.js?v=' . filemtime(public_path() . '/modules/news/js/admin.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('news::news.module name'),
		route('admin.news.index')
	)
	->append(
		trans('news::news.articles'),
		route('admin.news.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit news'))
		{!! Toolbar::save(route('admin.news.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.news.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('news.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.news.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

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

				<div class="form-group form-block">
					<div class="form-check">
						<input type="checkbox" name="fields[template]" id="field-template" class="form-check-input" value="1"<?php if ($row->template) { echo ' checked="checked"'; } ?> />
						<label for="field-template" class="form-check-label">{{ trans('news::news.template') }}</label>
						<span class="form-text text-muted">Templates do not appear as public articles.</span>
					</div>
				</div>

				<div class="form-group">
					<label for="field-newstypeid">{{ trans('news::news.type') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[newstypeid]" id="field-newstypeid" class="form-control required">
						<?php foreach ($types as $type): ?>
							<option value="<?php echo $type->id; ?>"<?php if ($row->newstypeid == $type->id): echo ' selected="selected"'; endif;?>
								data-tagresources="{{ $type->tagresources }}"
								data-tagusers="{{ $type->tagusers }}"
								data-location="{{ $type->location }}"
								data-url="{{ $type->url }}"
								data-future="{{ $type->future }}"
								data-ongoing="{{ $type->ongoing }}">{{ $type->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-headline">{{ trans('news::news.headline') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[headline]" id="field-headline" class="form-control required" value="{{ $row->headline }}" />
				</div>

				<div class="form-group type-option type-location <?php if (!$row->type->location) { echo ' d-none'; } ?>">
					<label for="field-location">{{ trans('news::news.location') }}:</label>
					<input type="text" name="fields[location]" id="field-location" class="form-control" value="{{ $row->location }}" />
				</div>

				<div class="form-group type-option type-tagresources <?php if (!$row->type->tagresources) { echo ' d-none'; } ?>">
					<?php
					$r = array();
					foreach ($row->resources as $resource)
					{
						$r[] = $resource->resource->name . ':' . $resource->id;
					}
					?>
					<label for="field-resources">{{ trans('news::news.tag resources') }}:</label>
					<!-- <input type="text" name="resources" id="field-resources" class="form-control form-resources" data-uri="{{ url('/') }}/api/resources/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ implode(', ', $r) }}" /> -->
					<select class="form-control basic-multiple" name="resources[]" multiple="multiple" data-placeholder="Select resource...">
						<?php
						$resources = App\Modules\Resources\Entities\Asset::orderBy('name', 'asc')->get();
						foreach ($resources as $resource)
						{
							?>
							<option value="{{ $resource->id }}">{{ $resource->name }}</option>
							<?php
						}
						?>
					</select>
				</div>

				<div class="form-group type-option type-tagusers <?php if (!$row->type->tagusers) { echo ' d-none'; } ?>">
					<?php
					$r = array();
					foreach ($row->associations()->where('assoctype', '=', 'user')->get() as $assoc)
					{
						$u = App\Modules\Users\Models\User::find($assoc->associd);
						$r[] = ($u ? $u->name : trans('global.unknown')) . ':' . $assoc->id;
					}
					?>
					<label for="field-users">{{ trans('news::news.tag users') }}:</label>
					<input type="text" name="users" id="field-users" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ implode(', ', $r) }}" />
				</div>

				<div class="form-group">
					<label for="field-body">{{ trans('news::news.body') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<textarea name="fields[body]" id="field-body" class="form-control" rows="35" cols="40">{{ $row->body }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<!-- <table class="meta">
				<caption>Metadata</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('news::news.id') }}:</th>
						<td>
							{{ $row->id }}
							<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
						</td>
					</tr>
					<tr>
						<th scope="row">{{ trans('news::news.created') }}:</th>
						<td>
							<?php if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00'): ?>
								{{ $row->datetimecreated }}
							<?php else: ?>
								{{ trans('global.unknown') }}
							<?php endif; ?>
						</td>
					</tr>
					<?php if ($row->getOriginal('datetimeremoved') && $row->getOriginal('datetimeremoved') != '0000-00-00 00:00:00'): ?>
						<tr>
							<th scope="row"><?php echo trans('news::news.removed'); ?>:</th>
							<td>
								{{ $row->datetimeremoved }}
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table> -->

			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-published">{{ trans('pages::pages.state') }}:</label>
					<select name="fields[published]" id="field-published" class="form-control">
						<option value="0"<?php if ($row->published == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
						<option value="1"<?php if ($row->published == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-datetimenews">{{ trans('news::news.publish up') }}:</label>
					<span class="input-group input-datetime">
						<input type="text" class="form-control datetime" name="fields[datetimenews]" id="field-datetimenews" value="<?php echo e(Carbon\Carbon::parse($row->datetimenews ? $row->datetimenews : $row->datetimenews)); ?>" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>

				<div class="form-group">
					<label for="field-datetimenewsend">{{ trans('news::news.publish down') }}:</label>
					<span class="input-group input-datetime">
						<input type="text" class="form-control datetime" name="fields[datetimenewsend]" id="field-datetimenewsend" value="<?php echo ($row->datetimenewsend ? e(Carbon\Carbon::parse($row->datetimenewsend)->toDateTimeString()) : ''); ?>" placeholder="<?php echo ($row->datetimenewsend ? '' : trans('global.never')); ?>" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>
			</fieldset>

			@include('history::admin.history')
		</div>
	</div>

	@csrf
</form>
@stop