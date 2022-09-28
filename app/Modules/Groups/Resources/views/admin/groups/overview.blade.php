
<form action="{{ route('admin.groups.store') }}" method="post" name="adminForm" id="group-form" class="editform" data-api="{{ route('api.groups.update', ['id' => $row->id]) }}">
	<div class="row">
		<div class="col col-md-7">
			<div class="card mb-4">
				<div class="card-header">
					@if (auth()->user() && auth()->user()->can('edit groups'))
					<div class="float-right">
						<a class="btn-edit edit-hide" href="{{ route('admin.groups.edit', ['id' => $row->id]) }}" data-tip="{{ trans('global.edit') }}">
							<span class="fa fa-pencil" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('global.edit') }}</span>
						</a>
						<span class="spinner-border spinner-border-sm d-none" role="status"><span class="sr-only">{{ trans('global.loading') }}</span></span>
						<a class="btn-edit-save text-primary edit-show d-none ml-3" href="{{ route('admin.groups.edit', ['id' => $row->id]) }}" data-tip="{{ trans('global.save') }}">
							<span class="fa fa-save" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('global.save') }}</span>
						</a>
						<a class="btn-edit-cancel text-danger edit-show d-none ml-3" href="{{ route('admin.groups.show', ['id' => $row->id]) }}" data-tip="{{ trans('global.cancel') }}">
							<span class="fa fa-ban" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('global.cancel') }}</span>
						</a>
					</div>
					@endif
					<h3 class="card-title pt-0">{{ trans('global.details') }}</h3>
				</div>
				<div class="card-body">
					<div class="edit-hide">
						<dl>
							<div class="form-group">
								<dt>{{ trans('groups::groups.created') }}:</dt>
								<dd class="mx-0">{{ $row->datetimecreated->format('F j, Y') }}</dd>
							</div>

							@if ($row->trashed())
							<div class="form-group">
								<dt>{{ trans('groups::groups.removed') }}:</dt>
								<dd class="mx-0">{{ $row->datetimeremoved->format('F j, Y') }}</dd>
							</div>
							@endif

							<div class="form-group">
								<dt>{{ trans('groups::groups.name') }}:</dt>
								<dd class="mx-0">{{ $row->name }}</dd>
							</div>

							<div class="form-group">
								<dt>{{ trans('groups::groups.description') }}:</dt>
								<dd class="mx-0">{{ $row->description ? $row->description : trans('global.none') }}</dd>
							</div>

							<div class="form-group">
								<dt>{{ trans('groups::groups.unix group base name') }}:</dt>
								<dd class="mx-0">{{ $row->unixgroup ? $row->unixgroup : trans('global.none') }}</dd>
							</div>

							<div class="form-group">
								<dt>{{ trans('groups::groups.cascade managers') }}:</dt>
								<dd class="mx-0">
									@if ($row->cascademanagers)
										<span class="text-success"><span class="fa fa-check-circle" aria-hidden="true"></span> {{ trans('global.yes') }}</span>
									@else
										<span class="text-warning"><span class="fa fa-ban" aria-hidden="true"></span> {{ trans('global.no') }}</span>
									@endif
								</dd>
							</div>
						</dl>
					</div>
					@if (auth()->user() && auth()->user()->can('edit groups'))
					<div class="edit-show d-none">
						<div class="form-group">
							<label for="field-group-name">{{ trans('groups::groups.name') }} <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="fields[name]" id="field-group-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="48" value="{{ $row->name }}" />
							<span class="invalid-feedback">{{ trans('groups::groups.invalid.title') }}</span>
						</div>

						<div class="form-group">
							<label for="field-description'">{{ trans('groups::groups.description') }}</label>
							<textarea class="form-control" name="fields[description]" id="field-description" cols="50" rows="5" maxlength="5000">{{ $row->description }}</textarea>
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
								<div class="form-group">
									<label for="field-unixgroup">{{ trans('groups::groups.unix group base name') }}</label>
									<input type="text" class="form-control input-unixgroup{{ $errors->has('fields.unixgroup') ? ' is-invalid' : '' }}" name="fields[unixgroup]" id="field-unixgroup" maxlength="10" pattern="[a-z0-9\-]+" value="{{ $row->unixgroup }}" />
									<span class="form-text text-muted">{{ trans('groups::groups.unix group base name hint') }}</span>
								</div>
							</div>
							<div class="col col-md-6">
								<div class="form-group">
									<label for="field-unixid">{{ trans('groups::groups.unix id') }}</label>
									<input type="text" class="form-control" name="fields[unixid]" id="field-unixid" value="{{ $row->unixid }}" />
									<span class="form-text text-muted">{{ trans('groups::groups.unix group id') }}</span>
								</div>
							</div>
						</div>

						<div class="form-group mb-0">
							<div class="form-check">
								<input type="checkbox" name="fields[prefix_unixgroup]" id="field-prefix_unixgroup" class="form-check-input" value="1"{{ $row->prefix_unixgroup ? ' checked="checked"' : '' }} />
								<label for="field-prefix_unixgroup" class="form-check-label">{{ trans('groups::groups.prefix unixgroup') }}</label>
								<span class="form-text text-muted">{{ trans('groups::groups.prefix unixgroup desc') }}</span>
							</div>
						</div>
					</div>
					@endif
				</div>
			</div><!-- / .card -->

			<fieldset class="adminform">
				<legend>{{ trans('groups::groups.unix groups') }}</legend>

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
					@if (auth()->user() && auth()->user()->can('edit groups'))
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
					@endif
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
					@if (auth()->user() && auth()->user()->can('edit groups'))
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
					@endif
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
					@if (auth()->user() && auth()->user()->can('edit groups'))
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
					@endif
				</table>
			</fieldset>

			<input type="hidden" name="id" value="{{ $row->id }}" />
		</div>
	</div>
	@csrf
</form>
