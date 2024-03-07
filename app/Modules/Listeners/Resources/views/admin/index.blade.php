@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('listeners::listeners.module name'),
		route('admin.listeners.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit.state listeners'))
		{!!
			Toolbar::divider();
			Toolbar::publish(route('admin.listeners.publish'), trans('listeners::listeners.enable'), true);
			Toolbar::unpublish(route('admin.listeners.unpublish'), trans('listeners::listeners.disable'), true);
			Toolbar::divider();
			Toolbar::checkin(route('admin.listeners.checkin'));
		!!}
	@endif

	@if (auth()->user()->can('admin listeners'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('listeners')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('listeners::listeners.listener manager') }}
@stop

@section('content')
<form action="{{ route('admin.listeners.index') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row grid">
			<div class="col col-md-4 filter-search">
				<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
				</span>
			</div>
			<div class="col">
				<label class="sr-only visually-hidden" for="filter_state">{{ trans('listeners::listeners.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="">{{ trans('listeners::listeners.state all') }}</option>
					<?php echo App\Halcyon\Html\Builder\Select::options(App\Modules\Listeners\Helpers\Admin::stateOptions(), 'value', 'text', $filters['state'], true); ?>
				</select>
			</div>
			<div class="col">
				<label class="sr-only visually-hidden" for="filter_folder">{{ trans('listeners::listeners.select folder') }}</label>
				<select name="folder" id="filter_folder" class="form-control filter filter-submit">
					<option value="">{{ trans('listeners::listeners.select folder') }}</option>
					<?php echo App\Halcyon\Html\Builder\Select::options(App\Modules\Listeners\Helpers\Admin::folderOptions(), 'value', 'text', $filters['folder']); ?>
				</select>
			</div>
			<div class="col">
				<label class="sr-only visually-hidden" for="filter_access">{{ trans('listeners::listeners.access') }}</label>
				<select name="faccess" id="filter_access" class="form-control filter filter-submit">
					<option value="">{{ trans('listeners::listeners.access all') }}</option>
					<?php echo App\Halcyon\Html\Builder\Select::options(App\Halcyon\Html\Builder\Access::assetgroups(), 'value', 'text', $filters['access']); ?>
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
					<caption class="sr-only visually-hidden">{!! trans('listeners::listeners.listener manager') !!}</caption>
					<thead>
						<tr>
							@if (auth()->user()->can('edit listeners'))
							<th class="text-center">
								<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
							</th>
							@endif
							<th scope="col" class="priority-5">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.id'), 'extension_id', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.name'), 'name', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="text-center">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.state'), 'enabled', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-3 text-center">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.access'), 'access', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-4 text-center">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.ordering'), 'ordering', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-5">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.folder'), 'folder', $filters['order_dir'], $filters['order']); ?>
							</th>
							<th scope="col" class="priority-5">
								<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.element'), 'element', $filters['order_dir'], $filters['order']); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$folders = $rows->pluck('folder')->toArray();
					$ordering   = ($filters['order'] == 'ordering');
					?>
					@foreach ($rows as $i => $row)
						<?php
						$row->registerLanguage();
						$path = $row->path;

						if (!$path):
							$row->enabled = 0;
						endif;

						$canEdit    = $path ? auth()->user()->can('edit listeners') : false;
						$canCheckin = auth()->user()->can('manage listeners') || $row->checked_out == auth()->user()->id || $row->checked_out == 0;
						$canChange  = auth()->user()->can('edit.state listeners') && $canCheckin;
						?>
						<tr<?php if (!$path) { echo ' class="locked"'; } ?>>
							<td class="text-center">
								@if ($path && $canEdit)
									{!! App\Halcyon\Html\Builder\Grid::id($i, $row->id) !!}
								@elseif (!$path)
									<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span>
								@endif
							</td>
							<td class="priority-5">
								{{ $row->id }}
							</td>
							<td>
								@if ($row->checked_out && !$canCheckin)
									<?php echo App\Halcyon\Html\Builder\Grid::checkedout($i, $row->editor, $row->checked_out_time, '', $canCheckin); ?>
								@else
									@if ($canEdit)
										<a href="{{ route('admin.listeners.edit', ['id' => $row->id]) }}">
											<!-- {{ trans(strtolower('listener.' . $row->folder . '.' . $row->element . '::' . $row->element . '.listener name')) }} -->
											{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
										</a>
									@else
										{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
									@endif
								@endif
								@if (!$path)
									<div class="text-sm text-muted">{{ trans('listeners::listeners.error missing files') }}</div>
								@endif
							</td>
							<td class="text-center">
								@if ($canEdit)
									@if ($row->enabled)
										<a class="badge badge-success" data-tip="{{ trans('listeners::listeners.click to unpublish') }}" href="{{ route('admin.listeners.unpublish', ['id' => $row->id]) }}">
											{{ trans('listeners::listeners.published') }}
										</a>
									@else
										<a class="badge badge-secondary" data-tip="{{ trans('listeners::listeners.click to publish') }}" href="{{ route('admin.listeners.publish', ['id' => $row->id]) }}">
											{{ trans('listeners::listeners.unpublished') }}
										</a>
									@endif
								@else
									@if ($row->enabled)
										<span class="badge badge-success">
											{{ trans('listeners::listeners.published') }}
										</span>
									@else
										<span class="badge badge-secondary">
											{{ trans('listeners::listeners.unpublished') }}
										</span>
									@endif
								@endif
							</td>
							<td class="priority-3 text-center">
								<span class="badge access {{ str_replace(' ', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
							</td>
							<td class="priority-4 text-center">
								<span class="badge badge-secondary">{{ $row->ordering }}</span>
								@if ($ordering && auth()->user()->can('edit listeners'))
									@if ($filters['order_dir'] == 'asc')
										<span class="ordering-control">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$folders[$i-1] == $row->folder), route('admin.listeners.orderup', ['id' => $row->id])) !!}</span>
										<span class="ordering-control">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$folders[$i+1] == $row->folder), route('admin.listeners.orderdown', ['id' => $row->id])) !!}</span>
									@elseif ($filters['order_dir'] == 'desc')
										<span class="ordering-control">{!! Html::grid('orderUp', (($rows->currentPage() - 1) * $rows->perPage()), $i, (@$folders[$i-1] == $row->folder), route('admin.listeners.orderup', ['id' => $row->id])) !!}</span>
										<span class="ordering-control">{!! Html::grid('orderDown', (($rows->currentPage() - 1) * $rows->perPage()), $i, $rows->total(), (@$folders[$i+1] == $row->folder), route('admin.listeners.orderdown', ['id' => $row->id])) !!}</span>
									@endif
								@endif
							</td>
							<td class="priority-5">
								{{ $row->folder }}
							</td>
							<td class="priority-5">
								{!! App\Halcyon\Utility\Str::highlight(e($row->element), $filters['search']) !!}
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
</form>

@stop