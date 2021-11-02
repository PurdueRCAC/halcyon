
@push('scripts')
<script src="{{ asset('modules/groups/js/site.js?v=' . filemtime(public_path() . '/modules/groups/js/site.js')) }}"></script>
@endpush

<div class="card">
	<div class="card-header">
		<?php /*<div class="row">
			<div class="col-md-9">
				<h3 class="card-title">{{ trans('users::users.groups') }}</h3>
			</div>
			<div class="col-md-3 text-right">
				@if (auth()->user()->can('manage groups'))
				<a class="btn btn-primary float-right add-group" href="{{ route('admin.users.edit', ['id' => $user->id, 'section' => 'groups']) }}">
					<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('groups::groups.add user to') }}
				</a>
				@endif
			</div>
		</div>*/ ?>
		<h3 class="card-title">{{ trans('users::users.groups') }}</h3>

		<div id="new_group_dialog" title="Create new group" class="new-group-dialog">
			<form method="post" action="{{ route('site.users.account.section', ['section' => 'groups']) }}">
				<div class="form-group">
					<label for="new_group_input">Enter a name for a new group:</label>
					<input type="text" id="new_group_input" class="form-control" data-userid="{{ $user->id }}" data-api="{{ route('api.groups.create') }}" data-uri="{{ route('site.users.account.section', ['section' => 'groups']) }}" value="" />
					<div class="form-text text-muted">{{ $user->name }} will be added as a manager.</div>
				</div>

				<span id="new_group_action" class="alert alert-warning hide"></span>

				<div class="dialog-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<button type="submit" id="new_group_btn" class="btn btn-success">
								<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('global.button.create') }}
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="card-body">
	@if (count($groups))
		<table class="table">
			<caption class="sr-only">Active Groups</caption>
			<thead>
				<tr>
					<th scope="col">
						Group
					</th>
					<th scope="col">
						Base Unix group
					</th>
					<th scope="col">
						Membership
					</th>
					<th scope="col">
						Joined
					</th>
				</tr>
			</thead>
			<tbody>
		@foreach ($groups as $g)
			<tr>
				<td>
					<a href="{{ route('admin.groups.edit', ['id' => $g->groupid]) }}">
						{{ $g->group->name }}
					</a>
				</td>
				<td>
					{!! $g->group->unixgroup ? $g->group->unixgroup : '<span class="none text-muted">' . trans('global.none') . '</span>' !!}
				</td>
				<td>
					@if ($g->isManager())
						<span class="badge badge-success">
					@elseif ($g->isViewer())
						<span class="badge badge-info">
					@elseif ($g->isPending())
						<span class="badge badge-warning">
					@else ($g->isMember())
						<span class="badge badge-secondary">
					@endif
						{{ $g->type->name }}
					</span>
				</td>
				<td>
					{{ $g->datecreated ? $g->datecreated->format('Y-m-d') : ($g->datetimecreated ? $g->datetimecreated->format('Y-m-d') : trans('global.unknown')) }}
				</td>
			</tr>
		@endforeach
			</tbody>
		</table>
	@else
		<p class="alert alert-info">You do not appear to be a member of any groups.</p>

		<h3>What is this page?</h3>
		<p>If you're a manager or member of a group, you'll find it listed here. You will also find groups listed where you're a member of at least one of its resource queues or unix groups.</p>
	@endif
	</div><!-- / #everything -->
</div><!-- / .contentInner -->
