
	<table class="table table-hover adminlist">
		<caption><?php echo trans('core::info.DIRECTORY_PERMISSIONS'); ?></caption>
		<thead>
			<tr>
				<th scope="col">
					<?php echo trans('core::info.DIRECTORY'); ?>
				</th>
				<th scope="col">
					<?php echo trans('core::info.STATUS'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($info as $dir => $data): ?>
				<tr>
					<td>
						<?php echo App\Modules\Core\Helpers\Informant::message($dir, $data['message']);?>
					</td>
					<td>
						<?php echo App\Modules\Core\Helpers\Informant::writable($data['writable']);?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
