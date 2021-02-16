@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/users/js/users.js?v=' . filemtime(public_path() . '/modules/users/js/users.js')) }}"></script>
<script>
jQuery(document).ready(function ($) {
	$('.add-facet').on('click', function(e){
		e.preventDefault();

		var btn = $(this);
		var key = $(btn.attr('href') + '-key'),
			value = $(btn.attr('href') + '-value'),
			access = $(btn.attr('href') + '-access');

		// create new relationship
		$.ajax({
			url: btn.data('api'),
			type: 'post',
			data: {
				'user_id' : btn.data('userid'),
				'key' : name.val(),
				'value' : name.val(),
				'access' : name.val()
			},
			dataType: 'json',
			async: false,
			success: function(response) {
				Halcyon.message('success', 'Item added');

				var c = name.closest('table');
				var li = c.find('tr.hidden');

				if (typeof(li) !== 'undefined') {
					var template = $(li)
						.clone()
						.removeClass('hidden');

					template
						.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
						.data('id', response.id);

					template.find('a').each(function(i, el){
						$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
					});

					var content = template
						.html()
						.replace(/\{id\}/g, response.id)
						.replace(/\{key\}/g, response.key)
						.replace(/\{value\}/g, response.value)
						.replace(/\{access\}/g, response.access);

					template.html(content).insertBefore(li);
				}

				key.val(''),
				value.val(''),
				access.val(0);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				//console.log(xhr);
				Halcyon.message('danger', xhr.responseJSON.message);
			}
		});
	});

	$('#main').on('click', '.remove-facet', function(e){
		e.preventDefault();

		var result = confirm($(this).data('confirm'));

		if (result) {
			var field = $($(this).attr('href'));

			$.ajax({
				url: $(this).data('api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function(data) {
					Halcyon.message('success', 'Item removed');
					field.remove();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});
});
</script>
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
{{ trans('users::system.users') }}: {{ $user->id ? 'Edit: #' . $user->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.users.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	@if ($errors->any())
		<div class="alert alert-error">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="tabs">
		<ul>
			<li><a href="#user-account">Account</a></li>
			<li><a href="#user-attributes">Attributes</a></li>
		@if (auth()->user()->can('view users.notes'))
			<li><a href="#user-notes">Notes</a></li>
		@endif
			<!-- <li><a href="#user-history">History</a></li> -->
		</ul>
		<div id="user-account">
	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				@if ($user->sourced)
					<p class="alert alert-info">{{ trans('users::users.sourced description') }}</p>
				@endif

				<div class="form-group">
					<label id="field_username-lbl" for="field_username">{{ trans('users::users.username') }} <span class="required star">{{ trans('global.required') }}</span></label>
					<input type="text" name="ufields[username]" id="field_username" value="{{ $user->username }}" class="form-control required<?php if ($user->id) { echo ' readonly" readonly="readonly'; } ?>" />
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('users::users.name') }}: <span class="required star">{{ trans('global.required') }}</span></label>
					<input type="text" class="form-control required<?php if ($user->sourced) { echo ' readonly" readonly="readonly'; } ?>" required name="fields[name]" id="field-name" value="{{ $user->name }}" />
				</div>

				<div class="form-group">
					<label for="field-organization_id">{{ trans('users::users.organization id') }}:</label>
					<input type="text" class="form-control" name="fields[puid]" id="field-organization_id" value="{{ $user->puid }}" />
				</div>

				<div class="form-group">
					<label for="field-api_token">{{ trans('users::users.api token') }}:</label>
					<div class="row">
						<div class="col col-md-10">
							<input type="text" class="form-control readonly" readonly="readonly" name="fields[api_token]" id="field-api_token" value="{{ $user->api_token }}" />
						</div>
						<div class="col col-md-2">
							<button class="btn btn-secondary btn-apitoken">{{ trans('users::users.regenerate') }}</button>
						</div>
					</div>
					<span class="form-text text-muted">{{ trans('users::users.api token hint') }}</span>
				</div>
			</fieldset>

			<fieldset id="user-groups" class="adminform">
				<legend>{{ trans('users::users.assigned roles') }}</legend>

				<div class="form-group">
					<?php
					$roles = $user->roles
						->pluck('role_id')
						->all();

					echo App\Halcyon\Html\Builder\Access::roles('fields[newroles]', $roles, true); ?>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<table class="meta">
				<caption class="sr-only">{{ trans('global.metadata') }}</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('users::users.register date') }}</th>
						<td>{{ $user->datecreated }}</td>
					</tr>
					<tr>
						<th scope="row">{{ trans('users::users.last visit date') }}</th>
						<td>{{ $user->hasVisited() ? $user->last_visit : trans('global.never') }}</td>
					</tr>
					@if ($user->isTrashed())
					<tr>
						<th scope="row">{{ trans('users::users.removed date') }}</th>
						<td>{{ $user->dateremoved }} ?></td>
					</tr>
					@endif
				</tbody>
			</table>

			<fieldset class="adminform">
				<legend>{{ trans('users::users.status') }}</legend>

				<div class="form-group">
					<label id="field-block-lbl" for="field-block">{{ trans('users::users.block this user') }}</label>
					<fieldset id="field-block" class="radio">
						<ul>
							<li>
								<div class="form-check">
									<input class="form-check-input" type="radio" id="field-block0" name="fields[block]" value="0"<?php if ($user->block == 0) { echo ' checked="checked"'; } ?> />
									<label class="form-check-label" for="field-block0">{{ trans('global.no') }}</label>
								</div>
							</li>
							<li>
								<div class="form-check">
									<input class="form-check-input" type="radio" id="field-block1" name="fields[block]" value="1"<?php if ($user->block == 1) { echo ' checked="checked"'; } ?> />
									<label class="form-check-label" for="field-block1">{{ trans('global.yes') }}</label>
								</div>
							</li>
						</ul>
					</fieldset>
				</div>
			</fieldset>

			<?php /*@foreach ($user->sessions as $session)
			<fieldset class="adminform">
				<legend>{{ trans('users::users.sessions') }}</legend>
				<div class="panel session">
					<div class="card-body">
						<div class="session-ip">
							<strong>{{ $session->ip_address == '::1' ? 'localhost' : $session->ip_address }}</strong>
						</div>
					@if ($session->id == session()->getId())
						<div class="session-current">
							Your current session
						</div>
					@endif
					</div>
				</div>
			</fieldset>
			@endforeach*/ ?>
				</div><!-- / .col -->
			</div><!-- / .grid -->
		</div><!-- / #user-account -->
		<div id="user-attributes">
			<div class="card">
				<table class="table table-hover">
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
						<tr>
							<td>
								@if ($facet->locked)
									<span class="icon-lock glyph">{{ trans('users::users.locked') }}</span>
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
								<a href="#facet-{id}" class="btn btn-secondary btn-danger remove-facet"
									data-api="{{ route('api.users.facets.delete', ['id' => $facet->id]) }}"
									data-confirm="{{ trans('users::users.confirm delete') }}">
									<span class="icon-trash glyph">{{ trans('global.trash') }}</span>
								</a>
							</td>
						</tr>
						<?php
						$i++;
						?>
					@endforeach
						<tr class="hidden" id="facet-{id}" data-id="{id}">
							<td></td>
							<td><input type="text" name="facet[{$i}][key]" class="form-control" value="{key}" /></td>
							<td><input type="text" name="facet[{$i}][value]" class="form-control" value="{value}" /></td>
							<td>{access}</td>
							<td class="text-right">
								<input type="hidden" name="facet[{i}][id]" class="form-control" value="{id}" />
								<a href="#facet-{id}" class="btn btn-secondary btn-danger remove-facet"
									data-api="{{ route('api.users.facets.create') }}/{id}"
									data-confirm="{{ trans('users::users.confirm delete') }}">
									<span class="icon-trash glyph">{{ trans('global.trash') }}</span>
								</a>
							</td>
						</tr>
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
								<a href="#newfacet" class="btn btn-secondary btn-success add-facet"
									data-userid="{{ $user->id }}"
									data-api="{{ route('api.users.facets.create') }}">
									<span class="icon-plus glyph">{{ trans('global.add') }}</span>
								</a>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		@if (auth()->user()->can('view users.notes'))
			<div id="user-notes">
				<div class="row">
					<div class="col-md-6">
						<?php
						$notes = $user->notes()->orderBy('created_at', 'desc')->get();
						if (count($notes))
						{
							foreach ($notes as $note)
							{
								?>
								<div class="card">
									<div class="card-body">
										<h4 class="card-title">{{ $note->subject }}</h4>
										{!! $note->body !!}
									</div>
									<div class="card-footer">
										<div class="row">
											<div class="col-md-6">
												<span class="datetime">
													<time datetime="{{ $note->created_at->toDateTimeString() }}">
														@if ($note->created_at->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
															{{ $note->created_at->diffForHumans() }}
														@else
															{{ $note->created_at->format('Y-m-d') }}
														@endif
													</time>
												</span>
												<span class="creator">
													{{ $note->creator ? $note->creator->name : trans('global.unknown') }}
												</span>
											</div>
											<div class="col-md-6 text-right">
												@if (auth()->user()->can('manage users.notes'))
													<button data-api="{{ route('api.users.notes.update', ['id' => $note->id]) }}" class="btn btn-sm btn-secondary">
														<span class="icon-edit glyph">{{ trans('global.edit') }}</span>
													</button>
													<button data-api="{{ route('api.users.notes.delete', ['id' => $note->id]) }}" class="btn btn-sm btn-danger">
														<span class="icon-trash glyph">{{ trans('global.trash') }}</span>
													</button>
												@endif
											</div>
										</div>
									</div>
								</div>
								<?php
							}
						}
						else
						{
							?>
							<p>No notes found.</p>
							<?php
						}
						?>
					</div>
					<div class="col-md-6">
						<?php /*<fieldset class="adminform">
							<legend>{{ trans('global.details') }}</legend>

							<div class="form-group">
								<label for="field-subject">{{ trans('users::notes.subject') }}: <span class="required">{{ trans('global.required') }}</span></label><br />
								<input type="text" class="form-control required" name="fields[subject]" id="field-subject" value="" />
							</div>

							<div class="form-group">
								<label for="field-body">{{ trans('users::notes.body') }}:</label>
								{!! editor('fields[body]', '', ['rows' => 15, 'class' => 'minimal no-footer']) !!}
							</div>

							<div class="form-group">
								<label for="field-state">{{ trans('global.state') }}:</label>
								<select name="fields[state]" class="form-control" id="field-state">
									<option value="0">{{ trans('global.unpublished') }}</option>
									<option value="1">{{ trans('global.published') }}</option>
									<option value="2">{{ trans('global.trashed') }}</option>
								</select>
							</div>
						</fieldset>*/ ?>
					</div>
				</div>
			</div><!-- / #user-notes -->
		@endif
		<!-- <div id="user-history">
			History
		</div>/ #user-history -->
	</div><!-- / .tabs -->

	<input type="hidden" name="id" value="{{ $user->id }}" />

	@csrf
</form>
@stop