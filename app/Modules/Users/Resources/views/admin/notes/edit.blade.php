@extends('layouts.master')

@php
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
{!! config('users.name') !!}: {{ trans('users::notes.notes') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.users.notes.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-subject">{{ trans('users::notes.subject') }}: <span class="required">{{ trans('global.required') }}</span></label><br />
					<input type="text" class="form-control required" name="fields[subject]" id="field-subject" value="{{ $row->subject }}" />
				</div>

				<div class="form-group">
					<label for="field-body">{{ trans('users::users.FIELD_BODY') }}:</label>
					{!! editor('fields[body]', $row->body, ['rows' => 15, 'class' => 'minimal no-footer']) !!}
				</div>

				<div class="form-group">
					<label for="field-category_id">{{ trans('users::users.FIELD_CATEGORY') }}:</label>
					<select name="fields[catid]" id="field-category_id">
						<option value="0">{{ trans('JOPTION_SELECT_CATEGORY') }}</option>
						<?php echo Html::select('options', Html::category('options', 'com_members'), 'value', 'text', $row->category_id); ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-category_id">{{ trans('users::users.FIELD_USER') }}:</label>
					<?php echo Components\Members\Helpers\Admin::getUserInput('fields[user_id]', 'fielduser_id', $row->user_id); ?>
				</div>

				<div class="form-group">
					<label for="field-state">{{ trans('users::users.FIELD_STATE') }}:</label>
					<select name="fields[state]" id="field-state">
						<option value="0"<?php if ($row->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
						<option value="1"<?php if ($row->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="2"<?php if ($row->state == 2) { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
					</select>
				</div>

				<div class="form-group" data-hint="{{ trans('users::users.FIELD_REVIEW_TIME_DESC') }}">
					<label for="field-review_time">{{ trans('users::users.FIELD_REVIEW_TIME_LABEL') }}:</label>
					<?php echo Html::input('calendar', 'fields[review_time]', ($row->review_time && $row->review_time != '0000-00-00 00:00:00' ? $row->review_time : ''), array('id' => 'field-review_time')); ?>
				</div>
			</fieldset>
		</div>
		<div class="col span5">
			<table class="meta">
				<tbody>
					<tr>
						<th scope="row">{{ trans('users::access.id') }}:</th>
						<td>
							{{ $row->id }}
							<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	@csrf
</form>
@stop
