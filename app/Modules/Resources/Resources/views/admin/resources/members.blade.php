@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js?v=' . filemtime(public_path() . '/modules/core/vendor/handlebars/handlebars.min-v4.7.6.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/datatables.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/datatables.min.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/datatables/dataTables.bootstrap4.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/datatables/dataTables.bootstrap4.min.js')) }}"></script>
<script>
$(document).ready(function () {

	/*var container = $('#members-list');

	if (container.length) {
		$.ajax({
					url: container.attr('data-api'),
					type: 'get',
					dataType: 'json',
					async: false,
					success: function(result) {
						var source   = $(container.attr('data-row')).html(),
							template = Handlebars.compile(source);

						for (var i = 0; i < result.length; i++)
						{
							var context  = {
									"index" : container.find('tr').length,
									"id": result[i].id,
									"name": result[i].name,
									"username": result[i].username,
									"email": result[i].email
								},
								html = template(context);

							container.append($(html));
							//$(html).insertBefore(container.find('tr:last-child'));
						}
					},
					error: function() {
						if (numerrorboxes == 0) {
							alert("An error occurred while updating account. Please reload page and try again or contact help.");
							numerrorboxes++;
						}
					}
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
				{ data: 'email' }
			],
			paging: true,
			//scrollY: '50vh',
			//scrollCollapse: true,
			pageLength: 20,
			headers: true,
			info: true,
			ordering: true,
			lengthChange: true,
			dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'i>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'p><'col-sm-12 col-md-7'l>>"
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
{!! config('resources.name') !!}
@stop

@section('content')
@component('resources::admin.submenu')
@endcomponent

<form action="{{ route('admin.resources.members', ['id' => $asset->id]) }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<div class="card mb-4">
	<table class="table table-hover adminlist datatable" id="members-list" data-row="#members-row" data-api="{{ route('api.resources.members', ['id' => $asset->id]) }}">
		<caption>{{ $asset->name }}: {{ trans('resources::assets.active users') }}</caption>
		<thead>
			<tr>
				<th scope="col" class="priority-5">
					{!! trans('resources::assets.id') !!}
				</th>
				<th scope="col">
					{!! trans('users::users.name') !!}
				</th>
				<th scope="col">
					{!! trans('users::users.username') !!}
				</th>
				<th scope="col" class="priority-4">
					{!! trans('users::users.email') !!}
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="4">
					<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.loading') }}</span></span>
				</td>
			</tr>
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
			</tr>
		@endforeach
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
		</tr>
	</script>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="export" id="export" value="" />

	@csrf
</form>

@stop
