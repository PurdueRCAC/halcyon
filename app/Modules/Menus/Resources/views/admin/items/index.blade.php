@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/menus/js/menus.js?v=' . filemtime(public_path() . '/modules/menus/js/menus.js')) }}"></script>
@endpush

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
		@if (auth()->user()->can('delete menus'))
			{!! Toolbar::deleteList('', route('admin.menus.items.delete')) !!}
		@endif
		@if (auth()->user()->can('edit.state menus'))
			{!!
				Toolbar::custom(route('admin.menus.items.restore'), 'publish', 'publish', 'Restore', false);
				Toolbar::spacer();
			!!}
		@endif
	@else
		@if (auth()->user()->can('manage menus'))
			{!!
				Toolbar::checkin('admin.menus.checkin');
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
		@if (auth()->user()->can('delete menus'))
			{!! Toolbar::deleteList('', route('admin.menus.items.delete')) !!}
		@endif
		@if (auth()->user()->can('create menus'))
			{!! Toolbar::addNew(route('admin.menus.items.create', ['menutype' => $filters['menutype']])) !!}
		@endif
		@if (auth()->user()->can('manage menus'))
			{!!
				Toolbar::spacer();
				Toolbar::link('refresh', 'menus::menus.rebuild', route('admin.menus.rebuild', ['menutype' => $filters['menutype']]));
			!!}
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
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-9 text-right filter-select">
				<label class="sr-only" for="filter_menutype">{{ trans('menus::menus.menu type') }}</label>
				<select name="menutype" id="filter_menutype" class="form-control filter filter-submit">
					<?php echo \App\Halcyon\Html\Builder\Select::options(\App\Modules\Menus\Helpers\Html::menus(), 'value', 'text', $filters['menutype']); ?>
				</select>

				<label class="sr-only" for="filter_level">{{ trans('menus::menus.level') }}</label>
				<select name="level" id="filter_level" class="form-control filter filter-submit">
					<option value="">{{ trans('menus::menus.all levels') }}</option>
					<?php echo \App\Halcyon\Html\Builder\Select::options($f_levels, 'value', 'text', $filters['level']); ?>
				</select>

				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="">{{ trans('menus::menus.all states') }}</option>
					<?php //echo \App\Halcyon\Html\Builder\Select::options(\App\Halcyon\Html\Builder\Grid::publishedOptions(array('archived' => false)), 'value', 'text', $filters['state'], true); ?>
					<option value="published"<?php if ($filters['state'] == 'published') { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished') { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_access">{{ trans('global.access') }}</label>
				<select name="access" id="filter_access" class="form-control filter filter-submit">
					<option value="">{{ trans('menus::menus.all access levels') }}</option>
					<?php echo \App\Halcyon\Html\Builder\Select::options(\App\Halcyon\Html\Builder\Access::assetgroups(), 'value', 'text', $filters['access']); ?>
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
		<caption class="sr-only">{{ trans('menus::menus.items') }}</caption>
		<thead>
			<tr>
				<th>
					<span class="form-check"><input type="checkbox" name="toggle" value="" id="toggle-all" class="form-check-input checkbox-toggle toggle-all" /> <label for="toggle-all" class="sr-only">Check all</label></span>
				</th>
				<th scope="col" class="priority-5">{{ trans('menus::menus.id') }}</th>
				<th scope="col">{{ trans('menus::menus.title') }}</th>
				<th scope="col" class="text-center priority-3">
					<?php echo \App\Halcyon\Html\Builder\Grid::sort(trans('global.state'), 'state', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="text-center priority-4">
					<?php echo \App\Halcyon\Html\Builder\Grid::sort(trans('menus::menus.access'), 'access', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="text-center">
					<?php echo \App\Halcyon\Html\Builder\Grid::sort(trans('global.ordering'), 'lft', $filters['order_dir'], $filters['order']); ?>
				</th>
				<!-- <th></th> -->
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
			<?php $orderkey = array_search($row->id, $ordering[$row->parent_id]); ?>
			<tr<?php if ($row->trashed()) { echo ' class="trashed"'; } ?> id="row-{{ $row->id }}" data-parent="{{ $row->parent_id }}" data-id="{{ $row->id }}">
				<td>
					@if ($canChange)
						{!! Html::grid('id', $i, $row->id) !!}
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
						<div class="smallsub" title="{{ $row->path }}">
							{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level - 1) !!}
							@if ($row->type != 'url')
								@if (empty($row->note))
									<span class="text-muted">/{{ trim($row->link, '/') }}</span>
								@else
									{!! trans('global.LIST_ALIAS_NOTE', ['alias' => $row->alias, 'note' => $row->note]) !!}
								@endif
							@elseif ($row->type == 'url')
								<span class="text-muted">{{ $row->link }}</span>
								@if ($row->note)
									{!! trans('global.LIST_NOTE', ['note' => $row->note]) !!}
								@endif
							@endif
						</div>
					@endif
				</td>
				<td class="text-center priority-3">
					@if ($row->trashed())
						@if ($canChange)
							<a class="badge badge-danger" href="{{ route('admin.menus.items.restore', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.restore" data-tip="{{ trans('menus::menus.restore menu item') }}">
								{{ trans('global.trashed') }}
							</a>
						@else
							<span class="badge badge-danger">
								{{ trans('global.trashed') }}
							</span>
						@endif
					@elseif ($row->state)
						@if ($canChange)
							<a class="badge badge-success" href="{{ route('admin.menus.items.unpublish', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.unpublish" data-tip="{{ trans('menus::menus.unpublish menu item') }}">
								{{ trans('global.published') }}
							</a>
						@else
							<span class="badge badge-success">
								{{ trans('global.published') }}
							</span>
						@endif
					@else
						@if ($canChange)
							<a class="badge badge-secondary" href="{{ route('admin.menus.items.publish', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.publish" data-tip="{{ trans('menus::menus.publish menu item') }}">
								{{ trans('global.unpublished') }}
							</a>
						@else
							<span class="badge badge-secondary">
								{{ trans('global.unpublished') }}
							</span>
						@endif
						<?php //echo App\Modules\Menus\Helpers\Html::state($row->published, $i, $canChange, 'cb'); ?>
					@endif
				</td>
				<td class="text-center priority-4">
					<span class="badge access {{ preg_replace('/[^a-z0-9\-_]+/', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
				</td>
				<td class="text-center">
					<?php $orderkey = array_search($row->id, $ordering[$row->parent_id]); ?>
					<?php if ($canChange): ?>
						<span>{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, isset($ordering[$row->parent_id][$orderkey - 1]), route('admin.menus.items.orderup', ['id' => $row->id])) !!}</span>
						<span>{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), isset($ordering[$row->parent_id][$orderkey + 1]), route('admin.menus.items.orderdown', ['id' => $row->id])) !!}</span>
						<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
						<!-- <input type="text" name="order[]" size="5" value="<?php echo $orderkey + 1;?>" <?php echo $disabled ?> class="form-control text-area-order" /> -->
						<?php $originalOrders[] = $orderkey + 1; ?>
					<?php else : ?>
						<?php echo $orderkey + 1;?>
					<?php endif; ?>
				</td>
				<!-- <td>
					<div class="draghandle" draggable="true">
						<svg class="glyph draghandle-icon" viewBox="0 0 24 24"><path d="M10,4c0,1.1-0.9,2-2,2S6,5.1,6,4s0.9-2,2-2S10,2.9,10,4z M16,2c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,2,16,2z M8,10 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,10,8,10z M16,10c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,10,16,10z M8,18 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,18,8,18z M16,18c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,18,16,18z"></path></svg>
					</div>
				</td> -->
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