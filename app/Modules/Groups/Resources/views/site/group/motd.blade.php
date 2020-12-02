
					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Group Notice
						</div>
						<div class="card-body panel-body">
							@if ($canManage)
								<form method="post" action="{{ route('site.users.account.section', ['section' => 'groups']) }}">
									<fieldset>
										<legend class="sr-only">Set Group Notice</legend>

										<div class="form-group">
											<label for="MotdText_<?php echo $group->id; ?>">Enter the notice your group will see at login</label>
											<textarea id="MotdText_<?php echo $group->id; ?>" class="form-control" cols="38" rows="4"><?php echo $group->motd ? $group->motd->motd : ''; ?></textarea>
										</div>

										<div class="form-group">
											<input type="button" value="Set Notice" class="motd-set btn btn-success" data-group="<?php echo $group->id; ?>" />
											<?php if ($group->motd) { ?>
												<input type="button" value="Delete Notice" class="motd-delete btn btn-danger" data-group="<?php echo $group->id; ?>" />
											<?php } ?>
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
					$motds = $group->motds();

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
								Past Notices
							</div>
							<ul class="list-group list-group-flush">
								@foreach ($past as $motd)
									<li class="list-group-item">
										<a href="{{ route('site.users.account.section', ['section' => 'groups', 'group' => $group->id, 'deletemotd' => $motd->id]) }}" class="delete motd-delete"><i class="fa fa-trash"></i><span class="sr-only">Delete</span></a>
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