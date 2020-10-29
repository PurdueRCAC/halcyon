
	<table class="table table-hover adminlist">
		<caption class="sr-only"><?php echo trans('core::info.configuration file'); ?></caption>
		<thead>
			<tr>
				<th scope="col">
					<?php echo trans('core::info.setting'); ?>
				</th>
				<th scope="col">
					<?php echo trans('core::info.value'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($info as $key => $value): ?>
				<tr>
					<td>
						<?php echo $key; ?>
					</td>
					<td>
						<?php
						if (is_array($value))
						{
							foreach ($value as $ky => $val)
							{
								if (is_array($val))
								{
									foreach ($val as $k => $v)
									{
										echo htmlspecialchars($k, ENT_QUOTES) .' = ' . htmlspecialchars($v, ENT_QUOTES) . '<br />';
									}
								}
								else
								{
									echo htmlspecialchars($ky, ENT_QUOTES) .' = ' . htmlspecialchars($val, ENT_QUOTES) . '<br />';
								}
							}
						}
						else
						{
							echo htmlspecialchars($value, ENT_QUOTES);
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
