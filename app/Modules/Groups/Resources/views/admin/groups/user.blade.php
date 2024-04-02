@if (count($groups))
	<div class="card">
		<div class="card-header">
			<?php /*<div class="row">
				<div class="col-md-6">
					<h3 class="card-title">{{ trans('groups::groups.groups') }}</h3>
				</div>
				<div class="col-md-6 text-right">
					<a class="btn btn-primary float-right add-group" href="#add-to-group" data-toggle="modal">
						<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('groups::groups.add to group') }}
					</a>
				</div>
			</div>*/ ?>
			<h3 class="card-title">{{ trans('groups::groups.groups') }}</h3>
		</div>

		<div class="card-body">
			<table class="table">
				<caption class="sr-only visually-hidden">Active Groups</caption>
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
						<a href="{{ route('admin.groups.show', ['id' => $g->groupid]) }}">
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
						<time>{{ $g->datecreated ? $g->datecreated->format('Y-m-d') : ($g->datetimecreated ? $g->datetimecreated->format('Y-m-d') : trans('global.unknown')) }}</time>
					</td>
				</tr>
			@endforeach
				</tbody>
			</table>
		</div>
	</div>
@else
	<div class="card w-50 mx-auto">
		<div class="card-header">
			<?php /*<div class="row">
				<div class="col-md-6">
					<h3 class="card-title">{{ trans('groups::groups.groups') }}</h3>
				</div>
				<div class="col-md-6 text-right">
					@if (auth()->user()->can('manage groups'))
					<a class="btn btn-primary float-right add-group" href="#add-to-group" data-toggle="modal">
						<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('groups::groups.add to group') }}
					</a>
					@endif
				</div>
			</div>*/ ?>
			<h3 class="card-title">{{ trans('groups::groups.groups') }}</h3>
		</div>
		<div class="card-body">
			<p class="alert alert-info">This user does not appear to be a member of any groups.</p>

			<h4>{{ trans('groups::groups.what is this page') }}</h4>
			<p>If user a manager or member of a group, it will be listed here. You will also find groups listed where the user is a member of at least one of its resource queues or unix groups.</p>
		</div>
	</div>
@endif

<div id="add-to-group" class="modal fade" tabindex="-1" aria-labelledby="add-to-group-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="add-to-group-title">{{ trans('groups::groups.choose group') }}</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

				<form method="post" action="{{ route('site.users.account.section', ['section' => 'groups']) }}">
					<div class="form-group">
						<label for="new_group">Search for a group:</label>
						<input type="text" name="groupid" id="new_group" class="form-control" data-userid="{{ $user->id }}" data-api="{{ route('api.groups.index') }}" value="" />
					</div>

					<div class="form-group">
						<label for="new_group_membertype">Membership type:</label>
						<select class="form-control" id="new_group_membertype" name="membertype">
							<option value="1">Member</option>
							<option value="2">Manager</option>
							<option value="3">Usage Viewer</option>
						</select>
					</div>

					@csrf
					<input type="hidden" name="userid" value="{{ $user->id }}" />
					<span id="new_group_action" class="alert alert-warning hide"></span>

					<div class="dialog-footer">
						<div class="row">
							<div class="col-md-12 text-right">
								<button type="submit" id="new_group_btn" class="btn btn-success" data-api="{{ route('api.groups.members.create') }}">
									<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('global.button.create') }}
								</button>
							</div>
						</div>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>
