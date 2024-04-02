@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/handlebars/handlebars.min-v4.7.7.js') }}"></script>
<script src="{{ timestamped_asset('modules/finder/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('finder::finder.module name'),
		route('admin.finder.index')
	)
	->append(
		trans('finder::finder.facets'),
		route('admin.finder.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit finder'))
		{!! Toolbar::save(route('admin.finder.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.finder.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('finder.name') !!}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.finder.store') }}" method="post" name="adminForm" id="item-form" class="editform">
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
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-name">{{ trans('finder::finder.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('finder::finder.description') }}</label>
					<textarea name="fields[description]" id="field-description" class="form-control{{ $errors->has('fields.description') ? ' is-invalid' : '' }}" rows="5" cols="50">{{ $row->description }}</textarea>
				</div>

				<fieldset>
					<legend>{{ trans('finder::finder.control type') }}</legend>

					<div class="form-group">
						<div class="form-check">
							<input type="radio" name="fields[control_type]" id="field-control_type-radio" class="form-check-input" value="radio"<?php if ($row->control_type == 'radio') { echo ' checked="checked"'; } ?> />
							<label for="field-control_type-radio" class="form-check-label">{{ trans('finder::finder.radio') }}</label>
						</div>
					</div>

					<div class="form-group">
						<div class="form-check">
							<input type="radio" name="fields[control_type]" id="field-control_type-checkbox" class="form-check-input" value="checkbox"<?php if ($row->control_type == 'radio') { echo ' checked="checked"'; } ?> />
							<label for="field-control_type-checkbox" class="form-check-label">{{ trans('finder::finder.checkbox') }}</label>
						</div>
					</div>
				</fieldset>

				<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
			</fieldset>
		</div>
		<div class="col col-md-6">
			<fieldset class="adminform">
				<legend>{{ trans('finder::finder.choices') }}</legend>

				<div id="choices">
				@foreach ($row->choices()->orderBy('weight', 'asc')->get() as $k => $choice)
				<fieldset id="choice-{{ $k }}">
					<?php
					$matches = $choice->services->pluck('service_id')->toArray();
					?>
					<div class="row">
						<div class="col-md-10">
							<div class="form-group">
								<label for="choice-{{ $k }}-name">{{ trans('finder::finder.value') }}</label>
								<input type="text" name="choice[{{ $k }}][name]" id="choice-{{ $k }}-name" class="form-control{{ $errors->has('fields.name') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $choice->name }}" />
								<input type="hidden" name="choice[{{ $k }}][id]" id="choice-{{ $k }}-id" value="{{ $choice->id }}" />
							</div>
						</div>
						<div class="col-md-2 text-right">
							<button class="btn remove-choice" data-confirm="{{ trans('finder::finder.confirm delete') }}" data-target="#choice-{{ $k }}">
								<span class="fa fa-trash text-danger" aria-hidden="true" data-tip="{{ trans('finder::finder.remove choice') }}"></span>
								<span class="sr-only visually-hidden">{{ trans('finder::finder.remove choice') }}</span>
							</button>
						</div>
					</div>

					<div class="row">
						<div class="col">
							<?php
							$i = 0;
							?>
							@foreach ($services as $service)
								<div class="form-group-wrap">
									<div class="form-check">
										<input type="checkbox" name="choice[{{ $k }}][matches]" id="choice-{{ $k }}-{{ $service->id }}" class="form-check-input" value="{{ $service->id }}"<?php if (in_array($service->id, $matches)) { echo ' checked="checked"'; } ?> />
										<label for="choice-{{ $k }}-{{ $service->id }}" class="form-check-label">{{ $service->title }}</label>
									</div>
								</div>
								<?php
								$i++;
								if ($i == 8):
									?>
						</div>
						<div class="col">
									<?php
									$i = 0;
								endif;
								?>
							@endforeach
						</div>
					</div>
				</fieldset>
				@endforeach
				</div>
				<div class="text-right">
					<button class="btn btn-secondary add-choice" id="add-choice" data-template="#choice-template" data-container="#choices">{{ trans('finder::finder.add choice') }}</button>
				</div>

				<script type="text/x-handlebars-template" id="choice-template">
				<fieldset id="choice-<?php echo '{{i}}'; ?>">
					<div class="row">
						<div class="col-md-10">
							<div class="form-group">
								<label for="choice-<?php echo '{{i}}'; ?>-name">{{ trans('finder::finder.value') }}</label>
								<input type="text" name="choice[<?php echo '{{i}}'; ?>][name]" id="choice-<?php echo '{{i}}'; ?>-name" class="form-control" required maxlength="250" value="" />
								<input type="hidden" name="choice[<?php echo '{{i}}'; ?>][id]" id="choice-<?php echo '{{i}}'; ?>-id" value="" />
							</div>
						</div>
						<div class="col-md-2 text-right">
							<button class="btn remove-choice" data-confirm="{{ trans('finder::finder.confirm delete') }}" data-target="#choice-<?php echo '{{i}}'; ?>">
								<span class="fa fa-trash text-danger" aria-hidden="true" data-tip="{{ trans('finder::finder.remove choice') }}"></span>
								<span class="sr-only visually-hidden">{{ trans('finder::finder.remove choice') }}</span>
							</button>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<?php
							$i = 0;
							?>
							@foreach ($services as $service)
								<div class="form-group-wrap">
									<div class="form-check">
										<input type="checkbox" name="choice[<?php echo '{{i}}'; ?>][matches]" id="choice-<?php echo '{{i}}'; ?>-{{ $service->id }}" class="form-check-input" value="{{ $service->id }}" />
										<label for="choice-<?php echo '{{i}}'; ?>-{{ $service->id }}" class="form-check-label">{{ $service->title }}</label>
									</div>
								</div>
								<?php
								$i++;
								if ($i == 8):
									?>
						</div>
						<div class="col">
									<?php
									$i = 0;
								endif;
								?>
							@endforeach
						</div>
					</div>
				</fieldset>
				</script>
			</fieldset>

		</div>
	</div>

	@csrf
</form>
@stop