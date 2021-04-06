@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('impact::impact.module name'),
		route('admin.impact.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin impact'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('impact')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('impact::impact.module name') }}
@stop

@section('content')
<?php
$first = true;
$last = false;

for ($i = 0; $i < count($all_rows); $i++)
{
	$curr_itsequence = $all_rows[$i]->itsequence;
	$curr_isequence  = $all_rows[$i]->isequence;

	if ($first):
		?>
		<div class="card mb-3">
			<table class="table table-hover">
				<caption><?php echo $all_rows[$i]->name; ?></caption>
				<thead>
					<?php
					$first = false;

					if ($all_rows[$i]->name == 'Partners by Community Cluster'):
						?>
						<tr>
							<th scope="col">{{ trans('impact::impact.cluster') }}</th>
							<th scope="col" class="text-right">{{ trans('impact::impact.departments') }}</th>
							<th scope="col" class="text-right">{{ trans('impact::impact.faculty') }}</th>
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
							<th scope="col" class="text-right"><?php echo $all_rows[$i]->columnname; ?></th>
						</tr>
						<?php
					else:
						?>
						<tr>
							<th scope="col">{{ trans('impact::impact.metric') }}</th>
							<th scope="col" class="text-right">{{ trans('impact::impact.value') }}</th>
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
					<td class="text-right">{{ number_format(intval($all_rows[$i]->value)) }}</td>
					<td class="text-right">{{ number_format(intval($all_rows[$i+1]->value)) }}</td>
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
						<td class="text-right"><?php echo number_format(intval($all_rows[$i]->value), 2); ?></td>
						<?php
					else:
						?>
						<td class="text-right"><?php echo number_format(intval($all_rows[$i]->value)); ?></td>
						<?php
					endif;
				else:
					?>
					<td class="text-right"><?php echo $all_rows[$i]->value; ?></td>
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
<div class="card">
	<table class="table table-hover">
		<caption>{{ trans('impact::impact.research funding') }}</caption>
		<thead>
			<tr>
				<th scope="col">{{ trans('impact::impact.fiscal year') }}</th>
				<th scope="col" class="text-right">{{ trans('impact::impact.faculty awards') }}</th>
				<th scope="col" class="text-right">{{ trans('impact::impact.total awards') }}</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($data as $row)
				<tr>
					<td>{{ $row->fiscalyear }}</td>
					<td class="text-right">${{ number_format(round(($row->awards/100)/1000000), 1) }} {{ trans('impact::impact.million') }}</td>
					<td class="text-right">${{ number_format(round(($row->totalawards/100)/1000000), 1) }} {{ trans('impact::impact.million') }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>
@endsection
