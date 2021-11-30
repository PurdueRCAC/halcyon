@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('tags::tags.module name'),
		route('admin.tags.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete tags'))
		{!! Toolbar::deleteList('', route('admin.tags.delete')) !!}
	@endif

	@if (auth()->user()->can('create tags'))
		{!! Toolbar::addNew(route('admin.tags.create')) !!}
	@endif

	@if (auth()->user()->can('admin tags'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('tags')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('tags.name') !!}
@stop

@section('content')
<form action="{{ route('admin.tags.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar">
		<div class="row grid">
			<div class="col-md-12 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button type="submit" class="btn btn-secondary sr-only">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<table class="table table-hover adminlist">
			<caption class="sr-only">{{ trans('tags::tags.tags') }}</caption>
			<thead>
				<tr>
					@if (auth()->user()->can('delete tags'))
						<th>
							{!! Html::grid('checkall') !!}
						</th>
					@endif
					<th scope="col" class="priority-5">
						{!! Html::grid('sort', trans('tags::tags.id'), 'id', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{!! Html::grid('sort', trans('tags::tags.name'), 'name', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-2">
						{!! Html::grid('sort', trans('tags::tags.slug'), 'slug', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-3 text-right">
						{!! Html::grid('sort', trans('tags::tags.tagged'), 'tagged_count', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-3 text-right">
						{!! Html::grid('sort', trans('tags::tags.aliases'), 'alias_count', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-4 text-center">
						{!! Html::grid('sort', trans('tags::tags.created'), 'created', $filters['order_dir'], $filters['order']) !!}
					</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($rows as $i => $row)
					<tr>
						@if (auth()->user()->can('delete tags'))
							<td>
								{!! Html::grid('id', $i, $row->id) !!}
							</td>
						@endif
						<td class="priority-5">
							{{ $row->id }}
						</td>
						<td>
							@if (auth()->user()->can('edit tags'))
								<a href="{{ route('admin.tags.edit', ['id' => $row->id]) }}">
									{{ $row->name }}
								</a>
							@else
								{{ $row->name }}
							@endif
						</td>
						<td class="priority-2">
							@if (auth()->user()->can('edit tags'))
								<a href="{{ route('admin.tags.edit', ['id' => $row->id]) }}">
									{{ $row->slug }}
								</a>
							@else
								{{ $row->slug }}
							@endif
						</td>
						<td class="priority-3 text-right">
							<?php
							if (!$row->tagged_count):
								$c = $row->tagged()->count();
								if ($c):
									$row->update(['tagged_count' => $c]);
								endif;
							endif;
							?>
							@if (!$row->tagged_count)
								<span class="none">
							@endif
							{{ $row->tagged_count }}
							@if (!$row->tagged_count)
								</span>
							@endif
						</td>
						<td class="priority-3 text-right">
							@if (!$row->alias_count)
								<span class="none">
							@endif
							{{ $row->alias_count }}
							@if (!$row->alias_count)
								</span>
							@endif
						</td>
						<td class="priority-4 text-center">
							<span class="datetime">
								@if ($row->created_at)
									<time datetime="{{ $row->created_at->toDateTimeString() }}">
										@if ($row->created_at->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
											{{ $row->created_at->diffForHumans() }}
										@else
											{{ $row->created_at->format('Y-m-d') }}
										@endif
									</time>
								@else
									<span class="never">{{ trans('global.unknown') }}</span>
								@endif
							</span>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
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
