@extends('layouts.master')

@section('title')
{!! trans('widgets::widgets.widget manager') !!}: Create: {{ trans('widgets::widgets.choose type') }}
@stop

@php
	app('pathway')
		->append(
			config('widgets::widgets.module name'),
			route('admin.widgets.index')
		)
		->append(
			config('widgets::widgets.choose type'),
			route('admin.widgets.select')
		);
@endphp

@section('content')

<table id="new-modules-list" class="adminlist">
	<thead>
		<tr>
			<th scope="col">{{ trans('widgets::widgets.title') }}</th>
			<th scope="col">{{ trans('widgets::widgets.widget') }}</th>
		</tr>
	</thead>
	<tbody>
	@foreach ($items as $item)
		<tr>
			<td>
				<a href="{{ route('admin.widgets.create') . '?eid=' . $item->id }}" class="editlinktip hasTip" title="{{ $item->name . ' :: ' . $item->desc }}" target="_top">{{ $item->name }}</a>
			</td>
			<td>
				{{ $item->element }}
			</td>
		</tr>
	@endforeach
	</tbody>
</table>

@stop