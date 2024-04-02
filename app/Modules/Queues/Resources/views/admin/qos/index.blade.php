@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.qos')
	);
@endphp

@section('toolbar')
	@if ($filters['state'] == 'trashed')
		@if (auth()->user()->can('delete queues.qos'))
			{!! Toolbar::deleteList('', route('admin.queues.qos.delete')) !!}
		@endif
		@if (auth()->user()->can('edit.state queues.qos'))
			{!!
				Toolbar::custom(route('admin.queues.qos.restore'), 'publish', 'publish', 'Restore', true);
				Toolbar::spacer();
			!!}
		@endif
	@else
		@if (auth()->user()->can('delete queues.qos'))
			{!! Toolbar::deleteList('', route('admin.queues.qos.delete')) !!}
		@endif

		@if (auth()->user()->can('create queues.qos'))
			{!! Toolbar::addNew(route('admin.queues.qos.create')) !!}
		@endif
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
{{ trans('queues::queues.module name') }}: {{ trans('queues::queues.qos') }}
@stop

@section('content')
@component('queues::admin.submenu')
	qos
@endcomponent

<form action="{{ route('admin.queues.qos') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only visually-hidden" for="filter_state">{{ trans('queues::queues.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.all states') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.enabled') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.trashed') }}</option>
				</select>

				<label class="sr-only visually-hidden" for="filter_scheduler">{{ trans('queues::queues.scheduler') }}:</label>
				<select name="scheduler" id="filter_scheduler" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all schedulers') }}</option>
					<?php foreach ($schedulers as $scheduler): ?>
						<?php $selected = ($scheduler->id == $filters['scheduler'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $scheduler->id }}"<?php echo $selected; ?>>{{ $scheduler->hostname }}</option>
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
		<caption class="sr-only visually-hidden">{{ trans('queues::queues.qos') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete queues.qos'))	
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col">
					{!! Html::grid('sort', trans('queues::queues.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('queues::queues.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('queues::queues.description'), 'description', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-right">
					{!! Html::grid('sort', trans('queues::queues.priority'), 'priority', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{{ trans('queues::queues.scheduler') }}
				</th>
				<th scope="col" class="text-right">
					{{ trans('queues::queues.queues') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr{!! ($row->trashed() ? ' class="trashed"' : '') !!}>
				@if (auth()->user()->can('delete queues.qos'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td>
					{{ $row->id }}
				</td>
				<td>
					@if ($row->trashed())
						<span class="text-danger">
							<span class="fa fa-trash" aria-hidden="true"></span>
							<span class="sr-only visually-hidden">{{ trans('global.trashed') }}</span>
						</span>
					@endif
					@if (auth()->user()->can('edit queues.qos'))
						<a href="{{ route('admin.queues.qos.edit', ['id' => $row->id]) }}">
							{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
						</a>
					@else
						{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
					@endif
				</td>
				<td>
					{{ $row->description }}
				</td>
				<td class="text-right">
					{{ $row->priority }}
				</td>
				<td>
					{{ $row->scheduler ? $row->scheduler->hostname : '' }}
				</td>
				<td class="text-right">
					@if (auth()->user()->can('edit queues.qos'))
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
</form>

@stop
