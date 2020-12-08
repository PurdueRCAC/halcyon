
<div class="card panel panel-default">
	<div class="card-header panel-heading">
		Storage Spaces
	</div>
	<div class="card-body panel-body">
		@if (count($group->directories) > 0)
			<table class="simpleTable">
				<caption class="sr-only">Below is a list of all storage spaces:</caption>
				<thead class="resource">
					<tr>
						<th scope="col">Resource</th>
						<th scope="col">Path</th>
						<th scope="col" class="text-right">Size</th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($group->directories as $dir)
				{
					if (!$dir->bytes)
					{
						continue;
					}
					?>
					<tr>
						<td>
							{{ $dir->storageResource->name }}
						</td>
						<td>
							@if (auth()->user()->can('manage storage'))
								<a href="/admin/storage/edit/?g={{ $group->id }}&r={{ $dir->storageResource->id }}">
									{{ $dir->storageResource->path . '/' . $dir->path }}
								</a>
							@else
									{{ $dir->storageResource->path . '/' . $dir->path }}
							@endif
						</td>
						<td class="text-right">
							{{ App\Halcyon\Utility\Number::formatBytes($dir->bytes) }}
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		@endif
	</div>
</div>
