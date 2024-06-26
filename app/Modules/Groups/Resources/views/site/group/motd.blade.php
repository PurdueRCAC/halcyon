
<div class="card panel panel-default">
	<div class="card-header panel-heading">
		{{ trans('groups::groups.motd') }}
	</div>
	<div class="card-body panel-body">
		@if ($canManage)
			<form method="post" action="{{ route('site.users.account.section', ['section' => 'groups']) }}">
				<div class="form-group">
					<label for="MotdText_{{ $group->id }}">{{ trans('groups::groups.enter motd') }}</label>
					<textarea id="MotdText_{{ $group->id }}" data-api="{{ route('api.groups.motd.create') }}" class="form-control" cols="38" rows="4">{{ $group->motd ? $group->motd->motd : '' }}</textarea>
					@if ($group->motd)
						<p class="form-text text-muted">Set on <time datimetime="{{ $group->motd->datetimecreated->toDateTimeLocalString() }}">{{ $group->motd->datetimecreated->format('F j, Y') }}</time></p>
					@endif
				</div>

				<div id="MotdText_error" class="alert alert-danger hide"></div>

				<div class="row">
					<div class="col-sm-6">
						<button class="motd-set btn btn-success" data-group="{{ $group->id }}">{{ trans('groups::groups.set notice') }}</button>
					</div>
					<div class="col-sm-6 text-right text-end">
					@if ($group->motd)
						<button class="motd-delete btn btn-danger" id="MotdText_delete_{{ $group->id }}" data-api="{{ route('api.groups.motd.delete', ['id' => $group->motd->id]) }}" data-group="{{ $group->id }}"><span class="fa fa-trash"></span> {{ trans('groups::groups.delete notice') }}</button>
					@endif
					</div>
				</div>
			</form>
		@else
			<p class="text-muted">
				<time datimetime="{{ $group->datetimecreated->toDateTimeLocalString() }}">{{ $group->datetimecreated->format('F j, Y') }}</time> to <time datimetime="{{ $group->datetimeremoved->toDateTimeLocalString() }}">{{ $group->datetimeremoved->format('F j, Y') }}</time>
			</p>
			<blockquote>
				<p>{{ $group->motd }}</p>
			</blockquote>
		@endif
	</div><!-- / .card-body -->
</div><!-- / .card -->

<?php
$motds = $group->motds()->withTrashed();

if ($group->motd):
	$motds->where('id', '!=', $group->motd->id);
endif;

$past = $motds
	->orderBy('datetimecreated', 'desc')
	->get();

if (count($past)):
	?>
	<div class="card">
		<div class="card-header panel-heading">
			{{ trans('groups::groups.past notices') }}
		</div>
		<ul class="list-group list-group-flush">
			@foreach ($past as $motd)
				<li class="list-group-item">
					<p class="text-muted">
						<time datimetime="{{ $motd->datetimecreated->toDateTimeLocalString() }}">{{ $motd->datetimecreated->format('F j, Y') }}</time> to
						@if ($motd->trashed())
							<time datimetime="{{ $motd->datetimeremoved->toDateTimeLocalString() }}">{{ $motd->datetimeremoved->format('F j, Y') }}</time>
						@else
							{{ trans('global.never') }}
						@endif
					</p>
					<blockquote>
						<p>
							@if (trim($motd->motd))
								{{ $motd->motd }}
							@else
								<span class="none">{{ trans('global.none') }}</span>
							@endif
						</p>
					</blockquote>
				</li>
			@endforeach
		</ul>
	</div>
	<?php
endif;
