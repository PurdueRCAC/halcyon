@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/widgets/js/admin.js?v=' . filemtime(public_path() . '/modules/widgets/js/admin.js')) }}"></script>
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

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! trans('widgets::widgets.module name') !!}
@stop

@section('content')
<form action="{{ route('admin.widgets.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-3 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-9 text-right filter-select">
				<label class="sr-only" for="filter_client_id">{{ trans('widgets::widgets.client type') }}</label>
				<select name="client_id" id="filter_client_id" class="form-control filter filter-submit">
					<option value="0"<?php if ($filters['client_id'] == '0'): echo ' selected="selected"'; endif;?>>{{ trans('widgets::widgets.client.site') }}</option>
					<option value="1"<?php if ($filters['client_id'] == '1'): echo ' selected="selected"'; endif;?>>{{ trans('widgets::widgets.client.admin') }}</option>
				</select>

				<label class="sr-only" for="filter_state">{{ trans('widgets::widgets.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('widgets::widgets.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_position">{{ trans('widgets::widgets.select position') }}</label>
				<select name="position" id="filter_position" class="form-control filter filter-submit">
					<option value="">{{ trans('widgets::widgets.all positions') }}</option>
					@foreach (App\Modules\Widgets\Helpers\Admin::getPositions($filters['client_id']) as $position)
						<option value="{{ $position->value }}"<?php if ($filters['position'] == $position->position) { echo ' selected="selected"'; } ?>>{{ $position->position }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_widget">{{ trans('widgets::widgets.select widget') }}</label>
				<select name="widget" id="filter_widget" class="form-control filter filter-submit">
					<option value="">{{ trans('widgets::widgets.all widgets') }}</option>
					@foreach (App\Modules\Widgets\Helpers\Admin::getWidgets($filters['client_id']) as $widget)
						<option value="{{ $widget->element }}"<?php if ($filters['widget'] == $widget->element) { echo ' selected="selected"'; } ?>>{{ $widget->name }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_access">{{ trans('widgets::widgets.access level') }}</label>
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

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('widgets::widgets.module name') }}</caption>
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
			?>
		@foreach ($rows as $i => $row)
			<tr class="row-{{ ($row->published ? 'published' : 'unpublished') }}">
				@if (auth()->user()->can('edit.state widgets') || auth()->user()->can('delete widgets'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-6">
					{{ $row->id }}
				</td>
				<td>
					@if ($row->checked_out)
						<a class="glyph icon-check-square warning" data-tip="{{ trans('widgets::widgets.checked out') }}">
							{{ trans('widgets::widgets.checked out') }}
						</a>
					@endif
					@if (auth()->user()->can('edit widgets'))
						<a href="{{ route('admin.widgets.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->title }}
					@if (auth()->user()->can('edit widgets'))
						</a>
					@endif
					@if (!$row->path())
						<div class="smallsub">
							<span class="icon-alert-triangle text-warning">{{ trans('widgets::widgets.error missing files') }}</span>
						</div>
					@endif
					@if ($row->note)
						<div class="smallsub">
							{{ trans('widgets::widgets.note', ['note' => $row->note]) }}
						</div>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit.state widgets'))
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
					@if (auth()->user()->can('edit widgets'))
						<a href="{{ route('admin.widgets.edit', ['id' => $row->id]) }}">
					@endif
							{{ $row->position }}
					@if (auth()->user()->can('edit widgets'))
						</a>
					@endif
				</td>
				<td class="priority-3 text-center">
					@if (auth()->user()->can('edit widgets'))
						@if ($filters['order_dir'] == 'asc')
							<span class="ordering-control">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$positions[$i-1] == $row->position), route('admin.widgets.orderup', ['id' => $row->id])) !!}</span>
							<span class="ordering-control">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$positions[$i+1] == $row->position), route('admin.widgets.orderdown', ['id' => $row->id])) !!}</span>
						@elseif ($filters['order_dir'] == 'desc')
							<span class="ordering-control">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$positions[$i-1] == $row->position), route('admin.widgets.orderup', ['id' => $row->id])) !!}</span>
							<span class="ordering-control">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$positions[$i+1] == $row->position), route('admin.widgets.orderdown', ['id' => $row->id])) !!}</span>
						@endif
					@else
						{{ $row->ordering }}
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
					<span class="badge access {{ str_replace(' ', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
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

	<div id="new-widget" class="modal fade" tabindex="-1" aria-labelledby="new-widget-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title" id="new-widget-title">{{ trans('widgets::widgets.choose type') }}</h3>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				 <div class="modal-body">

					<table id="new-modules-list" class="table table-hover adminlist">
						<caption class="sr-only">{{ trans('widgets::widgets.available widgets') }}</caption>
						<thead>
							<tr>
								<th scope="col">{{ trans('widgets::widgets.title') }}</th>
								<th scope="col">{{ trans('widgets::widgets.widget') }}</th>
							</tr>
						</thead>
						<tbody>
						@foreach ($widgets as $item)
							<tr>
								<td>
									<span class="editlinktip hasTip" title="{{ $item->name }} :: {{ $item->desc }}"><a href="{{ route('admin.widgets.create', ['eid' => $item->id]) }}">{{ $item->name }}</a></span>
								</td>
								<td>
									{{ $item->element }}
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>

	@csrf
</form>
@stop
