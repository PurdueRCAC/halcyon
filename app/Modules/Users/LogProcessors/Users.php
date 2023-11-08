<?php
namespace App\Modules\Users\LogProcessors;

use App\Modules\ContactReports\Models\Report;
use App\Modules\History\Models\Log;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\Facet;
use Carbon\Carbon;

/**
 * Users log processor
 */
class Users
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->app == 'api' && substr($record->uri, 0, strlen('/api/users')) != '/api/users')
		{
			return $record;
		}

		if ($record->classname == 'UsersController')
		{
			if (in_array($record->transportmethod, ['PUT', 'DELETE'])
			&& (!$record->targetuserid || $record->targetuserid < 0))
			{
				$parts = explode('/', $record->uri);
				$id = end($parts);
				$id = intval($id);

				$record->targetuserid = $id;
			}

			if ($record->transportmethod == 'POST')
			{
				$record->summary = 'Created User Account';

				if (auth()->user() && auth()->user()->can('manage users'))
				{
					$record->summary = 'Created <a href="' . route('admin.users.index') . '">User Account</a>';
				}
			}
			elseif ($record->transportmethod == 'PUT')
			{
				$record->summary = 'Updated User Account';

				if (auth()->user() && auth()->user()->can('manage users'))
				{
					$record->summary = 'Updated <a href="' . route('admin.users.show', ['id' => $record->targetuserid]) . '">User Account</a>';
				}

				if ($facets = $record->getExtraProperty('facets'))
				{
					$record->summary = 'Updated <a href="' . route('admin.users.show', ['id' => $record->targetuserid]) . '">User Account attribute</a>';
					foreach ($facets as $key => $val)
					{
						$record->summary .= ' "' . $key . '"';
					}
				}

				if ($record->targetuserid != $record->userid)
				{
					$u = User::find($record->targetuserid);

					if ($u)
					{
						$record->summary .= ' ' . $u->name . ' (' . $u->username . ')';
					}
				}
			}
			elseif ($record->transportmethod == 'DELETE')
			{
				$record->summary = 'Removed User Account';

				if (auth()->user() && auth()->user()->can('manage users'))
				{
					$record->summary = 'Removed <a href="' . route('admin.users.index') . '">User Account</a>';
				}

				if ($record->targetuserid != $record->userid)
				{
					$u = User::find($record->targetuserid);

					if ($u)
					{
						$record->summary .= ' ' . $u->name . ' (' . $u->username . ')';
					}
				}
			}
		}
		elseif ($record->classname == 'FacetsController')
		{
			$facet = null;

			if (in_array($record->transportmethod, ['PUT', 'DELETE'])
			&& (!$record->targetuserid || $record->targetuserid < 0))
			{
				$parts = explode('/', $record->uri);
				$id = end($parts);
				$id = intval($id);

				$facet = Facet::find($id);

				if ($facet)
				{
					$record->targetobjectid = $id;
					$record->targetuserid = $facet->user_id;
				}
			}

			if ($record->transportmethod == 'POST')
			{
				$record->summary = 'Created User Account attribute';

				if (auth()->user() && auth()->user()->can('manage users'))
				{
					$record->summary = 'Created <a href="' . route('admin.users.index') . '">User Account</a>';
				}
			}
			elseif ($record->transportmethod == 'PUT')
			{
				$record->summary = 'Updated User Account attribute';

				if (auth()->user() && auth()->user()->can('manage users'))
				{
					$record->summary = 'Updated <a href="' . route('admin.users.show', ['id' => $record->targetuserid]) . '">User Account attribute</a>';
				}

				if ($key = $record->getExtraProperty('key'))
				{
					$record->summary .= ' "' . $key . '"';
				}

				if ($record->targetuserid != $record->userid)
				{
					$u = User::find($record->targetuserid);

					if ($u)
					{
						$record->summary .= ' ' . $u->name . ' (' . $u->username . ')';
					}
				}
			}
			elseif ($record->transportmethod == 'DELETE')
			{
				$record->summary = 'Removed User Account attribute';

				if (auth()->user() && auth()->user()->can('manage users'))
				{
					$record->summary = 'Removed <a href="' . route('admin.users.index') . '">User Account attribute</a>';
				}

				if ($facet)
				{
					$record->summary .= ' "' . $facet->key . '"';
				}

				if ($record->targetuserid != $record->userid)
				{
					$u = User::find($record->targetuserid);

					if ($u)
					{
						$record->summary .= ' ' . $u->name . ' (' . $u->username . ')';
					}
				}
			}
		}

		return $record;
	}
}
