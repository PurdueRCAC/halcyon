@extends('layouts.master')

@section('title'){{ trans('groups::groups.groups') }}@stop

@php
app('pathway')
	->append(
		trans('groups::groups.groups'),
		route('site.groups.index')
	);
@endphp

@section('content')
<div class="row">
<div class="col-md-12">
	@if (auth()->user()->can('manage groups'))
		<div class="row">
			<div class="col-md-9">
				<h2>{{ trans('groups::groups.groups') }}</h2>
			</div>
			<div class="col-md-3 text-right">
				<a class="btn btn-outline-secondary float-right add-group" href="{{ route('site.groups.create') }}">
					<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('global.create') }}
				</a>
			</div>
		</div>

		<div id="new_group_dialog" title="Create new group" class="modal dialog">
			<form method="post" action="{{ route('site.groups.create') }}" class="modal-content">
				<div class="form-group">
					<label for="new_group_input">Enter a name for a new group:</label>
					<input type="text" id="new_group_input" class="form-control" data-api="{{ route('api.groups.create') }}" data-uri="{{ route('site.groups.index') }}" value="" required />
				</div>

				<div id="new_group_action" class="alert alert-danger hide"></div>

				<div class="dialog-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<span id="#new_group_spinner" class="spinner-border spinner-border-sm hide" role="status"><span class="sr-only">Sending...</span></span>
							<button type="submit" id="new_group_btn" data-indicator="new_group_spinner" class="btn btn-success">
								<span class="fa fa-plus-circle" aria-hidden="true"></span> {{ trans('global.button.create') }}
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	@else
		<h2>{{ trans('groups::groups.groups') }}</h2>
	@endif

	<div id="everything">
	@if (count($rows))
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
		@foreach ($rows as $g)
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
				<h3 class="card-title">What is this page?</h3>
				<p>If you're a manager or member of a group, you'll find it listed here. You will also find groups listed where you're a member of at least one of its resource queues or unix groups.</p>
			</div>
		</div>
	@endif
	</div><!-- / #everything -->
</div><!-- / .contentInner -->
</div>
@stop