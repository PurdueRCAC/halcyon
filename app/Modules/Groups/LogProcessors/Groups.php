<?php
namespace App\Modules\Groups\LogProcessors;

use App\Modules\History\Models\Log;

/**
 * Groups log processor
 */
class Groups
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if (!in_array($record->classname, ['GroupsController']) || $record->summary)
		{
			return $record;
		}

		if ($record->classmethod == 'create')
		{
			$group = '';
			if ($name = $record->getExtraProperty('name'))
			{
				$group = $name;
			}

			$record->summary = 'Created group ' . $group;
		}

		if ($record->classmethod == 'update'
		 || $record->classmethod == 'delete')
		{
			$uri = explode('/', $record->uri);
			$id = end($uri);

			$g = Group::find($id);
			$group = $g ? $g->name : '';

			if ($record->classmethod == 'update')
			{
				$record->summary = 'Updated group ' . $group;
			}

			if ($record->classmethod == 'delete')
			{
				$record->summary = 'Removed group ' . $group;
			}
		}

		return $record;
	}
}
