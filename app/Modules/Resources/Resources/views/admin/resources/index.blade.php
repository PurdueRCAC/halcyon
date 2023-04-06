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
	@if (auth()->user()->can('delete resources'))
		@if ($filters['state'] == 'trashed')
			{!! Toolbar::custom(route('admin.resources.restore'), 'refresh', 'refresh', trans('global.button.restore'), true) !!}
		@else
			{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.resources.delete')) !!}
		@endif
	@endif

	@if (auth()->user()->can('create resources'))
		{!! Toolbar::addNew(route('admin.resources.create')) !!}
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
{{ trans('resources::resources.module name') }}
@stop

@section('content')
@component('resources::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.resources.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col filter-search col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
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
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_batchsystem">{{ trans('resources::assets.batchsystem') }}</label>
				<select name="batchsystem" id="filter_batchsystem" class="form-control filter filter-submit">
					<option value="0">{{ trans('resources::assets.all batchsystems') }}</option>
					@foreach ($batchsystems as $batchsystem)
						<option value="{{ $batchsystem->id }}"<?php if ($filters['batchsystem'] == $batchsystem->id): echo ' selected="selected"'; endif;?>>{{ $batchsystem->name }}</option>
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
		<caption class="sr-only">{{ trans('resources::resources.resources') }}</caption>
		<thead>
			<?php
			/* Experimental reworking of filters
			<tr class="filters">
				@if (auth()->user()->can('delete resources'))
				<th></th>
				@endif
				<th colspan="4">
					<span class="input-group input-group-sm">
						<input type="text" name="search" id="filter_search" class="form-control form-control-sm filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</th>
				<th>
					<select name="type" id="filter_type" class="form-control form-control-sm filter filter-submit">
						<option value="0">{{ trans('resources::assets.all types') }}</option>
						@foreach ($types as $type)
							<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
						@endforeach
					</select>
				</th>
				<th>
					<select name="batchsystem" id="filter_batchsystem" class="form-control form-control-sm filter filter-submit">
						<option value="0">{{ trans('resources::assets.all batchsystems') }}</option>
						@foreach ($batchsystems as $batchsystem)
							<option value="{{ $batchsystem->id }}"<?php if ($filters['batchsystem'] == $batchsystem->id): echo ' selected="selected"'; endif;?>>{{ $batchsystem->name }}</option>
						@endforeach
					</select>
				</th>
				<th></th>
				<th></th>
			</tr>*/
			?>
			<tr>
				@if (auth()->user()->can('delete resources'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
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
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('resources::assets.batchsystem'), 'batchsystem', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{{ trans('resources::assets.subresources') }}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('global.ordering'), 'display', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-5">
					Users
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<?php
			$disabled = false;
			$trashed = $row->trashed();
			$cls = $trashed ? 'trashed' : 'active';
			if ($filters['state'] == 'trashed'):
				//$cls = '';
				if (!$trashed):
					$disabled = true;
				endif;
			endif;
			?>
			<tr class="{{ $cls }}">
				@if (!$disabled && auth()->user()->can('delete resources'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					{!! $row->treename !!}
					@if ($trashed)
						<span class="glyph icon-trash text-danger" aria-hidden="true" data-tip="Removed on {{ $row->datetimeremoved->format('Y-m-d') }}"></span>
					@endif
					@if (!$disabled && auth()->user()->can('edit resources'))
						<a href="{{ route('admin.resources.edit', ['id' => $row->id]) }}">
					@endif
						{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
					@if (!$disabled && auth()->user()->can('edit resources'))
						</a>
					@endif
				</td>
				<td>
					@if (!$disabled && auth()->user()->can('edit resources'))
						<a href="{{ route('admin.resources.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->rolename }}
					@if (!$disabled && auth()->user()->can('edit resources'))
						</a>
					@endif
				</td>
				<td class="priority-4">
					@if (!$disabled && auth()->user()->can('edit resources'))
						<a href="{{ route('admin.resources.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->listname }}
					@if (!$disabled && auth()->user()->can('edit resources'))
						</a>
					@endif
				</td>
				<td class="priority-3">
					@if ($row->resourcetype)
						@php
						$t = $types->where('id', $row->resourcetype)->first();
						@endphp
						{{ $t ? $t->name : trans('global.unknown') }}
					@else
						{{ trans('global.none') }}
					@endif
				</td>
				<td class="priority-6">
					@php
					$b = $batchsystems->where('id', $row->batchsystem)->first();
					@endphp
					{{ $b ? $b->name : '' }}
				</td>
				<td class="priority-4 text-right">
					<a href="{{ route('admin.resources.subresources', ['resource' => $row->id]) }}">
						{{ $row->children_count }}
					</a>
				</td>
				<td class="priority-6 order">
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
				<td class="priority-5 text-center">
					@if ($row->children_count)
						<a href="{{ route('admin.resources.members', ['id' => $row->id]) }}" data-tip="{{ trans('resources::assets.active users') }}">
							<span class="fa fa-users" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('resources::assets.active users') }}</a>
						</a>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>

	{{ $paginator->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>

@stop
