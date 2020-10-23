@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/contactreports/js/admin.js') }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('contactreports::contactreports.module name'),
		route('admin.contactreports.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete contactreports'))
		{!! Toolbar::deleteList('', route('admin.contactreports.delete')) !!}
	@endif

	@if (auth()->user()->can('create contactreports'))
		{!! Toolbar::addNew(route('admin.contactreports.create')) !!}
	@endif

	@if (auth()->user()->can('admin contactreports'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('contactreports')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('contactreports.name') !!}
@stop

@section('content')
<form action="{{ route('admin.contactreports.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-6">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-3">
				<label class="sr-only" for="filter_start">{{ trans('contactreports::contactreports.start') }}</label>
				<span class="input-group">
					<input type="text" name="start" id="filter_start" class="form-control filter filter-submit date" value="{{ $filters['start'] }}" placeholder="Start date" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span>
				</span>
			</div>
			<div class="col col-md-3">
				<label class="sr-only" for="filter_stop">{{ trans('contactreports::contactreports.stop') }}</label>
				<span class="input-group">
					<input type="text" name="stop" id="filter_stop" class="form-control filter filter-submit date" value="{{ $filters['stop'] }}" placeholder="End date" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
				</span>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('contactreports::contactreports.contact reports') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('contactreports::contactreports.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('contactreports::contactreports.report'), 'report', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('contactreports::contactreports.group'), 'groupid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('contactreports::contactreports.contacted'), 'datetimecontact', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{{ trans('contactreports::contactreports.comments') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit contactreports'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit contactreports'))
						<a href="{{ route('admin.contactreports.edit', ['id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit($row->report, 70) }}
						</a>
					@else
						<span>
							{{ Illuminate\Support\Str::limit($row->report, 70) }}
						</span>
					@endif
				</td>
				<td class="priority-4">
					@if ($row->group && $row->group->id)
						{{ $row->group->name }}
					@else
						<span class="none">{{ trans('global.none') }}</span>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->getOriginal('datetimecontact') && $row->getOriginal('datetimecontact') != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimecontact }}">
								@if ($row->datetimecontact->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->datetimecontact->diffForHumans() }}
								@else
									{{ $row->datetimecontact->format('Y-m-d') }}
								@endif
							</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<a href="{{ route('admin.contactreports.comments', ['report' => $row->id]) }}">
						{{ $row->comments_count }}
					</a>
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