@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/courses/js/admin.js?v=' . filemtime(public_path() . '/modules/courses/js/admin.js')) }}"></script>
<script>
$( document ).ready(function() {
	$('.type-dependant').hide();
	//$('.type-'+$('[name="type"]').val()).show();
	//$('.menu-page').fadeIn();
	$('[name="type"]')
		.on('change', function(){
			$('.type-dependant').hide();
			$('.type-'+$(this).val()).show();

			/*if ($(this).val() == 'separator') {
				if (!$('#fields_title').val()) {
					$('#fields_title').val('[ separator ]');
				}
			}*/
		})
		.each(function(i, el){
			$('.type-'+$(el).val()).show();
		});

	$('#fields_page_id').on('change', function(e){
		if ($('#fields_title').val() == '') {
			$('#fields_title').val($(this).children("option:selected").text().replace(/\|\— /g, ''));
		}
	});
});
</script>
@stop

@php
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
	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-type">{{ trans('courses::courses.type') }}:</label>
					<select name="type" id="field-type" class="form-control">
						<option value="course">{{ trans('courses::courses.course') }}</option>
						<option value="workshop">{{ trans('courses::courses.workshop') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-classname">{{ trans('courses::courses.course name') }}:</label>
					<input type="text" name="fields[classname]" id="field-classname" class="form-control" maxlength="250" value="{{ $row->classname }}" />
				</div>

				<div class="form-group type-course type-dependant">
					<label for="field-semester">{{ trans('courses::courses.semester') }}:</label>
					<input type="text" name="fields[semester]" id="field-semester" class="form-control" maxlength="250" value="{{ $row->semester }}" />
				</div>

				<div class="form-group type-course type-dependant">
					<label for="field-crn">{{ trans('courses::courses.crn') }}:</label>
					<input type="text" name="fields[crn]" id="field-crn" class="form-control" maxlength="250" value="{{ $row->crn }}" />
				</div>

				<div class="form-group type-course type-dependant">
					<label for="field-coursenumber">{{ trans('courses::courses.course number') }}:</label>
					<input type="text" name="fields[coursenumber]" id="field-coursenumber" class="form-control" maxlength="250" value="{{ $row->coursenumber }}" />
				</div>

				<div class="form-group type-course type-dependant">
					<label for="field-department">{{ trans('courses::courses.department') }}:</label>
					<input type="text" name="fields[department]" id="field-department" class="form-control" maxlength="250" value="{{ $row->department }}" />
				</div>

				<div class="form-group type-course type-dependant">
					<label for="field-reference">{{ trans('courses::courses.reference') }}:</label>
					<input type="text" name="fields[reference]" id="field-reference" class="form-control" maxlength="250" value="{{ $row->reference }}" />
				</div>

				<div class="form-group">
					<label for="field-userid">{{ trans('courses::courses.owner') }}:</label>
					<span class="input-group">
						<input type="text" name="fields[userid]" id="field-userid" class="form-control form-users" data-uri="{{ route('api.users.index') }}?search=%s" value="{{ ($row->user ? $row->user->name . ':' . $row->userid : '') }}" />
						<span class="input-group-append"><span class="input-group-text icon-user"></span></span>
					</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-datetimestart">{{ trans('courses::courses.date start') }}:</label>
					{!! Html::input('calendar', 'fields[datetimestart]', $row->datetimestart) !!}
				</div>

				<div class="form-group">
					<label for="field-datetimestop">{{ trans('courses::courses.date stop') }}:</label>
					{!! Html::input('calendar', 'fields[datetimestop]', $row->datetimestop) !!}
				</div>
			</fieldset>
		</div>
	</div>

	{!! Html::input('hidden', 'id', $row->id) !!}

	@csrf
</form>
@stop