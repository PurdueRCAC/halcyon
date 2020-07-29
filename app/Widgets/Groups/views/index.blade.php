<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<div class="card widget <?php echo $widget->module; ?>">

		<table class="table">
			<thead>
				<tr>
					<th scope="col">{{ trans('widget.groups::groups.name') }}</th>
					<th></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($groups as $group)
				<tr>
					<td>
						<a href="<?php echo route('admin.groups.index'); ?>">
							{{ $group->name }}
						</a>
					</td>
					<td>
						info
					</td>
					<td>
						here
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>

</div>
