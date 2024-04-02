@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/software/js/site.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('software::software.software'),
		route('site.software.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create')),
		($row->id ? route('site.software.edit', ['id' => $row->id]) : route('site.software.create'))
	);
@endphp

@section('title') {{ trans('software::software.module name') }}: {{ $row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create') }} @stop

@section('content')
<div class="pull-right">
	<a href="{{ route('site.software.index') }}" class="btn btn-secondary">{{ trans('software::software.back') }}</a>
</div>

<h2>{{ trans('software::software.module name') }}: {{ $row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create') }}</h2>

<form action="{{ route('site.software.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" enctype="multipart/form-data">

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
		<div class="col col-md-8">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-type">{{ trans('software::software.type') }} <span class="required">{{ trans('global.required') }}</span></label>
					<select name="type_id" id="field-type" class="form-control" required>
						@foreach ($types as $type)
							<option value="{{ $type->id }}" data-alias="{{ $type->alias }}" <?php if ($row->type_id == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->title }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<label for="field-title">{{ trans('software::software.title') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="title" id="field-title" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}" required maxlength="255" value="{{ $row->title }}" />
					<span class="invalid-feedback">{{ trans('software::software.invalid.title') }}</span>
					{!! $errors->first('title', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<div class="form-group">
					<label for="field-alias">{{ trans('software::software.alias') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="alias" id="field-alias" class="form-control{{ $errors->has('alias') ? ' is-invalid' : '' }}" required maxlength="255" value="{{ $row->alias }}" />
					<span class="invalid-feedback">{{ trans('software::software.invalid.alias') }}</span>
					{!! $errors->first('alias', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('software::software.description') }}:</label>
					<textarea name="description" id="field-description" class="form-control" rows="3" cols="50" maxlength="500">{{ $row->description }}</textarea>
					<span class="invalid-feedback">{{ trans('software::software.error.invalid description') }}</span>
				</div>

				<div class="form-group">
					<label for="field-content">{{ trans('software::software.content') }}:</label>
					{!! editor('content', $row->content, ['id' => 'field-content', 'rows' => 35, 'class' => ($errors->has('content') ? 'is-invalid' : '')]) !!}
					<span class="invalid-feedback">{{ trans('software::software.error.invalid content') }}</span>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<legend>{{ trans('software::software.versions') }}</legend>

				<?php
				$i = 0;
				?>
				@foreach ($row->versions()->orderBy('title', 'asc')->get() as $version)
					<div class="row version" id="version{{ $i }}">
						<div class="col-md-3">
							<div class="form-group">
								<label for="version{{ $i }}-title" class="sr-only visually-hidden">{{ trans('software::software.versions') }}</label>
								<input type="text" name="version[{{ $i }}][title]" id="version{{ $i }}-title" class="form-control" value="{{ $version->title }}" />
							</div>
							<input type="hidden" name="version[{{ $i }}][id]" value="{{ $version->id }}" />
						</div>
						<div class="col-md-8">
							<?php
							$r = $version->associations->pluck('resource_id')->toArray();
							?>
							<div class="form-group">
								<label for="version{{ $i }}-resources" class="sr-only visually-hidden">{{ trans('software::software.resources') }}</label>
								<select class="form-control resources-select" id="version{{ $i }}-resources" name="version[{{ $i }}][resources][]" multiple="multiple" data-placeholder="Select resources ...">
									<?php
									foreach ($resources as $resource):
										?>
										<option value="{{ $resource->id }}"<?php if (in_array($resource->id, $r)) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
										<?php
									endforeach;
									?>
								</select>
							</div>
						</div>
						<div class="col-md-1">
							<button class="btn text-danger remove-version" data-target="version{{ $i }}">
								<span class="fa fa-trash" aria-hidden="true"></span>
								<span class="sr-only visually-hidden">{{ trans('global.button.trash') }}</span>
							</button>
						</div>
					</div>
					<?php
					$i++;
					?>
				@endforeach

				<div class="row d-none" id="version<?php echo '{{id}}'; ?>">
					<div class="col-md-3">
						<div class="form-group">
							<label for="version<?php echo '{{id}}'; ?>-title" class="sr-only visually-hidden">{{ trans('software::software.versions') }}</label>
							<input type="text" name="version[<?php echo '{{id}}'; ?>][title]" id="version<?php echo '{{id}}'; ?>-title" class="form-control" value="" />
						</div>
						<input type="hidden" name="version[<?php echo '{{id}}'; ?>][id]" value="" />
					</div>
					<div class="col-md-8">
						<div class="form-group">
							<label for="version<?php echo '{{id}}'; ?>-resources" class="sr-only visually-hidden">{{ trans('software::software.resources') }}</label>
							<select class="form-control" id="version<?php echo '{{id}}'; ?>-resources" name="version[<?php echo '{{id}}'; ?>][resources][]" multiple="multiple">
								@foreach ($resources as $resource)
									<option value="{{ $resource->id }}">{{ $resource->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-md-1">
						<button class="btn text-danger remove-version" data-target="version<?php echo '{{id}}'; ?>">
							<span class="fa fa-trash" aria-hidden="true"></span>
							<span class="sr-only visually-hidden">{{ trans('global.button.trash') }}</span>
						</button>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12 text-right">
						<button class="btn btn-success add-version" data-length="{{ $i }}" data-target="version<?php echo '{{id}}'; ?>">
							<span class="fa fa-plus" aria-hidden="true"></span>
							<span class="sr-only visually-hidden">{{ trans('global.button.add') }}</span>
						</button>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-4">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-state">{{ trans('global.state') }}</label>
					<select name="state" id="field-state" class="form-control">
						<option value="1"<?php if ($row->state): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
						<option value="0"<?php if (!$row->state): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					</select>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-8 text-center">
			<input type="submit" class="btn btn-success" value="Save" />
			<a href="{{ route('site.software.index') }}" class="btn btn-link">{{ trans('global.button.cancel') }}</a>
		</div>
	</div>
	

	<input type="hidden" name="id" value="{{ $row->id }}" />
	@csrf
</form>
@stop