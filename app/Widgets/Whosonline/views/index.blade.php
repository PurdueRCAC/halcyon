<?php
/**
 * @package  Whosonline widget
 */
?>
<div class="<?php echo $widget->params->get('moduleclass_sfx', ''); ?>">
	<?php if ($widget->params->get('showmode', 0) == 0 || $widget->params->get('showmode', 0) == 2) : ?>
		<table>
			<thead>
				<tr>
					<th scope="col"><?php echo trans('widget.whosonline::whosonline.LOGGEDIN'); ?></th>
					<th scope="col"><?php echo trans('widget.whosonline::whosonline.GUESTS'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo number_format($loggedInCount); ?></td>
					<td><?php echo number_format($guestCount); ?></td>
				</tr>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ($widget->params->get('showmode', 0) == 1 || $widget->params->get('showmode', 0) == 2) : ?>
		<table>
			<thead>
				<tr>
					<th scope="col"><?php echo trans('widget.whosonline::whosonline.LOGGEDIN_NAME'); ?></th>
					<th scope="col"><?php echo trans('widget.whosonline::whosonline.action'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($loggedInList as $loggedin) : ?>
					<tr>
						<td><?php echo $loggedin->get('name'); ?></td>
						<td>
							<a href="<?php echo route('admin.users.edit', ['id' => $loggedin->get('id')]); ?>">
								<?php echo trans('widget.whosonline::whosonline.LOGGEDIN_VIEW_PROFILE'); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<table>
		<tbody>
			<tr>
				<td>
					<a class="btn btn-secondary opposite" href="<?php echo route('admin.users.index'); ?>">
						<span class="fa fa-arrow-right" aria-hidden="true"></span>
						<?php echo trans('widget.whosonline::whosonline.VIEW_ALL_ACTIVITIY'); ?>
					</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>
