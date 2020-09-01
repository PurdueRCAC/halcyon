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

	@if (auth()->user()->can('create widgets'))
		{!! Toolbar::addNew(route('admin.widgets.create')) !!}
	@endif

	@if (auth()->user()->can('delete widgets'))
		{!! Toolbar::deleteList(trans('widgets::widgets.confirm delete'), route('admin.widgets.delete')) !!}
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
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

				<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
			</div>
			<div class="col col-md-9 text-right filter-select">
				<label class="sr-only" for="filter_client_id">{{ trans('widgets::widgets.client type') }}</label>
				<select name="client_id" id="filter_client_id" class="form-control filter filter-submit">
					<option value="0"<?php if ($filters['client_id'] == '0'): echo ' selected="selected"'; endif;?>>{{ trans('widgets::widgets.client.site') }}</option>
					<option value="1"<?php if ($filters['client_id'] == '1'): echo ' selected="selected"'; endif;?>>{{ trans('widgets::widgets.client.admin') }}</option>
				</select>

				<label class="sr-only" for="filter_state">{{ trans('widgets::widgets.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('widgets::widgets.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_position">{{ trans('widgets::widgets.select position') }}</label>
				<select name="position" id="filter_position" class="form-control filter filter-submit">
					<option value="">{{ trans('widgets::widgets.select position') }}</option>
					<?php foreach (App\Modules\Widgets\Helpers\Admin::getPositions($filters['client_id']) as $position): ?>
						<option value="<?php echo $position->value; ?>"<?php if ($filters['position'] == $position->position) { echo ' selected="selected"'; } ?>><?php echo e($position->position); ?></option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only" for="filter_widget">{{ trans('widgets::widgets.select widget') }}</label>
				<select name="widget" id="filter_widget" class="form-control filter filter-submit">
					<option value="">{{ trans('widgets::widgets.select widget') }}</option>
					<?php foreach (App\Modules\Widgets\Helpers\Admin::getWidgets($filters['client_id']) as $widget): ?>
						<option value="<?php echo $widget->element; ?>"<?php if ($filters['widget'] == $widget->element) { echo ' selected="selected"'; } ?>><?php echo e($widget->name); ?></option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only" for="filter_access">{{ trans('widgets::widgets.access level') }}</label>
				<select name="access" id="filter_access" class="form-control filter filter-submit">
					<option value="">{{ trans('widgets::widgets.access select') }}</option>
					<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
						<option value="<?php echo $access->id; ?>"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>><?php echo e($access->title); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('widgets::widgets.module name') }}</caption>
		<thead>
			<tr>
				<th>
					<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
				</th>
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
				<th scope="col" class="priority-2">
					{{ trans('widgets::widgets.pages') }}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('widgets::widgets.access'), 'access', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{{ trans('widgets::widgets.language') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit widgets'))
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit widgets'))
						<a href="{{ route('admin.widgets.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->title }}
					@if (auth()->user()->can('edit widgets'))
						</a>
					@endif
					@if (!$row->path())
						<p class="smallsub">
							{{ trans('widgets::widgets.error missing files') }}
						</p>
					@endif
					@if ($row->note)
						<p class="smallsub">
							{{ trans('widgets::widgets.note', ['note' => $row->note]) }}
						</p>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit.state widgets'))
						@if ($row->published == 1)
							<a class="btn btn-sm btn-secondary published" href="{{ route('admin.widgets.unpublish', ['id' => $row->id]) }}">
								{{ trans('widgets::widgets.published') }}
							</a>
						@else
							<a class="btn btn-sm btn-secondary unpublished" href="{{ route('admin.widgets.publish', ['id' => $row->id]) }}">
								{{ trans('widgets::widgets.unpublished') }}
							</a>
						@endif
					@else
						@if ($row->published == 1)
							<span class="badge published">
								{{ trans('widgets::widgets.published') }}
							</span>
						@else
							<span class="badge unpublished">
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
				<td class="priority-3">
					{{ $row->ordering }}
				</td>
				<td class="priority-4">
					<?php
					if (substr($row->widget, 0, 4) == 'mod_')
					{
						echo substr($row->widget, 4);
					}
					else
					{
						echo $row->widget;
					}
					?>
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
						$pages = trans('widgets::widgets.ASSIGNED_VARIES_EXCEPT');
					}
					elseif ($row->pages > 0)
					{
						$pages = trans('widgets::widgets.ASSIGNED_VARIES_ONLY');
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
				<td class="priority-5 center">
					<?php if ($row->language == ''): ?>
						{{ trans('global.default') }}
					<?php elseif ($row->language == '*'): ?>
						{{ trans('global.all') }}
					<?php else: ?>
						<?php echo $row->language_title ? e($row->language_title) : trans('global.undefined'); ?>
					<?php endif; ?>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	<div id="new-widget" class="hide">
		<h2 class="modal-title">{{ trans('widgets::widgets.TYPE_CHOOSE') }}</h2>

		<table id="new-modules-list" class="adminlist">
			<thead>
				<tr>
					<th scope="col">{{ trans('globa.title') }}</th>
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

	dialog = $("#new-widget").dialog({
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