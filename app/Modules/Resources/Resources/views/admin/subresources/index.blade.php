@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		trans('resources::resources.subresources'),
		route('admin.resources.subresources')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete resources'))
		@if ($filters['state'] == 'trashed')
			{!! Toolbar::custom(route('admin.resources.subresources.restore'), 'refresh', 'refresh', trans('global.restore'), false) !!}
		@else
			{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.resources.subresources.delete')) !!}
		@endif
	@endif

	@if (auth()->user()->can('create resources'))
		{!! Toolbar::addNew(route('admin.resources.subresources.create', ['resource' => $filters['resource']])) !!}
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

<form action="{{ route('admin.resources.subresources') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col filter-search col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col filter-select col-md-8 text-right">
				<label class="sr-only" for="filter_state">{{ trans('resources::assets.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="all"<?php if ($filters['state'] == 'all'): echo ' selected="selected"'; endif;?>>{{ trans('resources::assets.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_resource">{{ trans('resources::assets.resource') }}</label>
				<select name="resource" id="filter_resource" class="form-control filter filter-submit">
					<option value="0">{{ trans('resources::assets.all resources') }}</option>
					<?php foreach ($resources as $resource): ?>
						<option value="{{ $resource->id }}"<?php if ($filters['resource'] == $resource->id): echo ' selected="selected"'; endif;?>>{{ str_repeat('- ', $resource->level) . $resource->name }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('resources::resources.subresources') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('edit.state resources') || auth()->user()->can('delete resources'))
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
				<th scope="col" class="text-center">
					{!! Html::grid('sort', trans('resources::assets.cluster'), 'cluster', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{{ trans('resources::assets.resource') }}
				</th>
				<th scope="col" class="priority-4 text-right">
					{!! Html::grid('sort', trans('resources::assets.node mem'), 'nodemem', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3 text-right">
					{!! Html::grid('sort', trans('resources::assets.node cores'), 'nodecores', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{!! Html::grid('sort', trans('resources::assets.node gpus'), 'nodegpus', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">{{ trans('resources::assets.node attributes') }}</th>
				<th scope="col" class="priority-4">{{ trans('resources::assets.queues') }}</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<?php
			$cls = $row->isTrashed() ? 'trashed' : 'active';
			?>
			<tr class="{{ $cls }}">
				@if (auth()->user()->can('edit.state resources') || auth()->user()->can('delete resources'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					{!! $row->treename !!}
					@if ($row->isTrashed())
						<span class="fa fa-trash unknown" aria-hidden="true"></span>
					@endif
					@if (auth()->user()->can('edit resources') || auth()->user()->can('delete resources'))
						<a href="{{ route('admin.resources.subresources.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit resources') || auth()->user()->can('delete resources'))
						</a>
					@endif
				</td>
				<td class="text-center">
					@if (auth()->user()->can('edit resources') || auth()->user()->can('delete resources'))
						<a href="{{ route('admin.resources.subresources.edit', ['id' => $row->id]) }}">
					@endif
						{!! $row->cluster ? $row->cluster : '<span class="unknown">' . trans('global.none') . '</span>' !!}
					@if (auth()->user()->can('edit resources') || auth()->user()->can('delete resources'))
						</a>
					@endif
				</td>
				<td>
					{!! $row->association ? $row->association->resource->name : '<span class="unknown">' . trans('global.none') . '</span>' !!}
				</td>
				<td class="priority-4 text-right">
					{!! $row->nodemem ? $row->nodemem : '<span class="unknown">' . trans('global.none') . '</span>' !!}
				</td>
				<td class="priority-3 text-right">
					{{ $row->nodecores }}
					<?php
					/*$soldpercent = $row->totalcores ? round(($row->soldcores / $row->totalcores) * 100, 1) : 0;
					$loanedpercent = $row->totalcores ? round(($row->loanedcores / $row->totalcores) * 100, 1) : 0;
					echo 'total cores: ' . $row->totalcores . ' avail: ' . ($row->totalcores - $row->soldcores - $row->loanedcores);
					?>
					<!-- <div class="row">
						<div class="col col-md-4">{{ $row->soldcores }} sold</div>
						<div class="col col-md-4">{{ $row->loanedcores }} loaned</div>
						<div class="col col-md-4">of {{ $row->totalcores }}</div>
					</div> -->
					<span class="progress" style="height: 0.2em">
						<span class="progress-bar bg-info" style="width: <?php echo $soldpercent; ?>%" aria-valuenow="<?php echo $soldpercent; ?>" aria-valuemin="0" aria-valuemax="100"></span>
						<span class="progress-bar bg-warning" style="width: <?php echo $loanedpercent; ?>%" aria-valuenow="<?php echo $loanedpercent; ?>" aria-valuemin="0" aria-valuemax="100"></span>
					</span>*/?>
				</td>
				<td class="priority-4 text-right">
					{{ $row->nodegpus }}
				</td>
				<td class="priority-4">
					{!! $row->nodeattributes ? $row->nodeattributes : '<span class="unknown">' . trans('global.none') . '</span>' !!}
				</td>
				<td class="priority-4 text-right">
					@if ($row->queuestatus > 1)
						<span class="glyph icon-alert-triangle text-warning tip" aria-hidden="true" data-tip="{{ trans('One or more stopped queues') }}">{{ trans('One or more stopped queues') }}</span>
					@endif
					<a href="{{ route('admin.queues.index', ['resource' => $row->resourceid]) }}">
						{{ $row->queues_count }}
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop
