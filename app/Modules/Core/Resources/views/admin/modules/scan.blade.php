@extends('layouts.master')

@php
	app('pathway')
		->append(
			trans('core::modules.module name'),
			route('admin.modules.index')
		);
@endphp

@section('title')
{{ trans('core::modules.module name') }}
@stop

@section('content')

<form action="{{ route('admin.modules.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	@if (count($rows))
	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('core::modules.module manager') }}</caption>
		<thead>
			<tr>
				<th scope="col">
					Name
				</th>
				<th scope="col" class="priority-5">
					Description
				</th>
				<th scope="col">
					Path
				</th>
				<th scope="col" class="priority-5 text-right">
					Options
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					{{ ($row->name == $row->element ? trans($row->name . '::' . $row->name . '.module name') : $row->name) }}
				</td>
				<td class="priority-4">
					{{ $row->manifest('description') ? $row->manifest('description') : trans('global.none') }}
				</td>
				<td class="priority-4">
					{{ str_replace(base_path(), '', $row->path()) }}
				</td>
				<td class="text-right">
					<a href="{{ route('admin.modules.install', ['element' => $row->element]) }}" class="btn btn-secondary">
						Install
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="boxchecked" value="0" />
	@csrf
</form>
@stop
