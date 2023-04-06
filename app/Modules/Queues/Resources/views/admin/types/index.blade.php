@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.types')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete queues.types'))
		{!! Toolbar::deleteList('', route('admin.queues.types.delete')) !!}
	@endif

	@if (auth()->user()->can('create queues.types'))
		{!! Toolbar::addNew(route('admin.queues.types.create')) !!}
	@endif

	@if (auth()->user()->can('admin queues'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('queues')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('queues::queues.module name') }}: {{ trans('queues::queues.types') }}
@stop

@section('content')
@component('queues::admin.submenu')
	{{ request()->segment(3) }}
@endcomponent

<form action="{{ route('admin.queues.types') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
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
		<caption class="sr-only">{{ trans('queues::queues.types') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete queues.types'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('queues::queues.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('queues::queues.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{{ trans('queues::queues.queues') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete queues.types'))
					<td data-th="Select">
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td data-th="{{ trans('queues::queues.id') }}" class="priority-5">
					{{ $row->id }}
				</td>
				<td data-th="{{ trans('queues::queues.name') }}">
					@if (auth()->user()->can('edit queues.types'))
						<a href="{{ route('admin.queues.types.edit', ['id' => $row->id]) }}">
							{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
						</a>
					@else
						{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
					@endif
				</td>
				<td data-th="{{ trans('queues::queues.queues') }}" class="priority-4 text-right">
					@if (auth()->user()->can('edit queues.types'))
						<a href="{{ route('admin.queues.index', ['type' => $row->id]) }}">
							{{ number_format($row->queues_count) }}
						</a>
					@else
						{{ number_format($row->queues_count) }}
					@endif
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
</form>

@stop
