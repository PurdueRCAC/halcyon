@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.types'),
		route('admin.resources.types')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete resources'))
		{!! Toolbar::deleteList('', route('admin.resources.types.delete')) !!}
	@endif

	@if (auth()->user()->can('create resources.types'))
		{!! Toolbar::addNew(route('admin.resources.types.create')) !!}
	@endif

	@if (auth()->user()->can('admin resources.types'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('resources');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('resources.name') !!}: {{ trans('resources::resources.types') }}
@stop

@section('content')
@component('resources::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.resources.types') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('resources::resources.types') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete resources.types'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('resources::assets.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('resources::assets.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('resources::assets.description'), 'description', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{{ trans('resources::assets.resources') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete resources.types'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit resources.types'))
					<a href="{{ route('admin.resources.types.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit resources.types'))
					</a>
					@endif
				</td>
				<td class="priority-2">
					@if (auth()->user()->can('edit resources.types'))
					<a href="{{ route('admin.resources.types.edit', ['id' => $row->id]) }}">
					@endif
						@if ($row->description)
							{{ Illuminate\Support\Str::limit(strip_tags($row->description), 70) }}
						@else
							<span class="text-muted none">{{ trans('global.none') }}</span>
						@endif
					@if (auth()->user()->can('edit resources.types'))
					</a>
					@endif
				</td>
				<td class="priority-4 text-right">
					<a href="{{ route('admin.resources.index') }}?type={{ $row->id }}">
						{{ number_format($row->resources_count) }}
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>

	{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop
