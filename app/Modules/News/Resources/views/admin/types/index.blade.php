@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('news::news.module name'),
		route('admin.news.index')
	)
	->append(
		trans('news::news.types')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete news.types'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.news.types.delete')) !!}
	@endif

	@if (auth()->user()->can('create news.types'))
		{!! Toolbar::addNew(route('admin.news.types.create')) !!}
	@endif

	@if (auth()->user()->can('admin news'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('news')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('news.name') !!}: {{ trans('news::news.types') }}
@stop

@section('content')

@component('news::admin.submenu')
	types
@endcomponent

<form action="{{ route('admin.news.types') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('news::news.types') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete news.types'))
					<th>
						<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
					</th>
				@endif
				<th scope="col" class="priority-5">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.id'), 'id', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.name'), 'name', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-5 text-center">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.location'), 'location', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-5 text-center">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.future'), 'future', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-5 text-center">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.ongoing'), 'ongoing', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-5 text-center">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.tag resources'), 'tagresources', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-5 text-center">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.tag users'), 'tagusers', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col" class="priority-5 text-center">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('news::news.url'), 'url', $filters['order_dir'], $filters['order']); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete news.types'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td class="priority-6">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit news.types'))
						<a href="{{ route('admin.news.types.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						<span>
							{{ $row->name }}
						</span>
					@endif
				</td>
				<td class="priority-5 text-center">
					@if ($row->location)
						<span class="badge badge-success">
							<span class="icon-check" aria-hidden="true"></span><span class="sr-only">{{ trans('global.yes') }}</span>
						</span>
					@else
						<span class="badge badge-secondary">
							<span class="icon-minus" aria-hidden="true"></span><span class="sr-only">{{ trans('global.no') }}</span>
						</span>
					@endif
				</td>
				<td class="priority-5 text-center">
					@if ($row->future)
						<span class="badge badge-success">
							<span class="icon-check" aria-hidden="true"></span><span class="sr-only">{{ trans('global.yes') }}</span>
						</span>
					@else
						<span class="badge badge-secondary">
							<span class="icon-minus" aria-hidden="true"></span><span class="sr-only">{{ trans('global.no') }}</span>
						</span>
					@endif
				</td>
				<td class="priority-5 text-center">
					@if ($row->ongoing)
						<span class="badge badge-success">
							<span class="icon-check" aria-hidden="true"></span><span class="sr-only">{{ trans('global.yes') }}</span>
						</span>
					@else
						<span class="badge badge-secondary">
							<span class="icon-minus" aria-hidden="true"></span><span class="sr-only">{{ trans('global.no') }}</span>
						</span>
					@endif
				</td>
				<td class="priority-5 text-center">
					@if ($row->tagresources)
						<span class="badge badge-success">
							<span class="icon-check" aria-hidden="true"></span><span class="sr-only">{{ trans('global.yes') }}</span>
						</span>
					@else
						<span class="badge badge-secondary">
							<span class="icon-minus" aria-hidden="true"></span><span class="sr-only">{{ trans('global.no') }}</span>
						</span>
					@endif
				</td>
				<td class="priority-5 text-center">
					@if ($row->tagusers)
						<span class="badge badge-success">
							<span class="icon-check" aria-hidden="true"></span><span class="sr-only">{{ trans('global.yes') }}</span>
						</span>
					@else
						<span class="badge badge-secondary">
							<span class="icon-minus" aria-hidden="true"></span><span class="sr-only">{{ trans('global.no') }}</span>
						</span>
					@endif
				</td>
				<td class="priority-5 text-center">
					@if ($row->url)
						<span class="badge badge-success">
							<span class="icon-check" aria-hidden="true"></span><span class="sr-only">{{ trans('global.yes') }}</span>
						</span>
					@else
						<span class="badge badge-secondary">
							<span class="icon-minus" aria-hidden="true"></span><span class="sr-only">{{ trans('global.no') }}</span>
						</span>
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

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop