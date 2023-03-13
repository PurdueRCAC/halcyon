@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.levels')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete users.levels'))
		{!! Toolbar::deleteList('', route('admin.users.levels.delete')) !!}
	@endif

	@if (auth()->user()->can('create users.levels'))
		{!! Toolbar::addNew(route('admin.users.levels.create')) !!}
	@endif

	@if (auth()->user()->can('admin users'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('users')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::users.module name') }}: {{ trans('users::access.levels') }}
@stop

@section('content')

@component('users::admin.submenu')
	levels
@endcomponent

<form action="{{ route('admin.users.levels') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-xs-12 col-sm-12 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card md-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('users::users.users') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('users::access.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('users::access.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{{ trans('users::users.visible for roles') }}
				</th>
				<?php /*<th scope="col" class="priority-3">
					{!! Html::grid('sort', 'users::access.ordering', 'ordering', $filters['order_dir'], $filters['order']) !!}
				</th>*/ ?>
			</tr>
		</thead>
		<tbody>
		<?php
		$ordering  = ($filters['order'] == 'ordering');
		$n = $rows->count();
		?>
		@foreach ($rows as $i => $row)
			<tr>
				<td class="center">
					@if (auth()->user()->can('edit users.levels'))
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="center priority-4">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit users.levels'))
						<a href="{{ route('admin.users.levels.edit', ['id' => $row->id]) }}">
							{{ $row->title }}
						</a>
					@else
						{{ $row->title }}
					@endif
				</td>
				<td>
					{{ $row->visibleByRoles() }}
				</td>
				<?php /*<td class="order">
					@if (auth()->user()->can('edit users.levels'))
						<?php if ($ordering) :?>
							<?php if ($filters['order_dir'] == 'asc') : ?>
								<span><?php echo ($i > 0) ? App\Halcyon\Html\Builder\Grid::orderUp($i, 'orderup', '', 'global.move up', true, 'cb') : '&#160;'; ?></span>
								<span><?php echo ($i < ($n - 1)) ? App\Halcyon\Html\Builder\Grid::orderDown($i, 'orderdown', '', 'global.move down', true, 'cb') : '&#160;'; ?></span>
							<?php elseif ($filters['order_dir'] == 'desc') : ?>
								<span><?php echo ($i > 0) ? App\Halcyon\Html\Builder\Grid::orderUp($i, 'orderdown', '', 'global.move up', true, 'cb') : '&#160;'; ?></span>
								<span><?php echo ($i < ($n - 1)) ? App\Halcyon\Html\Builder\Grid::orderDown($i, 'orderup', '', 'global.move down', true, 'cb') : '&#160;'; ?></span>
							<?php endif; ?>
						<?php endif; ?>
						<?php $disabled = $ordering ? '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="{{ $row->ordering }}" <?php echo $disabled ?> class="text-area-order" />
					@else
						{{ $row->ordering }}
					@endif
				</td>
				<td class="order nowrap center hidden-phone">
					<?php
					$iconClass = '';
					if (!auth()->user()->can('edit users.levels'))
					{
						$iconClass = ' inactive';
					}
					elseif (!$ordering)
					{
						$iconClass = ' inactive tip-top hasTooltip" title="' . trans('users::users.ordering disabled');
					}
					?>
					<span class="sortable-handler<?php echo $iconClass ?>">
						<span class="fa fa-ellipsis-v" aria-hidden="true"></span>
					</span>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="hide" />
				</td>*/ ?>
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