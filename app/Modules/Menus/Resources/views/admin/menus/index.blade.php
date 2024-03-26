@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/menus/js/menus.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var btnnew = document.getElementById('toolbar-plus');
	if (btnnew) {
		btnnew.setAttribute('data-toggle', 'modal');
		btnnew.setAttribute('data-target', '#new-menu');

		btnnew.setAttribute('data-bs-toggle', 'modal');
		btnnew.setAttribute('data-bs-target', '#new-menu');

		btnnew.addEventListener('click', function (e) {
			e.preventDefault();
		});
	}
});
</script>
@endpush

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
<form action="{{ route('admin.menus.index') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid mb-3">
		<div class="row">
			<div class="col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
				</span>
			</div>
			<div class="col-md-6">
			</div>
			<div class="col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*">{{ trans('menus::menus.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published') { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
			<table class="table table-hover adminlist">
				<caption class="sr-only visually-hidden">{{ trans('menus::menus.menu manager') }}</caption>
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
								<span class="sr-only visually-hidden">Menu requires a published widget to be visible.</span>
							@endif
							@if (auth()->user()->can('edit menus'))
								<a data-href="{{ route('admin.menus.edit', ['id' => $row->id]) }}" href="#item{{ $row->id }}" data-toggle="modal" data-bs-toggle="modal">
									{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
								</a>

								<div id="item{{ $row->id }}" class="modal fade" tabindex="-1" aria-labelledby="item{{ $row->id }}-title" aria-hidden="true">
									<div class="modal-dialog modal-dialog-slideout modal-dialog-scrollable">
										<div class="modal-content">
											<div class="modal-header">
												<h3 class="modal-title" id="item{{ $row->id }}-title">{{ trans('global.edit') . ' #' . $row->id }}</h3>
												<button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>
											<div class="modal-body">
												<form action="{{ route('admin.menus.store') }}" method="post">
													<div class="form-group">
														<label for="field-title">{{ trans('menus::menus.title') }} <span class="required">{{ trans('global.required') }}</span></label>
														<input type="text" name="title" id="field-title" class="form-control" required maxlength="250" value="{{ $row->title }}" />
														<span class="invalid-feedback">{{ trans('menus::menus.invalid.title') }}</span>
													</div>

													<div class="form-group">
														<label for="field-menutype">{{ trans('menus::menus.item type') }} <span class="required">{{ trans('global.required') }}</span></label>
														<input type="text" name="menutype" id="field-menutype" class="form-control{{ $errors->has('fields.menutype') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->menutype }}" />
														<span class="form-text text-muted">{{ trans('menus::menus.menutype hint') }}</span>
													</div>

													<div class="form-group">
														<label for="field-description">{{ trans('menus::menus.description') }}</label>
														<textarea name="description" id="field-description" class="form-control" maxlength="255" rows="2" cols="40">{{ $row->description }}</textarea>
													</div>

													<div class="form-group mb-0 text-center">
														<input type="submit" class="btn btn-success" value="{{ trans('global.button.save') }}" data-api="{{ route('api.menus.update', ['id' => $row->id]) }}" />
													</div>

													<input type="hidden" name="id" value="{{ $row->id }}" />
													@csrf
												</form>
											</div>
										</div>
									</div>
								</div>
							@else
								{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
							@endif
							@if ($row->description)
								<br /><span class="text-muted">{{ $row->description }}</span>
							@endif
						</td>
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
		<div class="placeholder py-4 mx-auto text-center">
			<div class="placeholder-body p-4">
				<span class="fa fa-ban display-4 text-muted" aria-hidden="true"></span>
				<p>{{ trans('global.no results') }}</p>
			</div>
		</div>
	@endif

	<input type="hidden" name="boxchecked" value="0" />
</form>

<div id="new-menu" class="modal fade" tabindex="-1" aria-labelledby="new-menu-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="new-menu-title">{{ trans('menus::menus.create menu') }}</h3>
				<button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="{{ route('admin.menus.store') }}" method="post">
					<div class="form-group">
						<label for="field-title">{{ trans('menus::menus.title') }} <span class="required">{{ trans('global.required') }}</span></label>
						<input type="text" name="title" id="field-title" class="form-control" required maxlength="250" value="" />
						<span class="invalid-feedback">{{ trans('menus::menus.invalid.title') }}</span>
					</div>

					<div class="form-group">
						<label for="field-menutype">{{ trans('menus::menus.item type') }} <span class="required">{{ trans('global.required') }}</span></label>
						<input type="text" name="menutype" id="field-menutype" class="form-control{{ $errors->has('fields.menutype') ? ' is-invalid' : '' }}" required maxlength="250" value="" />
						<span class="form-text text-muted">{{ trans('menus::menus.menutype hint') }}</span>
					</div>

					<div class="form-group">
						<label for="field-description">{{ trans('menus::menus.description') }}</label>
						<textarea name="description" id="field-description" class="form-control" maxlength="255" rows="2" cols="40"></textarea>
					</div>

					<div class="form-group mb-0 text-center">
						<input type="submit" class="btn btn-success" value="{{ trans('global.button.save') }}" />
					</div>
					@csrf
				</form>
			</div>
		</div>
	</div>
</div>
@stop