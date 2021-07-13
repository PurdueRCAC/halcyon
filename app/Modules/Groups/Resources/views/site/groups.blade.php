
@push('scripts')
<script src="{{ asset('modules/groups/js/site.js?v=' . filemtime(public_path() . '/modules/groups/js/site.js')) }}"></script>
<script>
	$(document).ready(function() {
		var dialog = $(".new-group-dialog").dialog({
			autoOpen: false,
			height: 'auto',
			width: 500,
			modal: true
		});

		$('.add-group').on('click', function (e) {
			e.preventDefault();
			$(".new-group-dialog").dialog('open');
		});

		$('#new_group_btn')
			.on('click', function (e) {
				e.preventDefault();
				CreateNewGroup();
			});
		$('#new_group_input')
			.on('keyup', function (e) {
				if (e.keyCode == 13) {
					CreateNewGroup();
				}
			});
	});
/**
 * Create new group
 *
 * @return  {void}
 */
function CreateNewGroup() {
	var input = document.getElementById("new_group_input"),
		name = input.value;

	if (!name) {
		document.getElementById('new_group_action').innerHTML = 'Please enter a group name';
		return;
	}

	var post = JSON.stringify({
		'name': name,
		'userid': input.getAttribute('data-userid')
	});

	WSPostURL(input.getAttribute('data-api'), post, function(xml) {
		if (xml.status < 400) {
			var results = JSON.parse(xml.responseText);

			window.location.reload(true); // = input.getAttribute('data-uri') + '/' + results.data.id;
		} else if (xml.status == 409) {
			document.getElementById('new_group_action').innerHTML = ERRORS['creategroupduplicate'];
		} else {
			document.getElementById('new_group_action').innerHTML = ERRORS['creategroup'];
		}
	});
}
</script>
@endpush

<div class="contentInner">
	@if (auth()->user()->can('manage groups'))
		<div class="row">
			<div class="col-md-9">
				<h2>{{ trans('users::users.groups') }}</h2>
			</div>
			<div class="col-md-3 text-right">
				<a class="btn btn-outline-secondary float-right add-group" href="{{ route('site.users.account.section', ['section' => 'groups']) }}">
					<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('global.create') }}
				</a>
			</div>
		</div>

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
	@else
		<h2>{{ trans('users::users.groups') }}</h2>
	@endif

	<div id="everything">
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
					<a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $g->groupid, 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">
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
					@if ($g->datecreated && $g->datecreated != '0000-00-00 00:00:00' && $g->datecreated != '-0001-11-30 00:00:00')
						<time datetime="{{ $g->datecreated->format('Y-m-d\TH:i:s\Z') }}">
							@if ($g->datecreated->getTimestamp() > Carbon\Carbon::now()->getTimestamp())
								{{ $g->datecreated->diffForHumans() }}
							@else
								{{ $g->datecreated->format('F j, Y') }}
							@endif
						</time>
					@elseif ($g->datetimecreated && $g->datetimecreated != '0000-00-00 00:00:00' && $g->datetimecreated != '-0001-11-30 00:00:00')
						<time datetime="{{ $g->datetimecreated->format('Y-m-d\TH:i:s\Z') }}">
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
		<p class="alert alert-info">You do not appear to be a member of any groups.</p>

		<h3>What is this page?</h3>
		<p>If you're a manager or member of a group, you'll find it listed here. You will also find groups listed where you're a member of at least one of its resource queues or unix groups.</p>
	@endif
	</div><!-- / #everything -->
</div><!-- / .contentInner -->
