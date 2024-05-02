@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('mailer::mailer.module name'),
		route('admin.mailer.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete mail'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.mailer.delete')) !!}
	@endif
	@if (auth()->user()->can('create mail'))
		{!! Toolbar::addNew(route('admin.mailer.create')) !!}
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
	messages
@endcomponent

<form action="{{ route('admin.mailer.index') }}" method="get" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only visually-hidden">{{ trans('mailer::mailer.menu manager') }}</caption>
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
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('mailer::mailer.sent at'), 'sent_at', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-6">
					{!! Html::grid('sort', trans('mailer::mailer.sent by'), 'sent_by', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-6">
					{{ trans('mailer::mailer.to') }}
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
						<a href="{{ route('admin.mailer.show', ['id' => $row->id]) }}">
							{{ $row->subject }}
						</a>
					@else
						{{ $row->subject }}
					@endif
				</td>
				<td class="priority-6">
					@if ($row->sent_at)
						<time datetime="{{ $row->sent_at->toDateTimeLocalString() }}">
							@if ($row->sent_at->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->sent_at->diffForHumans() }}
							@else
								{{ $row->sent_at->format('Y-m-d') }}
							@endif
						</time>
					@else
						<span class="text-muted never">{{ trans('global.never') }}</span>
					@endif
				</td>
				<td>
					{{ $row->sender ? $row->sender->name : trans('global.unknown') }}
				</td>
				<td>
					<?php
					$recipients = implode(', ', $row->recipients->get('to', []));
					?>
					{{ Illuminate\Support\Str::limit($recipients, 70) }}
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
</form>
@stop