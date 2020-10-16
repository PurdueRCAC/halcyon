@extends('layouts.master')

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
			Toolbar::publishList(route('admin.queues.enable'));
			Toolbar::unpublishList(route('admin.queues.disable'));
			Toolbar::spacer();
		!!}
	@endif

	@if (auth()->user()->can('create queues'))
		{!! Toolbar::addNew(route('admin.queues.create')) !!}
	@endif

	@if (auth()->user()->can('delete queues'))
		{!! Toolbar::deleteList('', route('admin.queues.delete')) !!}
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
{!! config('queues.name') !!}
@stop

@section('content')
@component('queues::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.queues.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
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
				<label class="sr-only" for="filter_state">{{ trans('queues::queues.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.all states') }}</option>
					<option value="enabled"<?php if ($filters['state'] == 'enabled'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.enabled') }}</option>
					<option value="disabled"<?php if ($filters['state'] == 'disabled'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.disabled') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_type">{{ trans('queues::queues.type') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all types') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only" for="filter_resource">{{ trans('queues::queues.resource') }}:</label>
				<select name="resource" id="filter_resource" class="form-control filter filter-submit">
					<option value="0">{{ trans('queues::queues.all resources') }}</option>
					<?php foreach ($resources as $resource): ?>
						<?php $selected = ($resource->id == $filters['resource'] ? ' selected="selected"' : ''); ?>
						<option value="{{ $resource->id }}"<?php echo $selected; ?>>{{ str_repeat('- ', $resource->level) . $resource->name }}</option>
					<?php endforeach; ?>
				</select>

				<label class="sr-only" for="filter_class">{{ trans('queues::queues.class') }}:</label>
				<select name="class" id="filter_class" class="form-control filter filter-submit">
					<option value="*">{{ trans('queues::queues.all queue classes') }}</option>
					<option value="system"<?php if ($filters['class'] == 'system'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.system queues') }}</option>
					<option value="owner"<?php if ($filters['class'] == 'owner'): echo ' selected="selected"'; endif;?>>{{ trans('queues::queues.owner queues') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" id="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" id="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('queues::queues.queues') }}</caption>
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th colspan="2" class="text-center">{{ trans('queues::queues.nodes') }}</th>
				<th colspan="2" class="text-center">{{ trans('queues::queues.cores') }}</th>
				<th></th>
				<th></th>
			</tr>
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
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('queues::queues.state'), 'enabled', $filters['order_dir'], $filters['order']) !!}
				</th>
				<!-- <th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('queues::queues.cluster'), 'cluster', $filters['order_dir'], $filters['order']) !!}
				</th> -->
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('queues::queues.group'), 'groupid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-center">
					{!! Html::grid('sort', trans('queues::queues.class'), 'groupid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-right">
					{{ trans('queues::queues.total') }}
				</th>
				<th scope="col" class="text-right">
					{{ trans('queues::queues.loans') }}
				</th>
				<th scope="col" class="text-right">
					{{ trans('queues::queues.total') }}
				</th>
				<th scope="col" class="text-right">
					{{ trans('queues::queues.loans') }}
				</th>
				<th scope="col">
					{{ trans('queues::queues.resource') }}
				</th>
				<th scope="col" class="text-right">
					{{ trans('queues::queues.walltime') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr<?php if ($row->isTrashed()) { echo ' class="trashed"'; } ?>>
				<td>
					@if (auth()->user()->can('edit queues'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					<!--
					@if ($row->groupid <= 0)
						<span class="glyph icon-cpu" data-tip="{{ trans('queues::queues.system') }}">{{ trans('queues::queues.system') }}</span>
					@else
						<span class="glyph icon-user" data-tip="{{ trans('queues::queues.owner') }}">{{ trans('queues::queues.owner') }}</span>
					@endif
					-->
					@if (auth()->user()->can('edit queues'))
					<a href="{{ route('admin.queues.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit queues'))
					</a>
					@endif
				</td>
				<!-- <td class="text-right">
					@if (auth()->user()->can('edit queues'))
					<a href="{{ route('admin.queues.edit', ['id' => $row->id]) }}">
					@endif
						{{ number_format($row->defaultwalltime / 60) }} min
					@if (auth()->user()->can('edit queues'))
					</a>
					@endif
				</td>
				<td class="priority-4">
					@if ($row->getOriginal('datetimeremoved') && $row->getOriginal('datetimeremoved') != '0000-00-00 00:00:00' && $row->getOriginal('datetimeremoved') != '-0001-11-30 00:00:00')
						@if (auth()->user()->can('edit queues'))
							<a class="btn btn-secondary state trashed" href="{{ route('admin.queues.restore', ['id' => $row->id]) }}" title="{{ trans('queues::queues.set state to', ['state' => trans('global.enabled')]) }}">
								{{ trans('global.trashed') }}
							</a>
						@else
							<span class="badge state trashed">
								{{ trans('global.trashed') }}
							</span>
						@endif
					@elseif ($row->enabled)
						@if (auth()->user()->can('edit queues'))
							<a class="btn btn-secondary state published" href="{{ route('admin.queues.disable', ['id' => $row->id]) }}" title="{{ trans('queues::queues.set state to', ['state' => trans('global.disabled')]) }}">
								{{ trans('global.enabled') }}
							</a>
						@else
							<span class="badge state published">
								{{ trans('global.enabled') }}
							</span>
						@endif
					@else
						@if (auth()->user()->can('edit queues'))
							<a class="btn btn-secondary state unpublished" href="{{ route('admin.queues.enable', ['id' => $row->id]) }}" title="{{ trans('queues::queues.set state to', ['state' => trans('global.enabled')]) }}">
								{{ trans('global.disabled') }}
							</a>
						@else
							<span class="badge state unpublished">
								{{ trans('global.disabled') }}
							</span>
						@endif
					@endif
				</td> -->
				<td class="text-center">
					@if ($row->datetimeremoved && $row->datetimeremoved != '0000-00-00 00:00:00' && $row->datetimeremoved != '-0001-11-30 00:00:00')
						@if (auth()->user()->can('edit queues'))
							<a class="glyph icon-trash" href="{{ route('admin.queues.restore', ['id' => $row->id]) }}" title="{{ trans('queues::queues.set state to', ['state' => trans('global.enabled')]) }}">
								{{ trans('global.trashed') }}
							</a>
						@else
							<span class="glyph icon-trash">
								{{ trans('global.trashed') }}
							</span>
						@endif
					@else
						<?php if ($row->enabled && $row->started && $row->active) { ?>
							<?php if ($row->reservation) { ?>
								<a class="glyph icon-circle" href="{{ route('admin.queues.stop', ['id' => $row->id]) }}" data-tip="Queue has dedicated reservation.">
									{{ trans('Queue has dedicated reservation.') }}
								</a>
							<?php } else { ?>
								<a class="glyph icon-check-circle success" href="{{ route('admin.queues.stop', ['id' => $row->id]) }}" data-tip="Queue is running.">
									{{ trans('Queue is running.') }}
								</a>
							<?php } ?>
						<?php } else if ($row->active) { ?>
							<a class="glyph icon-minus-circle" href="{{ route('admin.queues.start', ['id' => $row->id]) }}" data-tip="{{ trans('Queue is stopped or disabled.') }}">
								{{ trans('Queue is stopped or disabled.') }}
							</a>
						<?php } else if (!$row->active) { ?>
							<a class="glyph icon-alert-triangle warning" href="{{ route('admin.queues.start', ['id' => $row->id]) }}" data-tip="{{ trans('Queue has no active resources. Remove queue or sell/loan nodes.') }}">
								{{ trans('Queue has no active resources. Remove queue or sell/loan nodes.') }}
							</a>
						<?php } ?>
					@endif
				</td>
				<!-- <td>
					{{ $row->cluster }}
				</td> -->
				<td class="priority-4">
					@if ($row->group)
						<a href="{{ route('admin.groups.edit', ['id' => $row->groupid]) }}">
							{{ $row->group->name }}
						</a>
					@else
						<span class="unknown">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-4 text-center">
					@if ($row->groupid <= 0)
						<span class="icon-cpu" data-tip="{{ trans('queues::queues.system') }}">{{ trans('queues::queues.system') }}</span>
					@else
						<span class="icon-user" data-tip="{{ trans('queues::queues.owner') }}">{{ trans('queues::queues.owner') }}</span>
					@endif
				</td>
				<td class="text-right">
					{!! $row->totalnodes ? $row->totalnodes : '<span class="none">' . $row->totalnodes . '</span>' !!}
				</td>
				<td class="text-right">
					{!! $row->loanednodes ? $row->loanednodes : '<span class="none">' . $row->loanednodes . '</span>' !!}
				</td>
				<td class="text-right">
					{!! $row->totalcores ? $row->totalcores : '<span class="none">' . $row->totalcores . '</span>' !!}
				</td>
				<td class="text-right">
					{!! $row->loanedcores ? $row->loanedcores : '<span class="none">' . $row->loanedcores . '</span>' !!}
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
				<td class="priority-4">
					@if ($row->subresourceid)
						@if ($row->resource)
							@if ($row->subresource)
								<span data-tip="{{ $row->subresource->name }}">
							@endif
							{{ $row->resource->name }}
							@if ($row->subresource)
								</span>
							@endif
						@else
							<span class="unknown">{{ trans('global.unknown') }}</span>
						@endif
						<!--(
						@if ($row->subresource)
							{{ $row->subresource->name }}
						@else
							<span class="unknown">{{ trans('global.unknown') }}</span>
						@endif
						)-->
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="text-right">
					<?php
					$walltime = $row->walltimes()->first();
					if ($walltime)
					{
						echo $row->walltimes()->first()->humanWalltime;
					}
					?>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	<div class="container-fluid">
		<div class="row">
			<div class="col col-md-8">
				{{ $rows->render() }}
			</div>
			<div class="col col-md-4 text-right">
				{{ $rows->total() }}
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop
