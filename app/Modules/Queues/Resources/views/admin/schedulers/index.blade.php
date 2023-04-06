@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.schedulers')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete queues.schedulers'))
		{!! Toolbar::deleteList('', route('admin.queues.schedulers.delete')) !!}
	@endif

	@if (auth()->user()->can('create queues.schedulers'))
		{!! Toolbar::addNew(route('admin.queues.schedulers.create')) !!}
	@endif

	@if (auth()->user()->can('admin queues'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('queues')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('queues::queues.module name') }}: {{ trans('queues::queues.schedulers') }}
@stop

@section('content')
@component('queues::admin.submenu')
	{{ request()->segment(3) }}
@endcomponent

<form action="{{ route('admin.queues.schedulers') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only" for="filter_state">{{ trans('queues::queues.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.all states') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.enabled') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_batchsystem">{{ trans('queues::queues.batch system') }}:</label>
				<select name="batchsystem" id="filter_batchsystem" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all batch systems') }}</option>
					<?php foreach ($batchsystems as $batchsystem): ?>
						<?php $selected = ($batchsystem->id == $filters['batchsystem'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $batchsystem->id }}"<?php echo $selected; ?>>{{ $batchsystem->name }}</option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only" for="filter_policy">{{ trans('queues::queues.scheduler policy') }}:</label>
				<select name="policy" id="filter_policy" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all scheduler policies') }}</option>
					<?php foreach ($policies as $policy): ?>
						<?php $selected = ($policy->id == $filters['policy'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $policy->id }}"<?php echo $selected; ?>>{{ $policy->name }}</option>
					<?php endforeach; ?>
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
		<caption class="sr-only">{{ trans('queues::queues.schedulers') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete queues.schedulers'))	
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('queues::queues.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('queues::queues.hostname'), 'hostname', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-center">
					{!! Html::grid('sort', trans('queues::queues.batch system'), 'batchsystem', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('queues::queues.scheduler policy'), 'schedulerpolicyid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-center">
					{!! Html::grid('sort', trans('queues::queues.default max walltime'), 'defaultmaxwalltime', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3 text-right">
					{{ trans('queues::queues.queues') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr{!! ($row->trashed() ? ' class="trashed"' : '') !!}>
				@if (auth()->user()->can('delete queues.schedulers'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if ($row->trashed())
						<span class="glyph icon-trash text-danger">{{ trans('global.trashed') }}</span>
					@endif
					@if (auth()->user()->can('edit queues.schedulers'))
						<a href="{{ route('admin.queues.schedulers.edit', ['id' => $row->id]) }}">
							{!! App\Halcyon\Utility\Str::highlight(e($row->hostname), $filters['search']) !!}
						</a>
					@else
						{!! App\Halcyon\Utility\Str::highlight(e($row->hostname), $filters['search']) !!}
					@endif
				</td>
				<td class="priority-4 text-center">
					{{ $row->batchsystm ? $row->batchsystm->name : '' }}
				</td>
				<td class="priority-5">
					{{ $row->policy ? $row->policy->name : '' }}
				</td>
				<td class="priority-4 text-center">
					@if ($row->defaultmaxwalltime)
						{{ $row->humanDefaultmaxwalltime() }}
					@endif
				</td>
				<td class="priority-3 text-right">
					@if (auth()->user()->can('edit queues.schedulers'))
						<a href="{{ route('admin.queues.index') }}?type={{ $row->id }}">
							{{ number_format($row->queues_count) }}
						</a>
					@else
						{{ number_format($row->queues_count) }}
					@endif
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

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop
