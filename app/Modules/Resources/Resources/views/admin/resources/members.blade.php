@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<!-- <script src="{{ timestamped_asset('modules/core/vendor/handlebars/handlebars.min-v4.7.7.js') }}"></script> -->
<script src="{{ timestamped_asset('modules/core/vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	/*var container = document.getElementById('members-list');

	if (container.length) {
		fetch(container.attr('data-api'), {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',
				'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
			}
		})
		.then(function (response) {
			if (response.ok) {
				return response.json();
			}
			return response.json().then(function (data) {
				var msg = data.message;
				if (typeof msg === 'object') {
					msg = Object.values(msg).join('<br />');
				}
				throw msg;
			});
		})
		.then(function(result) {
			var source   = document.querySelector(container.getAttribute('data-row')).innerHTML,
				template = Handlebars.compile(source);

			for (var i = 0; i < result.length; i++)
			{
				var context  = {
						"index" : container.find('tr').length,
						"id": result[i].id,
						"name": result[i].name,
						"username": result[i].username,
						"email": result[i].email,
						"queues": result[i].queues
					},
					html = template(context);

				container.append($(html));
				//$(html).insertBefore(container.find('tr:last-child'));
			}
		})
		.catch(function (err) {
			Halcyon.message('danger', err);
		});
	}*/
	var container = $('#members-list');

	if (container.length) {
		container.DataTable({
			ajax: {
				url: container.attr('data-api'),
				dataSrc: function (response) {
					if (response.length > 0) {
						$('#export').val(JSON.stringify(response));
					}
					//You have to return back the response
					return response;
				},
			},
			columns: [
				{ data: 'id' },
				{ data: 'name' },
				{ data: 'username' },
				{ data: 'email' },
				{ data: 'queues',
					render: function (data, type, row) {
						var rows = new Array;
						for (var i = 0; i < data.length; i++)
						{
							rows.push(data[i].name);
						}
						return rows.join('<br />');
					}
				}
			],
			paging: true,
			//scrollY: '50vh',
			//scrollCollapse: true,
			pageLength: 20,
			headers: true,
			info: true,
			ordering: true,
			lengthChange: true,
			dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'i>><'card my-4'<'row'<'col-sm-12'tr>>><'row'<'col-sm-12 col-md-5'p><'col-sm-12 col-md-7'l>>"
		});
	}
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('resources::resources.module name'),
		route('admin.resources.index')
	)
	->append(
		$asset->name,
		route('admin.resources.edit', ['id' => $asset->id])
	)
	->append(
		trans('resources::assets.active users'),
		route('admin.resources.members', ['id' => $asset->id])
	);
@endphp

@section('toolbar')
	{!!
		Toolbar::custom(route('admin.resources.members', ['id' => $asset->id, 'active' => 'export']), 'export', 'export', trans('resources::assets.export'), false);
		Toolbar::spacer();
	!!}

	@if (auth()->user()->can('admin resources'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('resources');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('resources::resources.module name') }}: {{ trans('resources::assets.active users') }}
@stop

@section('content')
@component('resources::admin.submenu')
@endcomponent

<form action="{{ route('admin.resources.members', ['id' => $asset->id]) }}" method="post" name="adminForm" id="adminForm" class="for-inline">

	<div class="car mb-4">
	<table class="table table-hover adminlist datatable" id="members-list" data-row="#members-row" data-api="{{ route('api.resources.members', ['id' => $asset->id]) }}">
		<caption>{{ $asset->name }}: {{ trans('resources::assets.active users') }}</caption>
		<thead>
			<tr>
				<th scope="col" class="priority-5">
					{{ trans('resources::assets.id') }}
				</th>
				<th scope="col">
					{{ trans('users::users.name') }}
				</th>
				<th scope="col">
					{{ trans('users::users.username') }}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('users::users.email') }}
				</th>
				<th scope="col" class="priority-4">
					{{ trans('resources::assets.queues') }}
				</th>
			</tr>
		</thead>
		<tbody>
			@if (count($rows))
				@foreach ($rows as $i => $row)
					<tr>
						<td class="priority-5">
							{{ $row->id }}
						</td>
						<td>
							@if (auth()->user()->can('manage users'))
								<a href="{{ route('admin.users.show', ['id' => $row->id]) }}">
							@endif
								{{ $row->name }}
							@if (auth()->user()->can('manage users'))
								</a>
							@endif
						</td>
						<td>
							@if (auth()->user()->can('manage users'))
								<a href="{{ route('admin.users.show', ['id' => $row->id]) }}">
							@endif
								{{ $row->username }}
							@if (auth()->user()->can('manage users'))
								</a>
							@endif
						</td>
						<td class="priority-4">
							@if (auth()->user()->can('manage users'))
								<a href="{{ route('admin.users.show', ['id' => $row->id]) }}">
							@endif
								{{ $row->email }}
							@if (auth()->user()->can('manage users'))
								</a>
							@endif
						</td>
						<td>
							@if (isset($row->queues))
								@foreach ($row->queues as $queue)
									<a href="{{ route('admin.queues.show', ['id' => $queue->id]) }}">{{ $queue->name }}</a>
								@endforeach
							@endif
							@if (isset($row->directories))
								@foreach ($row->directories as $dir)
									<a href="{{ route('admin.storage.directory.edit', ['id' => $dir->id]) }}">{{ $dir->name }}</a>
								@endforeach
							@endif
						</td>
					</tr>
				@endforeach
			@else
				<tr>
					<td colspan="5">
						<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only visually-hidden">{{ trans('global.loading') }}</span></span>
					</td>
				</tr>
			@endif
		</tbody>
	</table>
	</div>
	<script id="members-row" type="text/x-handlebars-template">
		<tr>
			<td class="priority-5">
				<?php echo '{{ id }}'; ?>
			</td>
			<td>
				@if (auth()->user()->can('manage users'))
					<a href="{{ route('admin.users.index') }}<?php echo '/{{ id }}'; ?>">
				@endif
					<?php echo '{{ name }}'; ?>
				@if (auth()->user()->can('manage users'))
					</a>
				@endif
			</td>
			<td>
				@if (auth()->user()->can('manage users'))
					<a href="{{ route('admin.users.index') }}<?php echo '/{{ id }}'; ?>">
				@endif
					<?php echo '{{ username }}'; ?>
				@if (auth()->user()->can('manage users'))
					</a>
				@endif
			</td>
			<td class="priority-4">
				@if (auth()->user()->can('manage users'))
					<a href="{{ route('admin.users.index') }}<?php echo '/{{ id }}'; ?>">
				@endif
					<?php echo '{{ email }}'; ?>
				@if (auth()->user()->can('manage users'))
					</a>
				@endif
			</td>
			<td>
			</td>
		</tr>
	</script>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="export" id="export" value="" />

	@csrf
</form>

@stop
