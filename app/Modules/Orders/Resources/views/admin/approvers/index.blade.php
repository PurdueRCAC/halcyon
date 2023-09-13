@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var btnnew = document.getElementById('toolbar-plus');
	if (btnnew) {
		btnnew.setAttribute('data-toggle', 'modal');
		btnnew.setAttribute('data-target', '#new-approver');

		btnnew.addEventListener('click', function (e) {
			e.preventDefault();
		});
	}

	if (typeof TomSelect !== 'undefined') {
		var sselects = document.querySelectorAll(".searchable-select");
		if (sselects.length) {
			sselects.forEach(function (input) {
				new TomSelect(input);
			});
		}
	}

	document.querySelectorAll(".form-user").forEach(function(el) {
		var sel = new TomSelect(el, {
			valueField: 'id',
			labelField: 'name',
			searchField: ['name', 'username', 'email'],
			plugins: {
				clear_button:{
					title: 'Remove selected',
				}
			},
			persist: false,
			//create: true,
			load: function (query, callback) {
				var url = el.getAttribute('data-api') + '?search=' + encodeURIComponent(query);

				fetch(url, {
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
						'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
					}
				})
				.then(response => response.json())
				.then(json => {
					for (var i = 0; i < json.data.length; i++) {
						if (!json.data[i].id) {
							json.data[i].id = json.data[i].username;
						}
					}
					callback(json.data);
				}).catch(function () {
					callback();
				});
			},
			render: {
				option: function (item, escape) {
					var name = item.name;
					var label = name || item.username;
					var caption = name ? item.username : null;
					return '<div>' +
						'<span class="label">' + escape(label) + '</span>' +
						(caption ? '&nbsp;<span class="caption text-muted">(' + escape(caption) + ')</span>' : '') +
						'</div>';
				},
				item: function (item) {
					if (item.name.match(/\([a-z0-9-]+\)$/)) {
						if (isNaN(item.id)) {
							item.id = item.username;
						}
						item.username = item.name.replace(/([^\(]+\()/, '').replace(/\)$/, '');
						item.name = item.name.replace(/\s(\([a-z0-9-]+\))$/, '');
					}
					return `<div data-id="${ escape(item.id) }">${item.name}&nbsp;<span class="text-muted">(${ escape(item.username) })</span></div>`;
				}
			}
		});
		sel.on('item_add', function (value, item) {
			let td = el.closest('td');
			let btn = td.querySelector('.approver-save');
			if (!btn) {
				return;
			}
			btn.setAttribute('data-userid', value);
			btn.setAttribute('data-name', item.innerHTML);
		});
		sel.on('item_remove', function(e) {
			let td = el.closest('td');
			let btn = td.querySelector('.approver-save');
			if (!btn) {
				return;
			}
			btn.setAttribute('data-userid', '0');
			btn.setAttribute('data-name', '');
		});
	});

	document.querySelectorAll('.approver-edit,.approver-cancel').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			let node = this.closest('tr');
			node.querySelectorAll('.approver-edit-controls').forEach(function(item) {
				item.classList.toggle('d-none');
			});
		});
	});

	document.querySelectorAll('.approver-save').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			let btn = this;
			let post = {
				departmentid: this.getAttribute('data-departmentid'),
				userid: this.getAttribute('data-userid'),
			}
			document.getElementById(this.getAttribute('data-target')).innerHTML = this.getAttribute('data-name');

			fetch(this.getAttribute('data-api'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
				},
				body: JSON.stringify(post)
			})
			.then(function (response) {
				if (response.ok) {
					e.preventDefault();

					let node = btn.closest('tr');
					node.querySelectorAll('.approver-edit-controls').forEach(function(item) {
						item.classList.toggle('d-none');
					});

					//window.location.reload(true);
					return;
				}
				return response.json().then(function (data) {
					var msg = data.message;
					if (typeof msg === 'object') {
						msg = Object.values(msg).join('<br />');
					}
					throw msg;
				});
			})
			.catch(function (err) {
				alert(err);
			});
		});
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('admin.orders.index')
	)
	->append(
		trans('orders::orders.account approvers'),
		route('admin.orders.approvers')
	);
@endphp

@section('title')
{{ trans('orders::orders.module name') }}: {{ trans('orders::orders.account approvers') }}
@stop

@section('content')
@component('orders::admin.submenu')
	approvers
@endcomponent

<form action="{{ route('admin.orders.approvers') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid form-inline">
		<div class="row">
			<div class="col col-md-12">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>

				<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	@if (count($rows))
	<div class="card mb-4">
		<div class="table-responsive">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('orders::orders.approvers') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete orders.approvers'))
					<th scope="col">
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col">
					{!! Html::grid('sort', trans('orders::orders.department'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" colspan="2">
					{{ trans('orders::orders.account approver') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete orders.approvers'))
					<td>
						{!! Html::grid('id', $i, $row->id) !!}
					</td>
				@endif
				<td>
					@if ($row->level > 0)
						<span class="gi">{!! str_repeat('|&mdash;', $row->level - 1) !!}</span>
					@endif
					@if (auth()->user()->can('edit orders'))
						<a href="{{ route('admin.orders.approvers.edit', ['id' => $row->id]) }}">
					@endif
						{!! App\Halcyon\Utility\Str::highlight(e($row->name), $filters['search']) !!}
					@if (auth()->user()->can('edit orders'))
						</a>
					@endif
				</td>
				<td width="30%">
					<?php
					$user = null;
					if ($row->userid):
						$user = \App\Modules\Users\Models\User::find($row->userid);
					endif;
					?>
					<span class="approver-edit-controls approver-info"  id="user{{ $row->id }}">
						@if ($user)
							{{ $user->name }} ({{ $user->username}})
						@endif
					</span>
					@if (auth()->user()->can('manage orders'))
						<div class="approver-edit-controls d-none" id="user{{ $row->id }}-field">
							<span class="input-group">
								<label for="field-userid" class="sr-only">{{ trans('orders::orders.account approver') }}</label>
								<select name="userid" id="userid{{ $row->id }}" class="form-control form-control-s form-user" data-api="{{ route('api.users.index') }}">
									@if ($user)
										<option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username}})</option>
									@endif
								</select>
								<span class="input-group-append">
									<button class="btn btn-s input-group-text approver-save"
										data-api="{{ route('api.orders.approvers.create') }}"
										data-target="user{{ $row->id }}"
										data-userid="{{ $row->userid }}"
										data-departmentid="{{ $row->id }}"
										data-name="{{ $user ? $user->name . ' (' . $user->username . ')' : '' }}">
										<span class="fa fa-save" aria-hidden="true"></span>
										<span class="sr-only">{{ trans('global.save') }}</span>
									</button>
								</span>
							</span>
						</div>
					@endif
				</td>
				<td class="text-right">
					@if (auth()->user()->can('manage orders'))
						<a class="approver-edit-controls approver-edit" href="#user{{ $row->id }}-field" data-hide="user{{ $row->id }}" data-show="user{{ $row->id }}-cancel" id="user{{ $row->id }}-edit" title="{{ trans('global.button.edit') }}">
							<span class="fa fa-pencil" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('global.button.edit') }}</span>
						</a>
						<a class="approver-edit-controls approver-cancel d-none" href="#user{{ $row->id }}" id="user{{ $row->id }}-cancel" title="{{ trans('global.button.cancel') }}">
							<span class="fa fa-ban" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('global.button.cancel') }}</span>
						</a>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
		</div>
	</div>

	{{ $paginator->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
</form>

<div id="new-approver" class="modal fade" tabindex="-1" aria-labelledby="new-approver-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title" id="new-approver-title">Assign Department Approver</h3>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				 <div class="modal-body">
					<form action="{{ route('admin.orders.approvers.store') }}" method="post">
						<div class="form-group">
							<label for="field-departmentid">Department</label>
							<select name="departmentid" id="field-departmentid" data-category="collegedeptid" class="form-control searchable-select">
								<option value="0">{{ trans('groups::groups.select department') }}</option>
								@foreach ($departments as $d)
									@php
									if ($d->level == 0):
										continue;
									endif;

									$prf = '';
									foreach ($d->ancestors() as $ancestor):
										if (!$ancestor->parentid):
											continue;
										endif;

										$prf .= $ancestor->name . ' > ';
									endforeach;
									@endphp
									<option value="{{ $d->id }}">{{ $prf . $d->name }}</option>
								@endforeach
							</select>
						</div>

						<div class="form-group">
							<label for="field-userid">{{ trans('storage::storage.owner') }}</label>
							<select name="userid" id="field-userid" class="form-control form-users" data-api="{{ route('api.users.index') }}">
							</select>
						</div>
						@csrf
					</form>
				</div>
			</div>
		</div>
	</div>
@stop
