@push('scripts')
<script src="{{ timestamped_asset('modules/groups/js/motd.js') }}"></script>
@endpush

<div class="card panel mb-4 panel-default">
	<div class="card-header panel-heading">
		{{ trans('groups::groups.motd') }}
	</div>
	<div class="card-body panel-body">
		<form method="post" action="{{ route('admin.groups.edit', ['id' => $group->id]) }}">
			<fieldset>
				<legend class="sr-only">{{ trans('groups::groups.set notice') }}</legend>

				<div class="form-group">
					<label for="MotdText_{{ $group->id }}">{{ trans('groups::groups.enter motd') }}</label>
					<textarea id="MotdText_{{ $group->id }}" data-api="{{ route('api.groups.motd.create') }}" class="form-control" cols="38" rows="4">{{ $group->motd ? $group->motd->motd : '' }}</textarea>
				</div>

				<div class="form-group">
					<button class="motd-set btn btn-success" data-group="{{ $group->id }}">{{ trans('groups::groups.set notice') }}</button>
					@if ($group->motd)
						<button class="motd-delete btn btn-danger" id="MotdText_delete_{{ $group->id }}" data-api="{{ route('api.groups.motd.delete', ['id' => $group->motd->id]) }}" data-group="{{ $group->id }}"><span class="icon-trash"></span> {{ trans('groups::groups.delete notice') }}</button>
					@endif
				</div>
			</fieldset>
		</form>
	</div><!-- / .card-body -->
</div><!-- / .card -->

<?php
$motds = $group->motds()->withTrashed();

if ($group->motd)
{
	$motds->where('id', '!=', $group->motd->id);
}

$past = $motds
	->orderBy('datetimecreated', 'desc')
	->get();

if (count($past))
{
	?>
	<div class="card panel panel-default mb-4">
		<div class="card-header panel-heading">
			{{ trans('groups::groups.past notices') }}
		</div>
		<div class="card-body panel-body">
			<table class="table table-hover">
				<caption class="sr-only">{{ trans('groups::groups.past motd') }}</caption>
				<thead>
					<tr>
						<th scope="col">{{ trans('groups::groups.from') }}</th>
						<th scope="col">{{ trans('groups::groups.until') }}</th>
						<th scope="col">{{ trans('groups::groups.message') }}</th>
					</tr>
				</thead>
				<tbody>
				@foreach ($past as $motd)
					<tr>
						<td>
							<time datetime="{{ $motd->datetimecreated->toDateTimeLocalString() }}">{{ $motd->datetimecreated->format('F j, Y') }}</time>
						</td>
						<td>
							@if ($motd->datetimeremoved)
								<time datetime="{{ $motd->datetimeremoved->toDateTimeLocalString() }}">{{ $motd->datetimeremoved->format('F j, Y') }}</time>
							@else
								{{ trans('global.never') }}
							@endif
						</td>
						<td>
							{{ $motd->motd }}
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
