@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('menus::menus.module name'),
		route('admin.menus.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete menus'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.menus.delete')) !!}
	@endif
	@if (auth()->user()->can('create menus'))
		{!! Toolbar::addNew(route('admin.menus.create')) !!}
	@endif
	@if (auth()->user()->can('admin menus'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('menus')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('menus::menus.menu manager') }}
@stop

@section('content')
<form action="{{ route('admin.menus.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

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

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('menus::menus.menu manager') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('menus::menus.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('menus::menus.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('menus::menus.item type') }}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('menus::menus.published items') }}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('menus::menus.unpublished items') }}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('menus::menus.trashed items') }}
				</th>
				<th scope="col">
					{{ trans('menus::menus.linked widgets') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit menus'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit menus'))
						<a href="{{ route('admin.menus.edit', ['id' => $row->id]) }}">
							{{ $row->title }}
						</a>
					@else
						{{ $row->title }}
					@endif
				</td>
				<td class="priority-4">
					@if (auth()->user()->can('edit menus'))
						<a href="{{ route('admin.menus.edit', ['id' => $row->id]) }}">
							{{ $row->menutype }}
						</a>
					@else
						{{ $row->menutype }}
					@endif
				</td>
				<td class="priority-4">
					<a href="{{ route('admin.menus.items', ['menutype' => $row->menutype]) }}">
						{{ $row->countPublishedItems() }}
					</a>
				</td>
				<td class="priority-4">
					<a href="{{ route('admin.menus.items', ['menutype' => $row->menutype]) }}">
						{{ $row->countUnpublishedItems() }}
					</a>
				</td>
				<td class="priority-4">
					<a href="{{ route('admin.menus.items', ['menutype' => $row->menutype]) }}">
						{{ $row->countTrashedItems() }}
					</a>
				</td>
				<td>
					@if (isset($widgets[$row->menutype]))
						<ul>
							@foreach ($widgets[$row->menutype] as $widget)
								<li>
									@if (auth()->user()->can('edit menus'))
										<a href="{{ route('admin.menus.widgets', ['id' => $widget->id]) }}"  title="{{ trans('menus::menus.edit widget settings') }}">
											{!! trans('menus::menus.access position', ['title' => $widget->title, 'access_title' => $widget->access_title, 'position' => $widget->position]) !!}
										</a>
									@else
										{!! trans('menus::menus.access position', ['title' => $widget->title, 'access_title' => $widget->access_title, 'position' => $widget->position]) !!}
									@endif
								</li>
							@endforeach
						</ul>
					@else
						<a class="btn btn-secondary btn-sm" href="{{ route('admin.widgets.create') }}?eid={{ $menuwidget->id }}&params[menutype]={{ $row->menutype }}">
							<i class="fa fa-plus"></i> {{ trans('menus::menus.add menu widget') }}
						</a>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop