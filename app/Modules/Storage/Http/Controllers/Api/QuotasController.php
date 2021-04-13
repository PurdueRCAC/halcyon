<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
//use App\Modules\Messages\Models\Message;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Users\Models\User;

/**
 * Quotas
 *
 * @apiUri    /api/storage/quotas
 */
class QuotasController extends Controller
{
	/**
	 * Display a listing of quotas for a user.
	 * 
	 * Note that if the user has any current quota usage information,
	 * a message is added to the queue to provide the latest info.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/storage/quotas/{username?}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "username",
	 * 		"description":   "User username to retrieve data for",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "Current user's username"
	 * 		}
	 * }
	 * @return Response
	 */
	public function index(Request $request, $username)
	{
		/*$filters = array(
			'username'  => $request->input('username', auth()->user()->username)
		);*/

		if ($username)
		{
			$user = User::findByUsername($username); //$filters['username']);
		}
		else
		{
			$user = auth()->user();
		}

		if (!$user)
		{
			return new ResourceCollection(collect([]));
		}

		// Get records
		$d = (new Directory)->getTable();
		$r = (new StorageResource)->getTable();

		$rows = Directory::query()
			->select($d . '.*', $r . '.getquotatypeid')
			->join($r, $r . '.id', $d . '.storageresourceid')
			->where($d . '.owneruserid', '=', $user->id)
			->where(function($where) use ($r, $d)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
			->get();

		$g = (new UnixGroup)->getTable();

		$rows2 = Directory::query()
			->select($d . '.*', $r . '.getquotatypeid')
			->join($r, $r . '.id', $d . '.storageresourceid')
			->join($g, $g . '.id', $d . '.unixgroupid')
			->where($d . '.owneruserid', '=', $user->id)
			->where(function($where) use ($r, $d)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
			->get();

		$rows = $rows->merge($rows2);

		$rows->each(function ($row, $key)
		{
			$data = $row->usage()
				->orderBy('datetimerecorded', 'desc')
				->limit(1)
				->get()
				->first();

			$row->path              = $row->storageResource->path . '/' . $row->path;
			$row->total_block_usage = 0;
			$row->block_limit       = 1;
			$row->space             = 0;
			$row->quota             = 0;
			$row->total_file_usage  = 0;
			$row->file_limit        = 1;
			$row->timestamp         = 0;

			$row->api = route('api.storage.read', ['id' => $row->id]);

			if ($data)
			{
				if ($data->quota != 0)
				{
					$row->total_block_usage = ($data->space / 1024);
					$row->block_limit       = ($data->quota / 1024);
					$row->space             = ($data->space);
					$row->quota             = ($data->quota);
				}

				if ($data->filequota != 0)
				{
					$row->total_file_usage = $data->files;
					$row->file_limit       = $data->filequota;
				}

				$row->timestamp = strtotime($data->datetimerecorded);
			}

			// Force refresh?
			if (!$data || date("U") - strtotime($data->datetimerecorded) > 900)
			{
				// If we know how
				if ($row->getquotatypeid)
				{
					// Assuming no pending requests
					$message = $row->messages()
						->where('messagequeuetypeid', '=', $row->getquotatypeid)
						->where(function($where)
						{
							$where->whereNull('datetimecompleted')
								->orWhere('datetimecompleted', '=', '0000-00-00 00:00:00');
						})
						->get()
						->first();

					if (!$message)
					{
						$row->addMessageToQueue($row->getquotatypeid, auth()->user() ? auth()->user()->id : 0);
					}
				}
			}
		});

		return new ResourceCollection($rows);
	}
}
