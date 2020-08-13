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
	 * Display a listing of quotas for a user
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/quotas
	 * @apiParameter {
	 * 		"name":          "username",
	 * 		"description":   "User username to retrieve data for",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "Current user's username"
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 *      "default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'username'  => $request->input('username', auth()->user()->username),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'name'),
			'order_dir' => $request->input('order_dir', 'ASC')
		);

		if ($filters['username'])
		{
			$user = User::findByUsername($filters['username']);
		}
		else
		{
			$user = auth()->user();
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
					//$message = Message::query()
						//->where('targetobjectid', '=', $row->id)
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
						$row->addMessageToQueue($row->getquotatypeid, auth()->user()->id);
						/*$message = new Message;
						$message->userid = auth()->user()->id;
						$message->targetobjectid = $row->id;
						$message->messagequeuetypeid = $row->getquotatypeid;
						$message->save();*/
					}
				}
			}
		});

		return new ResourceCollection($rows);
	}
}
