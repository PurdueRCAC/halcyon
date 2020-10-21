@extends('layouts.master')

@section('toolbar')
	@if (auth()->user()->can('create users.roles'))
		{!! Toolbar::addNew(route('admin.users.roles.create')) !!}
	@endif

	@if (auth()->user()->can('delete users.roles'))
		{!! Toolbar::deleteList('', route('admin.users.roles.delete')) !!}
	@endif

	@if (auth()->user()->can('admin users'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('users')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('users.name') !!}: {{ trans('users::users.roles') }}
@stop

@section('content')

@component('users::admin.submenu')
	@if (request()->segment(3) == 'levels')
		levels
	@else
		roles
	@endif
@endcomponent

<form action="{{ route('admin.users.roles') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar">
		<div class="filter-search">
			<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
			<input type="text" name="filter_search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="" />
		</div>

		<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('users::users.roles') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', 'users::users.id', 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', 'users::access.title', 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3 text-right">
					{{ trans('users::access.users') }}
				</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$canEdit = auth()->user()->can('edit users.roles');
		?>
		@foreach ($rows as $i => $row)
			<?php
			$level = $row->countDescendents();
			?>
			<tr>
				<td class="center">
					@if ($canEdit)
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="center priority-4">
					{{ $row->id }}
				</td>
				<td>
					{!! str_repeat('<span class="gi">|&mdash;</span>', $level) !!}
					@if ($canEdit)
						<a href="{{ route('admin.users.roles.edit', ['id' => $row->id]) }}">
							{{ $row->title }}
						</a>
					@else
						{{ $row->title }}
					@endif
				</td>
				<td class="text-right">
					{{ number_format($row->maps_count) }}
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop