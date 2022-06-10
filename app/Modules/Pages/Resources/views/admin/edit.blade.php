@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ asset('modules/pages/js/pages.js?v=' . filemtime(public_path() . '/modules/pages/js/pages.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('pages::pages.module name'),
		route('admin.pages.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit users'))
		{!! Toolbar::save(route('admin.pages.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.pages.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('pages::pages.module name') }}: {{ $row->id ?  trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.pages.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	<div class="row">
		<div class="col-md-8">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				@if ($row->alias != 'home')
					<div class="form-group">
						<label for="field-parent_id">{{ trans('pages::pages.parent') }}: <span class="required">{{ trans('global.required') }}</span></label>
						<select name="fields[parent_id]" id="field-parent_id" class="form-control">
							<option value="1" data-path="">{{ trans('pages::pages.home') }}</option>
							@foreach ($parents as $page)
								<?php $selected = ($page->id == $row->parent_id ? ' selected="selected"' : ''); ?>
								<option value="{{ $page->id }}"<?php echo $selected; ?> data-path="/{{ $page->path }}"><?php echo str_repeat('|&mdash; ', $page->level) . e($page->title); ?></option>
							@endforeach
						</select>
					</div>
				@else
					<input type="hidden" name="fields[parent_id]" value="{{ $row->parent_id }}" />
				@endif

				<div class="form-group">
					<label for="field-title">{{ trans('pages::pages.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[title]" id="field-title" class="form-control{{ $errors->has('fields.title') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->title }}" />
					<span class="invalid-feedback">{{ trans('pages::pages.invalid.title') }}</span>
				</div>

				<div class="form-group">
					<label for="field-alias">{{ trans('pages::pages.path') }}:</label>
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
							<div class="input-group-text">{{ url('/') }}<span id="parent-path">{{ ($row->parent && trim($row->parent->path, '/') ? '/' . $row->parent->path : '') }}</span>/</div>
						</div>
						<input type="text" name="fields[alias]" id="field-alias" aria-describedby="field-alias-hint" class="form-control{{ $errors->has('fields.alias') ? ' is-invalid' : '' }}" maxlength="250"<?php if ($row->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $row->alias }}" />
					</div>
					<span class="form-text text-muted" id="field-alias-hint">{{ trans('pages::pages.path hint') }}</span>
				</div>

				<div class="form-group{{ $errors->has('content') ? ' is-invalid' : '' }}">
					<label for="field-content">{{ trans('pages::pages.content') }}: <span class="required">{{ trans('global.required') }}</span></label>
					{!! editor('fields[content]', $row->content, ['rows' => 45, 'class' => 'required', 'required' => 'required']) !!}
				</div>
			</fieldset>
		</div>
		<div class="col-md-4">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-access">{{ trans('pages::pages.access') }}:</label>
					<select class="form-control" name="fields[access]" id="field-access"<?php if ($row->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
							<option value="{{ $access->id }}"<?php if ($row->access == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<label for="field-state">{{ trans('pages::pages.state') }}:</label>
					<select class="form-control" name="fields[state]" id="field-state"<?php if ($row->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						<option value="0"<?php if ($row->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
						<option value="1"<?php if ($row->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-publish_up">{{ trans('pages::pages.publish up') }}:</label>
					{!! Html::input('calendar', 'fields[publish_up]', Carbon\Carbon::parse($row->publish_up ? $row->publish_up : $row->created)) !!}
				</div>

				<div class="form-group">
					<label for="field-publish_down">{{ trans('pages::pages.publish down') }}:</label>
					<span class="input-group input-datetime">
						<input type="text" name="fields[publish_down]" id="field-publish_down" class="form-control datetime" value="<?php echo ($row->publish_down ? e(Carbon\Carbon::parse($row->publish_down)->toDateTimeString()) : ''); ?>" placeholder="<?php echo ($row->publish_down ? '' : trans('global.never')); ?>" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>
			</fieldset>

			<div class="accordion" id="parameters">
				<div class="card">
					<div class="card-header" id="options-heading">
						<h3 class="my-0 py-0">
							<a href="#options-options" class="btn btn-link btn-block text-left" data-toggle="collapse" data-target="#options-options" aria-expanded="true" aria-controls="options-options">
								<span class="fa fa-chevron-right" aria-hidden="true"></span>
								{{ trans('pages::pages.options') }}
							</a>
						</h3>
					</div>
					<div id="options-options" class="collapse show" aria-labelledby="options-heading" data-parent="#parameters">
					<fieldset class="card-body mb-0">
						<div class="form-group">
							<label for="params-show_title">{{ trans('pages::pages.params.show title') }}:</label>
							<select class="form-control" aria-describedby="params-show_title" name="params[show_title]" id="params-show_title">
								<option value="0"<?php if (!$row->params->get('show_title', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_title', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text text-muted" id="params-show_title-hint">{{ trans('pages::pages.params.show title desc') }}</span>
						</div>

						<div class="form-group">
							<label for="params-show_author">{{ trans('pages::pages.params.show author') }}:</label>
							<select class="form-control" aria-describedby="params-show_author" name="params[show_author]" id="params-show_author">
								<option value="0"<?php if (!$row->params->get('show_author')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_author')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text text-muted" id="params-show_author-hint">{{ trans('pages::pages.params.show author desc') }}</span>
						</div>

						<div class="form-group">
							<label for="params-show_create_date">{{ trans('pages::pages.params.show create date') }}:</label>
							<select class="form-control" aria-describedby="params-show_create_date-hint" name="params[show_create_date]" id="params-show_create_date">
								<option value="0"<?php if (!$row->params->get('show_create_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_create_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text text-muted" id="params-show_create_date-hint">{{ trans('pages::pages.params.show create date desc') }}</span>
						</div>

						<div class="form-group">
							<label for="params-show_modify_date">{{ trans('pages::pages.params.show modify date') }}:</label>
							<select class="form-control" aria-describedby="params-show_modify_date-hint" name="params[show_modify_date]" id="params-show_modify_date">
								<option value="0"<?php if (!$row->params->get('show_modify_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_modify_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text text-muted" id="params-show_modify_date-hint">{{ trans('pages::pages.params.show modify date desc') }}</span>
						</div>

						<div class="form-group">
							<label for="params-show_publish_date">{{ trans('pages::pages.params.show publish date') }}:</label>
							<select class="form-control" aria-describedby="params-show_publish_date" name="params[show_publish_date]" id="params-show_publish_date">
								<option value="0"<?php if (!$row->params->get('show_publish_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_publish_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text text-muted" id="params-show_publish_date-hint">{{ trans('pages::pages.params.show publish date desc') }}</span>
						</div>

						<div class="form-group">
							<label for="params-layout">{{ trans('pages::pages.params.layout') }}:</label>
							<select class="form-control" aria-describedby="params-layout" name="params[layout]" id="params-layout">
								<option value=""<?php if (!$row->params->get('layout')) { echo ' selected="selected"'; } ?>>{{ trans('pages::pages.params.default layout') }}</option>
								<option value="none"<?php if ($row->params->get('layout') == 'none') { echo ' selected="selected"'; } ?>>{{ trans('pages::pages.params.no layout') }}</option>
								<option value="raw"<?php if ($row->params->get('layout') == 'raw') { echo ' selected="selected"'; } ?>>{{ trans('pages::pages.params.raw layout') }}</option>
							</select>
							<span class="form-text text-muted" id="params-layout-hint">{{ trans('pages::pages.params.layout desc') }}</span>
						</div>

						<fieldset id="param-styles">
							<legend>{{ trans('pages::pages.params.styles') }}</legend>
							<div class="px-3 py-3">
								@php
								$i = 0;
								@endphp
								@foreach ($row->params->get('styles', []) as $style)
									<div class="input-group mb-3" id="params-styles-{{ $i }}-row">
										<label class="sr-only" for="params-styles-{{ $i }}">{{ trans('pages::pages.styles') }}:</label>
										<input type="text" class="form-control" name="params[styles][{{ $i }}]" id="params-styles-{{ $i }}" value="{{ $style }}" />
										<div class="input-group-append">
											<a href="#params-styles-{{ $i }}-row" class="btn btn-danger delete-row" id="params-styles-{{ $i }}-btn" data-id="params-styles-{{ $i }}"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></a>
										</div>
									</div>
									@php
									$i++;
									@endphp
								@endforeach

								<div class="d-none input-group mb-3" id="params-styles-{{ $i }}">
									<label class="sr-only" for="params-styles-{{ $i }}">{{ trans('pages::pages.styles') }}:</label>
									<input type="text" class="form-control" name="params[styles][{{ $i }}]" id="params-styles-{{ $i }}" value="" />
									<div class="input-group-append">
										<a href="#params-styles-{{ $i }}" class="btn btn-danger delete-row disabled" id="params-styles-{{ $i }}-btn" data-id="params-styles-{{ $i }}"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></a>
									</div>
								</div>

								<div class="text-right">
									<button data-type="style" data-container="param-styles" class="add-row btn btn-success param-style-new"><span class="glyph icon-plus">{{ trans('global.add') }}</span></button>
								</div>
							</div>
						</fieldset>

						<fieldset id="param-scripts">
							<legend>{{ trans('pages::pages.params.scripts') }}</legend>

							<div class="px-3 py-3">
								@php
								$i = 0;
								@endphp
								@foreach ($row->params->get('scripts', []) as $script)
									<div class="input-group mb-3" id="params-scripts-{{ $i }}-row">
										<label class="sr-only" for="params-scripts-{{ $i }}">{{ trans('pages::pages.scripts') }}:</label>
										<input type="text" class="form-control" name="params[scripts][{{ $i }}]" id="params-scripts-{{ $i }}" value="{{ $script }}" />
										<div class="input-group-append">
											<a href="#params-scripts-{{ $i }}-row" class="btn btn-danger delete-row" id="params-scripts-{{ $i }}-btn" data-id="params-scripts-{{ $i }}"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></a>
										</div>
									</div>
									@php
									$i++;
									@endphp
								@endforeach
								<div class="d-none input-group mb-3" id="params-scripts-{{ $i }}-row">
									<label class="sr-only" for="params-scripts-{{ $i }}">{{ trans('pages::pages.scripts') }}:</label>
									<input type="text" class="form-control" name="params[scripts][{{ $i }}]" id="params-scripts-{{ $i }}" value="" />
									<div class="input-group-append">
										<a href="#params-scripts-{{ $i }}-row" class="btn btn-danger delete-row disabled" id="params-scripts-{{ $i }}-btn" data-id="params-scripts-{{ $i }}"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></a>
									</div>
								</div>

								<div class="text-right">
									<button data-type="script" data-container="param-scripts" class="add-row btn btn-success param-script-new"><span class="glyph icon-plus">{{ trans('global.add') }}</span></button>
								</div>
							</div>
						</fieldset>
					</fieldset>
					</div>
				</div>

				<div class="card">
					<div class="card-header" id="meta-heading">
						<h3 class="my-0 py-0">
							<a href="#meta-options" class="btn btn-link btn-block text-left" data-toggle="collapse" data-target="#meta-options" aria-expanded="true" aria-controls="meta-options">
								<span class="fa fa-chevron-right" aria-hidden="true"></span>
								{{ trans('pages::pages.metadata') }}
							</a>
						</h3>
					</div>
					<div id="meta-options" class="collapse" aria-labelledby="meta-heading" data-parent="#parameters">
						<fieldset class="card-body mb-0">
							<div class="form-group">
								<label for="field-metakey">{{ trans('pages::pages.metakey') }}:</label>
								<textarea class="form-control" name="fields[metakey]" id="field-metakey" rows="3" cols="40">{{ $row->metakey }}</textarea>
							</div>

							<div class="form-group">
								<label for="field-metadesc">{{ trans('pages::pages.metadesc') }}:</label>
								<textarea class="form-control" name="fields[metadesc]" id="field-metadesc" rows="3" cols="40">{{ $row->metadesc }}</textarea>
							</div>

							<div class="form-group">
								<label for="field-metadata">{{ trans('pages::pages.metadata') }}:</label>
								<textarea class="form-control" name="fields[metadata]" id="field-metadata" rows="3" cols="40">{{ json_encode($row->metadata->all()) }}</textarea>
							</div>
						</fieldset>
					</div>
				</div>
			</div><!-- / .accordion -->

		</div>
	</div>

	<div class="dialog">
		@include('history::admin.history')
	</div>

	@csrf
</form>
@stop