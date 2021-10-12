@extends('layouts.master')

@section('content')
<h2>{!! config('resources.name') !!}</h2>

<form action="{{ route('site.resources.index') }}" method="post" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="form-inline">
		<legend>Filter</legend>
		<div class="row">
			<div class="col-sm-6 filter-search span4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
				</div>

				<button type="submit" class="btn btn-default">{{ trans('search.submit') }}</button>
			</div>
			<div class="col-sm-6 filter-select span8">
				<div class="form-group">
					<label class="sr-only" for="filter_type">{{ trans('resources::assets.type') }}</label>
					<select name="type" id="filter_type" class="form-control filter filter-submit">
						<option value="0">{{ trans('resources::assets.all types') }}</option>
						<?php foreach ($types as $type): ?>
							<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					<span class="form-check"><input type="checkbox" name="toggle" value="" id="toggle-all" class="form-check-input checkbox-toggle toggle-all" /><label for="toggle-all"></label></span>
				</th>
				<th scope="col" class="priority-5"><?php echo trans('resources::assets.id'); ?></th>
				<th scope="col"><?php echo trans('resources::assets.name'); ?></th>
				<th scope="col"><?php echo trans('resources::assets.role name'); ?></th>
				<th scope="col" class="priority-4">{{ trans('resources::assets.list name') }}</th>
				<th scope="col" class="priority-3">{{ trans('resources::assets.type') }}</th>
				<th scope="col" class="priority-4">{{ trans('resources::assets.created') }}</th>
				<th scope="col" class="priority-2">{{ trans('resources::assets.removed') }}</th>
				<th scope="col"><?php echo trans('resources::assets.resources'); ?></th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					<a href="{{ app('request')->root() }}/admin/resources/edit/{{ $row->id }}">
						{{ $row->name }}
					</a>
				</td>
				<td>
					<a href="{{ app('request')->root() }}/admin/resources/edit/{{ $row->id }}">
						{{ $row->rolename }}
					</a>
				</td>
				<td class="priority-4">
					<a href="{{ app('request')->root() }}/admin/resources/edit/{{ $row->id }}">
						{{ $row->listname }}
					</a>
				</td>
				<td class="priority-3">
					{{ $row->type->name }}
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datetimecreated && $row->datetimecreated != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimecreated }}">{{ $row->datetimecreated }}</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->trashed())
							<time datetime="{{ $row->datetimeremoved }}">{{ $row->datetimeremoved }}</time>
						@else
							<span class="never">{{ trans('global.never') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<a href="{{ app('request')->root() }}/admin/resources/children/{{ $row->id }}">
						0
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop