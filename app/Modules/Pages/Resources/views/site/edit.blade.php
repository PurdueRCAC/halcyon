@extends('layouts.master')

@section('title'){{ trans('pages::pages.create page') }}@stop

@section('content')
	<h2>{{ trans('pages::pages.create page') }}</h2>

	<form action="{{ route('site.pages.store') }}" data-api="{{ route('api.pages.create') }}" method="post" name="pageform" id="pageform" class="editform">

		<div class="alert alert-danger d-none"></div>

		<div class="form-group">
			<label class="form-label" for="field-parent_id">{{ trans('pages::pages.parent') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</span></label>
			<select name="parent_id" id="field-parent_id" class="form-control">
				<option value="1" data-path="">{{ trans('pages::pages.home') }}</option>
				@foreach ($parents as $p)
					<?php $selected = ($p->id == $page->parent_id ? ' selected="selected"' : ''); ?>
					<option value="{{ $p->id }}"<?php echo $selected; ?> data-path="/{{ $p->path }}"><?php echo str_repeat('|&mdash; ', $p->level) . e($p->title); ?></option>
				@endforeach
			</select>
		</div>

		<div class="form-group">
			<label class="form-label" for="field-title">{{ trans('pages::pages.title') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</span></label>
			<input type="text" name="title" id="field-title" class="form-control required" maxlength="250" value="{{ $page->title }}" />
		</div>

		<div class="form-group">
			<label class="form-label" for="field-alias">{{ trans('pages::pages.path') }}:</label>
			<div class="input-group mb-2 mr-sm-2">
				<div class="input-group-prepend">
					<div class="input-group-text">{{ url('/') }}<span id="parent-path">{{ ($page->parent && trim($page->parent->path, '/') ? '/' . $page->parent->path : '') }}</span>/</div>
				</div>
				<input type="text" name="alias" id="field-alias" aria-describedby="field-alias-hint" class="form-control{{ $errors->has('fields.alias') ? ' is-invalid' : '' }}" maxlength="250"<?php if ($page->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $page->alias }}" />
			</div>
			<span class="form-text text-muted">{{ trans('pages::pages.path hint') }}</span>
		</div>

		<div class="form-group">
			<label class="form-label" for="field-content">{{ trans('pages::pages.content') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</span></label>
			{!! editor('content', $page->getOriginal('content'), ['rows' => 35, 'class' => 'required', 'id' => 'field-content']) !!}
		</div>

		<div class="row">
			<div class="form-group col-md-6">
				<label class="form-label" for="field-access">{{ trans('pages::pages.access') }}:</label>
				<select class="form-control" name="access" id="field-access"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
					<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
						<option value="<?php echo $access->id; ?>"<?php if ($page->access == $access->id) { echo ' selected="selected"'; } ?>><?php echo e($access->title); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="form-group col-md-6">
				<label class="form-label" for="field-state">{{ trans('pages::pages.state') }}:</label>
				<select class="form-control" name="state" id="field-state"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
					<option value="0"<?php if ($page->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
					<option value="1"<?php if ($page->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
				</select>
			</div>

			<div class="form-group col-md-6">
				<label class="form-label" for="field-publish_up">{{ trans('pages::pages.publish up') }}:</label>
				<input type="text" name="publish_up" id="field-publish_up" class="form-control datetime date-pick" value="<?php echo ($page->publish_up ? $page->publish_up->toDateTimeString() : $page->created->toDateTimeString()); ?>" />
			</div>

			<div class="form-group col-md-6">
				<label class="form-label" for="field-publish_down">{{ trans('pages::pages.publish down') }}:</label>
				<input type="text" name="publish_down" id="field-publish_down" class="form-control datetime date-pick" value="<?php echo ($page->publish_down ? $page->publish_down->toDateTimeString() : ''); ?>" placeholder="<?php echo ($page->publish_down ? '' : trans('global.never')); ?>" />
			</div>
		</div>

		<input type="hidden" name="id" value="{{ $page->id }}" />
		@csrf

		<div class="text-center mb-3">
			<button class="btn btn-success" id="save-page" type="submit">
				{{ trans('global.save') }}
				<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only visually-hidden">{{ trans('global.saving') }}</span></span>
			</button>
			<a href="{{ route('page', ['uri' => $page->parent->path]) }}" class="cancel btn btn-link">{{ trans('global.button.cancel') }}</a>
		</div>
	</form>

	@push('scripts')
		<script src="{{ timestamped_asset('modules/pages/js/site.js') }}"></script>
	@endpush
@stop
