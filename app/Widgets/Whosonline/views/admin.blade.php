<?php
/**
 * @package  Whosonline widget
 */
?>
<div class="card widget {{ $widget->widget }}" id="{{ $widget->widget . $widget->id }}">
	<table class="table table-hover whosonline-list">
		<caption>{{ trans('widget.whosonline::whosonline.who is online') }}</caption>
		<thead>
			<tr>
				<th scope="col">{{ trans('widget.whosonline::whosonline.user') }}</td>
				<th scope="col" class="priority-3">{{ trans('widget.whosonline::whosonline.last activity') }}</th>
				@if ($editAuthorized)
					<th scope="col" class="text-right">{{ trans('widget.whosonline::whosonline.logout') }}</th>
				@endif
			</tr>
		</thead>
		<tbody>
			<?php if (count($rows) > 0): ?>
				<?php foreach ($rows as $k => $row): ?>
					<?php if (($k+1) <= $params->get('display_limit', 25)): ?>
						<tr>
							<td>
								<?php
								// Get user object
								$user = App\Modules\Users\Models\User::find($row->user_id);
								//$user = $user ?: new App\Modules\Users\Models\User;

								// Display link if we are authorized
								if ($editAuthorized && $user):
									echo '<a href="' . route('admin.users.edit', ['id' => $user->id]) . '" title="' . trans('widget.whosonline::whosonline.edit user') . '">' . e($user->name) . ' (' . e($user->username) . ')</a>';
								else:
									if ($user):
										echo e($user->name) . ' (' . e($user->username) . ')';
									else:
										echo trans('widget.whosonline::whosonline.guest');
									endif;
								endif;
								/*
								$agent = new Jenssegers\Agent\Agent;
								$agent->setUserAgent($row->user_agent);
								echo '<br /><span class="text-muted">Device: ' . $agent->browser() . ' on ' . $agent->platform() . '</span>';
								*/
								?>
							</td>
							<td class="priority-3">
								{{ Carbon\Carbon::parse($row->last_activity)->diffForHumans() }}
							</td>
							@if ($editAuthorized)
							<td class="text-right">
								@if ($user)
									<a class="btn btn-sm btn-danger force-logout" href="{{ route('admin.users.edit', ['id' => $row->user_id]) }}">
										{{ trans('widget.whosonline::whosonline.logout') }}
									</a>
								@endif
							</td>
							@endif
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr>
					<td colspan="{{ ($editAuthorized ? 3 : 2) }}" class="view-all text-center">
						<a class="btn btn-secondary" href="{{ route('admin.users.index') }}">{{ trans('widget.whosonline::whosonline.view all') }}</a>
					</td>
				</tr>
			<?php else: ?>
				<tr>
					<td colspan="{{ ($editAuthorized ? 3 : 2) }}">
						{{ trans('widget.whosonline::whosonline.no results') }}
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
