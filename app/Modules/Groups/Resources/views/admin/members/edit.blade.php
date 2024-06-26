@extends('layouts.master')

@section('toolbar')
	@if (auth()->user()->can('edit groups'))
		{!! Toolbar::save(route('admin.groups.members.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.groups.members.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('groups::groups.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.creat') }}
@stop

@section('content')
<form action="{{ route('admin.groups.members.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.VALIDATION_FORM_FAILED') }}">
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
		<div class="col-md-7 mx-auto">
			<fieldset class="adminform">
				<legend><span>{{ trans('global.details') }}</span></legend>

				<div class="form-group">
					<label for="field-name">{{ trans('groups::groups.name') }}</label>
					<input type="text" name="fields[name]" id="field-user" class="form-control disabled" disabled="disabled" readonly="readonly" value="{{ $row->user->name }}" />
					<span class="text-muted">{{ trans('groups::groups.name hint') }}</span>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('groups::groups.unix groups') }}</legend>

				<table>
					<thead>
						<tr>
							<th scope="col">{{ trans('groups::groups.unix group') }}</th>
							<th scope="col" class="text-center">{{ trans('groups::groups.member') }}</th>
							<th scope="col" class="text-right text-end">Added</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($row->group->unixGroups as $u)
						<tr id="unixgroup-{{ $u->id }}" data-id="{{ $u->id }}">
							<td>{{ $u->longname }}</td>
							<td class="text-center">
								@php
								$has = $u->members->search(function ($item, $key) use ($u)
								{
									return $item->unixgroupid == $u->id;
								});
								@endphp
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="unixgroup-{{ $u->id }}" name="unixgroups[{{ $u->id }}]" value="1"<?php if ($has !== false) { echo ' checked="checked"'; } ?> />
									<label class="form-check-label" for="unixgroup-{{ $u->id }}">{{ trans('global.yes') }}</label>
								</div>
							</td>
							<td class="text-right text-end">
								@if ($has !== false)
									<time>{{ $u->datetimecreated }}</time>
								@endif
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="userid" value="{{ $row->userid }}" />

	@csrf
</form>
@stop