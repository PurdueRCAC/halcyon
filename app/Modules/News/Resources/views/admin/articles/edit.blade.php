@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/news/js/admin.js?v=' . filemtime(public_path() . '/modules/news/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

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
{{ trans('news::news.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.news.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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

				<div class="form-group template-hide{{ $row->template ? ' hide' : '' }}">
					<label for="template_select">Template</label>
					<select id="template_select" name="template_select" class="form-control">
						<option value="0">(No Template)</option>
						@foreach ($templates as $template)
							<option value="{{ route('api.news.read', ['id' => $template['id']]) }}" data-api="{{ route('api.news.read', ['id' => $template['id']]) }}">{{ $template['headline'] }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<label for="field-newstypeid">{{ trans('news::news.type') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[newstypeid]" id="field-newstypeid" class="form-control required" required>
						<?php foreach ($types as $type): ?>
							<option value="{{ $type->id }}"<?php if ($row->newstypeid == $type->id): echo ' selected="selected"'; endif;?>
								data-tagresources="{{ $type->tagresources }}"
								data-tagusers="{{ $type->tagusers }}"
								data-location="{{ $type->location }}"
								data-url="{{ $type->url }}"
								data-future="{{ $type->future }}"
								data-ongoing="{{ $type->ongoing }}">{{ $type->name }}</option>
							@foreach ($type->children as $child)
								<option value="{{ $child->id }}"<?php if ($row->newstypeid == $child->id): echo ' selected="selected"'; endif;?>
									data-tagresources="{{ $child->tagresources }}"
									data-tagusers="{{ $child->tagusers }}"
									data-location="{{ $child->location }}"
									data-url="{{ $child->url }}"
									data-future="{{ $child->future }}"
									data-ongoing="{{ $child->ongoing }}">|_ {{ $child->name }}</option>
							@endforeach
						<?php endforeach; ?>
					</select>
					<span class="invalid-feedback">{{ trans('news::news.error.invalid type') }}</span>
				</div>

				<div class="form-group">
					<label for="field-headline">{{ trans('news::news.headline') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[headline]" id="field-headline" class="form-control required" required value="{{ $row->headline }}" />
					<span class="invalid-feedback">{{ trans('news::news.error.invalid headline') }}</span>
				</div>

				<div class="form-group type-option type-location <?php if (!$row->type->location) { echo ' d-none'; } ?>">
					<label for="field-location">{{ trans('news::news.location') }}:</label>
					<input type="text" name="fields[location]" id="field-location" class="form-control" value="{{ $row->location }}" />
				</div>

				<div class="form-group type-option type-url <?php if (!$row->type->url) { echo ' d-none'; } ?>">
					<label for="field-url">{{ trans('news::news.url') }}:</label>
					<input type="text" name="fields[url]" id="field-url" class="form-control" value="{{ $row->url }}" />
				</div>

				<div class="form-group type-option type-tagresources <?php if (!$row->type->tagresources) { echo ' d-none'; } ?>">
					<?php
					$r = array();
					/*foreach ($row->resources as $resource)
					{
						$r[] = $resource->resource->name . ':' . $resource->id;
					}*/
					?>
					<label for="field-resources">{{ trans('news::news.tag resources') }}:</label>
					<!-- <input type="text" name="resources" id="field-resources" class="form-control form-resources" data-uri="{{ url('/') }}/api/resources/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ implode(', ', $r) }}" /> -->
					<select class="form-control basic-multiple" name="resources[]" id="field-resources" multiple="multiple" data-placeholder="Select resource...">
						<?php
						$resources = App\Modules\Resources\Models\Asset::orderBy('name', 'asc')->get();
						foreach ($resources as $resource):
							$selected = '';
							foreach ($row->resources as $r):
								if ($r->resourceid == $resource->id):
									$selected = ' selected="selected"';
									break;
								endif;
							endforeach;
							?>
							<option value="{{ $resource->id }}"{!! $selected !!}>{{ $resource->name }}</option>
							<?php
						endforeach;
						?>
					</select>
				</div>

				<div class="form-group type-option type-tagusers <?php if (!$row->type->tagusers) { echo ' d-none'; } ?>">
					<?php
					$r = array();
					foreach ($row->associations()->where('assoctype', '=', 'user')->get() as $assoc):
						$u = App\Modules\Users\Models\User::find($assoc->associd);
						$r[] = ($u ? $u->name : trans('global.unknown')) . ':' . $assoc->id;
					endforeach;
					?>
					<label for="field-users">{{ trans('news::news.tag users') }}:</label>
					<input type="text" name="associations" id="field-users" class="form-control form-users" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ implode(', ', $r) }}" />
				</div>

				<div class="form-group">
					<label for="field-body">{{ trans('news::news.body') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<span class="form-text text-muted">{!! trans('news::news.body formatting') !!} <button class="btn btn-link preview float-right" data-target="#preview-modal" data-toggle="modal" data-id="{{ $row->id }}" data-api="{{ route('api.news.preview') }}">Preview</button></span>
					{!! markdown_editor('fields[body]', $row->body, ['id' => 'field-body', 'rows' => 35, 'class' => ($errors->has('fields.body') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="invalid-feedback">{{ trans('queues::queues.error.invalid body') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-published">{{ trans('pages::pages.state') }}:</label>
					<select name="fields[published]" id="field-published" class="form-control">
						<option value="0"<?php if ($row->published == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
						<option value="1"<?php if ($row->published == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					</select>
				</div>

				<div class="form-group template-hide{{ $row->template ? ' hide' : '' }}">
					<label for="field-datetimenews">{{ trans('news::news.publish up') }}:</label>
					<span class="input-group input-datetime">
						<input type="text" class="form-control datetime" name="fields[datetimenews]" id="field-datetimenews" value="{{ $row->hasStart() ? $row->datetimenews->toDateTimeString() : '' }}" placeholder="{{ trans('news::news.now') }}" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>

				<div class="form-group template-hide{{ $row->template ? ' hide' : '' }}">
					<label for="field-datetimenewsend">{{ trans('news::news.publish down') }}:</label>
					<span class="input-group input-datetime">
						<input type="text" class="form-control datetime" name="fields[datetimenewsend]" id="field-datetimenewsend" value="{{ $row->hasEnd() ? $row->datetimenewsend->toDateTimeString() : '' }}" placeholder="{{ trans('news::news.never') }}" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>Variables</legend>

				<p>Variables can be included in the headline or body of an article. These allow for inserting information set in other fields, such as start date, end date, location, and/or resources.</p>

				<table class="table table-bordered">
					<caption class="sr-only">Available Variables</caption>
					<tbody>
						<tr>
							<th scope="row">%date%</th>
							<td>
								Includes end date &amp; time, if set. Example:<br />
								Thursday, April 15, 2021<br />
								April 15 - 16, 2021
							</td>
						</tr>
						<tr>
							<th scope="row">%datetime%</th>
							<td>
								Includes end date &amp; time, if set. Example:<br />
								Thursday, April 15, 2021 at 3:45pm<br />
								Thursday, April 15, 2021 from 3:45pm - 4:45pm<br />
								Thursday, April 15, 2021 at 3:45pm - Friday, April 16, 2021 at 3:45pm
							</td>
						</tr>
						<tr>
							<th scope="row">%time%</th>
							<td>
								Includes end date &amp; time, if set. Example:<br />
								3:45pm<br />
								3:45pm - 4:45pm
							</td>
						</tr>
						<tr>
							<th scope="row">%updatedatetime%</th>
							<td>
								Updated date &amp; time
							</td>
						</tr>
						<tr>
							<th scope="row">%startdatetime%</th>
							<td>
								Start date &amp; time
							</td>
						</tr>
						<tr>
							<th scope="row">%startdate%</th>
							<td>
								Start date
							</td>
						</tr>
						<tr>
							<th scope="row">%starttime%</th>
							<td>
								Start time
							</td>
						</tr>
						<tr>
							<th scope="row">%enddatetime%</th>
							<td>
								End date &amp; time
							</td>
						</tr>
						<tr>
							<th scope="row">%enddate%</th>
							<td>
								End date
							</td>
						</tr>
						<tr>
							<th scope="row">%endtime%</th>
							<td>
								End time
							</td>
						</tr>
						<tr>
							<th scope="row">%location%</th>
							<td>
								Location
							</td>
						</tr>
						<tr>
							<th scope="row">%resources%</th>
							<td>
								List of tagged resource names. Example:<br />
								Bell, Brown and Halstead
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>

			<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
		</div>
	</div>

	@include('news::formatting')

	<div class="modal" id="preview-modal" tabindex="-1" aria-labelledby="preview-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="preview-title">News Preview</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="preview">
					<div class="spinner-border" role="status">
						<span class="sr-only">Loading...</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="mailpreview-modal" tabindex="-1" aria-labelledby="mailpreview-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="mailpreview-title">Mail Preview</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="mailpreview">
					<div class="spinner-border" role="status">
						<span class="sr-only">Loading...</span>
					</div>
				</div>
				<div class="modal-footer text-right">
					<button id="mailsend" data-dismiss="modal" class="btn btn-success" data-confirm="You have unsaved changes that need to be saved before mailing news item. Would you like to save the changes?">Send mail</button>
				</div>
			</div>
		</div>
	</div>

	@csrf
</form>
@stop