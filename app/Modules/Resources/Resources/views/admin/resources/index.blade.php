@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.resources'),
		route('admin.resources.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('create resources'))
		{!! Toolbar::addNew(route('admin.resources.create')) !!}
	@endif

	@if (auth()->user()->can('delete resources'))
		@if ($filters['state'] == 'trashed')
			{!! Toolbar::custom(route('admin.resources.restore'), 'refresh', 'refresh', trans('global.restore'), false) !!}
		@else
			{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.resources.delete')) !!}
		@endif
	@endif

	@if (auth()->user()->can('admin resources'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('resources');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('resources.name') !!}
@stop

@section('content')
@component('resources::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.resources.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col filter-search col-md-4">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

				<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
			</div>
			<div class="col filter-select col-md-8 text-right">
				<label class="sr-only" for="filter_state">{{ trans('resources::assets.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="all"<?php if ($filters['state'] == 'all'): echo ' selected="selected"'; endif;?>>{{ trans('resources::assets.all states') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active'): echo ' selected="selected"'; endif;?>>{{ trans('global.active') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_type">{{ trans('resources::assets.type') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value="0">{{ trans('resources::assets.all types') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only" for="filter_batchsystem">{{ trans('resources::assets.batchsystem') }}</label>
				<select name="batchsystem" id="filter_batchsystem" class="form-control filter filter-submit">
					<option value="0">{{ trans('resources::assets.all batchsystems') }}</option>
					<?php foreach ($batchsystems as $batchsystem): ?>
						<option value="{{ $batchsystem->id }}"<?php if ($filters['batchsystem'] == $batchsystem->id): echo ' selected="selected"'; endif;?>>{{ $batchsystem->name }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('resources::assets.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('resources::assets.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('resources::assets.role name'), 'rolename', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('resources::assets.list name'), 'listname', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3">
					{!! Html::grid('sort', trans('resources::assets.type'), 'resourcetype', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('resources::assets.batchsystem'), 'batchsystem', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{{ trans('resources::assets.subresources') }}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('global.ordering'), 'display', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<?php
				$disabled = false;
				$trashed = $row->isTrashed();
				$cls = $trashed ? 'trashed' : 'active';
				if ($filters['state'] == 'trashed')
				{
					$cls = '';
					if (!$trashed)
					{
						$disabled = true;
					}
				}
			?>
			<tr class="{{ $cls }} @if ($disabled) disabled @endif">
				<td>
					@if (!$disabled && auth()->user()->can('edit resources'))
					<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					{!! $row->treename !!}
					@if ($trashed)
						<span class="fa fa-trash unknown" aria-hidden="true"></span>
					@endif
					@if (!$disabled && auth()->user()->can('edit resources'))
						<a href="{{ route('admin.resources.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (!$disabled && auth()->user()->can('edit resources'))
						</a>
					@endif
				</td>
				<td>
					@if (!$disabled && auth()->user()->can('edit resources'))
						<a href="{{ route('admin.resources.edit', ['id' => $row->id]) }}">
					@endif
						{!! $row->rolename ? $row->rolename : '<span class="unknown">' . trans('global.none') . '</span>' !!}
					@if (!$disabled && auth()->user()->can('edit resources'))
						</a>
					@endif
				</td>
				<td class="priority-4">
					@if (!$disabled && auth()->user()->can('edit resources'))
						<a href="{{ route('admin.resources.edit', ['id' => $row->id]) }}">
					@endif
						{!! $row->listname ? $row->listname : '<span class="unknown">' . trans('global.none') . '</span>' !!}
					@if (!$disabled && auth()->user()->can('edit resources'))
						</a>
					@endif
				</td>
				<td class="priority-3">
					{{ $row->type->name }}
				</td>
				<td class="priority-4">
					{!! $row->batchsystm ? $row->batchsystm->name : '<span class="unknown">' . trans('global.none') . '</span>' !!}
				</td>
				<td class="priority-4 text-right">
					<a href="{{ route('admin.resources.subresources', ['resource' => $row->id]) }}">
						{{ $row->children_count }}
					</a>
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
					{{ $row->display }}
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $paginator->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>

@stop
