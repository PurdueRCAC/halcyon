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
										<label for="MotdText_{{ $group->id }}">Enter the notice your group will see at login</label>
										<textarea id="MotdText_{{ $group->id }}" data-api="{{ route('api.groups.motd.create') }}" class="form-control" cols="38" rows="4">{{ $group->motd ? $group->motd->motd : '' }}</textarea>
									</div>

									<div class="form-group">
										<button class="motd-set btn btn-success" data-group="{{ $group->id }}">{{ trans('groups::groups.set notice') }}</button>
										@if ($group->motd)
											<button class="motd-delete btn btn-danger" id="MotdText_delete_{{ $group->id }}" data-api="{{ route('api.groups.motd.delete', ['id' => $group->motd->id]) }}" data-group="{{ $group->id }}"><span class="icon-trash"></span> Delete Notice</button>
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
							<!--<ul class="list-group list-group-flush">-->
							<table class="table table-hover">
								<caption class="sr-only">{{ trans('groups::groups.past motd') }}</caption>
								<thead>
									<tr>
										<th scope="col">{{ trans('groups::groups.from') }}</th>
										<th scope="col">{{ trans('groups::groups.until') }}</th>
										<th scope="col">{{ trans('groups::groups.message') }}</th>
										<!-- <th scope="col">Options</th> -->
									</tr>
								</thead>
								<tbody>
								@foreach ($past as $motd)
									<tr>
										<td>
											{{ $motd->datetimecreated }}
										</td>
										<td>
											@if ($motd->datetimeremoved)
												{{ $motd->datetimeremoved }}
											@else
												trans('global.never')
											@endif
										</td>
										<td>
											{{ $motd->motd }}
										</td>
										<!-- <td>
											<a href="{{ route('admin.groups.edit', ['id' => $group->id, 'deletemotd' => $motd->id]) }}" class="btn btn-danger delete motd-delete">
												<span class="icon-trash"></span><span class="sr-only">{{ trans('global.delete') }}</span>
											</a>
										</td> -->
									</tr>
								<!-- <li class="list-group-item">
										<a href="{{ route('site.users.account.section', ['section' => 'groups', 'group' => $group->id, 'deletemotd' => $motd->id]) }}" class="delete motd-delete"><span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only">Delete</span></a>
										<p class="text-muted">
											{{ $motd->datetimecreated }} to
											@if ($motd->datetimeremoved)
												{{ $motd->datetimeremoved }}
											@else
												trans('global.never')
											@endif
										</p>
										<blockquote>
											<p>{{ $motd->motd }}</p>
										</blockquote>
									</li> -->
								@endforeach
								</tbody>
							</table>
					</div>
							<!--</ul>-->
						</div>
						<?php
					}
					?>