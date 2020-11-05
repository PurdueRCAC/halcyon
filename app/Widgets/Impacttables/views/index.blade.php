<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$first = true;
$last = false;

for ($i = 0; $i < count($all_rows); $i++)
{
	$curr_itsequence = $all_rows[$i]->itsequence;
	$curr_isequence  = $all_rows[$i]->isequence;

	if ($first)
	{
		?>
		<div class="inrows-wide">
			<table class="table table-hover inrows-wide">
				<caption><?php echo $all_rows[$i]->name; ?></caption>
				<thead class="thead-dark">
					<?php
					$first = false;

					if ($all_rows[$i]->name == 'Partners by Community Cluster')
					{
						?>
						<tr>
							<th scope="col">{{ trans('widget.impacttables::impacttables.cluster') }}</th>
							<th scope="col" class="numCol">{{ trans('widget.impacttables::impacttables.departments') }}</th>
							<th scope="col" class="numCol">{{ trans('widget.impacttables::impacttables.faculty') }}</th>
						</tr>
						<?php
					}
					elseif (preg_match("/[a-zA-Z]/i", $all_rows[$i]->columnname))
					{
						?>
						<tr>
							<th scope="col"><?php
							if ($all_rows[$i]->name == 'Top 500 Rankings Over Time')
							{
								echo 'Cluster';
							}
							elseif (preg_match("/\sby\s(.*?)$/i", $all_rows[$i]->name, $matches))
							{
								echo $matches[1];
							}
							?></th>
							<th scope="col" class="numCol"><?php echo $all_rows[$i]->columnname; ?></th>
						</tr>
						<?php
					}
					else
					{
						?>
						<tr>
							<th scope="col">{{ trans('widget.impacttables::impacttables.metric') }}</th>
							<th scope="col" class="numCol">{{ trans('widget.impacttables::impacttables.value') }}</th>
						</tr>
						<?php
					}
					?>
				</thead>
				<tbody>
		<?php
	}

	if ($all_rows[$i]->name == 'Partners by Community Cluster')
	{
		if (strpos($all_rows[$i]->rowname, '-2') === false)
		{
			?>
				<tr>
					<td><?php echo $all_rows[$i]->rowname; ?></td>
					<td class="numCol"><?php echo number_format(intval($all_rows[$i]->value)); ?></td>
					<td class="numCol"><?php echo number_format(intval($all_rows[$i+1]->value)); ?></td>
				</tr>
			<?php
		}
	}
	else
	{
		?>
			<tr>
				<td><?php echo $all_rows[$i]->rowname; ?></td>
				<?php
				if (is_numeric($all_rows[$i]->value))
				{
					if (floor($all_rows[$i]->value) != $all_rows[$i]->value)
					{
						?>
						<td class="numCol"><?php echo number_format(intval($all_rows[$i]->value), 2); ?></td>
						<?php
					}
					else
					{
						?>
						<td class="numCol"><?php echo number_format(intval($all_rows[$i]->value)); ?></td>
						<?php
					}
				}
				else
				{
					?>
					<td class="numCol"><?php echo $all_rows[$i]->value; ?></td>
					<?php
				}
				?>
			</tr>
		<?php
	}

	if ($i+1 === count($all_rows)
	 || $all_rows[$i+1]->isequence <= $curr_isequence
	 || $all_rows[$i+1]->itsequence == $curr_itsequence+1)
	{
		$last = true;
	}

	if ($last)
	{
		?>
				</tbody>
			</table>
		</div>
		<?php
		$first = true;
		$last = false;
	}
}
?>
<div class="inrows-wide">
	<table class="inrows-wide">
		<caption>{{ trans('widget.impacttables::impacttables.research funding') }}</caption>
		<thead class="thead-dark">
			<tr>
				<th scope="col">{{ trans('widget.impacttables::impacttables.fiscal year') }}</th>
				<th scope="col">{{ trans('widget.impacttables::impacttables.faculty awards') }}</th>
				<th scope="col">{{ trans('widget.impacttables::impacttables.total awards') }}</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data as $row) { ?>
				<tr>
					<td><?php echo $row->fiscalyear; ?></td>
					<td>$<?php echo (number_format(round(($row->awards/100)/1000000), 1)); ?> {{ trans('widget.impacttables::impacttables.million') }}</td>
					<td>$<?php echo (number_format(round(($row->totalawards/100)/1000000), 1)); ?> {{ trans('widget.impacttables::impacttables.million') }}</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
