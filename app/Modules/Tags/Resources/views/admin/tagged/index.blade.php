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
{!! config('tags.name') !!}
@stop

@section('content')
<form action="{{ route('admin.tags.tagged') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar">
		<div class="form-group filter-search">
			<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
			<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search']) }}" />

			<button type="submit" class="btn btn-secondary">{{ trans('search.submit') }}</button>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('tags::tags.tagged') }}</caption>
		<thead>
			<tr>
				<th>
					<span class="form-check"><input type="checkbox" name="toggle" value="" id="toggle-all" class="form-check-input checkbox-toggle toggle-all" /><label for="toggle-all"></label></span>
				</th>
				<th scope="col" class="priority-5">{{ trans('tags::tags.id') }}</th>
				<th scope="col">{{ trans('tags::tags.type') }}</th>
				<th scope="col">{{ trans('tags::tags.type id') }}</th>
				<th scope="col" class="priority-4">{{ trans('tags::tags.created') }}</th>
				<th scope="col" class="priority-4">{{ trans('tags::tags.created by') }}</th>
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
					<a href="{{ route('admin.tags.tagged.edit', ['id' => $row->id]) }}">
						{{ $row->taggable_type }}
					</a>
				</td>
				<td>
					<a href="{{ route('admin.tags.tagged.edit', ['id' => $row->id]) }}">
						{{ $row->taggable_id }}
					</a>
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
				<td>
					{{ $row->tagger->name }}
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
