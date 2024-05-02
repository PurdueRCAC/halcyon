
@push('scripts')
<script src="{{ timestamped_asset('modules/groups/js/site.js') }}"></script>
@endpush

<div class="contentInner">
	@if ($user->enabled && auth()->user()->can('manage groups'))
		<div class="row">
			<div class="col-md-9">
				<h2>{{ trans('groups::groups.groups') }}</h2>
			</div>
			<div class="col-md-3 text-right text-end">
				<a class="btn btn-outline-secondary float-right add-group" data-toggle="modal" data-bs-toggle="modal" href="#new_group_dialog">
					<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('global.create') }}
				</a>
			</div>
		</div>

		<div class="modal" id="new_group_dialog" tabindex="-1" aria-labelledby="new_group_dialog-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="new_group_dialog-title">{{ trans('groups::groups.create group') }}</div>
						<button type="button" class="btn-close close" data-dismiss="modal" aria-label="Close">
							<span class="visually-hidden" aria-hidden="true">&times;</span>
						</button>
					</div>
					<form method="post" action="{{ route('site.users.account.section', ['section' => 'groups']) }}">
						<div class="modal-body">
							<div class="form-group">
								<label for="new_group_input">{{ trans('groups::groups.name') }}</label>
								<input type="text" id="new_group_input" class="form-control" maxlength="48" data-userid="{{ $user->id }}" data-api="{{ route('api.groups.create') }}" data-uri="{{ route('site.users.account.section', ['section' => 'groups']) }}" value="" required />
								<div class="form-text text-muted">{{ trans('groups::groups.user will be added as a manager', ['user' => $user->name]) }}</div>
							</div>

							<div id="new_group_action" class="alert alert-danger hide"></div>
						</div>
						<div class="modal-footer">
							<div class="row">
								<div class="col-md-12 text-right text-end">
									<span id="new_group_spinner" class="spinner-border spinner-border-sm hide" role="status"><span class="sr-only visually-hidden">Sending...</span></span>
									<button type="submit" id="new_group_btn" data-indicator="new_group_spinner" class="btn btn-success">
										<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('global.button.create') }}
									</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	@else
		<h2>{{ trans('groups::groups.groups') }}</h2>
	@endif

	<div id="everything">
	@if (count($groups))
		<table class="table">
			<caption class="sr-only visually-hidden">{{ trans('groups::groups.groups') }}</caption>
			<thead>
				<tr>
					<th scope="col">
						{{ trans('groups::groups.group') }}
					</th>
					<th scope="col">
						{{ trans('groups::groups.base unix group') }}
					</th>
					<th scope="col">
						{{ trans('groups::groups.membership type') }}
					</th>
					<th scope="col" class="text-right text-end">
						{{ trans('groups::groups.joined') }}
					</th>
				</tr>
			</thead>
			<tbody>
		@foreach ($groups as $g)
			<tr>
				<td>
					<a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $g->groupid, 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">
						{{ $g->group->name }}
					</a>
				</td>
				<td>
					{!! $g->group->unixgroup ? $g->group->unixgroup : '' !!}
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
				<td class="text-right text-end">
					@if ($g->datecreated)
						<time datetime="{{ $g->datecreated->toDateTimeLocalString() }}">
							@if ($g->datecreated->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
								{{ $g->datecreated->diffForHumans() }}
							@else
								{{ $g->datecreated->format('F j, Y') }}
							@endif
						</time>
					@elseif ($g->datetimecreated)
						<time datetime="{{ $g->datetimecreated->toDateTimeLocalString() }}">
							@if ($g->datetimecreated->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
								{{ $g->datetimecreated->diffForHumans() }}
							@else
								{{ $g->datetimecreated->format('F j, Y') }}
							@endif
						</time>
					@else
						<span class="text-muted">{{ trans('global.unknown') }}</span>
					@endif
				</td>
			</tr>
		@endforeach
			</tbody>
		</table>
	@else
		<div class="card card-help">
			<div class="card-body">
				<h3 class="card-title">{{ trans('groups::groups.what is this page') }}</h3>
				<p>{{ trans('groups::groups.what this page is') }}</p>
			</div>
		</div>
	@endif
	</div><!-- / #everything -->
</div><!-- / .contentInner -->
