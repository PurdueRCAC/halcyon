@extends('layouts.master')

@php
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
{!! config('groups.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.groups.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.VALIDATION_FORM_FAILED') }}">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group" data-hint="{{ trans('groups::groups.name hint') }}">
					<label for="field-name">{{ trans('groups::groups.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-unixgroup">{{ trans('groups::groups.unix group base name') }}:</label>
							<input type="text" class="form-control" name="fields[unixgroup]" id="field-unixgroup" value="{{ $row->unixgroup }}" />
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group">
							<label for="field-unixid">{{ trans('groups::groups.unix id') }}:</label>
							<input type="text" class="form-control" name="fields[unixid]" id="field-unixid" value="{{ $row->unixid }}" />
						</div>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('groups::groups.unix groups') }}</legend>

				<table>
					<thead>
						<tr>
							<th scope="col">{{ trans('groups::groups.unix group') }}</th>
							<th scope="col" class="text-right">{{ trans('groups::groups.members') }}</th>
							<th scope="col" class="text-right"></th>
						</tr>
					</thead>
					<tbody>
					@foreach ($row->unixGroups as $i => $u)
						<tr id="unixgroup-{{ $u->id }}" data-id="{{ $u->id }}">
							<td>{{ $u->longname }}</td>
							<td class="text-right">{{ $u->members()->count() }}</td>
							<td class="text-right">
								<a href="#" class="btn btn-secondary btn-danger"><span class="icon-trash glyph">{{ trans('global.trash') }}</span></a>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>

				<p class="text-right">
					<button class="btn btn-secondary btn-success"><span class="icon-plus glyph">{{ trans('global.add') }}</span></button>
				</p>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('groups::groups.department') }}</legend>

				<table>
					<tbody>
					@foreach ($row->departments as $dept)
						<tr id="department-{{ $dept->id }}" data-id="{{ $dept->id }}">
							<td>{{ $dept->department->name }}</td>
							<td class="text-right">
								<a href="#" class="btn btn-secondary btn-danger"><span class="icon-trash glyph">{{ trans('global.trash') }}</span></a>
							</td>
						</tr>
					@endforeach
					</tbody>
					<tfoot>
						<tr>
							<td>
								<select name="department" class="form-control">
									<option value="0">{{ trans('groups::groups.select department') }}</option>
									@foreach ($departments as $d)
										@php
										if ($d->level == 0):
											continue;
										endif;
										@endphp
										<option value="{{ $d->id }}">{{ str_repeat('- ', $d->level) . $d->name }}</option>
									@endforeach
								</select>
							</td>
							<td class="text-right">
								<button class="btn btn-secondary btn-success"><span class="icon-plus glyph">{{ trans('global.add') }}</span></button>
							</td>
						</tr>
					</tfoot>
				</table>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('groups::groups.field of science') }}</legend>

				<table>
					<tbody>
					@foreach ($row->fieldsOfScience as $field)
						<tr id="fieldofscience-{{ $field->id }}" data-id="{{ $field->id }}">
							<td>{{ $field->field->name }}</td>
							<td class="text-right">
								<a href="#" class="btn btn-secondary btn-danger"><span class="icon-trash glyph">{{ trans('global.trash') }}</span></a>
							</td>
						</tr>
					@endforeach
					</tbody>
					<tfoot>
						<tr>
							<td>
								<select name="fieldofscience" class="form-control">
									<option value="0">{{ trans('groups::groups.select field of science') }}</option>
									@foreach ($fields as $f)
										@php
										if ($f->level == 0):
											continue;
										endif;
										@endphp
										<option value="{{ $f->id }}">{{ str_repeat('- ', $f->level) . $f->name }}</option>
									@endforeach
								</select>
							</td>
							<td class="text-right">
								<button class="btn btn-secondary btn-success"><span class="icon-plus glyph">{{ trans('global.add') }}</span></button>
							</td>
						</tr>
					</tfoot>
				</table>
			</fieldset>

			@if ($row->id)
				<div class="data-wrap">
					<h4>{{ trans('pages::pages.history') }}</h4>
					<ul class="entry-log">
						<?php
						$history = $row->history()->orderBy('created_at', 'desc')->get();
						//$prev = 0;
						if (count($history)):
							foreach ($history as $action):
								$actor = trans('global.unknown');

								if ($action->user):
									$actor = e($action->user->name);
								endif;

								$created = $action->created_at && $action->created_at != '0000-00-00 00:00:00' ? $action->created_at : trans('global.unknown');
								//$length = $action->length - $prev;

								$fields = array_keys(get_object_vars($action->new));
								foreach ($fields as $i => $k)
								{
									if (in_array($k, ['created_at', 'updated_at', 'deleted_at']))
									{
										unset($fields[$i]);
									}
								}
								?>
								<li>
									<span class="entry-log-data">{{ trans('groups::groups.history edited', ['user' => $actor, 'datetime' => $created]) }}</span><br />
									<span class="entry-diff">Changed fields: <?php echo implode(', ', $fields); ?></span>
								</li>
								<?php
							endforeach;
						else:
							?>
							<li>
								<span class="entry-diff">No history found.</span>
							</li>
							<?php
						endif;
						?>
					</ul>
				</div>
			@endif
		</div>
	</div>

	@csrf
</form>
@stop