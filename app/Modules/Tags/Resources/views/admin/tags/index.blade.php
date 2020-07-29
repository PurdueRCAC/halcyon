@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('tags::tags.module name'),
		route('admin.tags.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('create tags'))
		{!! Toolbar::addNew(route('admin.tags.delete')) !!}
	@endif

	@if (auth()->user()->can('delete tags'))
		{!! Toolbar::deleteList('', route('admin.tags.delete')) !!}
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
{!! config('tags.name') !!}
@stop

@section('content')
<form action="{{ route('admin.tags.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar">
		<div class="form-group filter-search">
			<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
			<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

			<button type="submit" class="btn btn-secondary">{{ trans('search.submit') }}</button>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('tags::tags.tags') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('tags::tags.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('tags::tags.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('tags::tags.slug'), 'slug', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3">{{ trans('tags::tags.tagged') }}</th>
				<th scope="col" class="priority-3">{{ trans('tags::tags.aliases') }}</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('tags::tags.created'), 'created', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit tags'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit tags'))
						<a href="{{ route('admin.tags.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
				</td>
				<td class="priority-2">
					@if (auth()->user()->can('edit tags'))
						<a href="{{ route('admin.tags.edit', ['id' => $row->id]) }}">
							{{ $row->slug }}
						</a>
					@else
						{{ $row->slug }}
					@endif
				</td>
				<td class="priority-3">
					{{ $row->tagged_count }}
				</td>
				<td class="priority-3">
					{{ $row->alias_count }}
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->getOriginal('created_at') && $row->getOriginal('created_at') != '0000-00-00 00:00:00')
							<time datetime="{{ $row->created_at }}">{{ $row->created_at }}</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>
@stop
