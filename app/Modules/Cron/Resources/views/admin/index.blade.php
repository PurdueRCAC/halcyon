@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/cron/js/admin.js') }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('cron::cron.module name'),
		route('admin.cron.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit.state cron'))
		{!!
			Toolbar::publishList(route('admin.cron.publish'));
			Toolbar::unpublishList(route('admin.cron.unpublish'));
			Toolbar::spacer();
		!!}
	@endif

	@if (auth()->user()->can('delete cron'))
		{!! Toolbar::deleteList('', route('admin.cron.delete')) !!}
	@endif

	@if (auth()->user()->can('create cron'))
		{!! Toolbar::addNew(route('admin.cron.create')) !!}
	@endif

	@if (auth()->user()->can('admin cron'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('cron')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('cron.name') !!}
@stop

@section('content')
<form action="{{ route('admin.cron.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

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
			<div class="col col-md-8 text-right filter-select">
				<label class="sr-only" for="filter_state">{{ trans('cron::cron.state') }}</label>
				<select name="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('cron::cron.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('cron::cron.module name') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete cron'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('cron::cron.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('cron::cron.description'), 'description', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('cron::cron.state'), 'state', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3">
					{!! Html::grid('sort', trans('cron::cron.overlap'), 'dont_overlap', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('cron::cron.active'), 'active', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('cron::cron.last run'), 'last_run', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('cron::cron.next run'), 'next_run', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete cron'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"><span class="sr-only">{{ trans('global.admin.record id', ['id' => $row->id]) }}</span></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit cron'))
						<a href="{{ route('admin.cron.edit', ['id' => $row->id]) }}">
							{{ $row->description }}
						</a>
					@else
						<span>
							{{ $row->description }}
						</span>
					@endif
				</td>
				<td>
					@if (auth()->user()->can('edit cron'))
						@if ($row->state)
							<a class="state publish" href="{{ route('admin.cron.unpublish', ['id' => $row->id]) }}" title="{{ trans('cron::cron.SET_THIS_TO', ['task' => 'unpublish']) }}">
								{{ trans('global.published') }}
							</a>
						@else
							<a class="state unpublish" href="{{ route('admin.cron.publish', ['id' => $row->id]) }}" title="{{ trans('cron::cron.SET_THIS_TO', ['task' => 'publish']) }}">
								{{ trans('global.unpublished') }}
							</a>
						@endif
					@else
						@if ($row->state)
							<span class="state publish">
								{{ trans('global.published') }}
							</span>
						@else
							<span class="state unpublish">
								{{ trans('global.unpublished') }}
							</span>
						@endif
					@endif
				</td>
				<td class="priority-3">
					@if ($row->dont_overlap)
						<span class="badge badge-danger tip" data-tip="{{ trans('cron::cron.dont overlap desc') }}">{{ trans('global.no') }}</span>
					@else
						<span class="badge badge-success tip" data-tip="{{ trans('cron::cron.overlap desc') }}">{{ trans('global.yes') }}</span>
					@endif
				</td>
				<td class="priority-2">
					@if ($row->active)
						<span class="badge badge-warning">
							{{ trans('cron::cron.active') }}
						</span>
					@else
						<span class="badge badge-success">
							{{ trans('cron::cron.inactive') }}
						</span>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->ran_at)
							<time datetime="{{ $row->ran_at }}">{{ $row->ran_at }}</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-2">
					<span class="datetime">
						<?php $nxt = $row->nextRun(); ?>
						<?php if ($nxt && $nxt != '0000-00-00 00:00:00') { ?>
							<time datetime="<?php echo $nxt; ?>"><?php echo $nxt; ?></time>
						<?php } else { ?>
							<?php echo $nxt; ?>
						<?php } ?>
					</span>
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