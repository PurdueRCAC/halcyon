@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		trans('users::users.notes')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete users.notes'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.users.notes.delete')) !!}
	@endif

	@if (auth()->user()->can('create users.notes'))
		{!! Toolbar::addNew(route('admin.users.notes.create')) !!}
	@endif

	@if (auth()->user()->can('admin users'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('users')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('users.name') !!}: Notes
@stop

@section('content')

@component('users::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.users.notes') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-6 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-6 text-right">
				<label class="sr-only" for="filter_published"></label>
				<select name="published" id="filter_published" class="form-control filter filter-submit">
					<option value="">{{ trans('global.option.published') }}</option>
					<?php echo App\Halcyon\Html\Builder\Select::options(App\Halcyon\Html\Builder\Grid::publishedOptions(), 'value', 'text', $filters['state'], true); ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card md-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('users::users.notes') }}</caption>
		<thead>
			<tr>
				<th>
					<?php echo App\Halcyon\Html\Builder\Grid::checkall(); ?>
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('users::users.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{{ trans('users::users.name') }}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('users::users.subject'), 'subject', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-3">
					{!! Html::grid('sort', trans('users::users.reviewed'), 'review_time', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td class="center">
					@if (auth()->user()->can('manage users'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="center priority-4">
					{{ $row->id }}
				</td>
				<td>
					{!! $row->user ? $row->user->name : '<span class="unknown">' . trans('global.unknown') . '</span>' !!}
				</td>
				<td>
					@if (auth()->user()->can('manage users'))
						<a href="{{ route('admin.users.notes.edit', ['id' => $row->id]) }}">
					@endif
						@if ($row->subject)
							{{ $row->subject }}
						@else
							{{ trans('users::users.empty subject') }}
						@endif
					@if (auth()->user()->can('manage users'))
						</a>
					@endif
				</td>
				<td>
					@if ($row->getOriginal('review_time') && $row->getOriginal('review_time') != '0000-00-00 00:00:00')
						<time datetime="{{ $row->review_time->toDateTimeString() }}">
							@if ($row->review_time->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
								{{ $row->review_time->diffForHumans() }}
							@else
								{{ $row->review_time }}
							@endif
						</time>
					@else
						<span class="state no">{{ trans('not reviewed') }}</span>
					@endif
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