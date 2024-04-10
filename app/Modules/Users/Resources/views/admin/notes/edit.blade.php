@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.notes'),
		route('admin.users.notes')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit users'))
		{!! Toolbar::save(route('admin.users.notes.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.users.notes.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::users.module name') }}: {{ trans('users::notes.notes') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.users.notes.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
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
		<div class="mx-auto col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group{{ $errors->has('user_id') ? ' has-error' : '' }}">
					<label for="field-user_id">{{ trans('users::notes.user') }}:</label>
					@if ($row->id)
						<input type="text" name="user" readonly class="form-control-plaintext" value="{{ $row->user ? $row->user->name : trans('global.unknown') }}" />
						<input type="hidden" name="fields[user_id]" id="field-user_id" value="{{ $row->user_id }}" />
					@else
						<span class="input-group input-user">
							<input type="text" name="fields[userid]" id="field-userid" class="form-control form-users" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" maxlength="250" value="" />
							<span class="input-group-append"><span class="input-group-text fa fa-user"></span></span>
						</span>
					@endif
				</div>

				<div class="form-group">
					<label for="field-subject">{{ trans('users::notes.subject') }}: <span class="required">{{ trans('global.required') }}</span></label><br />
					<input type="text" class="form-control" required name="fields[subject]" id="field-subject" value="{{ $row->subject }}" />
				</div>

				<div class="form-group">
					<label for="field-body">{{ trans('users::notes.body') }}:</label>
					{!! editor('fields[body]', $row->body, ['rows' => 15, 'class' => 'minimal no-footer', 'required' => 'required']) !!}
				</div>

				<input type="hidden" name="id" value="{{ $row->id }}" />
			</fieldset>
		</div>
	</div>

	@csrf
</form>
@stop
