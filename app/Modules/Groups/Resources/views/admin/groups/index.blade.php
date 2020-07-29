@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('groups::groups.module name'),
		route('admin.groups.index')
	)
	->append(
		trans('groups::groups.groups'),
		route('admin.groups.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('create groups'))
		{!! Toolbar::addNew(route('admin.groups.create')) !!}
	@endif

	@if (auth()->user()->can('delete groups'))
		{!! Toolbar::deleteList('', route('admin.groups.delete')) !!}
	@endif

	@if (auth()->user()->can('admin groups'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('groups')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('groups.name') !!}
@stop

@section('content')
@component('groups::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.groups.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

				<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
			</div>
			<div class="col col-md-8 text-right">
				<label class="sr-only" for="filter_fieldofscience">{{ trans('groups::groups.field of science') }}</label>
				<select name="fieldofscience" id="filter_fieldofscience" class="form-control filter-submit">
					<option value="0">{{ trans('groups::groups.select field of science') }}</option>
				</select>

				<label class="sr-only" for="filter_department">{{ trans('groups::groups.department') }}</label>
				<select name="department" id="filter_department" class="form-control filter-submit">
					<option value="0">{{ trans('groups::groups.select department') }}</option>
					@foreach ($departments as $department)
						@php
						if ($department->level == 0):
							continue;
						endif;
						@endphp
						<option value="{{ $department->id }}"<?php if ($filters['department'] == $department->id) { echo ' selected="selected"'; } ?>>{{ str_repeat('|- ', ($department->level - 1)) . $department->name }} (<?php echo App\Modules\Groups\Models\GroupDepartment::where('collegedeptid', '=', $department->id)->count(); ?>)</option>
					@endforeach
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('groups::groups.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.unix group'), 'unixgroup', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('groups::groups.members'), 'members_count', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('groups::groups.department') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit groups'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit groups'))
						<a href="{{ route('admin.groups.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit groups'))
						</a>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit groups'))
						<a href="{{ route('admin.groups.edit', ['id' => $row->id]) }}">
					@endif
						<?php echo $row->unixgroup ? e($row->unixgroup) : '<span class="unknown">' . trans('global.none') . '</span>'; ?>
					@if (auth()->user()->can('edit groups'))
						</a>
					@endif
				</td>
				<td class="priority-4">
					<a href="{{ route('admin.groups.members', ['group' => $row->id]) }}">
						{{ $row->members_count }}
					</a>
				</td>
				<td class="priority-4">
					<?php
					$departments = array();
					foreach ($row->departmentList as $d)
					{
						$departments[] = $d->name;
					}
					echo !empty($departments) ? e(implode(', ', $departments)) : '<span class="unknown">' . trans('global.none') . '</span>';
					?>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop
