@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('storage::storage.module name'),
		route('admin.storage.index')
	)
	->append(
		trans('storage::storage.notification types'),
		route('admin.storage.types')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete storage'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.storage.types.delete')) !!}
	@endif

	@if (auth()->user()->can('create storage'))
		{!! Toolbar::addNew(route('admin.storage.types.create')) !!}
	@endif

	@if (auth()->user()->can('admin storage'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('storage')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('storage::storage.module name') }}: {{ trans('storage::storage.notification types') }}
@stop

@section('content')

@component('storage::admin.submenu')
	types
@endcomponent
<form action="{{ route('admin.storage.types') }}" method="get" name="adminForm" id="adminForm" class="form-inline">
	<div class="container-fluid">
		<fieldset id="filter-bar" class="row">
			<div class="col filter-search col-md-12">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
					</span>
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
				<caption class="sr-only visually-hidden">{{ trans('storage::storage.module name') }}</caption>
				<thead>
					<tr>
						@if (auth()->user()->can('delete storage'))
							<th>
								{!! Html::grid('checkall') !!}
							</th>
						@endif
						<th scope="col" class="priority-5">
							{!! Html::grid('sort', trans('storage::storage.id'), 'id', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col">
							{!! Html::grid('sort', trans('storage::storage.name'), 'name', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col" class="priority-4">
							{!! Html::grid('sort', trans('storage::storage.time period'), 'defaulttimeperiodid', $filters['order_dir'], $filters['order']) !!}
						</th>
						<th scope="col" class="priority-4 numeric">
							{{ trans('storage::storage.notifications') }}
						</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($rows as $i => $row)
						<tr>
							@if (auth()->user()->can('delete storage'))
								<td>
									{!! Html::grid('id', $i, $row->id) !!}
								</td>
							@endif
							<td class="priority-5">
								{{ $row->id }}
							</td>
							<td>
								@if (auth()->user()->can('edit storage'))
								<a href="{{ route('admin.storage.types.edit', ['id' => $row->id]) }}">
								@endif
									{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
								@if (auth()->user()->can('edit storage'))
								</a>
								@endif
							</td>
							<td class="priority-4">
								@if ($row->defaulttimeperiodid)
									{{ $row->timeperiod->name }}
								@else
									<span class="text-muted">{{ trans('global.none') }}</span>
								@endif
							</td>
							<td class="priority-4 text-right">
								{{ number_format($row->notifications_count) }}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			{{ $rows->render() }}
			</div>
		</div>
		@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
		@endif
	</div>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
@stop