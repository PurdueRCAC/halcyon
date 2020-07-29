@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/history/js/admin.js?v=' . filemtime(public_path() . '/modules/history/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);
@endphp

@section('toolbar')
	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.history.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('history::history.history manager') }}: View: #{{ $row->id }}
@stop

@section('content')
<form action="{{ route('admin.history.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="grid row">
		<div class="col col-md-6 span6">
			<fieldset class="adminform">
				<legend><?php echo trans('global.details'); ?></legend>

				<div class="form-group">
					<label for="field-historable_id"><?php echo trans('history::history.item id'); ?>: <span class="required"><?php echo trans('global.required'); ?></span></label>
					<input type="text" name="fields[historable_id]" id="field-historable_id" class="form-control required" disabled="disabled" size="30" maxlength="250" value="{{ $row->historable_id }}" />
				</div>

				<div class="form-group">
					<label for="field-historable_type"><?php echo trans('history::history.item type'); ?>: <span class="required"><?php echo trans('global.required'); ?></span></label>
					<input type="text" name="fields[historable_type]" id="field-historable_type" class="form-control required" disabled="disabled" size="30" maxlength="250" value="{{ $row->historable_type }}" />
				</div>

				<div class="form-group">
					<label for="field-historable_table"><?php echo trans('history::history.item table'); ?>: <span class="required"><?php echo trans('global.required'); ?></span></label>
					<input type="text" name="fields[historable_table]" id="field-historable_table" class="form-control required" disabled="disabled" size="30" maxlength="250" value="{{ $row->historable_table }}" />
				</div>
			</fieldset>
		</div>
		<div class="col col-md-6 span6">
			<table class="meta">
				<tbody>
					<tr>
						<th scope="row"><?php echo trans('history::history.id'); ?>:</th>
						<td>
							{{ $row->id }}
							<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo trans('history::history.created'); ?>:</th>
						<td>
							<?php if ($row->getOriginal('created_at') && $row->getOriginal('created_at') != '0000-00-00 00:00:00'): ?>
								<time datetime="{{ $row->created }}">{{ $row->created_at }}</time>
							<?php else: ?>
								{{ trans('global.unknown') }}
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo trans('history::history.actor'); ?>:</th>
						<td>
							{{ $row->user ? $row->user->name : trans('global.unknown') }}
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo trans('history::history.action'); ?>:</th>
						<td>
							{{ $row->action }}
						</td>
					</tr>
					<?php if ($row->updated_at && $row->updated_at != '0000-00-00 00:00:00' && $row->updated_at != $row->created_at): ?>
						<tr>
							<th scope="row"><?php echo trans('history::history.modified'); ?>:</th>
							<td>
								<time datetime="<?php echo e($row->updated_at); ?>"><?php echo e($row->updated_at); ?></time>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<fieldset class="adminform">
		<legend>{{ trans('history::history.changes') }}</legend>
		<div class="grid row">
			<div class="col col-md-6 span6">
				<div class="form-group">
					<label for="field-old"><?php echo trans('history::history.old'); ?>:</label>
					<textarea name="fields[old]" id="field-old" class="form-control" rows="20" cols="40">{{ json_encode($row->getOriginal('old'), JSON_PRETTY_PRINT) }}</textarea>
				</div>
			</div>
			<div class="col col-md-6 span6">
				<div class="form-group">
					<label for="field-new"><?php echo trans('history::history.new'); ?>:</label>
					<textarea name="fields[new]" id="field-new" class="form-control" rows="20" cols="40">{{ json_encode($row->getOriginal('new'), JSON_PRETTY_PRINT) }}</textarea>
				</div>
			</div>
		</div>
	</fieldset>

	@csrf
</form>
@stop