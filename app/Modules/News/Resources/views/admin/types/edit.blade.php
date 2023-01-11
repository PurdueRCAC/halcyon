@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('news::news.module name'),
		route('admin.news.index')
	)
	->append(
		trans('news::news.types'),
		route('admin.news.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit news.types'))
		{!! Toolbar::save(route('admin.news.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.news.types.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	var alias = document.getElementById('field-alias');
	if (alias && !alias.value) {
		document.getElementById('field-name').addEventListener('keyup', function () {
			alias.value = this.value.toLowerCase()
				.replace(/\s+/g, '-')
				.replace(/[^a-z0-9\-_]+/g, '');
		});
	}
});
</script>
@endpush

@section('title')
{{ trans('news::news.module name') }}: {{ trans('news::news.types') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.news.types.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
		<div class="col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-parentid">{{ trans('news::news.parent') }}:</label>
					<select name="fields[parentid]" id="field-parentid" class="form-control">
						<option value="0">{{ trans('global.none') }}</option>
						@foreach ($parents as $parent)
							<option value="{{ $parent->id }}"<?php if ($parent->id == $row->parentid) { echo ' selected="selected"'; } ?>>{{ $parent->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('news::news.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" required maxlength="32" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('news::news.error.invalid name') }}</span>
				</div>

				<div class="form-group">
					<label for="field-alias">{{ trans('news::news.alias') }}:</label>
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
							<div class="input-group-text">{{ route('site.news.index') }}/</div>
						</div>
						<input type="text" name="fields[alias]" id="field-alias" aria-describedby="field-alias-hint" class="form-control{{ $errors->has('fields.alias') ? ' is-invalid' : '' }}" maxlength="32" pattern="[a-z0-9\-_]{1,32}" value="{{ $row->alias }}" />
					</div>
					<span class="form-text text-muted" id="field-alias-hint">{{ trans('news::news.alias hint') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.options') }}</legend>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-block">
							<div class="form-group form-check">
								<input type="checkbox" name="fields[location]" id="field-location" class="form-check-input" value="1" <?php if ($row->location): ?>checked="checked"<?php endif; ?> />
								<label for="field-location" class="form-check-label">{{ trans('news::news.location') }}</label>
								<span class="form-text">Allow for specifying a location on articles in this category?</span>
							</div>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-block">
							<div class="form-group form-check">
								<input type="checkbox" name="fields[future]" id="field-future" class="form-check-input" value="1" <?php if ($row->future): ?>checked="checked"<?php endif; ?> />
								<label for="field-future" class="form-check-label">{{ trans('news::news.future') }}</label>
								<span class="form-text">Display future events in listings?</span>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[ongoing]" id="field-ongoing" class="form-check-input" value="1" <?php if ($row->ongoing): ?>checked="checked"<?php endif; ?> />
							<label for="field-ongoing" class="form-check-label">{{ trans('news::news.ongoing') }}</label>
							<span class="form-text">Allow for specifying a location on articles in this category?</span>
						</div>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[url]" id="field-url" class="form-check-input" value="1" <?php if ($row->url): ?>checked="checked"<?php endif; ?> />
							<label for="field-url" class="form-check-label">{{ trans('news::news.url') }}</label>
							<span class="form-text">Allow for specifying a URL on articles in this category?</span>
						</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[tagresources]" id="field-tagresources" class="form-check-input" value="1" <?php if ($row->tagresources): ?>checked="checked"<?php endif; ?> />
							<label for="field-tagresources" class="form-check-label">{{ trans('news::news.tag resources') }}</label>
							<span class="form-text">Allow for tagging resources on articles in this category?</span>
						</div>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[tagusers]" id="field-tagusers" class="form-check-input" value="1" <?php if ($row->tagusers): ?>checked="checked"<?php endif; ?> />
							<label for="field-tagusers" class="form-check-label">{{ trans('news::news.tag users') }}</label>
							<span class="form-text">Allow for tagging users on articles in this category?</span>
						</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
	@csrf
</form>
@stop