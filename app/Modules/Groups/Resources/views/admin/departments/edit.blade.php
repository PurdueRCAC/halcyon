@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/select2/js/select2.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/groups/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('groups::groups.module name'),
		route('admin.groups.index')
	)
	->append(
		trans('groups::groups.departments'),
		route('admin.groups.departments')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit groups'))
		{!! Toolbar::save(route('admin.groups.departments.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.groups.departments.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('groups::groups.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.groups.departments.store') }}" method="post" name="adminForm" id="item-form" class="editform">
	<div class="row">
		<div class="col col-md-7 mx-auto">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-parentid">{{ trans('groups::groups.parent') }}</label>
					<select name="fields[parentid]" id="field-parentid" class="form-control searchable-select">
						<option value="1">{{ trans('global.none') }}</option>
						<?php foreach ($parents as $parent): ?>
							<?php
							if ($parent->level == 0 || $parent->id == $row->id):
								continue;
							endif;

							$ancestors = collect($parent->ancestors());
							$ids = $ancestors->pluck('id')->toArray();

							if ($parent->parentid > 1 && in_array($row->id, $ids)):
								continue;
							endif;

							$selected = ($parent->id == $row->parentid ? ' selected="selected"' : '');
							?>
							<option value="{{ $parent->id }}"<?php echo $selected; ?>><?php
								foreach ($ancestors as $ancestor):
									if (!$ancestor->parentid):
										continue;
									endif;
									?>{{ $ancestor->name }} &rsaquo; <?php
								endforeach;
								echo e($parent->name);
							?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('groups::groups.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->name }}" />
				</div>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
			</fieldset>
		</div>
	</div>

	@csrf
</form>
@stop