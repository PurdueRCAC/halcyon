<?php
	$found = array();

		foreach ($user->ownerofgroups as $ogroup)
		{
			$group = $ogroup->group;

			if (!$group)
			{
				continue;
			}

			$directories = $group->directories()
				->withTrashed()
				->whereISActive()
				->where('parentstoragedirid', '=', 0)
				->where('resourceid', '=', 64)
				->get();

			foreach ($directories as $directory)
			{
				if (isset($found[$directory->id]))
				{
					continue;
				}

				$found[$directory->id] = $directory;
			}
		}

		foreach ($user->memberofgroups as $mgroup)
		{
			$group = $mgroup->group;

			if (!$group)
			{
				continue;
			}

			$directories = $group->directories()
				->withTrashed()
				->whereISActive()
				->where('parentstoragedirid', '=', 0)
				->where('resourceid', '=', 64)
				->get();

			foreach ($directories as $directory)
			{
				if (isset($found[$directory->id]))
				{
					continue;
				}

				$found[$directory->id] = $directory;
			}
		}

		foreach ($user->memberofqueues as $queue)
		{
			$group = $queue->group;

			if (!$group)
			{
				continue;
			}

			$directories = $group->directories()
				->withTrashed()
				->whereISActive()
				->where('parentstoragedirid', '=', 0)
				->where('resourceid', '=', 64)
				->get();

			foreach ($directories as $directory)
			{
				if (isset($found[$directory->id]))
				{
					continue;
				}

				$found[$directory->id] = $directory;
			}
		}

		foreach ($user->memberofunixgroups as $unixgroup)
		{
			$group = $unixgroup->group;

			if (!$group)
			{
				continue;
			}

			$directories = $group->directories()
				->withTrashed()
				->whereISActive()
				->where('parentstoragedirid', '=', 0)
				->where('resourceid', '=', 64)
				->get();

			foreach ($directories as $directory)
			{
				if (isset($found[$directory->id]))
				{
					continue;
				}

				$found[$directory->id] = $directory;
			}
		}

	if (count($found))
	{
		?>
		<div class="card card-warning panel panel-warning">
			<div class="card-body panel-body">
				<p class="alert alert-warning"><strong>Note:</strong> The Data Depot storage service is <a href="https://www.rcac.purdue.edu/news/2968">transitioning to new hardware</a>. You are a member of one or more groups with Data Depot space. Check below for the migration status of the Depot space. For further details, check the <a href="https://www.rcac.purdue.edu/news/2970">FAQ</a>.</p>
				<table>
					<caption class="sr-only">Migration Status</caption>
					<thead>
						<tr>
							<th scope="col">Path</th>
							<th scope="col">Group</th>
							<th scope="col">Status</th>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ($found as $dir)
					{
						$status = '<span class="badge badge-secondary">Not migrated</span>';

						if (is_link($dir->fullPath))
						{
							$path = realpath($dir->fullPath);

							if (preg_match('/depot-?new/i', $path))
							{
								$status = '<span class="badge badge-success">Migrated</span>';
							}
						}
						?>
						<tr>
							<td><?php echo $dir->fullPath; ?></td>
							<td><a href="/account/groups/{{ $dir->groupid }}">{{ $dir->group->name }}</a></td>
							<td><?php echo $status; ?></td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
