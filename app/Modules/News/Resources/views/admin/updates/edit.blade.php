@extends('layouts.master')

@push('scripts')
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
		'Article # ' . $article->id,
		route('admin.news.index')
	)
	->append(
		trans('news::news.updates'),
		route('admin.news.updates', ['article' => $article->id])
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit knowledge'))
		{!! Toolbar::save(route('admin.news.updates.store', ['article' => $article->id])) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.news.updates.cancel', ['article' => $article->id]));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('news.name') !!} {{ trans('news::news.updates') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.news.updates.store', ['article' => $article->id]) }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<input type="hidden" name="article" value="{{ $article->id }}" />
	<input type="hidden" name="fields[newsid]" value="{{ $article->id }}" />

	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-body">{{ trans('news::news.body') }}: <span class="required">{{ trans('global.required') }}</span></label>
					{!! markdown_editor('fields[body]', $row->body, ['rows' => 15, 'class' => ($errors->has('fields.body') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="invalid-feedback">{{ trans('queues::queues.error.invalid name') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<?php /*<fieldset class="adminform">
				<legend><span>{{ trans('global.publishing') }}</span></legend>

				<div class="input-wrap form-group">
					<label for="field-published">{{ trans('pages::pages.state') }}:</label>
					<select name="published" id="field-published" class="form-control">
						<option value="published"<?php if (!$row->isTrashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="trashed"<?php if ($row->isTrashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
					</select>
				</div>
			</fieldset>*/ ?>

			@include('history::admin.history')
		</div>
	</div>

	@csrf
</form>
@stop