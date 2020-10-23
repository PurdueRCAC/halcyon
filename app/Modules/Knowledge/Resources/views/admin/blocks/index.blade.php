@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/knowledge/js/admin.js?v=' . filemtime(public_path() . '/modules/knowledge/js/admin.js')) }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('knowledge::knowledge.knowledge base'),
		route('admin.knowledge.index')
	)
	->append(
		trans('knowledge::knowledge.blocks'),
		route('admin.knowledge.blocks')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete knowledge'))
		{!! Toolbar::deleteList('', route('admin.knowledge.blocks.delete')) !!}
	@endif

	@if (auth()->user()->can('create knowledge'))
		{!! Toolbar::addNew(route('admin.knowledge.blocks.create')) !!}
	@endif

	@if (auth()->user()->can('admin knowledge'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('knowledge')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('knowledge.name') !!}: {{ trans('knowledge::knowledge.blocks') }}
@stop

@section('content')

@component('knowledge::admin.submenu')
	blocks
@endcomponent

<form action="{{ route('admin.knowledge.blocks') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('knowledge::knowledge.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('knowledge::knowledge.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('knowledge::knowledge.last update'), 'updated_at', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-right">
					{{ trans('knowledge::knowledge.used') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit knowledge'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit knowledge'))
						<a href="{{ route('admin.knowledge.blocks.edit', ['id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit($row->title, 70) }}
						</a>
					@else
						<span>
							{{ Illuminate\Support\Str::limit($row->title, 70) }}
						</span>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->getOriginal('updated_at') && $row->getOriginal('updated_at') != '0000-00-00 00:00:00')
							<time datetime="{{ Carbon\Carbon::parse($row->updated_at)->format('Y-m-d\TH:i:s\Z') }}">{{ $row->updated_at }}</time>
						@else
							@if ($row->getOriginal('created_at') && $row->getOriginal('created_at') != '0000-00-00 00:00:00')
								<time datetime="{{ Carbon\Carbon::parse($row->created_at)->format('Y-m-d\TH:i:s\Z') }}">
									@if ($row->getOriginal('created_at') > Carbon\Carbon::now()->toDateTimeString())
										{{ $row->created_at->diffForHumans() }}
									@else
										{{ $row->created_at }}
									@endif
								</time>
							@else
								<span class="never">{{ trans('global.unknown') }}</span>
							@endif
						@endif
					</span>
				</td>
				<td class="text-right">
					{{ $row->parents()->count() }}
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop