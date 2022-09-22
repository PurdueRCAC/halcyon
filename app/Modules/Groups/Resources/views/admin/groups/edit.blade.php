@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js?v=' . filemtime(public_path() . '/modules/core/vendor/handlebars/handlebars.min-v4.7.6.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/datatables.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/groups/js/admin.js?v=' . filemtime(public_path() . '/modules/groups/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('groups::groups.module name'),
		route('admin.groups.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit groups'))
		{!! Toolbar::save(route('admin.groups.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.groups.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('groups::groups.module name') }}: {{ $row->id ?  trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.groups.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-group-name">{{ trans('groups::groups.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-group-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="48" value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ trans('groups::groups.invalid.title') }}</span>
				</div>

				<div class="form-group">
					<label for="field-description'">{{ trans('groups::groups.description') }}</label>
					<textarea class="form-control" name="fields[description]" id="field-description" maxlength="2000">{{ $row->description' }}</textarea>
				</div>

				<div class="form-group">
					<div class="form-check">
						<input type="checkbox" name="fields[cascademanagers]" id="field-cascade" class="form-check-input" value="1"{{ $row->cascademanagers ? ' checked="checked"' : '' }} />
						<label for="field-cascade" class="form-check-label">{{ trans('groups::groups.cascade managers') }}</label>
						<span class="form-text text-muted">{{ trans('groups::groups.cascade managers desc') }}</span>
					</div>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group mb-0">
							<label for="field-unixgroup">{{ trans('groups::groups.unix group base name') }}</label>
							<input type="text" class="form-control input-unixgroup{{ $errors->has('fields.unixgroup') ? ' is-invalid' : '' }}" name="fields[unixgroup]" id="field-unixgroup" maxlength="10" pattern="[a-z0-9\-]+" value="{{ $row->unixgroup }}" />
							<span class="form-text text-muted">{{ trans('groups::groups.unix group base name hint') }}</span>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group mb-0">
							<label for="field-unixid">{{ trans('groups::groups.unix id') }}</label>
							<input type="text" class="form-control" name="fields[unixid]" id="field-unixid" value="{{ $row->unixid }}" />
							<span class="form-text text-muted">{{ trans('groups::groups.unix group id') }}</span>
						</div>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('groups::groups.unix groups') }}</legend>

				<div class="form-group">
					<div class="form-check">
						<input type="checkbox" name="fields[prefix_unixgroup]" id="field-prefix_unixgroup" class="form-check-input" value="1"{{ $row->prefix_unixgroup ? ' checked="checked"' : '' }} />
						<label for="field-prefix_unixgroup" class="form-check-label">{{ trans('groups::groups.prefix unixgroup') }}</label>
						<span class="form-text text-muted">{{ trans('groups::groups.prefix unixgroup desc') }}</span>
					</div>
				</div>

				@if (count($row->unixGroups))
				<table class="table table-hover">
					<caption class="sr-only">{{ trans('groups::groups.unix groups') }}</caption>
					<thead>
						<tr>
							<th scope="col">{{ trans('groups::groups.id') }}</th>
							<th scope="col">{{ trans('groups::groups.unix group') }}</th>
							<th scope="col">{{ trans('groups::groups.short name') }}</th>
							<th scope="col" class="text-right">{{ trans('groups::groups.members') }}</th>
							<th scope="col" class="text-right"></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($row->unixGroups as $i => $u)
							<tr id="unixgroup-{{ $u->id }}" data-id="{{ $u->id }}">
								<td>{{ $u->id }}</td>
								<td>{{ $u->longname }}</td>
								<td>{{ $u->shortname }}</td>
								<td class="text-right">{{ $u->members()->count() }}</td>
								<td class="text-right">
									<a href="#unixgroup-{{ $u->id }}" class="btn text-danger remove-unixgroup"
										data-api="{{ route('api.unixgroups.delete', ['id' => $u->id]) }}"
										data-confirm="{{ trans('groups::groups.confirm delete') }}">
										<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
									</a>
								</td>
							</tr>
						@endforeach
						<tr class="hidden" id="unixgroup-{id}" data-id="{id}">
							<td>{id}</td>
							<td>{longname}</td>
							<td>{shortname}</td>
							<td class="text-right">0</td>
							<td class="text-right">
								<a href="#unixgroup-{id}" class="btn text-danger remove-unixgroup"
									data-api="{{ route('api.unixgroups.create') }}/{id}"
									data-confirm="{{ trans('groups::groups.confirm delete') }}">
									<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
								</a>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td></td>
							<td colspan="3">
								@if ($row->prefix_unixgroup)
								<span class="input-group">
									<span class="input-group-prepend"><span class="input-group-text">{{ $row->unixgroup }}-</span></span>
									<input type="text" name="longname" id="longname" class="form-control input-unixgroup" maxlength="{{ (17 - strlen($row->unixgroup . '-')) }}" pattern="[a-z0-9]+" placeholder="{{ strtolower(trans('groups::groups.name')) }}" />
								</span>
								@else
								<input type="text" name="longname" id="longname" class="form-control input-unixgroup" maxlength="48" pattern="[a-z0-9-]+" placeholder="{{ strtolower(trans('groups::groups.name')) }}" value="{{ $row->unixgroup }}-" />
								@endif
							</td>
							<td class="text-right">
								<a href="#longname" class="btn text-success add-unixgroup"
									data-group="{{ $row->id }}"
									data-api="{{ route('api.unixgroups.create') }}">
									<span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('global.add') }}</span>
								</a>
							</td>
						</tr>
					</tfoot>
				</table>
				@else
					<p class="text-center"><span class="none">{{ trans('global.none') }}</span></p>
				@endif

				@if (!count($row->unixGroups))
					<div>
						<p class="text-center">
							<button class="btn btn-secondary create-default-unix-groups" data-api="{{ route('api.unixgroups.create') }}" data-group="{{ $row->id }}" data-value="{{ $row->unixgroup }}" data-all-groups="1" id="INPUT_groupsbutton_{{ $row->id }}">
								<span class="spinner-border spinner-border-sm d-none" role="status"></span> Create Default Unix Groups
							</button>
							<button class="btn btn-outline-secondary create-default-unix-groups" data-api="{{ route('api.unixgroups.create') }}" data-group="{{ $row->id }}" data-value="{{ $row->unixgroup }}" data-all-groups="0">
								<span class="spinner-border spinner-border-sm" role="status"></span> Create Base Group Only
							</button>
						</p>
						<p class="form-text">This will create default Unix groups; A base group, `apps`, and `data` group will be created. These will prefixed by the base name chosen. Once these are created, the groups and base name cannot be easily changed.</p>
					</div>
				@endif
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('groups::groups.department') }}</legend>

				<table class="table table-hover">
					<caption class="sr-only">{{ trans('groups::groups.department') }}</caption>
					<tbody>
					@foreach ($row->departments as $dept)
						<tr id="department-{{ $dept->id }}" data-id="{{ $dept->id }}">
							<td>
								<?php
								$prf = '';
								foreach ($dept->department->ancestors() as $ancestor):
									if (!$ancestor->parentid):
										continue;
									endif;

									$prf .= $ancestor->name . ' > ';
								endforeach;
								?>{{ $prf . $dept->department->name }}
							</td>
							<td class="text-right">
								<a href="#department-{{ $dept->id }}" class="btn text-danger remove-category"
									data-api="{{ route('api.groups.groupdepartments.delete', ['group' => $row->id, 'id' => $dept->id]) }}"
									data-confirm="{{ trans('groups::groups.confirm delete') }}">
									<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
								</a>
							</td>
						</tr>
					@endforeach
						<tr class="hidden" id="department-{id}" data-id="{id}">
							<td>{name}</td>
							<td class="text-right">
								<a href="#department-{id}" class="btn text-danger remove-category"
									data-api="{{ route('api.groups.groupdepartments.create', ['group' => $row->id]) }}/{id}"
									data-confirm="{{ trans('groups::groups.confirm delete') }}">
									<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
								</a>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td>
								<select name="department" id="department" data-category="collegedeptid" class="form-control searchable-select">
									<option value="0">{{ trans('groups::groups.select department') }}</option>
									@foreach ($departments as $d)
										@php
										if ($d->level == 0):
											continue;
										endif;

										$prf = '';
										foreach ($d->ancestors() as $ancestor):
											if (!$ancestor->parentid):
												continue;
											endif;

											$prf .= $ancestor->name . ' > ';
										endforeach;
										@endphp
										<option value="{{ $d->id }}">{{ $prf . $d->name }}</option>
									@endforeach
								</select>
							</td>
							<td class="text-right">
								<a href="#department"
									class="btn text-success add-category"
									data-group="{{ $row->id }}"
									data-api="{{ route('api.groups.groupdepartments.create', ['group' => $row->id]) }}">
									<span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('global.add') }}</span>
								</a>
							</td>
						</tr>
					</tfoot>
				</table>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('groups::groups.field of science') }}</legend>

				<table class="table table-hover">
					<caption class="sr-only">{{ trans('groups::groups.field of science') }}</caption>
					<tbody>
					@foreach ($row->fieldsOfScience as $field)
						<tr id="fieldofscience-{{ $field->id }}" data-id="{{ $field->id }}">
							<td>
								<?php
								$prf = '';
								foreach ($field->field->ancestors() as $ancestor):
									if (!$ancestor->parentid):
										continue;
									endif;

									$prf .= $ancestor->name . ' > ';
								endforeach;
								?>{{ $prf . $field->field->name }}
							</td>
							<td class="text-right">
								<a href="#fieldofscience-{{ $field->id }}" class="btn text-danger remove-category"
									data-api="{{ route('api.groups.groupfieldsofscience.delete', ['group' => $row->id, 'id' => $field->id]) }}"
									data-confirm="{{ trans('groups::groups.confirm delete') }}">
									<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
								</a>
							</td>
						</tr>
					@endforeach
						<tr class="hidden" id="fieldofscience-{id}" data-id="{id}">
							<td>{name}</td>
							<td class="text-right">
								<a href="#fieldofscience-{id}" class="btn text-danger remove-category"
									data-api="{{ route('api.groups.groupfieldsofscience.create', ['group' => $row->id]) }}/{id}"
									data-confirm="{{ trans('groups::groups.confirm delete') }}">
									<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">{{ trans('global.trash') }}</span>
								</a>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td>
								<select name="fieldofscience" id="fieldofscience" data-category="fieldofscienceid" class="form-control searchable-select">
									<option value="0">{{ trans('groups::groups.select field of science') }}</option>
									@foreach ($fields as $f)
										@php
										if ($f->level == 0):
											continue;
										endif;

										$prf = '';
										foreach ($f->ancestors() as $ancestor):
											if (!$ancestor->parentid):
												continue;
											endif;

											$prf .= $ancestor->name . ' > ';
										endforeach;
										@endphp
										<option value="{{ $f->id }}">{{ $prf . $f->name }}</option>
									@endforeach
								</select>
							</td>
							<td class="text-right">
								<a href="#fieldofscience"
									class="btn text-success add-category"
									data-group="{{ $row->id }}"
									data-api="{{ route('api.groups.groupfieldsofscience.create', ['group' => $row->id]) }}">
									<span class="fa fa-plus-circle" aria-hidden="true"></span><span class="sr-only">{{ trans('global.add') }}</span>
								</a>
							</td>
						</tr>
					</tfoot>
				</table>
			</fieldset>

			<input type="hidden" name="id" value="{{ $row->id }}" />
		</div>
	</div>
	@csrf
</form>
@stop