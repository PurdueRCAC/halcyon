@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/news/js/admin.js') }}"></script>
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
{{ trans('news::news.module name') }} {{ trans('news::news.updates') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
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
		<div class="col-md-7 mx-auto">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-body">{{ trans('news::news.body') }}: <span class="required">{{ trans('global.required') }}</span></label>
					{!! markdown_editor('fields[body]', $row->body, ['rows' => 15, 'class' => ($errors->has('fields.body') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="invalid-feedback">{{ trans('news::news.error.invalid body') }}</span>
				</div>
			</fieldset>
		</div>
	</div>

	@csrf
</form>
@stop