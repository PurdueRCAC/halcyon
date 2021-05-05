@extends('layouts.master')

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
{!! trans('widgets::widgets.widget manager') !!}
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

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('widgets::widgets.module name') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('edit.state widgets') || auth()->user()->can('delete widgets'))
					<th>
						<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
					</th>
				@endif
				<th scope="col" class="priority-5">
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
				<th scope="col" class="priority-4">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('widgets::widgets.widget'), 'widget', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-4">
					{{ trans('widgets::widgets.pages') }}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('widgets::widgets.access'), 'access', $filters['order_dir'], $filters['order']) !!}
				</th>
				<?php /*<th scope="col">
					{{ trans('widgets::widgets.language') }}
				</th>*/ ?>
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
				<td class="priority-5">
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
							<span>{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$positions[$i-1] == $row->position), route('admin.menus.items.orderup', ['id' => $row->id])) !!}</span>
							<span>{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$positions[$i+1] == $row->position), route('admin.widgets.orderdown', ['id' => $row->id])) !!}</span>
						@elseif ($filters['order_dir'] == 'desc')
							<span>{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$positions[$i-1] == $row->position), route('admin.menus.items.orderup', ['id' => $row->id])) !!}</span>
							<span>{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$positions[$i+1] == $row->position), route('admin.widgets.orderdown', ['id' => $row->id])) !!}</span>
						@endif
					@else
						{{ $row->ordering }}
					@endif
				</td>
				<td class="priority-4">
					{{ $row->widget }}
				</td>
				<td class="priority-4">
					<?php
					$pages = $row->pages;
					if (is_null($row->pages))
					{
						$pages = trans('global.none');
					}
					elseif ($row->pages < 0)
					{
						$pages = trans('widgets::widgets.all except selected');
					}
					elseif ($row->pages > 0)
					{
						$pages = trans('widgets::widgets.selected only');
					}
					else
					{
						$pages = trans('global.all');
					}
					echo $pages;
					?>
				</td>
				<td class="priority-4">
					<span class="badge access {{ str_replace(' ', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
				</td>
				<?php /*<td class="priority-5 center">
					<?php if ($row->language == ''): ?>
						{{ trans('global.default') }}
					<?php elseif ($row->language == '*'): ?>
						{{ trans('global.all') }}
					<?php else: ?>
						<?php echo $row->language_title ? e($row->language_title) : trans('global.undefined'); ?>
					<?php endif; ?>
				</td>*/ ?>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	<div id="new-widget" class="hide" title="{{ trans('widgets::widgets.choose type') }}">
		<h2 class="modal-title sr-only">{{ trans('widgets::widgets.choose type') }}</h2>

		<div class="card">
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

	@csrf
</form>

@stop

@section('scripts')
<script>
function validate(){
	var value = $('#menu_assignment').val(),
		list  = $('#menu-assignment');

	if (value == '-' || value == '0') {
		$('.btn-assignments').each(function(i, el) {
			$(el).prop('disabled', true);
		});
		list.find('input').each(function(i, el){
			$(el).prop('disabled', true);
			if (value == '-'){
				$(el).prop('checked', false);
			} else {
				$(el).prop('checked', true);
			}
		});
	} else {
		$('.btn-assignments').each(function(i, el) {
			$(el).prop('disabled', false);
		});
		list.find('input').each(function(i, el){
			$(el).prop('disabled', false);
		});
	}
}

$(document).ready(function() {
	var dialog = $("#new-widget").dialog({
		autoOpen: false,
		height: 400,
		width: 500,
		modal: true
	});

	$('#toolbar-plus').on('click', function(e){
		e.preventDefault();

		dialog.dialog("open");
	});

	if ($('#moduleorder').length) {
		data = $('#moduleorder');

		if (data.length) {
			modorders = JSON.parse(data.html());

			var html = '\n	<select class="form-control" id="' + modorders.name.replace('[', '-').replace(']', '') + '" name="' + modorders.name + '" id="' + modorders.id + '"' + modorders.attr + '>';
			var i = 0,
				key = modorders.originalPos,
				orig_key = modorders.originalPos,
				orig_val = modorders.originalOrder;
			for (x in modorders.orders) {
				if (modorders.orders[x][0] == key) {
					var selected = '';
					if ((orig_key == key && orig_val == modorders.orders[x][1])
					 || (i == 0 && orig_key != key)) {
						selected = 'selected="selected"';
					}
					html += '\n		<option value="' + modorders.orders[x][1] + '" ' + selected + '>' + modorders.orders[x][2] + '</option>';
				}
				i++;
			}
			html += '\n	</select>';

			$('#moduleorder').after(html);
		}
	}

	$('#menu_assignment-dependent').hide();
	if ($('#menu_assignment').val() != '0'
	 && $('#menu_assignment').val() != '-')
	{
		$('#menu_assignment-dependent').show();
	}

	$('#menu_assignment').on('change', function(){
		if ($(this).val() != '0'
		 && $(this).val() != '-')
		{
			$('#menu_assignment-dependent').show();
		}
		else
		{
			$('#menu_assignment-dependent').hide();
		}
	});
});
</script>
@stop