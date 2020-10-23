@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/knowledge/js/admin.js?v=' . filemtime(public_path() . '/modules/knowledge/js/admin.js')) }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('knowledge::knowledge.module name'),
		route('admin.knowledge.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit knowledge'))
		{!! Toolbar::save(route('admin.knowledge.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.knowledge.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('knowledge.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.knowledge.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

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
					<label for="field-parent_id">{{ trans('knowledge::knowledge.parent') }}:</label>
					<select name="fields[parent_id]" id="field-parent_id" class="form-control searchable-select">
						<option value="0">{{ trans('knowledge::knowledge.parent') }}</option>
						<?php foreach ($tree as $pa): ?>
							<?php $selected = ($pa->id == $row->parent_id ? ' selected="selected"' : ''); ?>
							<option value="{{ $pa->id }}"<?php echo $selected; ?> data-path="{{ $pa->path }}"><?php echo str_repeat('|&mdash; ', $pa->level) . e(Illuminate\Support\Str::limit($pa->title, 70)); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-alias">{{ trans('knowledge::knowledge.path') }}:</label>
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
							<div class="input-group-text">{{ route('site.knowledge.index') }}/<span id="parent-path">{{ dirname($row->path) }}</span>/</div>
						</div>
						<input type="text" name="page[alias]" id="field-alias" class="form-control" maxlength="250" value="{{ $page->alias }}" />
					</div>
					<span class="form-text text-muted hint">{{ trans('knowledge::knowledge.path hint') }}</span>
				</div>

				<div class="form-group">
					<label for="field-title">{{ trans('knowledge::knowledge.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="page[title]" id="field-title" class="form-control required" maxlength="250" value="{{ $page->title }}" />
				</div>

				@if (!$page->snippet)
					<div class="form-group form-check">
						<input type="checkbox" name="page[snippet]" id="field-snippet" class="form-check-input" value="1" />
						<label class="form-check-label" for="field-snippet">{{ trans('knowledge::knowledge.this is a snippet') }}</label>
					</div>
				@endif

				<div class="form-group">
					<label for="page--content">{{ trans('knowledge::knowledge.content') }}: <span class="required">{{ trans('global.required') }}</span></label>
					@if ($page->snippet)
						<div class="alert alert-warning">
							{{ trans('knowledge::knowledge.warning page is reusable') }}
						</div>
					@endif
					{!! editor('page[content]', $page->content, ['rows' => 35, 'class' => 'required']) !!}
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-access">{{ trans('knowledge::knowledge.access') }}:</label>
					<select class="form-control" name="fields[access]" id="field-access"<?php if ($row->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
							<option value="{{ $access->id }}"<?php if ($row->access == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-state">{{ trans('knowledge::knowledge.state') }}:</label><br />
					<select class="form-control" name="fields[state]" id="field-state"<?php if ($row->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						<option value="0"<?php if ($row->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
						<option value="1"<?php if ($row->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-publish_up">{{ trans('knowledge::knowledge.publish up') }}:</label><br />
					{!! Html::input('calendar', 'fields[publish_up]', Carbon\Carbon::parse($row->publish_up ? $row->publish_up : $page->created_at)) !!}
				</div>

				<div class="form-group">
					<label for="field-publish_down">{{ trans('knowledge::knowledge.publish down') }}:</label><br />
					<span class="input-group input-datetime">
						<input type="text" name="fields[publish_down]" id="field-publish_down" class="form-control datetime" value="<?php echo ($row->publish_down ? e(Carbon\Carbon::parse($row->publish_down)->toDateTimeString()) : ''); ?>" placeholder="<?php echo ($row->publish_down ? '' : trans('global.never')); ?>" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('global.options') }}</legend>

				<fieldset>
					<legend>{{ trans('knowledge::knowledge.show title') }}</legend>

					<div class="row">
						<div class="col col-md-6 form-group">
							<div class="form-check">
								<input type="radio" name="params[show_title]" id="params-show_title_no" class="form-check-input" value="0"<?php if (!$page->options->get('show_title')) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="params-show_title_no">{{ trans('global.no') }}</label>
							</div>
						</div>

						<div class="col col-md-6 form-group">
							<div class="form-check">
								<input type="radio" name="params[show_title]" id="params-show_title_yes" class="form-check-input" value="1"<?php if ($page->options->get('show_title')) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="params-show_title_yes">{{ trans('global.yes') }}</label>
							</div>
						</div>
					</div>
				</fieldset>

				<fieldset>
					<legend>{{ trans('knowledge::knowledge.show toc') }}</legend>

					<div class="row">
						<div class="col col-md-6 form-group">
							<div class="form-check">
								<input type="radio" name="params[show_toc]" id="params-show_toc_no" class="form-check-input" value="0"<?php if (!$page->options->get('show_toc')) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="params-show_toc_no">{{ trans('global.no') }}</label>
							</div>
						</div>

						<div class="col col-md-6 form-group">
							<div class="form-check">
								<input type="radio" name="params[show_toc]" id="params-show_toc_yes" class="form-check-input" value="1"<?php if ($page->options->get('show_toc')) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="params-show_toc_yes">{{ trans('global.yes') }}</label>
							</div>
						</div>
					</div>
				</fieldset>

				<!-- <div class="form-group">
					<label for="params-show_title">{{ trans('knowledge::knowledge.show title') }}:</label><br />
					<select class="form-control" name="params[show_title]" id="params-show_title">
						<option value="0"<?php if (!$page->options->get('show_title')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
						<option value="1"<?php if ($page->options->get('show_title')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="params-show_toc">{{ trans('knowledge::knowledge.show toc') }}:</label><br />
					<select class="form-control" name="params[show_toc]" id="params-show_toc">
						<option value="0"<?php if (!$page->options->get('show_toc')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
						<option value="1"<?php if ($page->options->get('show_toc')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
					</select>
				</div> -->
			</fieldset>

			@sliders('start', 'module-sliders')
				@sliders('panel', trans('knowledge::knowledge.variables'), 'params-variables')
					<fieldset class="panelform">
						<table>
							<thead>
								<tr>
									<th scope="col">{{ trans('knowledge::knowledge.key') }}</th>
									<th scope="col">{{ trans('knowledge::knowledge.value') }}</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$i = 0;
							foreach ($page->options->get('variables', []) as $key => $val)
							{
								?>
								<tr id="params_variables_{{ $i }}">
									<td>
										<div class="input-group mb-2 mr-sm-2">
											<div class="input-group-prepend">
												<div class="input-group-text">resource.</div>
											</div>
											<input type="text" name="params[variables][{{ $i }}][key]" id="params_variables_{{ $i }}_key" value="{{ $key }}" class="form-control" />
										</div>
									</td>
									<td><input type="text" name="params[variables][{{ $i }}][value]" id="params_variables_{{ $i }}_value" value="{{ $val }}" class="form-control" /></td>
									<td><a href="#params_variables_{{ $i }}" class="glyph icon-trash">{{ trans('global.delete') }}</a></td>
								</tr>
								<?php
								$i++;
							}
							?>
								<tr id="params_variables_{{ $i }}">
									<td>
										<div class="input-group mb-2 mr-sm-2">
											<div class="input-group-prepend">
												<div class="input-group-text">resource.</div>
											</div>
											<input type="text" name="params[variables][{{ $i }}][key]" id="params_variables_{{ $i }}_key" value="" class="form-control" />
										</div>
									</td>
									<td><input type="text" name="params[variables][{{ $i }}][value]" id="params_variables_{{ $i }}_value" value="" class="form-control" /></td>
									<td><a href="#params_variables_{{ $i }}" class="btn btn-secondary"><span class="glyph icon-plus">{{ trans('global.add') }}</span></a></td>
								</tr>
							</tbody>
						</table>
					</fieldset>
				@sliders('panel', trans('knowledge::knowledge.inherited variables'), 'params-inherited')
					<fieldset class="panelform">
						<table>
							<thead>
								<tr>
									<th scope="col">{{ trans('knowledge::knowledge.key') }}</th>
									<th scope="col">{{ trans('knowledge::knowledge.value') }}</th>
								</tr>
							</thead>
							<tbody>
						<?php
						foreach ($row->ancestors() as $ancestor)
						{

							foreach ($ancestor->page->options->get('variables', []) as $key => $val)
							{
							?>
							<tr>
								<th scope="row">${resource.{{ $key }}}</th>
								<td>{{ $val }}</td>
							</tr>
							<?php
							}
						}
						?>
							</tbody>
						</table>
					</fieldset>
			@sliders('end')
		</div>
	</div>

	<input type="hidden" name="fields[page_id]" value="{{ $page->id }}" />
	<input type="hidden" name="id" value="{{ $row->id }}" />

	@csrf
</form>
@stop