@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var sselects = document.querySelectorAll('.searchable-select');
	if (sselects.length) {
		var sel, sels = new Array();
		sselects.forEach(function (el) {
			sel = new TomSelect(el, {
				plugins: ['dropdown_input']
			});
			sels.push(sel);
		});
	}
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.queues')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit.state queues'))
		{!!
			Toolbar::publishList(route('admin.queues.start'), trans('queues::queues.start scheduling'));
			Toolbar::unpublishList(route('admin.queues.stop'), trans('queues::queues.stop scheduling'));
			Toolbar::spacer();
		!!}
	@endif

	@if ($filters['resource'] && substr($filters['resource'], 0, 1) != 's')
		{!! Toolbar::link('publish', trans('queues::queues.start all scheduling'), route('admin.queues.startall', ['id' => $filters['resource']]), false) !!}
		{!! Toolbar::link('unpublish', trans('queues::queues.stop all scheduling'), route('admin.queues.stopall', ['id' => $filters['resource']]), false) !!}
	@endif

	@if (auth()->user()->can('delete queues'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.queues.delete')) !!}
	@endif

	{!!
		Toolbar::link('export', trans('queues::queues.export'), route('admin.queues.index', ['task' => 'export']), false);
		Toolbar::spacer();
	!!}

	@if (auth()->user()->can('create queues'))
		{!! Toolbar::addNew(route('admin.queues.create')) !!}
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
{{ trans('queues::queues.module name') }}
@stop

@section('content')
@component('queues::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.queues.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right text-end">
				<label class="sr-only visually-hidden" for="filter_state">{{ trans('queues::queues.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all states') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.enabled') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active'): echo ' selected="selected"'; endif;?>>&nbsp; &nbsp; {{ trans('queues::queues.active allocations') }}</option>
					<option value="disabled"<?php if ($filters['state'] == 'disabled'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.disabled') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.trashed') }}</option>
				</select>

				<label class="sr-only visually-hidden" for="filter_type">{{ trans('queues::queues.type') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all types') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					@endforeach
				</select>

				<label class="sr-only visually-hidden" for="filter_resource">{{ trans('queues::queues.resource') }}</label>
				<select name="resource" id="filter_resource" class="form-control filter filter-submit searchable-select">
					<option value="0">{{ trans('queues::queues.all resources') }}</option>
					<?php
					$units = array();
					foreach ($resources as $resource):
						$subresources = $resource->subresources->sortBy('name');
						if (!count($subresources)):
							continue;
						endif;
						$unit = 'nodes';
						if ($facet = $resource->getFacet('allocation_unit')):
							$unit = $facet->value;
						endif;
						$selected = ($resource->id == $filters['resource'] ? ' selected="selected"' : '');
						?>
						<option value="{{ $resource->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $resource->level) . $resource->name }}</option>
						<?php
						foreach ($subresources as $subresource):
							$units[$subresource->id] = $unit;
							$key = 's' . $subresource->id;
							$selected = ($filters['resource'] && $key == (string)$filters['resource'] ? ' selected="selected"' : '');
							?>
							<option value="{{ $key }}"<?php echo $selected; ?>>{{ str_repeat('- ', 1) . $subresource->name }}</option>
							<?php
						endforeach;
					endforeach;
					?>
				</select>

				<label class="sr-only visually-hidden" for="filter_class">{{ trans('queues::queues.class') }}</label>
				<select name="class" id="filter_class" class="form-control filter filter-submit">
					<option value="*">{{ trans('queues::queues.all queue classes') }}</option>
					<option value="system"<?php if ($filters['class'] == 'system'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.system queues') }}</option>
					<option value="owner"<?php if ($filters['class'] == 'owner'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.owner queues') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" id="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" id="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
		<table class="table table-hover adminlist">
			<caption class="sr-only visually-hidden">{{ trans('queues::queues.queues') }}</caption>
			<thead>
				<!-- <tr>
					<th></th>
					<th class="priority-5"></th>
					<th></th>
					<th></th>
					<th></th>
					<th class="priority-4"></th>
					<th class="priority-6"></th>
					<th class="priority-5" colspan="2">{{ trans('queues::queues.nodes') }}</th>
					<th class="priority-5" colspan="2">{{ trans('queues::queues.cores') }}</th>
					<th class="priority-2"></th>
					<th class="priority-6 text-right text-end"></th>
				</tr> -->
				<tr>
					<th>
						{!! Html::grid('checkall') !!}
					</th>
					<th scope="col" class="priority-5">
						{!! Html::grid('sort', trans('queues::queues.id'), 'id', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{!! Html::grid('sort', trans('queues::queues.name'), 'name', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{!! Html::grid('sort', trans('queues::queues.state'), 'enabled', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{!! Html::grid('sort', trans('queues::queues.scheduling'), 'started', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-4">
						{!! Html::grid('sort', trans('queues::queues.group'), 'groupid', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-6 text-center">
						{!! Html::grid('sort', trans('queues::queues.class'), 'groupid', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-5 text-right text-end">
						{{ trans('queues::queues.active allocation') }}
					</th>
					<!-- <th scope="col" class="priority-5 text-right text-end">
						{{ trans('queues::queues.loans') }}
					</th>
					<th scope="col" class="priority-5 text-right text-end">
						{{ trans('queues::queues.total') }}
					</th>
					<th scope="col" class="priority-5 text-right text-end">
						{{ trans('queues::queues.loans') }}
					</th> -->
					<th scope="col" class="priority-2">
						{{ trans('queues::queues.resource') }}
					</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($rows as $i => $row)
					<tr<?php if ($row->trashed()) { echo ' class="trashed"'; } ?>>
						<td>
							@if (auth()->user()->can('edit.state queues') || auth()->user()->can('delete queues'))
								{!! Html::grid('id', $i, $row->id) !!}
							@endif
						</td>
						<td class="priority-5">
							<a href="{{ route('admin.queues.show', ['id' => $row->id]) }}">
								{{ $row->id }}
							</a>
						</td>
						<td>
							<a href="{{ route('admin.queues.show', ['id' => $row->id]) }}">
								{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
							</a>
						</td>
						<td>
							@if ($row->trashed())
								@if (auth()->user()->can('edit queues'))
									<a class="badge badge-danger" href="{{ route('admin.queues.restore', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.set state to', ['state' => trans('global.enabled')]) }}">
										{{ trans('global.trashed') }}
									</a>
								@else
									<span class="badge badge-danger">
										{{ trans('global.trashed') }}
									</span>
								@endif
							@elseif ($row->enabled)
								@if (auth()->user()->can('edit queues'))
									<a class="badge badge-success" href="{{ route('admin.queues.disable', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.set state to', ['state' => trans('global.disabled')]) }}">
										{{ trans('global.enabled') }}
									</a>
								@else
									<span class="badge badge-success">
										{{ trans('global.enabled') }}
									</span>
								@endif
							@else
								@if (auth()->user()->can('edit queues'))
									<a class="badge badge-secondary" href="{{ route('admin.queues.enable', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.set state to', ['state' => trans('global.enabled')]) }}">
										{{ trans('global.disabled') }}
									</a>
								@else
									<span class="badge badge-secondary">
										{{ trans('global.disabled') }}
									</span>
								@endif
							@endif
						</td>
						<td class="text-center">
							@if ($row->trashed())
								@if (auth()->user()->can('edit queues'))
									<a class="text-danger" href="{{ route('admin.queues.restore', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.set state to', ['state' => trans('global.enabled')]) }}">
										<span class="fa fa-trash" aria-hidden="true"></span>
										<span class="sr-only visually-hidden">{{ trans('global.trashed') }}</span>
									</a>
								@else
									<span class="text-danger" data-tip="{{ trans('global.trashed') }}: {{ $row->datetimeremoved->format('Y-m-d') }}">
										<span class="fa fa-trash" aria-hidden="true"></span>
										<span class="sr-only visually-hidden">{{ trans('global.trashed') }}: <time datetime="{{ $row->datetimeremoved->toDateTimeString() }}">{{ $row->datetimeremoved->format('Y-m-d') }}</time></span>
									</span>
								@endif
							@else
								@if ($row->enabled && $row->started && $row->active)
									@if ($row->reservation)
										<a href="{{ route('admin.queues.stop', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.queue has dedicated reservation') }}">
											<span class="fa fa-dot-circle-o" aria-hidden="true"></span>
											<span class="sr-only visually-hidden">{{ trans('queues::queues.queue has dedicated reservation') }}</span>
										</a>
									@else
										<a class="text-success" href="{{ route('admin.queues.stop', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.queue is running') }}">
											<span class="fa fa-check-circle" aria-hidden="true"></span>
											<span class="sr-only visually-hidden">{{ trans('queues::queues.queue is running') }}</span>
										</a>
									@endif
								@elseif ($row->active)
									<a class="text-danger" href="{{ route('admin.queues.start', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.queue is stopped') }}">
										<span class="fa fa-minus-circle" aria-hidden="true"></span>
										<span class="sr-only visually-hidden">{{ trans('queues::queues.queue is stopped') }}</span>
									</a>
								@elseif (!$row->active)
									@if ($row->free)
										<a class="text-info" href="{{ route('admin.queues.start', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.queue is interactive') }}">
											<span class="fa fa-exclamation-circle" aria-hidden="true"></span>
											<span class="sr-only visually-hidden">{{ trans('queues::queues.queue has dedicated reservation') }}</span>
										</a>
									@else
										<a class="text-warning" href="{{ route('admin.queues.start', ['id' => $row->id]) }}" data-tip="{{ trans('queues::queues.queue has no active resources') }}">
											<span class="fa fa-exclamation-triangle" aria-hidden="true"></span>
											<span class="sr-only visually-hidden">{{ trans('queues::queues.queue has no active resources') }}</span>
										</a>
									@endif
								@endif
							@endif
						</td>
						<td class="priority-4">
							@if ($row->group)
								<!-- <a href="{{ route('admin.groups.show', ['id' => $row->groupid]) }}"> -->
									{{ $row->group->name }}
								<!-- </a> -->
							@else
								<span class="text-muted unknown">{{ trans('global.none') }}</span>
							@endif
						</td>
						<td class="priority-6 text-center">
							@if ($row->groupid <= 0)
								<span class="fa fa-microchip" aria-hidden="true"></span> {{ trans('queues::queues.system') }}
							@else
								<span class="fa fa-user" aria-hidden="true"></span> {{ trans('queues::queues.owner') }}
							@endif
						</td>
						<td class="text-right text-end">
							@if (!$row->active)
								@if ($upcoming = $row->getUpcomingLoanOrPurchase())
									@if ($upcoming->serviceunits > 0)
										{{ number_format($upcoming->serviceunits) }} <span class="text-muted">SUs</span>
									@else
										{{ number_format($upcoming->cores) }} <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>
									@endif
									<br /><span class="text-success">starts {{ $upcoming->datetimestart->diffForHumans() }}</span>
								@else
									<span class="text-muted">-</span>
								@endif
							@else
								<?php
								$unit = 'nodes';
								if (isset($units[$row->subresourceid])):
									$unit = $units[$row->subresourceid];
								else:
									if ($row->resource && $facet = $row->resource->getFacet('allocation_unit')):
										$unit = $facet->value;
									endif;

									$units[$row->subresourceid] = $unit;
								endif;
								?>
								@if ($unit == 'sus')
									{{ number_format($row->serviceunits) }} <span class="text-muted">{{ strtolower(trans('queues::queues.' . $unit)) }}</span>
								@elseif ($unit == 'gpus')
									<?php
									$nodes = ($row->subresource->nodecores ? round($row->totalcores / $row->subresource->nodecores, 1) : 0);
									$gpus = ($row->serviceunits && $row->serviceunits > 0 ? $row->serviceunits : round($nodes * $row->subresource->nodegpus));
									?>
									{{ number_format($row->totalcores) }} <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>,
									{{ number_format($gpus) }} <span class="text-muted">{{ strtolower(trans('queues::queues.' . $unit)) }}</span>
								@else
									{{ number_format($row->totalcores) }} <span class="text-muted">{{ strtolower(trans('queues::queues.cores')) }}</span>,
									{{ number_format($row->totalnodes) }} <span class="text-muted">{{ strtolower(trans('queues::queues.nodes')) }}</span>
								@endif
							@endif
						</div>
						<?php
							/*<td class="priority-5 text-right text-end">
							{!! $row->totalnodes ? number_format($row->totalnodes) : '<span class="text-muted none">' . $row->totalnodes . '</span>' !!}
						</td>
						<td class="priority-5 text-right text-end">
							{!! $row->loanednodes ? number_format($row->loanednodes) : '<span class="text-muted none">' . $row->loanednodes . '</span>' !!}
						</td>
						<td class="priority-5 text-right text-end">
							{!! $row->totalcores ? number_format($row->totalcores) : '<span class="text-muted none">' . $row->totalcores . '</span>' !!}
						</td>
						<td class="priority-5 text-right text-end">
							{!! $row->loanedcores ? number_format($row->loanedcores) : '<span class="text-muted none">' . $row->loanedcores . '</span>' !!}
							$soldpercent = $row->totalcores ? round(($row->soldcores / $row->totalcores) * 100, 1) : 0;
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
							</span>
						</td>*/?>
						<td class="priority-2">
							@if ($row->subresourceid)
								@if ($row->subresource)
									{{ $row->subresource->name }}
								@elseif ($row->resource)
									{{ $row->resource->name }}
								@else
									<span class="text-muted unknown">{{ trans('global.unknown') }}</span>
								@endif
							@else
								<span class="text-muted none">{{ trans('global.none') }}</span>
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
