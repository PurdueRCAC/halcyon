@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css?v=' . filemtime(public_path('/modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css'))) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js?v=' . filemtime(public_path('/modules/core/vendor/tom-select/js/tom-select.complete.min.js'))) }}"></script>
<script src="{{ asset('modules/knowledge/js/admin.js?v=' . filemtime(public_path() . '/modules/knowledge/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('knowledge::knowledge.module name'),
		route('admin.knowledge.index')
	)
	->append(
		trans('knowledge::knowledge.snippets'),
		route('admin.knowledge.snippets')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);

	$parentpath = '';
	if ($page->path)
	{
		if (trim($row->path, '/') != $page->alias)
		{
			$parentpath = dirname($row->path);
			$parentpath = trim($parentpath, '/');
			$parentpath = $parentpath ? '/' . $parentpath : '';
		}
	}
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit knowledge'))
		{!! Toolbar::save(route('admin.knowledge.snippets.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.knowledge.snippets.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('knowledge::knowledge.knowledge base') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.knowledge.snippets.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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

				@if ($page->snippet)
					<div class="alert alert-warning">
						{{ trans('knowledge::knowledge.warning page is reusable') }}
					</div>
				@endif

				<div class="form-group">
					<label for="field-parent_id">{{ trans('knowledge::knowledge.parent') }}</label>
					<select name="fields[parent_id]" id="field-parent_id" class="form-control searchable-select">
						<option value="1">{{ trans('global.none') }}</option>
						<?php foreach ($tree as $pa): ?>
							<?php $selected = ($pa->id == $row->parent_id ? ' selected="selected"' : ''); ?>
							<option value="{{ $pa->id }}"<?php echo $selected; ?> data-path="/{{ $pa->path }}"><?php echo str_repeat('|&mdash; ', $pa->level - 1) . e(Illuminate\Support\Str::limit($pa->title, 70)); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-title">{{ trans('knowledge::knowledge.title') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="page[title]" id="field-title" class="form-control{{ $errors->has('page.title') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $page->title }}" />
					<span class="invalid-feedback">{{ trans('knowledge::knowledge.invalid.title') }}</span>
				</div>

				<div class="form-group">
					<label for="field-alias">{{ trans('knowledge::knowledge.path') }}</label>
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
							<div class="input-group-text"><span id="parent-path">{{ $parentpath }}</span>/</div>
						</div>
						<input type="text" name="page[alias]" id="field-alias" class="form-control" maxlength="250" value="{{ $page->alias }}" />
					</div>
					<span class="form-text text-muted hint">{{ trans('knowledge::knowledge.path hint') }}</span>
				</div>

				<input type="hidden" name="page[snippet]" id="field-snippet" class="form-check-input" value="1" />

				<div class="form-group">
					<label for="page--content">{{ trans('knowledge::knowledge.content') }} <span class="required">{{ trans('global.required') }}</span></label>
					{!! editor('page[content]', $page->content, ['rows' => 35, 'class' => ($errors->has('page.title') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="invalid-feedback">{{ trans('knowledge::knowledge.invalid.content') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-access">{{ trans('knowledge::knowledge.access') }}</label>
					<select class="form-control" name="fields[access]" id="field-access"<?php if ($row->id && $row->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
							<option value="{{ $access->id }}"<?php if ($page->access == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-state">{{ trans('knowledge::knowledge.state') }}</label><br />
					<select class="form-control" name="fields[state]" id="field-state"<?php if ($row->id && $row->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						<option value="1"<?php if ($page->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="0"<?php if ($page->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
					</select>
				</div>

				<!--
				<div class="form-group">
					<label for="field-publish_up">{{ trans('knowledge::knowledge.publish up') }}</label>
					{!! Html::input('calendar', 'fields[publish_up]', Carbon\Carbon::parse($row->publish_up ? $row->publish_up : $page->created_at)) !!}
				</div>

				<div class="form-group">
					<label for="field-publish_down">{{ trans('knowledge::knowledge.publish down') }}</label>
					<span class="input-group input-datetime">
						<input type="text" name="fields[publish_down]" id="field-publish_down" class="form-control datetime" value="<?php echo ($row->publish_down ? e(Carbon\Carbon::parse($row->publish_down)->toDateTimeString()) : ''); ?>" placeholder="<?php echo ($row->publish_down ? '' : trans('global.never')); ?>" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>
				-->
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('global.options') }}</legend>

				<fieldset>
					<legend>{{ trans('knowledge::knowledge.show title') }}</legend>

					<div class="row">
						<div class="col col-md-6 form-group">
							<div class="form-check">
								<input type="radio" name="params[show_title]" id="params-show_title_no" class="form-check-input" value="0"<?php if (!$page->params->get('show_title')) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="params-show_title_no">{{ trans('global.no') }}</label>
							</div>
						</div>

						<div class="col col-md-6 form-group">
							<div class="form-check">
								<input type="radio" name="params[show_title]" id="params-show_title_yes" class="form-check-input" value="1"<?php if ($page->params->get('show_title')) { echo ' checked="checked"'; } ?> />
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
								<input type="radio" name="params[show_toc]" id="params-show_toc_no" class="form-check-input" value="0"<?php if (!$page->params->get('show_toc')) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="params-show_toc_no">{{ trans('global.no') }}</label>
							</div>
						</div>

						<div class="col col-md-6 form-group">
							<div class="form-check">
								<input type="radio" name="params[show_toc]" id="params-show_toc_yes" class="form-check-input" value="1"<?php if ($page->params->get('show_toc')) { echo ' checked="checked"'; } ?> />
								<label class="form-check-label" for="params-show_toc_yes">{{ trans('global.yes') }}</label>
							</div>
						</div>
					</div>
				</fieldset>
			</fieldset>

			@sliders('start', 'module-sliders')
				@sliders('panel', trans('knowledge::knowledge.variables'), 'params-variables')
					<fieldset class="panelform" id="param-variables">
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
							foreach ($page->params->get('variables', []) as $key => $val):
								?>
								<tr id="params-variables-{{ $i }}">
									<td>
										<div class="input-group mb-2 mr-sm-2">
											<div class="input-group-prepend">
												<div class="input-group-text">resource.</div>
											</div>
											<input type="text" name="params[variables][{{ $i }}][key]" id="params-variables-{{ $i }}-key" value="{{ $key }}" class="form-control" />
										</div>
									</td>
									<td>
										<input type="text" name="params[variables][{{ $i }}][value]" id="params-variables-{{ $i }}-value" value="{{ $val }}" class="form-control" />
									</td>
									<td>
										<a href="#params-variables-{{ $i }}" class="btn btn-danger delete-row">
											<span class="fa fa-trash" aria-hidden="true"></span>
											<span class="sr-only">{{ trans('global.delete') }}</span>
										</a>
									</td>
								</tr>
								<?php
								$i++;
							endforeach;
							?>
								<tr id="params-variables-{{ $i }}" class="d-none">
									<td>
										<div class="input-group mb-2 mr-sm-2">
											<div class="input-group-prepend">
												<div class="input-group-text">resource.</div>
											</div>
											<input type="text" name="params[variables][{{ $i }}][key]" id="params-variables-{{ $i }}-key" value="" class="form-control" />
										</div>
									</td>
									<td>
										<input type="text" name="params[variables][{{ $i }}][value]" id="params-variables-{{ $i }}-value" value="" class="form-control" />
									</td>
									<td>
										<a href="#params-variables-{{ $i }}" class="btn btn-danger delete-row disabled">
											<span class="fa fa-trash" aria-hidden="true"></span>
											<span class="sr-only">{{ trans('global.delete') }}</span>
										</a>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="3" class="text-right">
										<button data-type="script" data-container="param-variables" class="add-row btn btn-success param-variable-new">
											<span class="fa fa-plus" aria-hidden="true"></span>
											<span class="sr-only">{{ trans('global.add') }}</span>
										</button>
									</td>
								</tr>
							</tfoot>
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
						foreach ($row->ancestors() as $ancestor):
							foreach ($ancestor->page->params->get('variables', []) as $key => $val):
								?>
								<tr>
									<th scope="row">${resource.{{ $key }}}</th>
									<td>{{ $val }}</td>
								</tr>
								<?php
							endforeach;
						endforeach;
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
