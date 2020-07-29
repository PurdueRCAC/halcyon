@extends('layouts.master')

@section('scripts')
<script>
jQuery(document).ready(function($){
	var sortableHelper = function (e, ui) {
		ui.children().each(function () {
			$(this).width($(this).width());
		});
		return ui;
	};
	//var corresponding;
	$('.sortable').sortable({
		handle: '.draghandle',
		cursor: 'move',
		helper: sortableHelper,
		containment: 'parent',
		start: function (e, ui) {
			//corresponding = [];
			var height = ui.helper.outerHeight();
			$(this).find('> tr[data-parent=' + $(ui.item).data('id') + ']').each(function (idx, row) {

				height += $(row).outerHeight();
				// corresponding.push(row);
				//row.detach();
				/*var corresponding = $('tr[data-parent=' + $(ui.item).data('id') + ']');
				corresponding.detach();

				corresponding.each(function (idx, row) {
				});*/
				//row.insertAfter($(ui.item));

			});
			ui.placeholder.height(height);
		},
		update: function (e, ui) {
			//var tableHasUnsortableRows = $(this).find('> tbody > tr:not(.sortable)').length;

			$(this).find('> tr').each(function (idx, row) {
				var uniqID = $(row).attr('data-id'),
					correspondingFixedRow = $('tr[data-parent=' + uniqID + ']');
				correspondingFixedRow.detach().insertAfter($(this));
			});
		}/*,
		stop: function (e, ui) {
			corresponding.detach().insertAfter($(ui.item));
		}*/
	}).disableSelection();
});
</script>
@stop

@php
app('pathway')
	->append(
		trans('menus::menus.module name'),
		route('admin.menus.index')
	)
	->append(
		$menu->title,
		route('admin.menus.items', ['menutype' => $filters['menutype']])
	)
	->append(
		trans('menus::menus.items')
	);
@endphp

@section('toolbar')
	@if ($filters['state'] == 'trashed')
		@if (auth()->user()->can('edit.state menus'))
			{!!
				Toolbar::custom(route('admin.menus.items.restore'), 'publish', 'publish', 'Restore', false);
				Toolbar::spacer();
			!!}
		@endif
		@if (auth()->user()->can('delete menus'))
			{!! Toolbar::deleteList('', route('admin.menus.items.delete')) !!}
		@endif
	@else
		@if (auth()->user()->can('manage menus'))
			{!!
				Toolbar::checkin('admin.menus.checkin');
				Toolbar::custom(route('admin.menus.rebuild', ['menutype' => $filters['menutype']]), 'refresh', 'refresh_f2.png', 'menus::menus.rebuild', false);
				Toolbar::spacer();
			!!}
		@endif
		@if (auth()->user()->can('edit.state menus'))
			{!!
				Toolbar::publishList(route('admin.menus.items.publish'));
				Toolbar::unpublishList(route('admin.menus.items.unpublish'));
				Toolbar::spacer();
			!!}
		@endif
		@if (auth()->user()->can('create menus'))
			{!! Toolbar::addNew(route('admin.menus.items.create', ['menutype' => $filters['menutype']])) !!}
		@endif
		@if (auth()->user()->can('delete menus'))
			{!! Toolbar::deleteList('', route('admin.menus.items.delete')) !!}
		@endif
		@if (auth()->user()->can('admin menus'))
			{!!
				Toolbar::spacer();
				Toolbar::preferences('menus')
			!!}
		@endif
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('menus::menus.menu manager') }}: {{ $menu->title }}
@stop

