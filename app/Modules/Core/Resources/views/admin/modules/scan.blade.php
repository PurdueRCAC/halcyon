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

<form action="{{ route('admin.modules.index') }}" method="gett" name="adminForm" id="adminForm">

	@if (count($rows))
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<caption class="sr-only visually-hidden">{{ trans('core::modules.module manager') }}</caption>
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
							<th scope="col" class="priority-5 text-right text-end">
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
							<td class="text-right text-end">
								<a href="{{ route('admin.modules.install', ['element' => $row->element]) }}" class="btn btn-secondary">
									Install
								</a>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
		</div>
	@else
		<div class="placeholder py-4 mx-auto text-center">
			<div class="placeholder-body p-4">
				<span class="fa fa-ban display-4 text-muted" aria-hidden="true"></span>
				<p>{{ trans('global.no results') }}</p>
			</div>
		</div>
	@endif

	<input type="hidden" name="boxchecked" value="0" />
</form>
@stop
