@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/resources/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.resources'),
		route('admin.resources.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit resources'))
		{!! Toolbar::save(route('admin.resources.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.resources.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('resources::resources.module name') }}: <?php echo $row->id ? trans('globak.edit') . ': #' . $row->id : trans('global.create'); ?>
@stop

@section('content')
<form action="{{ route('admin.resources.store') }}" method="post" name="adminForm" id="item-form" class="editform">
	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	@if ($row->id && count($sections))
	<nav class="container-fluid">
		<ul id="user-tabs" class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation">
				<a class="nav-link active" href="#resource-details" data-toggle="tab" data-bs-toggle="tab" role="tab" id="resource-details-tab" aria-controls="resource-details" aria-selected="true">{{ trans('global.details') }}</a>
			</li>
			@foreach ($sections as $k => $section)
				<li class="nav-item" role="presentation">
					<a class="nav-link" href="#resource-{{ $k }}" data-toggle="tab" data-bs-toggle="tab" role="tab" id="resource-{{ $k }}-tab" aria-controls="resource-{{ $k }}" aria-selected="false">{{ $section['name'] }}</a>
				</li>
			@endforeach
		</ul>
	</nav>
	<div class="tab-content" id="user-tabs-content">
	@endif

		<div class="tab-pane show active" id="resource-details" role="tabpanel" aria-labelledby="resource-details-tab">
			<div class="row">
				<div class="col-sm-12 col-md-7">
					<fieldset class="adminform">
						<legend>{{ trans('global.details') }}</legend>

						<!-- <div class="row">
							<div class="col-md-6"> -->
								<div class="form-group">
									<label for="field-resourcetype">{{ trans('resources::assets.type') }}</label>
									<select name="fields[resourcetype]" id="field-resourcetype" class="form-control">
										<option value="0">{{ trans('global.none') }}</option>
										@foreach ($types as $type)
											<option value="{{ $type->id }}"<?php if ($row->resourcetype == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
										@endforeach
									</select>
								</div>
							<!-- </div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="field-producttype">{{ trans('resources::assets.product type') }}</label>
									<select name="fields[producttype]" id="field-producttype" class="form-control">
										<option value="0">{{ trans('global.none') }}</option>
										<option value="1"<?php if ($row->producttype == 1): echo ' selected="selected"'; endif;?>>{{ trans('resources::assets.cluster') }}</option>
										<option value="2"<?php if ($row->producttype == 2): echo ' selected="selected"'; endif;?>>{{ trans('resources::assets.hardware') }}</option>
										<option value="3"<?php if ($row->producttype == 3): echo ' selected="selected"'; endif;?>>{{ trans('resources::assets.service') }}</option>
									</select>
								</div>
							</div>
						</div> -->

						<div class="form-group">
							<label for="field-parentid">{{ trans('resources::assets.parent') }}</label>
							<select name="fields[parentid]" class="form-control">
								<option value="0">{{ trans('global.none') }}</option>
								@foreach ($parents as $parent)
									<?php $selected = ($parent->id == $row->parentid ? ' selected="selected"' : ''); ?>
									<option value="{{ $parent->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $parent->level) . $parent->name }}</option>
								@endforeach
							</select>
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-6">
								<div class="form-group">
									<label for="field-batchsystem">{{ trans('resources::assets.batchsystem') }}</label>
									<select name="fields[batchsystem]" class="form-control">
										<option value="0">{{ trans('global.none') }}</option>
										@foreach ($batchsystems as $batchsystem)
											<?php $selected = ($batchsystem->id == $row->batchsystem ? ' selected="selected"' : ''); ?>
											<option value="{{ $batchsystem->id }}"<?php echo $selected; ?>>{{ $batchsystem->name }}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-xs-12 col-md-6">
								<div class="form-group">
									<label for="field-access">{{ trans('resources::assets.access') }}:</label>
									<select class="form-control" name="fields[access]" id="field-access">
										<option value="0"<?php if ($row->access == 0) { echo ' selected="selected"'; } ?>>- {{ trans('global.default') }} -</option>
										@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
											<option value="{{ $access->id }}"<?php if ($row->access == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="field-name">{{ trans('resources::assets.name') }} <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="fields[name]" id="field-name" class="form-control" required maxlength="32" value="{{ $row->name }}" />
							<span class="invalid-feedback">{{ trans('resources::assets.invalid.name') }}</span>
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-6">
								<div class="form-group">
									<label for="field-rolename">{{ trans('resources::assets.role name') }}</label>
									<input type="text" name="fields[rolename]" id="field-rolename" class="form-control" maxlength="32" pattern="[a-zA-Z0-9_\-]" value="{{ $row->rolename }}" />
									<span class="form-text text-muted">{{ trans('resources::assets.role name desc') }}</span>
								</div>
							</div>
							<div class="col-xs-12 col-md-6">
								<div class="form-group">
									<label for="field-importhostname">{{ trans('resources::assets.list name') }}</label>
									<input type="text" name="fields[listname]" id="field-listname" class="form-control" maxlength="32" pattern="[a-zA-Z0-9_\-]" value="{{ $row->listname }}" />
									<span class="form-text text-muted">{{ trans('resources::assets.list name desc') }}</span>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="field-description">{{ trans('resources::assets.description') }}</label>
							{!! editor('fields[description]', $row->description, ['rows' => 5, 'maxlength' => 2000, 'id' => 'field-description']) !!}
						</div>
					</fieldset>
				</div>
				<div class="col-sm-12 col-md-5">
					<fieldset class="adminform">
						<legend>{{ trans('resources::assets.options') }}</legend>

						<div class="type-dependent type-0 <?php if ($row->resourcetype) { echo 'd-none'; } ?>">
							<div class="text-center m-4">
								<div class="display-4 text-muted"><span class="fa fa-sliders" aria-hidden="true"></span></div>
								<p>{{ trans('resources::assets.options desc') }}</p>
							</div>
						</div>

						@foreach ($types as $type)
							<div class="type-dependent type-{{ $type->id }} <?php if ($type->id != $row->resourcetype) { echo 'd-none'; } ?>">
								@if (count($type->facetTypes))
									@foreach ($type->facetTypes as $field)
										@include('resources::fields.' . $field->type)
									@endforeach
								@else
									<div class="text-center m-4">
										<div class="display-4 text-muted"><span class="fa fa-sliders" aria-hidden="true"></span></div>
										<p>{{ trans('resources::assets.no options') }}</p>
									</div>
								@endif
							</div>
						@endforeach
					</fieldset>
				</div>
			</div>
		</div><!-- / #resource-details -->
	@if ($row->id && count($sections))
		@foreach ($sections as $section)
			<div class="tab-pane" id="resource-{{ $k }}" role="tabpanel" aria-labelledby="resource-{{ $k }}-tab">
				{!! $section['content'] !!}
			</div><!-- / resource-{{ $k }} -->
		@endforeach
	</div><!-- / .tab-content -->
	@endif

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop
