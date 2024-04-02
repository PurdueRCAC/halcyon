@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('tags::tags.module name'),
		route('admin.tags.index')
	)
	->append(
		trans('tags::tags.tagged')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit tags'))
		{!! Toolbar::deleteList('', route('admin.tags.delete')) !!}
		{!! Toolbar::addNew(route('admin.tags.delete')) !!}
	@endif

	@if (auth()->user()->can('admin tags'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('tags')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('tags::tags.module name') }}: {{ trans('tags::tags.tagged') }}
@stop

@section('content')
<form action="{{ route('admin.tags.tagged') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid mb-3">
		<div class="row">
			<div class="col-md-3 mb-2 filter-search">
				<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
				</span>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button type="submit" class="btn btn-secondary sr-only visually-hidden">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<caption class="sr-only visually-hidden">{{ trans('tags::tags.tagged') }}</caption>
					<thead>
						<tr>
							<th>
								{!! Html::grid('checkall') !!}
							</th>
							<th scope="col" class="priority-5">
								{!! Html::grid('sort', trans('tags::tags.id'), 'id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('tags::tags.taggable type'), 'taggable_type', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('tags::tags.taggable id'), 'taggable_id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col" class="priority-4">
								{!! Html::grid('sort', trans('tags::tags.created'), 'created_at', $filters['order_dir'], $filters['order']) !!}
							</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($rows as $i => $row)
						<tr>
							<td>
								{!! Html::grid('id', $i, $row->id) !!}
							</td>
							<td class="priority-5">
								{{ $row->id }}
							</td>
							<td>
								{{ $row->taggable_type }}
							</td>
							<td>
								{{ $row->taggable_id }}
							</td>
							<td class="priority-4">
								<span class="datetime">
									@if ($row->created_at)
										<time datetime="{{ $row->created_at->toDateTimeLocalString() }}">{{ $row->created_at->format('Y-m-d') }}</time>
									@else
										<span class="never">{{ trans('global.unknown') }}</span>
									@endif
								</span>
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
