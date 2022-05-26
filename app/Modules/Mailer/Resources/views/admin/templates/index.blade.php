@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('mailer::mailer.module name'),
		route('admin.mailer.index')
	)
	->append(
		trans('mailer::mailer.templates'),
		route('admin.mailer.templates')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete mail'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.mailer.templates.delete')) !!}
	@endif
	@if (auth()->user()->can('create mail'))
		{!! Toolbar::addNew(route('admin.mailer.templates.create')) !!}
	@endif
	@if (auth()->user()->can('admin mailer'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('mailer')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('mailer::mailer.module name') }}
@stop

@section('content')
@component('mailer::admin.submenu')
	templates
@endcomponent

<form action="{{ route('admin.mailer.templates') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

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

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('mailer::mailer.templates') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('mailer::mailer.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('mailer::mailer.subject'), 'subject', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3">
					{!! Html::grid('sort', trans('mailer::mailer.created'), 'created_at', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('mailer::mailer.modified'), 'updated_at', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('mailer::mailer.alert level'), 'alert', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-6 text-right">
					{{ trans('mailer::mailer.options') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit mail'))
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</td>
				<td class="priority-6">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit mail'))
						<a href="{{ route('admin.mailer.templates.edit', ['id' => $row->id]) }}">
							{{ $row->subject }}
						</a>
					@else
						{{ $row->subject }}
					@endif
				</td>
				<td class="priority-4">
					<time datetime="{{ $row->created_at->toDateTimeLocalString() }}">
						@if ($row->created_at->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
							{{ $row->created_at->diffForHumans() }}
						@else
							{{ $row->created_at->format('Y-m-d') }}
						@endif
					</time>
				</td>
				<td class="priority-4">
					@if ($row->updated_at)
						<time datetime="{{ $row->updated_at->toDateTimeLocalString() }}">
							@if ($row->updated_at->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->updated_at->diffForHumans() }}
							@else
								{{ $row->updated_at->format('Y-m-d') }}
							@endif
						</time>
					@else
						<span class="text-muted never">{{ trans('global.never') }}</span>
					@endif
				</td>
				<td class="priority-6">
					@if ($row->alert)
						<span class="text-{{ $row->alert }}">{{ trans('mailer::mailer.alert.' . $row->alert) }}</span>
					@else
						<span class="text-muted">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="text-right">
					<a class="btn btn-secondary" href="{{ route('admin.mailer.create', ['id' => $row->id]) }}">
						<span class="fa fa-plus" aria-hidden="true"></span>
						{{ trans('mailer::mailer.use template') }}
					</a>
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