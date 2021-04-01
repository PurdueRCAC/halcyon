<?php
/**
 * @package  RCAC Impact Tables
 */

$first = true;
$last = false;

for ($i = 0; $i < count($all_rows); $i++)
{
	$curr_itsequence = $all_rows[$i]->itsequence;
	$curr_isequence  = $all_rows[$i]->isequence;

	if ($first):
		?>
		<div class="inrows-wide">
			<table class="table table-hover inrows-wide">
				<caption><?php echo $all_rows[$i]->name; ?></caption>
				<thead>
					<?php
					$first = false;

					if ($all_rows[$i]->name == 'Partners by Community Cluster'):
						?>
						<tr>
							<th scope="col">{{ trans('widget.impacttables::impacttables.cluster') }}</th>
							<th scope="col" class="numCol">{{ trans('widget.impacttables::impacttables.departments') }}</th>
							<th scope="col" class="numCol">{{ trans('widget.impacttables::impacttables.faculty') }}</th>
						</tr>
						<?php
					elseif (preg_match("/[a-zA-Z]/i", $all_rows[$i]->columnname)):
						?>
						<tr>
							<th scope="col"><?php
							if ($all_rows[$i]->name == 'Top 500 Rankings Over Time'):
								echo 'Cluster';
							elseif (preg_match("/\sby\s(.*?)$/i", $all_rows[$i]->name, $matches)):
								echo $matches[1];
							endif;
							?></th>
							<th scope="col" class="numCol"><?php echo $all_rows[$i]->columnname; ?></th>
						</tr>
						<?php
					else:
						?>
						<tr>
							<th scope="col">{{ trans('widget.impacttables::impacttables.metric') }}</th>
							<th scope="col" class="numCol">{{ trans('widget.impacttables::impacttables.value') }}</th>
						</tr>
						<?php
					endif;
					?>
				</thead>
				<tbody>
		<?php
	endif;

	if ($all_rows[$i]->name == 'Partners by Community Cluster'):
		if (strpos($all_rows[$i]->rowname, '-2') === false):
			?>
				<tr>
					<td>{{ $all_rows[$i]->rowname }}</td>
					<td class="numCol">{{ number_format(intval($all_rows[$i]->value)) }}</td>
					<td class="numCol">{{ number_format(intval($all_rows[$i+1]->value)) }}</td>
				</tr>
			<?php
		endif;
	else:
		?>
			<tr>
				<td><?php echo $all_rows[$i]->rowname; ?></td>
				<?php
				if (is_numeric($all_rows[$i]->value)):
					if (floor($all_rows[$i]->value) != $all_rows[$i]->value):
						?>
						<td class="numCol"><?php echo number_format(intval($all_rows[$i]->value), 2); ?></td>
						<?php
					else:
						?>
						<td class="numCol"><?php echo number_format(intval($all_rows[$i]->value)); ?></td>
						<?php
					endif;
				else:
					?>
					<td class="numCol"><?php echo $all_rows[$i]->value; ?></td>
					<?php
				endif;
				?>
			</tr>
		<?php
	endif;

	if ($i+1 === count($all_rows)
	 || $all_rows[$i+1]->isequence <= $curr_isequence
	 || $all_rows[$i+1]->itsequence == $curr_itsequence+1):
		$last = true;
	endif;

	if ($last):
		?>
				</tbody>
			</table>
		</div>
		<?php
		$first = true;
		$last = false;
	endif;
}
?>
<div class="inrows-wide">
	<table class="inrows-wide">
		<caption>{{ trans('widget.impacttables::impacttables.research funding') }}</caption>
		<thead>
			<tr>
				<th scope="col">{{ trans('widget.impacttables::impacttables.fiscal year') }}</th>
				<th scope="col">{{ trans('widget.impacttables::impacttables.faculty awards') }}</th>
				<th scope="col">{{ trans('widget.impacttables::impacttables.total awards') }}</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($data as $row)
				<tr>
					<td>{{ $row->fiscalyear }}</td>
					<td>${{ number_format(round(($row->awards/100)/1000000), 1) }} {{ trans('widget.impacttables::impacttables.million') }}</td>
					<td>${{ number_format(round(($row->totalawards/100)/1000000), 1) }} {{ trans('widget.impacttables::impacttables.million') }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>
