<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<div class="card mb-4 widget <?php echo $widget->module; ?>">

	<div class="overview-container">
		<?php
		$total = $confirmed + $unconfirmed;

		$percent = round(($confirmed / $total) * 100, 2);
		?>
		<table class="stats-overview">
			<tbody>
				<tr>
					<td colspan="3">
						<div>
							<div class="graph confirmed-graph">
								<strong class="bar"><span><?php echo $percent; ?>%</span></strong>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="confirmed">
						<a href="<?php echo route('admin.users.index') . '?activation=1&created_at='; ?>" title="<?php echo trans('widgets::members.CONFIRMED_TITLE'); ?>">
							{{ number_format($confirmed) }}
							<span><?php echo trans('widget.members::members.CONFIRMED'); ?></span>
						</a>
					</td>
					<td class="unconfirmed">
						<a href="<?php echo route('admin.users.index') . '?activation=-1&created_at='; ?>" title="<?php echo trans('widgets::members.UNCONFIRMED_TITLE'); ?>">
							{{ number_format($unconfirmed) }}
							<span><?php echo trans('widget.members::members.UNCONFIRMED'); ?></span>
						</a>
					</td>
					<td class="newest">
						<a href="<?php echo route('admin.users.index') . '?activation=0&created_at=' . gmdate("Y-m-d H:i:s", strtotime('-1 day')); ?>" title="<?php echo trans('widgets::members.NEW_TITLE'); ?>">
							{{ number_format($pastDay) }}
							<span><?php echo trans('widget.members::members.NEW'); ?></span>
						</a>
					</td>
				</tr>
			</tbody>
		</table>

		<?php
		$total = $approved + $unapproved;

		$percent = round(($approved / $total) * 100, 2);

		?>
		<table class="stats-overview">
			<tbody>
				<tr>
					<td colspan="3">
						<div>
							<div class="graph approved-graph">
								<strong class="bar"><span><?php echo $percent; ?>%</span></strong>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="approved">
						<a href="<?php echo route('admin.users.index'); ?>?approved=1" title="<?php echo trans('widget.members::members.APPROVED_TITLE'); ?>">
							<?php echo e($approved); ?>
							<span><?php echo trans('widget.members::members.APPROVED'); ?></span>
						</a>
					</td>
					<td class="unapproved">
						<a href="<?php echo route('admin.users.index'); ?>?approved=0" title="<?php echo trans('widget.members::members.UNAPPROVED_TITLE'); ?>">
							<?php echo e($unapproved); ?>
							<span><?php echo trans('widget.members::members.UNAPPROVED'); ?></span>
						</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<p class="note"><?php echo trans('widget.members::members.NOTE'); ?></p>
</div>
