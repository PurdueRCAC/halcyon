@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/widgets/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('widgets::widgets.module name'),
		route('admin.widgets.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('manage widgets'))
		{!!
			Toolbar::checkin(route('admin.widgets.checkin'));
			Toolbar::spacer();
		!!}
	@endif

	@if (auth()->user()->can('edit.state widgets'))
		{!!
			Toolbar::publishList(route('admin.widgets.publish'));
			Toolbar::unpublishList(route('admin.widgets.unpublish'));
			Toolbar::spacer();
		!!}
	@endif

	@if (auth()->user()->can('delete widgets'))
		{!! Toolbar::deleteList(trans('widgets::widgets.confirm delete'), route('admin.widgets.delete')) !!}
	@endif

	@if (auth()->user()->can('create widgets'))
		{!! Toolbar::addNew(route('admin.widgets.create')) !!}
	@endif

	@if (auth()->user()->can('admin widgets'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('widgets')
		!!}
	@endif

	{!! Toolbar::help('widgets::help.index') !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('widgets::widgets.module name') }}
@stop

@section('content')
<form action="{{ route('admin.widgets.index') }}" method="get" name="adminForm" id="adminForm">

	<nav class="container-fluid" aria-label="{{ trans('users::users.module sections') }}">
		<ul class="nav nav-tabs">
			<li class="nav-item">
				<a href="{{ route('admin.widgets.index', ['client_id' => 0]) }}" class="nav-link<?php if ($filters['client_id'] == '0'): echo ' active'; endif;?>">{{ trans('widgets::widgets.client.site') }}</a>
			</li>
			<li class="nav-item">
				<a href="{{ route('admin.widgets.index', ['client_id' => 1]) }}" class="nav-link<?php if ($filters['client_id'] == '1'): echo ' active'; endif;?>">{{ trans('widgets::widgets.client.admin') }}</a>
			</li>
		</ul>
	</nav><!-- / .sub-navigation -->

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-3 filter-search">
				<label class="sr-only visually-hidden form-label" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
				</span>
			</div>
			<div class="col">
				<label class="sr-only visually-hidden form-label" for="filter_state">{{ trans('widgets::widgets.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('widgets::widgets.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>
			</div>
			<div class="col">
				<label class="sr-only visually-hidden form-label" for="filter_position">{{ trans('widgets::widgets.select position') }}</label>
				<select name="position" id="filter_position" class="form-control filter filter-submit">
					<option value="">{{ trans('widgets::widgets.all positions') }}</option>
					@foreach (App\Modules\Widgets\Helpers\Admin::getPositions($filters['client_id']) as $position)
						<option value="{{ $position->position }}"<?php if ($filters['position'] == $position->position) { echo ' selected="selected"'; } ?>>{{ $position->position }}</option>
					@endforeach
				</select>
			</div>
			<div class="col">
				<label class="sr-only visually-hidden form-label" for="filter_widget">{{ trans('widgets::widgets.select widget') }}</label>
				<select name="widget" id="filter_widget" class="form-control filter filter-submit">
					<option value="">{{ trans('widgets::widgets.all widgets') }}</option>
					@foreach (App\Modules\Widgets\Helpers\Admin::getWidgets($filters['client_id']) as $widget)
						<option value="{{ $widget->element }}"<?php if ($filters['widget'] == $widget->element) { echo ' selected="selected"'; } ?>>{{ $widget->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="col">
				<label class="sr-only visually-hidden form-label" for="filter_access">{{ trans('widgets::widgets.access level') }}</label>
				<select name="access" id="filter_access" class="form-control filter filter-submit">
					<option value="">{{ trans('widgets::widgets.all levels') }}</option>
					@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
						<option value="{{ $access->id }}"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
					@endforeach
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
					<caption class="sr-only visually-hidden">{{ trans('widgets::widgets.module name') }}</caption>
					<thead>
						<tr>
							@if (auth()->user()->can('edit.state widgets') || auth()->user()->can('delete widgets'))
								<th>
									<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
								</th>
							@endif
							<th scope="col" class="priority-6">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('widgets::widgets.id'), 'id', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('widgets::widgets.title'), 'title', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('widgets::widgets.state'), 'state', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-4">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('widgets::widgets.position'), 'position', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-3">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('widgets::widgets.ordering'), 'ordering', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-5">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('widgets::widgets.widget'), 'widget', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-6">
								{{ trans('widgets::widgets.pages') }}
							</th>
							<th scope="col" class="priority-4">
								{!! Html::grid('sort', trans('widgets::widgets.access'), 'access', $filters['order_dir'], $filters['order']) !!}
							</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$positions = $rows->pluck('position')->toArray();
					$canEditState = auth()->user()->can('edit.state widgets');
					$canEdit = auth()->user()->can('edit widgets');
					$canDelete = auth()->user()->can('delete widgets');
					?>
					@foreach ($rows as $i => $row)
						<tr class="row-{{ ($row->published ? 'published' : 'unpublished') }}">
							@if ($canEditState || $canDelete)
								<td>
									{!! Html::grid('id', $i, $row->id) !!}
								</td>
							@endif
							<td class="priority-6">
								{{ $row->id }}
							</td>
							<td>
								@if ($row->checked_out)
									@php
									$user = App\Modules\Users\Models\User::find($row->checked_out);
									@endphp
									@if (auth()->user()->can('admin'))
									<a href="{{ route('admin.widgets.checkin', ['id[]' => $row->id]) }}" class="text-warning" data-tip="{{ trans('widgets::widgets.checked out', ['name' => $user ? $user->name : trans('global.unknown')]) }}">
										<span class="fa fa-check-square" aria-hidden="true"></span>
										<span class="sr-only visually-hidden">{{ trans('widgets::widgets.checked out', ['name' => $user ? $user->name : trans('global.unknown')]) }}</span>
									</a>
									@else
									<span class="text-warning" data-tip="{{ trans('widgets::widgets.checked out', ['name' => $user ? $user->name : trans('global.unknown')]) }}">
										<span class="fa fa-check-square" aria-hidden="true"></span>
										<span class="sr-only visually-hidden">{{ trans('widgets::widgets.checked out', ['name' => $user ? $user->name : trans('global.unknown')]) }}</span>
									</span>
									@endif
								@endif
								@if ($canEdit)
									<a href="{{ route('admin.widgets.edit', ['id' => $row->id]) }}">
								@endif
									{!! App\Halcyon\Utility\Str::highlight(e($row->title), $filters['search']) !!}
								@if ($canEdit)
									</a>
								@endif
								@if (!$row->path())
									<div class="smallsub">
										<span class="fa fa-exclamation-triangle text-warning">{{ trans('widgets::widgets.error missing files') }}</span>
									</div>
								@endif
								@if ($row->note)
									<div class="smallsub">
										{{ trans('widgets::widgets.note', ['note' => $row->note]) }}
									</div>
								@endif
							</td>
							<td>
								@if ($canEditState)
									@if ($row->published)
										<a class="badge badge-success" data-tip="{{ trans('widgets::widgets.click to unpublish') }}" href="{{ route('admin.widgets.unpublish', ['id' => $row->id]) }}">
											{{ trans('widgets::widgets.published') }}
										</a>
									@else
										<a class="badge badge-secondary" data-tip="{{ trans('widgets::widgets.click to publish') }}" href="{{ route('admin.widgets.publish', ['id' => $row->id]) }}">
											{{ trans('widgets::widgets.unpublished') }}
										</a>
									@endif
								@else
									@if ($row->published)
										<span class="badge badge-success">
											{{ trans('widgets::widgets.published') }}
										</span>
									@else
										<span class="badge badge-secondary">
											{{ trans('widgets::widgets.unpublished') }}
										</span>
									@endif
								@endif
							</td>
							<td class="priority-4">
								@if ($canEdit)
									<a href="{{ route('admin.widgets.edit', ['id' => $row->id]) }}">
								@endif
										{{ $row->position }}
								@if ($canEdit)
									</a>
								@endif
							</td>
							<td class="priority-3 text-center">
								<span class="badge badge-secondary">{{ $row->ordering }}</span>
								@if ($canEdit)
									@if ($filters['order_dir'] == 'asc')
										<span class="ordering-control">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$positions[$i-1] == $row->position), route('admin.widgets.orderup', ['id' => $row->id])) !!}</span>
										<span class="ordering-control">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$positions[$i+1] == $row->position), route('admin.widgets.orderdown', ['id' => $row->id])) !!}</span>
									@elseif ($filters['order_dir'] == 'desc')
										<span class="ordering-control">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$positions[$i-1] == $row->position), route('admin.widgets.orderup', ['id' => $row->id])) !!}</span>
										<span class="ordering-control">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$positions[$i+1] == $row->position), route('admin.widgets.orderdown', ['id' => $row->id])) !!}</span>
									@endif
								@endif
							</td>
							<td class="priority-5">
								{{ $row->widget }}
							</td>
							<td class="priority-6">
								<?php
								$pages = $row->pages;
								if (is_null($row->pages)):
									$pages = trans('global.none');
								elseif ($row->pages < 0):
									$pages = trans('widgets::widgets.all except selected');
								elseif ($row->pages > 0):
									$pages = trans('widgets::widgets.selected only');
								else:
									$pages = trans('global.all');
								endif;
								echo $pages;
								?>
							</td>
							<td class="priority-4">
								<span class="badge access {{ str_replace(' ', '', strtolower($row->viewlevel->title)) }}">{{ $row->viewlevel->title }}</span>
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

	<div id="new-widget" class="modal fade" tabindex="-1" aria-labelledby="new-widget-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title" id="new-widget-title">{{ trans('widgets::widgets.choose type') }}</h3>
					<button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
						<span class="visually-hidden" aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<div class="form-group mb-3">
						<label for="widget-search" class="sr-only visually-hidden form-label">{{ trans('widgets::widgets.search') }}</label>
						<input type="search" id="widget-search" class="form-control" placeholder="{{ trans('widgets::widgets.search') }} ..." value="" />
					</div>

					<div class="row" id="new-widgets-list">
						@foreach ($widgets as $item)
							<div class="col-md-4 mb-3 widget">
								<div class="card h-100">
									<img class="card-img-top" src="{{ $item->image }}" alt="" />
									<div class="card-body">
										<div class="card-title">{{ $item->name }}</div>
										@if ($item->desc)
											<p class="card-text">{{ $item->desc }}</p>
										@endif
									</div>
									<div class="card-footer text-center">
										<a class="btn btn-block btn-outline-secondary" href="{{ route('admin.widgets.create', ['eid' => $item->id]) }}">
											Select
										</a>
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
@stop
