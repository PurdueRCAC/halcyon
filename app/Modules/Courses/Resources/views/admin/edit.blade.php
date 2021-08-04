@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/courses/js/admin.js?v=' . filemtime(public_path() . '/modules/courses/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('courses::courses.module name'),
		route('admin.courses.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit courses'))
		{!! Toolbar::save(route('admin.courses.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.courses.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('courses.name') !!}
@stop

@section('content')
<form action="{{ route('admin.courses.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-userid">{{ trans('courses::courses.owner') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<span class="input-group">
						<input type="text" name="fields[userid]" id="field-userid" data-show="#field-options" class="form-control form-users redirect{{ $errors->has('fields.userid') ? ' is-invalid' : '' }}" required data-uri="{{ route('api.users.index') }}?search=%s" data-location="{{ $row->id ? route('admin.courses.edit', ['id' => $row->id]) : route('admin.courses.create') }}?userid=%s" value="{{ ($row->user ? $row->user->name . ':' . $row->userid : '') }}" />
						<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
					</span>
				</div>

				<div<?php if (!$row->userid) { echo ' class="d-none"'; } ?> id="field-options">
					<div class="form-group">
						<label for="field-type">{{ trans('courses::courses.type') }}:</label>
						<select name="type" id="field-type" class="form-control">
							<?php if ($row->id || count($classes)) { ?>
								<option value="course">{{ trans('courses::courses.course') }}</option>
							<?php } ?>
							<option value="workshop">{{ trans('courses::courses.workshop') }}</option>
						</select>
					</div>

					<div class="form-group">
						<label for="field-resourceid">{{ trans('courses::courses.resource') }}: <span class="required">{{ trans('global.required') }}</span></label>
						<select name="fields[resourceid]" id="field-resourceid" class="form-control{{ $errors->has('fields.resourceid') ? ' is-invalid' : '' }}" required>
							<option value="0">{{ trans('global.none') }}</option>
							<?php foreach ($resources as $resource): ?>
								<?php
								$selected = ($resource->id == $row->resourceid ? ' selected="selected"' : '');
								?>
								<option value="{{ $resource->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $resource->level) . $resource->name }}</option>
							<?php endforeach; ?>
						</select>
						<span class="invalid-feedback">{{ trans('courses::courses.invalid.resource') }}</span>
					</div>

					<div class="form-group">
						<label for="field-classname">{{ trans('courses::courses.course name') }}:</label>
						<input type="text" name="fields[classname]" id="field-classname" class="form-control" maxlength="255" value="{{ $row->classname }}" />
					</div>

					<?php if (!$row->id && count($classes) == 0) { ?>
						<p class="alert alert-warning">The selected user is not instructing any upcoming classes. Accounts for classes can only be created by instructors.</p>
						<input type="hidden" name="fields[semester]" id="field-semester" value="{{ $row->semester }}" />
						<input type="hidden" name="fields[crn]" id="field-crn" value="{{ $row->crn }}" />
						<input type="hidden" name="fields[coursenumber]" id="field-coursenumber"  value="{{ $row->coursenumber }}" />
						<input type="hidden" name="fields[department]" id="field-department" value="{{ $row->department }}" />
						<input type="hidden" name="fields[reference]" id="field-reference" value="{{ $row->reference }}" />
					<?php } else { ?>
						<div class="form-group type-course type-dependant">
							<label for="new_class_select">Class</label>
							<select class="form-control" id="new_class_select">
								<option value="first">(Select Class)</option>
								<?php foreach ($classes as $class) { ?>
									<option id="option_class_{{ $class->classExternalId }}"
										data-crn="{{ $class->classExternalId }}"
										data-classid="{{ $class->classId }}"
										data-userid="{{ auth()->user()->id }}"
										data-semester="{{ $class->semester }}"
										data-start="{{ $class->start }}"
										data-stop="{{ $class->stop }}"
										data-classname="{{ $class->courseTitle }}"
										data-count="<?php echo $class->enrollment ? count($class->enrollment) : 0; ?>"
										data-reference="<?php echo $class->reference; ?>"
										data-instructors="<?php echo e(json_encode($class->instructors)); ?>"
										data-students="<?php echo e('{ "students": ' . json_encode($class->student_list) . '}'); ?>">
										<?php echo $class->subjectArea . ' ' . $class->courseNumber . ' (' . $class->classExternalId . ') - ' . $class->semester; ?>
									</option>
								<?php } ?>
							</select>
						</div>

						<div class="form-group type-course type-dependant">
							<label for="field-semester">{{ trans('courses::courses.semester') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="fields[semester]" id="field-semester" class="form-control{{ $errors->has('fields.semester') ? ' is-invalid' : '' }}" required maxlength="16" value="{{ $row->semester }}" />
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group type-course type-dependant">
									<label for="field-crn">{{ trans('courses::courses.crn') }}:</label>
									<input type="text" name="fields[crn]" id="field-crn" class="form-control{{ $errors->has('fields.crn') ? ' is-invalid' : '' }}" maxlength="8" value="{{ $row->crn }}" />
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group type-course type-dependant">
									<label for="field-coursenumber">{{ trans('courses::courses.course number') }}:</label>
									<input type="text" name="fields[coursenumber]" id="field-coursenumber" class="form-control{{ $errors->has('fields.coursenumber') ? ' is-invalid' : '' }}" maxlength="255" value="{{ $row->coursenumber }}" />
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group type-course type-dependant">
									<label for="field-department">{{ trans('courses::courses.department') }}:</label>
									<input type="text" name="fields[department]" id="field-department" class="form-control{{ $errors->has('fields.department') ? ' is-invalid' : '' }}" maxlength="4" value="{{ $row->department }}" />
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group type-course type-dependant">
									<label for="field-reference">{{ trans('courses::courses.reference') }}:</label>
									<input type="text" name="fields[reference]" id="field-reference" class="form-control{{ $errors->has('fields.reference') ? ' is-invalid' : '' }}" maxlength="64" value="{{ $row->reference }}" />
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</fieldset>

		@if ($row->id)
			<fieldset class="adminform">
				<legend>{{ trans('courses::courses.members') }}</legend>

				<table>
					<caption class="sr-only">{{ trans('courses::courses.members') }}</caption>
					<thead>
						<tr>
							<th scope="col">{{ trans('courses::courses.user id') }}</th>
							<th scope="col">{{ trans('courses::courses.name') }}</th>
							<th scope="col" class="text-right"></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($row->members as $member)
						<tr id="member-{{ $member->id }}">
							<td>
								{{ $member->userid }}
							</td>
							<td>
								{{ $member->user ? $member->user->name : trans('global.unknown') }}
							</td>
							<td class="text-right">
								<a href="#member-{{ $member->id }}" class="btn btn-danger remove-member" data-id="{{ $member->id }}" data-confirm="{{ trans('global.confirm delete') }}" data-api="{{ route('api.courses.members.delete', ['id' => $row->id]) }}" data-success="Item removed">
									<span class="icon-trash"></span><span class="sr-only">{{ trans('global.delete') }}</span>
								</a>
							</td>
						</tr>
						@endforeach
						<tr class="d-none" id="member-<?php echo '{id}'; ?>">
							<td>
								<?php echo '{userid}'; ?>
							</td>
							<td>
								<?php echo '{name}'; ?>
							</td>
							<td class="text-right">
								<a href="#member-<?php echo '{id}'; ?>" class="btn btn-danger remove-member" data-id="<?php echo '{id}'; ?>" data-confirm="{{ trans('global.confirm delete') }}" data-api="{{ route('api.courses.members') }}<?php echo '{id}'; ?>" data-success="Item removed">
									<span class="icon-trash"></span><span class="sr-only">{{ trans('global.delete') }}</span>
								</a>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="2">
								<div class="form-group">
									<label for="member-userid" class="sr-only">{{ trans('courses::courses.member') }}:</label>
									<span class="input-group">
										<input type="text" name="member-userid" id="member-userid" class="form-control form-users" data-uri="{{ route('api.users.index') }}?search=%s" value="" />
										<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
									</span>
								</div>
							</td>
							<td class="text-right">
								<button class="btn btn-success add-member" data-api="{{ route('api.courses.members.create') }}" data-account="{{ $row->id }}" data-field="#member-userid" data-success="User added">
									<span class="icon-plus"></span><span class="sr-only">{{ trans('global.create') }}</span>
								</button>
							</td>
						</tr>
					</tfoot>
				</table>
			</fieldset>
		@endif
		</div>
		<div class="col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-datetimestart">{{ trans('courses::courses.date start') }}: <span class="required">{{ trans('global.required') }}</span></label>
					{!! Html::input('calendar', 'fields[datetimestart]', $row->datetimestart->format('Y-m-d'), ['required' => 'required', 'time' => false]) !!}
					<span class="invalid-feedback">{{ trans('courses::courses.invalid.start date') }}</span>
				</div>

				<div class="form-group">
					<label for="field-datetimestop">{{ trans('courses::courses.date stop') }}: <span class="required">{{ trans('global.required') }}</span></label>
					{!! Html::input('calendar', 'fields[datetimestop]', $row->datetimestop->format('Y-m-d'), ['required' => 'required', 'time' => false]) !!}
					<span class="invalid-feedback">{{ trans('courses::courses.invalid.end date') }}</span>
				</div>
			</fieldset>
		</div>
	</div>

	{!! Html::input('hidden', 'id', $row->id) !!}

	@csrf
</form>
@stop