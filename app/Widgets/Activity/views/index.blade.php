<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<div class="card mb-4 widget <?php echo $widget->module; ?>">

	<div class="overview-container">
		<table class="stats-overview">
			<tbody>
				@foreach ($activity as $act)
				<tr>
					<td>
						{{ $act->datetime }}
					</td>
					<td>
						{{ $act->transportmethod }}
					</td>
					<td>
						{{ $act->classname }}
					</td>
					<td>
						{{ $act->classmethod }}
					</td>
				</tr>
				@endforeach
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
