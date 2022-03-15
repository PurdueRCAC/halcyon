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
{!! trans('listeners::listeners.listener manager') !!}
@stop

@section('content')
<form action="{{ route('admin.listeners.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row grid">
			<div class="col col-md-4 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only" for="filter_state">{{ trans('listeners::listeners.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="">{{ trans('listeners::listeners.state all') }}</option>
					<?php echo App\Halcyon\Html\Builder\Select::options(App\Modules\Listeners\Helpers\Admin::stateOptions(), 'value', 'text', $filters['state'], true); ?>
				</select>

				<label class="sr-only" for="filter_folder">{{ trans('listeners::listeners.select folder') }}</label>
				<select name="folder" id="filter_folder" class="form-control filter filter-submit">
					<option value="">{{ trans('listeners::listeners.select folder') }}</option>
					<?php echo App\Halcyon\Html\Builder\Select::options(App\Modules\Listeners\Helpers\Admin::folderOptions(), 'value', 'text', $filters['folder']); ?>
				</select>

				<label class="sr-only" for="filter_access">{{ trans('listeners::listeners.access') }}</label>
				<select name="faccess" id="filter_access" class="form-control filter filter-submit">
					<option value="">{{ trans('listeners::listeners.access all') }}</option>
					<?php echo App\Halcyon\Html\Builder\Select::options(App\Halcyon\Html\Builder\Access::assetgroups(), 'value', 'text', $filters['access']); ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{!! trans('listeners::listeners.listener manager') !!}</caption>
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
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.state'), 'enabled', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-3">
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
		@foreach ($rows as $i => $row)
			<?php
			$row->registerLanguage();
			$path = $row->path;

			if (!$path):
				$row->enabled = 0;
			endif;

			$ordering   = ($filters['order'] == 'ordering');
			$canEdit    = $path ? auth()->user()->can('edit listeners') : false;
			$canCheckin = auth()->user()->can('manage listeners') || $row->checked_out == auth()->user()->id || $row->checked_out == 0;
			$canChange  = auth()->user()->can('edit.state listeners') && $canCheckin;
			?>
			<tr>
				<td class="text-center">
					@if ($path && $canEdit)
						{!! App\Halcyon\Html\Builder\Grid::id($i, $row->id) !!}
					@elseif (!$path)
						<span class="fa fa-exclamation-triangle text-warning"></span>
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
								{{ trans(strtolower('listener.' . $row->folder . '.' . $row->element . '::' . $row->element . '.listener name')) }}
							</a>
						@else
							{{ $row->name }}
						@endif
					@endif
					@if (!$path)
						<div class="text-sm text-muted">{{ trans('listeners::listeners.error missing files') }}</div>
					@endif
				</td>
				<td>
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
				<td class="priority-3">
					<span class="badge access {{ str_replace(' ', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
				</td>
				<td class="priority-4 text-center">
					{{ $row->ordering }}
				</td>
				<td class="priority-5">
					{{ $row->folder }}
				</td>
				<td class="priority-5">
					{{ $row->element }}
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop