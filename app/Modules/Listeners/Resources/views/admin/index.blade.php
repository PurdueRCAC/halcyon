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
			Toolbar::publish(route('admin.listeners.publish'), 'JTOOLBAR_ENABLE', true);
			Toolbar::unpublish(route('admin.listeners.unpublish'), 'JTOOLBAR_DISABLE', true);
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
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />

				<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
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
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th class="text-center">
					<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
				</th>
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
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.ordering'), 'ordering', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-4">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.folder'), 'folder', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-4">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.element'), 'element', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-4">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('listeners::listeners.access'), 'access', $filters['order_dir'], $filters['order']); ?>
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
			$canCheckin = auth()->user()->can('manage checkin') || $row->checked_out == auth()->user()->id || $row->checked_out == 0;
			$canChange  = auth()->user()->can('edit.state listeners') && $canCheckin;
			?>
			<tr>
				<td class="text-center">
					@if ($path && $canEdit)
						{!! App\Halcyon\Html\Builder\Grid::id($i, $row->extension_id) !!}
					@elseif (!$path)
						<span class="glyph icon-alert-triangle warning"></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->extension_id }}
				</td>
				<td>
					@if ($row->checked_out)
						<?php echo App\Halcyon\Html\Builder\Grid::checkedout($i, $row->editor, $row->checked_out_time, '', $canCheckin); ?>
					@else
						@if ($canEdit)
							<a href="{{ route('admin.listeners.edit', ['id' => $row->extension_id]) }}">
								{{ trans('listener.' . $row->folder . '.' . $row->element . '::' . $row->element . '.listener name') }}
							</a>
						@else
							{{ $row->name }}
						@endif
					@endif
					@if (!$path)
						<p class="smallsub">{{ trans('listeners::listeners.error missing files') }}</p>
					@endif
				</td>
				<td>
					@if ($canEdit)
						@if ($row->enabled == 1)
							<a class="btn btn-sm published" href="{{ route('admin.listeners.unpublish', ['id' => $row->extension_id]) }}">
								{{ trans('listeners::listeners.published') }}
							</a>
						@else
							<a class="btn btn-sm unpublished" href="{{ route('admin.listeners.publish', ['id' => $row->extension_id]) }}">
								{{ trans('listeners::listeners.unpublished') }}
							</a>
						@endif
					@else
						@if ($row->enabled == 1)
							<span class="state published">
								{{ trans('listeners::listeners.published') }}
							</span>
						@else
							<span class="state unpublished">
								{{ trans('listeners::listeners.unpublished') }}
							</span>
						@endif
					@endif
				</td>
				<td class="priority-3">
					{{ $row->ordering }}
				</td>
				<td class="priority-5">
					{{ $row->folder }}
				</td>
				<td class="priority-5">
					{{ $row->element }}
				</td>
				<td class="priority-4">
					<span class="badge access {{ str_replace(' ', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>

@stop