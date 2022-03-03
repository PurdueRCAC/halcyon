@push('scripts')
<script src="{{ asset('modules/resources/js/roles.js?v=' . filemtime(public_path() . '/modules/resources/js/roles.js')) }}"></script>
@endpush

<div class="card mb-3">
	<div class="card-header">
		<div class="row">
			<div class="col-md-9">
				<div class="card-title">Resources</div>
			</div>
			<div class="col-md-3 text-right">
				<a href="#manage_roles_dialog" id="manage_roles" data-membertype="1" class="btn btn-sm btn-link" data-tip="Manage Resource Access">
					<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only"> Manage</span>
				</a>
			</div>
		</div>
	</div>
	<div class="card-body">
		<table class="table table-hover" id="roles" data-api="{{ route('api.resources.index', ['limit' => 100]) }}">
			<caption class="sr-only">Roles</caption>
			<thead>
				<tr>
					<th scope="col">Resource</th>
					<th scope="col">Group</th>
					<th scope="col">Shell</th>
					<th scope="col">PI</th>
					<th scope="col">Status</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($resources as $resource)
				<tr>
					<td>{{ $resource->name }}</td>
					<td id="resource{{ $resource->id }}_group"></td>
					<td id="resource{{ $resource->id }}_shell"></td>
					<td id="resource{{ $resource->id }}_pi"></td>
					<td id="resource{{ $resource->id }}" data-api="{{ route('api.resources.members') }}">
						<span class="fa fa-exclamation-triangle text-warning" aria-hidde="true"></span>
						<span class="sr-only">Loading...</span>
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
</div>

<div id="manage_roles_dialog" data-id="{{ $user->id }}" title="Manage Access" class="dialog roles-dialog">
	<form method="post" action="{{ route('site.users.account') }}">
		<div class="form-group">
			<label for="role">Resource</label>
			<select id="role" class="form-control" data-id="{{ $user->id }}" data-api="{{ route('api.resources.members.create') }}">
				<option value="">(Select Resource)</option>
				@foreach ($resources as $resource)
					<option value="{{ $resource->id }}" data-api="{{ route('api.resources.members.read', ['id' => $resource->id . '.' . $user->id]) }}">{{ $resource->name }}</option>
				@endforeach
			</select>
		</div>

		<div class="hide" id="role_table">
			<div class="form-group">
				<label for="role_status">Status</label>
				<input type="text" disabled="disabled" class="form-control" id="role_status" />
			</div>
			<div class="form-group">
				<label for="role_group">Group</label>
				<input id="role_group" type="text" class="form-control" />
			</div>
			<div class="form-group">
				<label for="role_shell">Shell</label>
				<input id="role_shell" type="text" class="form-control" />
			</div>
			<div class="form-group">
				<label for="role_pi">PI</label>
				<input id="role_pi" type="text" class="form-control" />
			</div>
			<div class="form-group mb-0">
				<button id="role_add" class="btn btn-success role-add hide" data-id="{{ $user->id }}" data-api="{{ route('api.resources.members.create') }}">Add Role</button>
				<button id="role_modify" class="btn btn-success role-add hide" data-id="{{ $user->id }}">Modify Role</button>
				<button id="role_delete" class="btn btn-danger role-delete hide" data-id="{{ $user->id }}">Delete Role</button>
			</div>

			<span id="role_errors" class="alert alert-warning hide"></span>
		</div>
	</form>
</div>
