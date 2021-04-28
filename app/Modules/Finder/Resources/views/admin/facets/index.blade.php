@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('finder::finder.module name'),
		route('admin.finder.index')
	)
	->append(
		trans('finder::finder.finder'),
		route('admin.finder.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete finder'))
		{!! Toolbar::deleteList('', route('admin.finder.delete')) !!}
	@endif

	@if (auth()->user()->can('create finder'))
		{!! Toolbar::addNew(route('admin.finder.create')) !!}
	@endif

	@if (auth()->user()->can('admin finder'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('finder')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('finder.name') !!}
@stop

@section('content')
@component('finder::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.finder.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="btn input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col col-md-9 text-right">
				<label class="sr-only" for="filter_state">{{ trans('finder::finder.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('finder::finder.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('finder::finder.finder') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete finder'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('finder::finder.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('finder::finder.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('finder::finder.control type'), 'control_type', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('finder::finder.status'), 'status', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete finder'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit finder'))
						<a href="{{ route('admin.finder.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit finder'))
						</a>
					@endif
				</td>
				<td>
					{{ $row->control_type }}
				</td>
				<td class="priority-4">
					@if ($row->status)
						@if (auth()->user()->can('edit finder'))
							<a class="badge badge-success" href="{{ route('admin.finder.unpublish', ['id' => $row->id]) }}" data-tip="{{ trans('finder::finder.set state to', ['state' => trans('global.unpublished')]) }}">
						@endif
							{{ trans('finder::finder.published') }}
						@if (auth()->user()->can('edit finder'))
							</a>
						@endif
					@else
						@if (auth()->user()->can('edit finder'))
							<a class="badge badge-secondary" href="{{ route('admin.finder.publish', ['id' => $row->id]) }}" data-tip="{{ trans('finder::finder.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('finder::finder.unpublished') }}
						@if (auth()->user()->can('edit finder'))
							</a>
						@endif
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop
