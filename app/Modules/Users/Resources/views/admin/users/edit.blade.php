@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/users/js/users.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		($user->id ? trans('global.edit') . ' #' . $user->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit users'))
		{!! Toolbar::save(route('admin.users.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.users.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::system.users') }}: {{ $user->id ? trans('global.edit') . ': #' . $user->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.users.store') }}" method="post" name="adminForm" id="item-form" class="editform">

	@if ($errors->any())
		<div class="alert alert-error">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<nav class="container-fluid">
		<ul id="user-tabs" class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation"><a class="nav-link active" href="#user-account" data-toggle="tab" role="tab" id="user-account-tab" aria-controls="user-account" aria-selected="true">Account</a></li>
			@if ($user->id)
				<li class="nav-item" role="presentation"><a class="nav-link" href="#user-attributes" data-toggle="tab" role="tab" id="user-attributes-tab" aria-controls="user-attributes" aria-selected="false">{{ trans('users::users.attributes') }}</a></li>
			@endif
		</ul>
	</nav>
	<div class="tab-content" id="user-tabs-content">
		<div class="tab-pane show active" id="user-account" role="tabpanel" aria-labelledby="user-account-tab">
			<div class="row">
				<div class="col col-md-6">
					
					<fieldset class="adminform">
						<legend>{{ trans('global.details') }}</legend>

						@if ($user->sourced)
							<p class="alert alert-info">{{ trans('users::users.sourced description') }}</p>
						@endif

						<div class="form-group">
							<label for="field_username" id="field_username-lbl">{{ trans('users::users.username') }}: <span class="required star">{{ trans('global.required') }}</span></label>
							<input type="text" name="ufields[username]" id="field_username" value="{{ $user->username }}" maxlength="16" class="form-control<?php if ($user->id) { echo ' readonly" readonly="readonly'; } ?>" required />
							<span class="invalid-feedback">{{ trans('users::users.invalid.username') }}</span>
						</div>

						<div class="form-group">
							<label for="field-name">{{ trans('users::users.name') }}: <span class="required star">{{ trans('global.required') }}</span></label>
							<input type="text" class="form-control<?php if ($user->sourced) { echo ' readonly" readonly="readonly'; } ?>" required maxlength="128" name="fields[name]" id="field-name" value="{{ $user->name }}" />
							<span class="invalid-feedback">{{ trans('users::users.invalid.name') }}</span>
						</div>

						<div class="form-group">
							<label for="field_email" id="field_email-lbl">{{ trans('users::users.email') }}:</label>
							<input type="email" name="ufields[email]" id="field_email" value="{{ $user->email }}" maxlength="250" class="form-control" />
							<span class="invalid-feedback">{{ trans('users::users.invalid.email') }}</span>
						</div>

						<div class="form-group">
							<label for="field-organization_id">{{ trans('users::users.organization id') }}:</label>
							<input type="text" class="form-control" name="fields[puid]" id="field-organization_id" maxlength="10" value="{{ $user->puid }}" />
						</div>

						@if ($user->id)
						<div class="form-group">
							<label for="field-api_token">{{ trans('users::users.api token') }}:</label>
							<span class="input-group">
								<input type="text" class="form-control readonly" readonly="readonly" name="fields[api_token]" id="field-api_token" maxlength="100" value="{{ $user->api_token }}" />
								<span class="input-group-append">
									<button class="input-group-text btn btn-secondary btn-apitoken">{{ trans('users::users.regenerate') }}</button>
								</span>
							</span>
							<span class="form-text text-muted">{{ trans('users::users.api token hint') }}</span>
						</div>
						@endif
					</fieldset>
				</div>
				<div class="col col-md-6">
					<fieldset id="user-groups" class="adminform">
						<legend>{{ trans('users::users.assigned roles') }}</legend>

						<div class="form-group">
							<?php
							$roles = $user->roles
								->pluck('role_id')
								->all();

							if (empty($roles)):
								$roles = [App\Modules\Users\Models\User::defaultRole()];
							endif;

							echo App\Halcyon\Html\Builder\Access::roles('fields[newroles]', $roles, true);
							?>
						</div>
					</fieldset>

					<?php /*
					<fieldset class="adminform">
						<legend>{{ trans('users::users.sessions') }}</legend>
						<div class="card session">
						<ul class="list-group list-group-flush">
							@if (count($user->sessions))
								@foreach ($user->sessions as $session)
									<li class="list-group-item">
										<div class="session-ip card-title">
											<div class="row">
												<div class="col-md-4">
													<strong>{{ $session->ip_address == '::1' ? 'localhost' : $session->ip_address }}</strong>
												</div>
												<div class="col-md-4">
													{{ $session->last_activity->diffForHumans() }}
												</div>
												<div class="col-md-4 text-right">
													@if ($session->id == session()->getId())
														<span class="badge badge-info float-right">Your current session</span>
													@endif
												</div>
											</div>
										</div>
										<div class="session-current card-text text-muted">
											{{ $session->user_agent }}
										</div>
									</li>
								@endforeach
							@else
								<li class="list-group-item text-center">
									<span class="none">{{ trans('global.none') }}
								</li>
							@endif
							</ul>
						</div>
					</fieldset>
					*/ ?>
				</div><!-- / .col -->
			</div><!-- / .grid -->
		</div><!-- / #user-account -->

		@if ($user->id)
			<div class="tab-pane" id="user-attributes" role="tabpanel" aria-labelledby="user-attributes-tab">
				<div class="card">
					<table class="table table-hover">
						<caption class="sr-only visually-hidden">{{ trans('users::users.attributes') }}</caption>
						<thead>
							<tr>
								<th scope="col" width="25">{{ trans('users::users.locked') }}</th>
								<th scope="col">{{ trans('users::users.key') }}</th>
								<th scope="col">{{ trans('users::users.value') }}</th>
								<th scope="col">{{ trans('users::users.access') }}</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$i = 0;
						?>
						@foreach ($user->facets as $facet)
							<tr id="facet-{{ $facet->id }}">
								<td>
									@if ($facet->locked)
										<span class="fa fa-lock" aria-hidden="true"></span>
										<span class="sr-only visually-hidden">{{ trans('users::users.locked') }}</span>
									@endif
								</td>
								<td><input type="text" name="facet[{{ $i }}][key]" class="form-control" value="{{ $facet->key }}" {{ $facet->locked ? ' readonly="readonly"' : '' }} /></td>
								<td><input type="text" name="facet[{{ $i }}][value]" class="form-control" value="{{ $facet->value }}" {{ $facet->locked ? ' readonly="readonly"' : '' }} /></td>
								<td>
									<select name="facet[{{ $i }}][access]" class="form-control">
										<option value="0">{{ trans('users::users.private') }}</option>
										@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
											<option value="{{ $access->id }}"{{ $facet->access == $access->id ? ' selected="selected"' : '' }}>{{ $access->title }}</option>
										@endforeach
									</select>
								</td>
								<td class="text-right">
									<input type="hidden" name="facet[{{ $i }}][id]" class="form-control" value="{{ $facet->id }}" />
									<a href="#facet-{{ $facet->id }}" class="btn text-danger remove-facet"
										data-api="{{ route('api.users.facets.delete', ['id' => $facet->id]) }}"
										data-confirm="{{ trans('users::users.confirm delete') }}">
										<span class="fa fa-trash" aria-hidden="true"></span>
										<span class="sr-only visually-hidden">{{ trans('global.trash') }}</span>
									</a>
								</td>
							</tr>
							<?php
							$i++;
							?>
						@endforeach
						</tbody>
						<tfoot>
							<tr id="newfacet">
								<td></td>
								<td><input type="text" name="facet[{{ $i }}][key]" id="newfacet-key" class="form-control" value="" /></td>
								<td><input type="text" name="facet[{{ $i }}][value]" id="newfacet-value" class="form-control" value="" /></td>
								<td>
									<select name="facet[{{ $i }}][access]" id="newfacet-access" class="form-control">
										<option value="0">{{ trans('users::users.private') }}</option>
										@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
											<option value="{{ $access->id }}">{{ $access->title }}</option>
										@endforeach
									</select>
								</td>
								<td class="text-right">
									<a href="#newfacet" class="btn btn-success add-facet"
										data-userid="{{ $user->id }}"
										data-api="{{ route('api.users.facets.create') }}">
										<span class="fa fa-plus" aria-hidden="true"></span>
										<span class="sr-only visually-hidden">{{ trans('global.add') }}</span>
									</a>
								</td>
							</tr>
						</tfoot>
					</table>
					<script id="facet-template" type="text/x-handlebars-template">
						<tr id="facet-{id}" data-id="{id}">
							<td></td>
							<td><input type="text" name="facet[{i}][key]" class="form-control" value="{key}" /></td>
							<td><input type="text" name="facet[{i}][value]" class="form-control" value="{value}" /></td>
							<td>
								<select name="facet[{i}][access]" class="form-control">
									<option value="0">{{ trans('users::users.private') }}</option>
									@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
										<option value="{{ $access->id }}">{{ $access->title }}</option>
									@endforeach
								</select>
							</td>
							<td class="text-right">
								<input type="hidden" name="facet[{i}][id]" class="form-control" value="{id}" />
								<a href="#facet-{id}" class="btn text-danger remove-facet"
									data-api="{{ route('api.users.facets.create') }}/{id}"
									data-confirm="{{ trans('users::users.confirm delete') }}">
									<span class="fa fa-trash" aria-hidden="true"></span>
									<span class="sr-only visually-hidden">{{ trans('global.trash') }}</span>
								</a>
							</td>
						</tr>
					</script>
				</div>
			</div>
		@endif
	</div><!-- / .tab-content -->

	@csrf
	<input type="hidden" name="id" value="{{ $user->id }}" />
</form>
@stop