@section('content')
<?php
$saveOrder = ($filters['order'] == 'lft' && $filters['order_dir'] == 'asc');
?>
<form action="{{ route('admin.menus.items') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-3">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

				<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
			</div>
			<div class="col col-md-9 text-right filter-select">
				<label class="sr-only" for="filter_menutype">{{ trans('menus::menus.menu type') }}</label>
				<select name="menutype" id="filter_menutype" class="form-control filter filter-submit">
					<?php echo \App\Halcyon\Html\Builder\Select::options(\App\Modules\Menus\Helpers\Html::menus(), 'value', 'text', $filters['menutype']); ?>
				</select>

				<label class="sr-only" for="filter_level">{{ trans('menus::menus.level') }}</label>
				<select name="level" id="filter_level" class="form-control filter filter-submit">
					<option value="">{{ trans('menus::menus.option select level') }}</option>
					<?php echo \App\Halcyon\Html\Builder\Select::options($f_levels, 'value', 'text', $filters['level']); ?>
				</select>

				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="">{{ trans('global.option.select published') }}</option>
					<?php //echo \App\Halcyon\Html\Builder\Select::options(\App\Halcyon\Html\Builder\Grid::publishedOptions(array('archived' => false)), 'value', 'text', $filters['state'], true); ?>
					<option value="published"<?php if ($filters['state'] == 'published') { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished') { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_access">{{ trans('global.access') }}</label>
				<select name="access" id="filter_access" class="form-control filter filter-submit">
					<option value="">{{ trans('global.option.select access') }}</option>
					<?php echo \App\Halcyon\Html\Builder\Select::options(\App\Halcyon\Html\Builder\Access::assetgroups(), 'value', 'text', $filters['access']); ?>
				</select>

				<!-- <select name="language" class="form-control filter filter-submit">
					<option value="">{{ trans('global.option.select language') }}</option>
					<?php echo \App\Halcyon\Html\Builder\Select::options(\App\Halcyon\Html\Builder\Contentlanguage::existing(true, true), 'value', 'text', $filters['language']); ?>
				</select> -->
			</div>
		</div>
	</fieldset>

	@if ($filters['state'] == 'trashed')
		<div class="alert alert-warning">{{ trans('pages::pages.trashed must be restored') }}</div>
	@endif

	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('menus::menus.items') }}</caption>
		<thead>
			<tr>
				<th>
					<span class="form-check"><input type="checkbox" name="toggle" value="" id="toggle-all" class="form-check-input checkbox-toggle toggle-all" /><label for="toggle-all"></label></span>
				</th>
				<th scope="col" class="priority-5">{{ trans('menus::menus.id') }}</th>
				<th scope="col">{{ trans('menus::menus.title') }}</th>
				<th class="priority-3">
					<?php echo \App\Halcyon\Html\Builder\Grid::sort(trans('global.state'), 'state', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th class="priority-4">
					<?php echo \App\Halcyon\Html\Builder\Grid::sort(trans('menus::menus.access'), 'access', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th>
					<?php echo \App\Halcyon\Html\Builder\Grid::sort(trans('global.ordering'), 'lft', $filters['order_dir'], $filters['order']); ?>
					<?php if (auth()->user()->can('edit.state menus') && $saveOrder) :?>
						<?php echo \App\Halcyon\Html\Builder\Grid::order($rows, 'filesave', 'admin.menus.items.saveorder'); ?>
					<?php endif; ?>
				</th>
				<!-- <th class="priority-5">
					{{ trans('menus::menus.item type') }}
				</th>
				<th class="priority-2">
					<?php echo \App\Halcyon\Html\Builder\Grid::sort(trans('menus::menus.home'), 'home', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th class="priority-6">
					<?php echo \App\Halcyon\Html\Builder\Grid::sort(trans('menus::menus.language'), 'language', $filters['order_dir'], $filters['order']); ?>
				</th> -->
				<th></th>
			</tr>
		</thead>
		<?php
		$originalOrders = array();
		$canChange = auth()->user()->can('edit menus');
		$parent_id = 0;
		$p = 1;
		?>
		<tbody class="sortable" id="row-1" data-id="1">
		@foreach ($rows as $i => $row)
			<tr id="row-{{ $row->id }}" data-parent="{{ $row->parent_id }}" data-id="{{ $row->id }}">
			<?php $orderkey = array_search($row->id, $ordering[$row->parent_id]); ?>
				<td>
					@if ($canChange)
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level - 1) !!}
					@if ($row->checked_out)
						<?php echo \App\Halcyon\Html\Builder\Grid::checkedout($i, $row->editor, $row->checked_out_time, 'items.', $canCheckin); ?>
					@endif
					@if (!$row->trashed() && $canChange)
						<a href="{{ route('admin.menus.items.edit', ['id' => $row->id]) }}">
							@if ($row->type == 'separator')
								<span class="unknown">[ {{ $row->type }} ]</span>
							@else
								{{ $row->title }}
							@endif
						</a>
					@else
						{{ $row->title }}
					@endif
					@if ($row->type != 'separator')
						<p class="smallsub" title="{{ $row->path }}">
							{!! str_repeat('<span class="gtr">|&mdash;</span>', $row->level - 1) !!}
							@if ($row->type != 'url')
								@if (empty($row->note))
									/{{ trim($row->link, '/') }}
								@else
									{!! trans('global.LIST_ALIAS_NOTE', ['alias' => $row->alias, 'note' => $row->note]) !!}
								@endif
							@elseif ($row->type == 'url')
								{{ $row->link }}
								@if ($row->note)
									{!! trans('global.LIST_NOTE', ['note' => $row->note]) !!}
								@endif
							@endif
						</p>
					@endif
				</td>
				<td class="center priority-3">
					@if ($row->trashed())
						@if ($canChange)
							<a class="btn btn-secondary btn-sm trashed" href="{{ route('admin.menus.items.restore', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.restore" title="Restore menu item">
								{{ trans('global.trashed') }}
							</a>
						@else
							<span class="badge trashed">
								{{ trans('global.trashed') }}
							</span>
						@endif
					@elseif ($row->state)
						@if ($canChange)
							<a class="btn btn-secondary btn-sm published" href="{{ route('admin.menus.items.unpublish', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.unpublish" title="Unpublish menu item">
								{{ trans('global.published') }}
							</a>
						@else
							<span class="badge published">
								{{ trans('global.published') }}
							</span>
						@endif
					@else
						@if ($canChange)
							<a class="btn btn-secondary btn-sm unpublished" href="{{ route('admin.menus.items.publish', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.publish" title="Publish menu item">
								{{ trans('global.unpublished') }}
							</a>
						@else
							<span class="badge unpublished">
								{{ trans('global.unpublished') }}
							</span>
						@endif
						<?php //echo App\Modules\Menus\Helpers\Html::state($row->published, $i, $canChange, 'cb'); ?>
					@endif
				</td>
				<td class="center priority-4">
					<span class="access {{ preg_replace('/[^a-z0-9\-_]+/', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
				</td>
				<td class="order">
					<?php /*$orderkey = array_search($row->id, $ordering[$row->parent_id]); ?>
					<?php if ($canChange): ?>

							<span>{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, isset($ordering[$row->parent_id][$orderkey - 1]), route('admin.menus.items.orderup', ['id' => $row->id])) !!}</span>
							<span>{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), isset($ordering[$row->parent_id][$orderkey + 1]), route('admin.menus.items.orderdown', ['id' => $row->id])) !!}</span>

						<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $orderkey + 1;?>" <?php echo $disabled ?> class="text-area-order" />
						<?php $originalOrders[] = $orderkey + 1; ?>
					<?php else : ?>
						<?php echo $orderkey + 1;?>
					<?php endif;*/ ?>
					{{ $row->ordering }}
				</td>
				<!-- <td class="nowrap priority-5">
					<span title="<?php echo $row->item_type_desc ? htmlspecialchars($row->item_type_desc, ENT_COMPAT, 'UTF-8') : ''; ?>">
						{{ $row->menutype }}
					</span>
				</td>
				<td class="center priority-2">
					<?php if ($row->type == 'module'): ?>
						<?php if ($row->language == '*' || $row->home == '0'): ?>
							<?php echo \App\Halcyon\Html\Builder\Grid::isDefault($row->home, $i, 'items.', ($row->language != '*' || !$row->home) && $canChange); ?>
						<?php elseif ($canChange): ?>
							<a href="{{ route('admin.menus.setdefault', ['id' => $row->get('id')]) }}">
								{{ $row->language_title }}
							</a>
						<?php else: ?>
							{{ $row->language_title }}
						<?php endif; ?>
					<?php endif; ?>
				</td>
				<td class="center priority-6">
					@if ($row->language == '')
						{{ trans('global.default') }}
					@elseif ($row->language == '*')
						{{ trans('global.all') }}
					@else
						{{ $row->language_title ? $row->language_title : trans('global.undefined') }}
					@endif
				</td> -->
				<td>
					<div class="draghandle" draggable="true">
						<svg class="glyph draghandle-icon" viewBox="0 0 24 24"><path d="M10,4c0,1.1-0.9,2-2,2S6,5.1,6,4s0.9-2,2-2S10,2.9,10,4z M16,2c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,2,16,2z M8,10 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,10,8,10z M16,10c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,10,16,10z M8,18 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,18,8,18z M16,18c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,18,16,18z"></path></svg>
					</div>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="menutype" value="{{ $filters['menutype'] }}" />
	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>
@stop