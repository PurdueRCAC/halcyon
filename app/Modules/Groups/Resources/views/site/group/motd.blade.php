
					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							{{ trans('groups::groups.motd') }}
						</div>
						<div class="card-body panel-body">
							@if ($canManage)
								<form method="post" action="{{ route('site.users.account.section', ['section' => 'groups']) }}">
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
							@else
								<p class="text-muted">
									{{ $group->datetimecreated }} to {{ $group->datetimeremoved }}
								</p>
								<blockquote>
									<p>{{ $group->motd }}</p>
								</blockquote>
							@endif
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
						<div class="card panel panel-default">
							<div class="card-header panel-heading">
								{{ trans('groups::groups.past notices') }}
							</div>
							<ul class="list-group list-group-flush">
								@foreach ($past as $motd)
									<li class="list-group-item">
										<a href="{{ route('site.users.account.section', ['section' => 'groups', 'group' => $group->id, 'deletemotd' => $motd->id]) }}" class="delete motd-delete"><i class="fa fa-trash"></i><span class="sr-only">{{ trans('global.delete') }}</span></a>
										<p class="text-muted">
											{{ $motd->datetimecreated }} to
											@if ($motd->datetimeremoved && $motd->datetimeremoved != '0000-00-00 00:00:00')
												{{ $motd->datetimeremoved }}
											@else
												trans('global.never')
											@endif
										</p>
										<blockquote>
											<p>{{ $motd->motd }}</p>
										</blockquote>
									</li>
								@endforeach
							</ul>
						</div>
						<?php
					}
					?>