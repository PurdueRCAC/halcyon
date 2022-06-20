@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/publications/css/publications.css?v=' . filemtime(public_path('/modules/publications/css/publications.css'))) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/publications/js/site.js?v=' . filemtime(public_path() . '/modules/publications/js/site.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('publications::publications.publications'),
		route('site.publications.index')
	)
	->append(
		trans('global.create'),
		route('site.publications.create')
	);
@endphp

@section('title') {{ trans('publications::publications.module name') }}: {{ $row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create') }} @stop

@section('content')
<h2 class="mt-0">{{ trans('publications::publications.publications') }}: {{ $row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create') }}</h2>

<form action="{{ route('site.publications.store') }}" method="post" name="adminForm" id="item-form" class="editform" enctype="multipart/form-data">

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
		<div class="col col-md-8">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-type">{{ trans('publications::publications.type') }} <span class="required">{{ trans('global.required') }}</span></label>
					<select name="type_id" id="field-type" class="form-control" required>
						@foreach ($types as $type)
							<option value="{{ $type->id }}" data-alias="{{ $type->alias }}" <?php if ($row->type_id == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-isbn">{{ trans('publications::publications.isbn') }}</label>
							<input type="text" name="isbn" id="field-isbn" class="form-control" maxlength="50" value="{{ $row->isbn }}" />
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-doi">{{ trans('publications::publications.doi') }}</label>
							<input type="text" name="doi" id="field-doi" class="form-control" maxlength="255" value="{{ $row->doi }}" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-title">{{ trans('publications::publications.title') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="title" id="field-title" class="form-control{{ $errors->has('fields.title') ? ' is-invalid' : '' }}" required maxlength="500" value="{{ $row->title }}" />
					<span class="invalid-feedback">{{ trans('publications::publications.invalid.title') }}</span>
					{!! $errors->first('title', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-year">{{ trans('publications::publications.year') }} <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="year" id="field-year" class="form-control{{ $errors->has('fields.year') ? ' is-invalid' : '' }}" required maxlength="4" value="{{ $row->published_at ? $row->published_at->format('Y') : '' }}" />
							<span class="invalid-feedback">{{ trans('publications::publications.invalid.year') }}</span>
							{!! $errors->first('year', '<span class="form-text text-danger">:message</span>') !!}
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-month">{{ trans('publications::publications.month') }}</label>
							<select name="month" id="field-month" class="form-control{{ $errors->has('fields.month') ? ' is-invalid' : '' }}">
								<option value="01"<?php if ($row->published_at->format('m') == '01') { echo ' selected="selected"'; } ?>>{{ trans('global.month.january') }}</option>
								<option value="02"<?php if ($row->published_at->format('m') == '02') { echo ' selected="selected"'; } ?>>{{ trans('global.month.february') }}</option>
								<option value="03"<?php if ($row->published_at->format('m') == '03') { echo ' selected="selected"'; } ?>>{{ trans('global.month.march') }}</option>
								<option value="04"<?php if ($row->published_at->format('m') == '04') { echo ' selected="selected"'; } ?>>{{ trans('global.month.april') }}</option>
								<option value="05"<?php if ($row->published_at->format('m') == '05') { echo ' selected="selected"'; } ?>>{{ trans('global.month.may') }}</option>
								<option value="06"<?php if ($row->published_at->format('m') == '06') { echo ' selected="selected"'; } ?>>{{ trans('global.month.june') }}</option>
								<option value="07"<?php if ($row->published_at->format('m') == '07') { echo ' selected="selected"'; } ?>>{{ trans('global.month.july') }}</option>
								<option value="08"<?php if ($row->published_at->format('m') == '08') { echo ' selected="selected"'; } ?>>{{ trans('global.month.august') }}</option>
								<option value="09"<?php if ($row->published_at->format('m') == '09') { echo ' selected="selected"'; } ?>>{{ trans('global.month.september') }}</option>
								<option value="10"<?php if ($row->published_at->format('m') == '10') { echo ' selected="selected"'; } ?>>{{ trans('global.month.october') }}</option>
								<option value="11"<?php if ($row->published_at->format('m') == '11') { echo ' selected="selected"'; } ?>>{{ trans('global.month.november') }}</option>
								<option value="12"<?php if ($row->published_at->format('m') == '12') { echo ' selected="selected"'; } ?>>{{ trans('global.month.december') }}</option>
							</select>
							<span class="invalid-feedback">{{ trans('publications::publications.invalid.year') }}</span>
							{!! $errors->first('month', '<span class="form-text text-danger">:message</span>') !!}
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-author">{{ trans('publications::publications.author') }}</label>
					<textarea name="author" id="field-author" class="form-control" maxlength="3000" rows="3" cols="40">{{ $row->author }}</textarea>
				</div>

				<div class="form-group">
					<label for="field-editor">{{ trans('publications::publications.editor') }}</label>
					<textarea name="editor" id="field-editor" class="form-control" maxlength="3000" rows="1" cols="40">{{ $row->editor }}</textarea>
				</div>

				<div class="form-group">
					<label for="field-url">{{ trans('publications::publications.url') }}</label>
					<input type="text" name="url" id="field-url" class="form-control" maxlength="2083" value="{{ $row->url }}" />
				</div>

				<div class="form-group type-dependent type-unknown type-inbook type-conference">
					<label for="field-series">{{ trans('publications::publications.series') }}</label>
					<input type="text" name="series" id="field-series" class="form-control" maxlength="255" value="{{ $row->series }}" />
				</div>

				<div class="form-group type-dependent type-unknown type-inbook">
					<label for="field-booktitle">{{ trans('publications::publications.booktitle') }}</label>
					<input type="text" name="booktitle" id="field-booktitle" class="form-control" maxlength="1000" value="{{ $row->booktitle }}" />
				</div>

				<div class="row type-dependent type-unknown type-inbook">
					<div class="col col-md-6">
				<div class="form-group">
					<label for="field-edition">{{ trans('publications::publications.edition') }}</label>
					<input type="text" name="edition" id="field-edition" class="form-control" maxlength="100" value="{{ $row->edition }}" />
				</div>
					</div>
					<div class="col col-md-6">
				<div class="form-group">
					<label for="field-chapter">{{ trans('publications::publications.chapter') }}</label>
					<input type="text" name="chapter" id="field-chapter" class="form-control" maxlength="40" value="{{ $row->chapter }}" />
				</div>
					</div>
				</div>

				<div class="form-group type-dependent type-unknown type-journal type-proceedings type-magazine">
					<label for="field-journal">{{ trans('publications::publications.journal') }}</label>
					<input type="text" name="journal" id="field-journal" class="form-control" maxlength="255" value="{{ $row->journal }}" />
				</div>

				<div class="type-dependent type-unknown type-journal type-proceedings type-magazine">
					<div class="row">
						<div class="col col-md-6">
							<div class="form-group">
								<label for="field-issue">{{ trans('publications::publications.issue') }}</label>
								<input type="text" name="issue" id="field-issue" class="form-control" maxlength="40" value="{{ $row->issue }}" />
							</div>
						</div>
						<div class="col col-md-6">
							<div class="form-group">
								<label for="field-issuetitle">{{ trans('publications::publications.issue title') }}</label>
								<input type="text" name="issuetitle" id="field-issuetitle" class="form-control" maxlength="255" value="{{ $row->issuetitle }}" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col col-md-6">
							<div class="form-group">
								<label for="field-volume">{{ trans('publications::publications.volume') }}</label>
								<input type="text" name="volume" id="field-volume" class="form-control" maxlength="40" value="{{ $row->volume }}" />
							</div>
						</div>
						<div class="col col-md-6">
							<div class="form-group">
								<label for="field-number">{{ trans('publications::publications.number') }}</label>
								<input type="text" name="number" id="field-number" class="form-control" maxlength="40" value="{{ $row->number }}" />
							</div>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-pages">{{ trans('publications::publications.pages') }}</label>
					<input type="text" name="pages" id="field-pages" class="form-control" maxlength="40" value="{{ $row->pages }}" />
				</div>

				<div class="row type-dependent type-unknown type-inbook">
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-publisher">{{ trans('publications::publications.publisher') }}</label>
							<input type="text" name="publisher" id="field-publisher" class="form-control" maxlength="500" value="{{ $row->publisher }}" />
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-address">{{ trans('publications::publications.address') }}</label>
							<input type="text" name="address" id="field-address" class="form-control" maxlength="300" value="{{ $row->address }}" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-institution">{{ trans('publications::publications.institution') }}</label>
					<input type="text" name="institution" id="field-institution" class="form-control" maxlength="500" value="{{ $row->institution }}" />
				</div>

				<div class="form-group">
					<label for="field-organization">{{ trans('publications::publications.organization') }}</label>
					<input type="text" name="organization" id="field-organization" class="form-control" maxlength="500" value="{{ $row->organization }}" />
				</div>

				<div class="form-group">
					<label for="field-school">{{ trans('publications::publications.school') }}</label>
					<input type="text" name="school" id="field-school" class="form-control" maxlength="200" value="{{ $row->school }}" />
				</div>

				<div class="form-group">
					<label for="field-crossref">{{ trans('publications::publications.crossref') }}</label>
					<input type="text" name="crossref" id="field-crossref" class="form-control" maxlength="100" value="{{ $row->crossref }}" />
				</div>
			</fieldset>

			<div class="text-center">
				<input type="submit" class="btn btn-success" value="{{ trans('global.button.save') }}" />
				<a class="btn" href="{{ route('site.publications.index') }}">
					{{ trans('global.button.cancel') }}
				</a>
			</div>
		</div>
		<div class="col col-md-4">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-state">{{ trans('global.state') }}</label>
					<select name="state" id="field-state" class="form-control">
						<option value="1"<?php if ($row->state): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
						<option value="0"<?php if (!$row->state): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-note">{{ trans('publications::publications.note') }}</label>
					<textarea name="note" id="field-note" class="form-control" maxlength="2000" rows="3" cols="40">{{ $row->note }}</textarea>
				</div>
			</fieldset>

			<fieldset>
				<legend>{{ trans('publications::publications.attach') }}</legend>

				@if ($row->hasAttachment())
				<div class="form-group">
					{{ $row->filename }}

					<button class="btn btn-delete text-danger">
						<span class="fa fa-trash" aria-hidden="true"></span>
						<span class="sr-only">{{ trans('global.button.delete') }}</span>
					</button>
				</div>
				@endif

				<div class="form-group dropzone">
					<div id="uploader" class="fallback" data-instructions="Click or Drop files" data-list="#uploader-list">
						<label for="upload">Choose a file<span class="dropzone__dragndrop"> or drag it here</span></label>
						<input type="file" name="file" id="upload" class="form-control-file" />
					</div>
					<div class="file-list" id="uploader-list"></div>
					<input type="hidden" name="tmp_dir" id="ticket-tmp_dir" value="{{ ('-' . time()) }}" />
					<span class="form-text text-muted">Accepted formats: <code>pdf</code>, <code>docx</code></span>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" value="{{ $row->id }}" />
	@csrf
</form>
@stop