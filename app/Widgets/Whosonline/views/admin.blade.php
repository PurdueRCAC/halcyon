<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<div class="widget <?php echo $widget->module; ?>" id="<?php echo $widget->module . $widget->id; ?>">
	<table class="adminlist whosonline-list">
		<thead>
			<tr>
				<th scope="col"><?php echo trans('widget.whosonline::whosonline.user'); ?></td>
				<th scope="col" class="priority-3"><?php echo trans('widget.whosonline::whosonline.last activity'); ?></th>
				<?php if ($editAuthorized): ?>
					<th scope="col"><?php echo trans('widget.whosonline::whosonline.logout'); ?></th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php if (count($rows) > 0) : ?>
				<?php foreach ($rows as $k => $row) : ?>
					<?php if (($k+1) <= $params->get('display_limit', 25)) : ?>
						<tr>
							<td>
								<?php
								// Get user object
								$user = App\Modules\Users\Models\User::find($row->user_id);
								//$user = $user ?: new App\Modules\Users\Models\User;

								// Display link if we are authorized
								if ($editAuthorized && $user):
									echo '<a href="' . url('index.php?option=com_members&task=edit&id='. $user->id) . '" title="' . trans('widget.whosonline::whosonline.EDIT_USER') . '">' . e($user->name) . ' [' . e($user->username) . ']' . '</a>';
								else:
									if ($user):
										echo e($user->name) . ' [' . e($user->username) . ']';
									else:
										echo trans('global.guest');
									endif;
								endif;
								?>
							</td>
							<td class="priority-3">
								<?php echo Carbon\Carbon::parse($row->last_activity)->diffForHumans(); ?>
							</td>
							<td>
								<?php if ($editAuthorized && $user): ?>
									<a class="btn btn-sm btn-danger force-logout" href="<?php echo route('admin.users.edit', ['id' => $row->user_id]); ?>">
										<span><?php echo trans('widget.whosonline::whosonline.logout'); ?></span>
									</a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr>
					<td colspan="<?php echo ($editAuthorized) ? 3 : 2; ?>" class="view-all">
						<a href="<?php echo route('admin.users.index'); ?>"><?php echo trans('widget.whosonline::whosonline.view all'); ?></a>
					</td>
				</tr>
			<?php else : ?>
				<tr>
					<td colspan="<?php echo ($editAuthorized) ? 3 : 2; ?>">
						<?php echo trans('widget.whosonline::whosonline.no results'); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
