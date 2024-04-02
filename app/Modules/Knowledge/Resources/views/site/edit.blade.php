@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/select2/css/select2.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/knowledge/css/knowledge.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/select2/js/select2.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/knowledge/js/site.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('knowledge::knowledge.module name'),
		route('site.knowledge.index')
	)
	->append(
		$node->page->headline,
		route('site.knowledge.page', ['uri' => $node->path])
	)
	->append(
		trans('knowledge::knowledge.attach')
	);
@endphp

@section('title')
{!! config('knowledge.name') !!}: {{ trans('knowledge::knowledge.attach') }}
@stop

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@php
	$children = $root->publishedChildren();
	$path = explode('/', $node->path);
	@endphp
	@include('knowledge::site.list', ['nodes' => $children, 'path' => '', 'current' => $path, 'variables' => $root->page->variables])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="row">
		<h2>{{ $node->page->headline }}</h2>

		<form action="{{ route('site.knowledge.page', ['uri' => ($node->path ? $node->path : '/')]) }}" data-api="{{ route('api.knowledge.create') }}" method="post" name="pageform" id="pageform" class="editform">
			<fieldset>
				<legend>{{ trans('knowledge::knowledge.create child page') }}</legend>

				@if ($page->snippet)
					<div class="alert alert-warning">
						{{ trans('knowledge::knowledge.warning page is reusable') }}
					</div>
				@endif

				<div class="form-group">
					<label for="field-title">{{ trans('knowledge::knowledge.title') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="title" id="field-title" class="form-control{{ $errors->has('page.title') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $page->title }}" />
					<span class="invalid-feedback">{{ trans('knowledge::knowledge.invalid.title') }}</span>
				</div>

				<div class="form-group">
					<label for="field-alias">{{ trans('knowledge::knowledge.path') }}</label>
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
							<div class="input-group-text">{{ route('site.knowledge.index') }}<span id="parent-path">{{ $node->path }}</span>/</div>
						</div>
						<input type="text" name="alias" id="field-alias" class="form-control" maxlength="250"<?php if ($page->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $page->alias }}" />
					</div>
					<span class="form-text text-muted hint">{{ trans('knowledge::knowledge.path hint') }}</span>
				</div>

				<div class="form-group">
					<label for="field-content">{{ trans('pages::pages.content') }} <span class="required">{{ trans('global.required') }}</span></label>
					{!! editor('content', $page->content, ['rows' => 35, 'class' => 'required', 'id' => 'field-content']) !!}
				</div>
			</fieldset>

			<div class="row">
				<div class="col col-md-6">
					<fieldset>
						<legend>{{ trans('global.publishing') }}</legend>

						<div class="form-group">
							<label for="field-access">{{ trans('knowledge::knowledge.access') }}</label>
							<select class="form-control" name="access" id="field-access"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
								@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
									<option value="{{ $access->id }}"<?php if ($row->access == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
								@endforeach
							</select>
						</div>

						<div class="form-group">
							<label for="field-state">{{ trans('knowledge::knowledge.state') }}</label><br />
							<select class="form-control" name="state" id="field-state"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
								<option value="0"<?php if ($row->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
								<option value="2"<?php if ($row->state == 2) { echo ' selected="selected"'; } ?>>&nbsp;|_&nbsp;{{ trans('knowledge::knowledge.archived') }}</option>
								<option value="1"<?php if ($row->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
							</select>
						</div>
					</fieldset>
				</div>
				<div class="col-md-6">
					<fieldset>
						<legend>{{ trans('knowledge::knowledge.options') }}</legend>

						<div class="form-group">
							<label for="params-show_title">{{ trans('knowledge::knowledge.show title') }}</label>
							<select name="params[show_title]" id="params-show_title" class="form-control">
								<option value="0"<?php if (!$page->params->get('show_title', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($page->params->get('show_title', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
						</div>

						<div class="form-group">
							<label for="params-show_toc">{{ trans('knowledge::knowledge.show toc') }}</label>
							<select name="params[show_toc]" id="params-show_toc" class="form-control">
								<option value="0"<?php if (!$page->params->get('show_toc', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($page->params->get('show_toc', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
						</div>
					</fieldset>
				</div>
			</div>

			<input type="hidden" name="parent_id" value="{{ $node->id }}" />
			<input type="hidden" name="id" value="{{ $row->id }}" />
			<input type="hidden" name="page_id" value="{{ $page->id }}" />
			<input type="hidden" name="snippet" value="{{ $page->snippet }}" />

			@csrf

			<p class="text-center">
				<button class="btn btn-success" id="save-page" type="submit">
					{{ trans('global.save') }}
					<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only visually-hidden">{{ trans('global.saving') }}</span></span>
				</button>
				<a href="{{ route('site.knowledge.page', ['uri' => ($node->path ? $node->path : '/')]) }}" data-id="{{ $page->id }}" class="cancel btn btn-link">{{ trans('global.button.cancel') }}</a>
			</p>
		</form>
	</div>
</div>
</div>
@stop
