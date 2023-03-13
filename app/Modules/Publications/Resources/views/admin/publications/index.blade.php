@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('publications::publications.module name'),
		route('admin.publications.index')
	)
	->append(
		trans('publications::publications.publications')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit.state publications'))
		{!!
			Toolbar::publishList(route('admin.publications.publish'));
			Toolbar::unpublishList(route('admin.publications.unpublish'));
			Toolbar::spacer();
		!!}
	@endif
	@if (auth()->user()->can('delete publications'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.publications.delete')) !!}
	@endif
	@if (auth()->user()->can('create publications'))
		{!! Toolbar::addNew(route('admin.publications.create')) !!}
	@endif
	@if (auth()->user()->can('admin publications'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('publications')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('publications::publications.module name') }}
@stop

@section('content')

<form action="{{ route('admin.publications.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col filter-search col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col filter-select col-md-8 text-right">
				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_type">{{ trans('publications::publications.type') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value="0">{{ trans('publications::publications.all types') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					@endforeach
				</select>
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
		<caption class="sr-only">{{ trans('publications::publications.publications') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('publications::publications.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('publications::publications.type'), 'type_id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('publications::publications.author'), 'author', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('publications::publications.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{!! Html::grid('sort', trans('publications::publications.year'), 'published_at', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit publications'))
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-6">
					{{ $row->id }}
				</td>
				<td class="priority-3">
					@if ($row->type)
						@if (auth()->user()->can('edit publications'))
							<a href="{{ route('admin.publications.edit', ['id' => $row->id]) }}">
								{{ $row->type->name }}
							</a>
						@else
							{{ $row->type->name }}
						@endif
					@endif
				</td>
				<td class="priority-6">
					@if (auth()->user()->can('edit publications'))
						<a href="{{ route('admin.publications.edit', ['id' => $row->id]) }}">
							{{ $row->author }}
						</a>
					@else
						{{ $row->author }}
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit publications'))
						<a href="{{ route('admin.publications.edit', ['id' => $row->id]) }}">
							{{ $row->title }}
						</a>
					@else
						{{ $row->title }}
					@endif
				</td>
				<td class="priority-4 text-right">
					{{ $row->published_at->format('Y') }}
				</td>
				<td class="priority-3">
					@if ($row->trashed())
						@if (auth()->user()->can('edit publications'))
							<a class="badge badge-secondary state trashed" href="{{ route('admin.publications.restore', ['id' => $row->id]) }}" data-tip="{{ trans('publications::publications.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('publications::publications.trashed') }}
						@if (auth()->user()->can('edit publications'))
							</a>
						@endif
					@elseif ($row->isPublished())
						@if (auth()->user()->can('edit publications'))
							<a class="badge badge-success" href="{{ route('admin.publications.unpublish', ['id' => $row->id]) }}" data-tip="{{ trans('publications::publications.set state to', ['state' => trans('global.unpublished')]) }}">
						@endif
							{{ trans('publications::publications.published') }}
						@if (auth()->user()->can('edit publications'))
							</a>
						@endif
					@else
						@if (auth()->user()->can('edit publications'))
							<a class="badge badge-secondary" href="{{ route('admin.publications.publish', ['id' => $row->id]) }}" data-tip="{{ trans('publications::publications.set state to', ['state' => trans('global.published')]) }}">
						@endif
							{{ trans('publications::publications.unpublished') }}
						@if (auth()->user()->can('edit publications'))
							</a>
						@endif
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

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop