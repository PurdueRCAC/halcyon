@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('menus::menus.module name'),
		route('admin.menus.index')
	);
@endphp

@section('toolbar')
	@if ($filters['state'] == 'trashed' || $filters['state'] == '*')
		@if (auth()->user()->can('edit.state menus'))
			{!! Toolbar::publishList(route('admin.menus.restore'), 'Restore') !!}
		@endif
	@endif
	@if ($filters['state'] != 'trashed')
		@if (auth()->user()->can('delete menus'))
			{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.menus.delete')) !!}
		@endif
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

	{!! Toolbar::help('menus::admin.help.menus') !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('menus::menus.menu manager') }}
@stop

@section('content')
<form action="{{ route('admin.menus.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-3">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-9 text-right filter-select">
				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*">{{ trans('menus::menus.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published') { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
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
		<caption class="sr-only">{{ trans('menus::menus.menu manager') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('menus::menus.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('menus::menus.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<?php /*<th scope="col" class="priority-3">
					{{ trans('menus::menus.item type') }}
				</th>*/ ?>
				<th scope="col" class="priority-4 text-right">
					{{ trans('menus::menus.published items') }}
				</th>
				<th scope="col" class="priority-6 text-right">
					{{ trans('menus::menus.unpublished items') }}
				</th>
				<th scope="col" class="priority-6 text-right">
					{{ trans('menus::menus.trashed items') }}
				</th>
				<th scope="col" class="priority-5">
					{{ trans('menus::menus.linked widgets') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr<?php if ($row->trashed()) { echo ' class="trashed"'; } ?>>
				<td>
					@if (auth()->user()->can('edit menus'))
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-6">
					{{ $row->id }}
				</td>
				<td>
					@if (!isset($widgets[$row->menutype]))
						<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true" data-tip="Menu requires a published widget to be visible."></span>
						<span class="sr-only">Menu requires a published widget to be visible.</span>
					@endif
					@if (auth()->user()->can('edit menus'))
						<a href="{{ route('admin.menus.edit', ['id' => $row->id]) }}">
							{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
						</a>
					@else
						{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
					@endif
				</td>
				<?php /*<td class="priority-3">
					@if (auth()->user()->can('edit menus'))
						<a href="{{ route('admin.menus.edit', ['id' => $row->id]) }}">
							{{ $row->menutype }}
						</a>
					@else
						{{ $row->menutype }}
					@endif
				</td>*/ ?>
				<td class="priority-4 text-right">
					<a href="{{ route('admin.menus.items', ['menutype' => $row->menutype]) }}">
						{{ number_format($row->countPublishedItems()) }}
					</a>
				</td>
				<td class="priority-6 text-right">
					<a href="{{ route('admin.menus.items', ['menutype' => $row->menutype]) }}">
						{{ number_format($row->countUnpublishedItems()) }}
					</a>
				</td>
				<td class="priority-6 text-right">
					<a href="{{ route('admin.menus.items', ['menutype' => $row->menutype]) }}">
						{{ number_format($row->countTrashedItems()) }}
					</a>
				</td>
				<td class="priority-5">
					@if (isset($widgets[$row->menutype]))
						<ul>
							@foreach ($widgets[$row->menutype] as $widget)
								<li>
									@if (auth()->user()->can('edit menus'))
										<a href="{{ route('admin.widgets.edit', ['id' => $widget->id]) }}" title="{{ trans('menus::menus.edit widget settings') }}">
											{!! trans('menus::menus.access position', ['title' => $widget->title, 'access_title' => $widget->access_title, 'position' => $widget->position]) !!}
										</a>
									@else
										{!! trans('menus::menus.access position', ['title' => $widget->title, 'access_title' => $widget->access_title, 'position' => $widget->position]) !!}
									@endif
								</li>
							@endforeach
						</ul>
					@else
						<a class="btn btn-secondary btn-sm" href="{{ route('admin.widgets.create') }}?eid={{ $menuwidget ? $menuwidget->id : '' }}&amp;params[menutype]={{ $row->menutype }}">
							<span class="fa fa-plus" aria-hidden="true"></span> {{ trans('menus::menus.add menu widget') }}
						</a>
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
</form>
@stop