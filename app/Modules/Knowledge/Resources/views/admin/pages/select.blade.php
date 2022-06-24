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
		trans('knowledge::knowledge.attach')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit knowledge'))
		{!! Toolbar::save(route('admin.knowledge.attach')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.knowledge.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('knowledge::knowledge.knowledge base') }}: {{ trans('knowledge::knowledge.attach') }}
@stop

@section('content')
<form action="{{ route('admin.knowledge.attach') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
		<div class="col col-md-12">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-parent_id">{{ trans('knowledge::knowledge.parent') }}:</label>
					<select name="parent_id" id="field-parent_id" class="form-control searchable-select">
						<!--<option value="0">{{ trans('global.none') }}</option>-->
						<?php foreach ($parents as $pa): ?>
							<?php $selected = ($pa->id == $parent_id ? ' selected="selected"' : ''); ?>
							<option value="{{ $pa->id }}"<?php echo $selected; ?> data-path="/{{ $pa->path }}"><?php echo '/' . ltrim($pa->path, '/')  . ' &mdash; ' . e(Illuminate\Support\Str::limit($pa->title, 70)); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<table class="table table-hover">
					<tbody>
						<?php foreach ($snippets as $snippet): ?>
							<tr<?php if ($snippet->level > 1) { echo ' class="d-none"'; } ?> data-parent="{{ $snippet->parent_id }}">
								<td>
									<a href="#" class="toggle-tree" data-id="{{ $snippet->id }}">
										<span class="sr-only">Toggle copn/close</span>
									</a>
								</td>
								<td>
									{!! str_repeat('<span class="gi">|&mdash;</span>', $snippet->level - 1) !!}
									<span class="form-check">
										<input type="checkbox" name="snippets[{{ $snippet->parent_id }}][{{ $snippet->id }}][page_id]" id="snippet{{ $snippet->id }}" data-id="{{ $snippet->id }}" value="{{ $snippet->page_id }}" class="form-check-input snippet-checkbox" />
										<label for="snippet{{ $snippet->id }}" class="form-check-label">{{ Illuminate\Support\Str::limit($snippet->title, 70) }}</label>
									</span>
								</td>
								<td>
									<span class="form-text text-muted">{{ $snippet->path }}</span>
								</td>
								<td>
									<label for="field-{{ $snippet->id }}-access" class="sr-only">{{ trans('knowledge::knowledge.access') }}</label>
									<select class="form-control" name="snippets[{{ $snippet->parent_id }}][{{ $snippet->id }}][access]" id="field-{{ $snippet->id }}-access">
										<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
											<option value="{{ $access->id }}">{{ $access->title }}</option>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<label for="field-{{ $snippet->id }}-state" class="sr-only">{{ trans('knowledge::knowledge.state') }}</label>
									<select class="form-control" name="snippets[{{ $snippet->parent_id }}][{{ $snippet->id }}][state]" id="field-{{ $snippet->id }}-state">
										<option value="1">{{ trans('global.published') }}</option>
										<option value="0">{{ trans('global.unpublished') }}</option>
									</select>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</fieldset>
		</div>
	</div>

	@csrf
</form>
@stop
