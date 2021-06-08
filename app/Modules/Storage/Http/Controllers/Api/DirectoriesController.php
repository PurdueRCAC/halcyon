<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Utility\Number;
use App\Modules\Storage\Http\Resources\DirectoryResource;
use App\Modules\Storage\Http\Resources\DirectoryResourceCollection;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Notification;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Messages\Models\Type as MessageType;
use Carbon\Carbon;

/**
 * Directories
 * 
 * Directories found under a storage resource
 * 
 * @apiUri    /storage/directories
 */
class DirectoriesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/directories
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"datetimecreated",
	 * 				"datetimeremoved",
	 * 				"parentid"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "desc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'     => $request->input('search', ''),
			'resourceid' => $request->input('resourceid'),
			'storageresourceid' => $request->input('storageresourceid'),
			'groupid'   => $request->input('groupid'),
			'parentstoragedirid' => $request->input('parentstoragedirid'),
			'state'     => $request->input('state', 'active'),
			'quota'     => $request->input('quota'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'      => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'path'),
			'order_dir' => $request->input('order_dir', 'ASC')
		);

		// Get records
		$query = Directory::query();

		if ($filters['state'] != '*')
		{
			if ($filters['state'] == 'active')
			{
				$query->where('datetimeremoved', '=', '0000-00-00 00:00:00');
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
			}
		}

		// Filter by resource ID
		if ($filters['resourceid'])
		{
			$query->where('resourceid', '=', $filters['resourceid']);
		}

		if ($filters['groupid'])
		{
			$query->where('groupid', '=', $filters['groupid']);
		}

		if ($filters['parentstoragedirid'])
		{
			$query->where('parentstoragedirid', '=', $filters['parentstoragedirid']);
		}

		if ($filters['storageresourceid'])
		{
			$query->where('storageresourceid', '=', $filters['storageresourceid']);
		}

		// Filter by has/doesn't-have a quota
		if (!is_null($filters['quota']))
		{
			// Has a quota
			if ($filters['quota'] == 'true')
			{
				$query->where('bytes', '<>', 0);
			}
			// Doesn't have a quota
			elseif ($filters['quota'] == 'false')
			{
				$query->where('bytes', '=', 0);
			}
		}

		if (request()->segment(1) == 'ws')
		{
			$rows = $query
				->withCount('children')
				//->groupBy('id')
				->limit(1000)
				->orderBy($filters['order'], $filters['order_dir']);

			$rows->each(function($item, $key)
			{
				$item->type = $item->messagequeuetypeid;
			});

			return response()->json($rows, 200);
		}

		$rows = $query
			->withCount('children')
			//->groupBy('id')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends($filters);

		return new DirectoryResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/directories
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Directory name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource id",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group id",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "bytes",
	 * 		"description":   "Byes",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parentstorageid",
	 * 		"description":   "Parent directory ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "owneruserid",
	 * 		"description":   "Owner user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroupid",
	 * 		"description":   "Unix Group ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ownerread",
	 * 		"description":   "Owner read permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupread",
	 * 		"description":   "Group read permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupwrite",
	 * 		"description":   "Group write permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publicread",
	 * 		"description":   "Public read permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publicwrite",
	 * 		"description":   "Public write permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "autouser",
	 * 		"description":   "Auto user",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1,
	 * 				2,
	 * 				3
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "files",
	 * 		"description":   "Files",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "autouserunixgroupid",
	 * 		"description":   "Auto user Unix Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "storageresourceid",
	 * 		"description":   "Storage Resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @param  array    $data
	 * @param  integer  $offset
	 * @return Response
	 */
	public function create(Request $request, $data = array(), $offset = 0)
	{
		if (empty($data))
		{
			$request->validate([
				'name' => 'required|string|max:32',
				//'path' => 'nullable|string|max:255',
				'resourceid' => 'required|integer|min:1',
				'groupid' => 'required|integer|min:1',
				'bytes' => 'required|string',
				'parentstorageid' => 'nullable|integer',
				'owneruserid' => 'nullable|integer',
				'unixgroupid' => 'nullable|integer',
				'ownerread' => 'nullable|integer',
				'groupread' => 'nullable|integer',
				'groupwrite' => 'nullable|integer',
				'publicread' => 'nullable|integer',
				'publicwrite' => 'nullable|integer',
				'autouser' => 'nullable|in:0,1,2,3',
				'files' => 'nullable|integer',
				'autouserunixgroupid' => 'nullable|integer',
				'storageresourceid' => 'nullable|integer',
			]);

			$data = $request->all();
		}

		$bytesource = null;
		if (array_key_exists('bytesource', $data))
		{
			$bytesource = $data['bytesource'];
			unset($data['bytesource']);
		}

		$row = new Directory;

		// Set up permissions
		$row->ownerread   = 1;
		$row->ownerwrite  = 1;
		$row->groupread   = 1;
		$row->groupwrite  = 1;
		$row->publicread  = 0;
		$row->publicwrite = 0;
		$row->storageresourceid = 0;
		$row->fill($data);

		if ($row->parent)
		{
			// Disable parent groupwrite
			if (!$row->parent->autouser)
			{
				$return = $row->parent->update(['groupwrite' => 0]);

				if (!$return)
				{
					return response()->json(['message' => trans('Failed to update `storagedir` for :id', ['id' => $row->parentstoragedirid])], 415);
				}
			}

			$row->publicread = 1;
		}

		// Make sure name is sane
		if (!preg_match("/^([a-zA-Z0-9]+\.?[\-_ ]*)*[a-zA-Z0-9]$/", $row->name))
		{
			return response()->json(['message' => trans('Field `name` has invalid format')], 415);
		}

		if (!$row->autouserunixgroupid)
		{
			$row->autouserunixgroupid = $row->unixgroupid;
		}

		// Get parent so we can assemble a path
		$row->path = $row->parent ? $row->parent->path . '/' . $row->name : $row->name;

		if (strlen($row->path) > 255)
		{
			return response()->json(['message' => trans('Field `path` cannot be longer than 255 characters')], 415);
		}

		// Find appropriate bucket
		$bucket = null;
		foreach ($row->group->storagebuckets as $b)
		{
			if ($b['resourceid'] == $row->resourceid)
			{
				$bucket = $b;
				break;
			}
		}

		$parent = null;
		$bytes = $request->input('bytes');

		if (preg_match_all("/^(\-?\d*\.?\d+)\s*(\w+)$/", $bytes, $matches))
		{
			if ($bucket == null)
			{
				return response()->json(['message' => trans('Empty bucket')], 415);
			}

			if ($bytesource && $bytesource == 'p' && $row->parent)
			{
				// Deducting from parent
				// Check to see if parent has sufficient bytes
				$parent = $row->parent;

				// Find the byte source, next ancestor with a quota
				while ($parent->bytes == 0 && $parent->parentstoragedirid != 0)
				{
					$parent = $parent->parent;

					if (!$parent)
					{
						return response()->json(['message' => trans('Failed to retrieve `storagedir` for :bytesource', ['bytesource' => $bytesource])], 500);
					}
				}

				if ($parent->bytes <= $row->bytes)
				{
					return response()->json(['message' => trans('Parent quota is less than value submitted')], 415);
				}

				// Reduce bytesource appropriately
				$parent->bytes = ($parent->bytes - $row->bytes) . ' B';
			}
			elseif ($row->bytes > $bucket['unallocatedbytes'])
			{
				// Check to see if tried to allocate all remaining space but we missed a fwe bits because of rounding
				if (Number::formatBytes($row->bytes, 2) == Number::formatBytes($bucket['unallocatedbytes'], 2)
				 && $row->bytes != $bucket['unallocatedbytes'])
				{
					$row->bytes = $bucket['unallocatedbytes'];
				}

				if ($row->bytes > $bucket['unallocatedbytes'])
				{
					return response()->json(['message' => trans('Submitted bytes is greater than unallocatedbytes')], 415);
				}
			}
			else
			{
				// Check to see if tried to allocate all remaining space but we missed a fwe bits because of rounding
				if (Number::formatBytes($row->bytes, 2) == Number::formatBytes($bucket['unallocatedbytes'], 2)
				 && $row->bytes != $bucket['unallocatedbytes'])
				{
					$row->bytes = $bucket['unallocatedbytes'];
				}
			}
		}
		elseif ($bytes == '-')
		{
			if (!$row->parent)
			{
				return response()->json(['message' => trans('Missing or invalid parent value')], 415);
			}

			$row->bytes = 0;
		}
		elseif ($bytes == 'ALL')
		{
			if ($bucket == null)
			{
				$row->bytes = 0;
			}
			else
			{
				$row->bytes = $bucket['unallocatedbytes'];
			}
		}
		else
		{
			return response()->json(['message' => trans('Missing or invalid bytes value')], 415);
		}

		// Look for this entry, duplicate name, etc.
		$exist = Directory::query()
			->where('resourceid', '=', $row->resourceid)
			->where('groupid', '=', $row->groupid)
			->where('parentstoragedirid', '=', $row->parentstoragedirid)
			->where('name', '=', $row->name)
			->where('datetimecreated', '<=', Carbon::now()->toDateTimeString())
			->where(function ($where)
			{
				$where->whereNull('datetimeremoved')
					->where('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get()
			->first();

		if ($exist)
		{
			return response()->json(['message' => trans('Duplicate entry found for :name', ['name' => $row->name])], 409);
		}

		// Make sure both resourceid and storageresourceid are set
		if ($row->resourceid && !$row->storageresourceid)
		{
			$sr = StorageResource::query()
				->where('parentresourceid', '=', $row->resourceid)
				->get()
				->first();

			$row->storageresourceid = $sr->id;
		}
		elseif (!$row->resourceid && $row->storageresourceid)
		{
			$row->resourceid = $row->storageResource->resourceid;
		}

		$row->save();

		if ($parent)
		{
			$parent->save();
		}

		// If we have are requesting an autopopulate dir, then let's populate with the current list of users
		if ($row->autouser > 0)
		{
			$members = $row->autounixgroup->members;

			foreach ($members as $member)
			{
				// Set up object to pass back to ourselfs
				$data = [
					'bytes'       => '-',
					//'bytesource'  => '',
					'groupid'     => $row->groupid,
					'name'        => $member->user->username,
					'parentstoragedirid' => $row->id,
					'resourceid'  => $row->resourceid,
					'unixgroupid' => $row->unixgroupid,
					'userid'      => $row->userid,
				];

				if ($row->autouser == 1)
				{
					// Group readable
					$data['groupread']  = 1;
					$data['groupwrite'] = 0;
					$data['otherread']  = 0;
				}
				elseif ($row->autouser == 2)
				{
					// Private
					$data['groupread']  = 0;
					$data['groupwrite'] = 0;
					$data['otherread']  = 0;
				}
				elseif ($row->autouser == 3)
				{
					// Group readable writable
					$data['groupread']  = 1;
					$data['groupwrite'] = 1;
					$data['otherread']  = 0;
				}

				$this->create($request, $data, 10);
			}
		}

		return new DirectoryResource($row);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/directories/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Directory::query()
			->withTrashed()
			->where('id', '=', $id)
			->limit(1)
			->get()
			->first();

		if (!$row)
		{
			return response()->json(null, 404);
		}

		return new DirectoryResource($row);
	}

	/**
	 * Update a resource
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/directories/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Directory name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource id",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group id",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "bytes",
	 * 		"description":   "Bytes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parentstorageid",
	 * 		"description":   "Parent directory ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "owneruserid",
	 * 		"description":   "Owner user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroupid",
	 * 		"description":   "Unix Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ownerread",
	 * 		"description":   "Owner read permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupread",
	 * 		"description":   "Group read permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupwrite",
	 * 		"description":   "Group write permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publicread",
	 * 		"description":   "Public read permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publicwrite",
	 * 		"description":   "Public write permission",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "autouser",
	 * 		"description":   "Auto user",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1,
	 * 				2,
	 * 				3
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "files",
	 * 		"description":   "Files",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "autouserunixgroupid",
	 * 		"description":   "Auto user Unix Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "storageresourceid",
	 * 		"description":   "Storage Resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$row = Directory::findOrFail($id);

		// Need to handle special case of handled by MQ
		if ($request->has('rmdir')
		 && $request->has('returnstatus'))
		{
			if ($request->input('rmdir') == 1
			 && $request->input('returnstatus') == 0)
			{
				$row->delete();

				return response()->json(null, 204);
			}
		}

		$request->validate([
			'name' => 'nullable|string|max:32',
			'path' => 'nullable|string|max:255',
			'resourceid' => 'nullable|integer|min:1',
			'groupid' => 'nullable|integer|min:1',
			'parentstorageid' => 'nullable|integer',
			'owneruserid' => 'nullable|integer',
			'unixgroupid' => 'nullable|integer',
			'ownerread' => 'nullable|integer',
			'groupread' => 'nullable|integer',
			'groupwrite' => 'nullable|integer',
			'publicread' => 'nullable|integer',
			'publicwrite' => 'nullable|integer',
			'autouser' => 'nullable|in:0,1,2,3',
			'files' => 'nullable|integer',
			'autouserunixgroupid' => 'nullable|integer',
			'storageresourceid' => 'nullable|integer',
		]);

		//$row->fill($request->all());
		$keys = array(
			'name',
			'path',
			'resourceid',
			'groupid',
			'parentstorageid',
			'owneruserid',
			'unixgroupid',
			'ownerread',
			'groupread',
			'groupwrite',
			'publicread',
			'publicwrite',
			'autouser',
			'files',
			'autouserunixgroupid',
			'storageresourceid',
		);
		foreach ($keys as $key)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}

		if ($request->has('quotaupdate'))
		{
			// Fetch message type
			$type = MessageType::query()
				->where('resourceid', '=', $row->resourceid)
				->where('name', 'like', 'get % quota')
				->get()
				->first();

			if (!$type)
			{
				return response()->json(['message' => trans('Failed to retrieve messagequeuetype for resourceid :id', ['id' => $row->resourceid])], 415);
			}

			// Form message queue
			$row->addMessageToQueue($type->id, $row->userid);

			return new DirectoryResource($row);
		}

		if ($request->input('fixpermissions'))
		{
			// Fetch message type
			$type = MessageType::query()
				->where('resourceid', '=', $row->resourceid)
				->where('name', 'like', 'fix %')
				->get()
				->first();

			if (!$type)
			{
				return response()->json(['message' => trans('Failed to retrieve messagequeuetype for resourceid :id', ['id' => $row->resourceid])], 415);
			}

			// Form message queue
			$row->addMessageToQueue($type->id, $row->userid);

			return new DirectoryResource($row);
		}

		$unallocatedbytes = 0;

		if ($request->has('bytes'))
		{
			// Find appropriate bucket
			$bucket = null;
			foreach ($row->group->storagebuckets as $b)
			{
				if ($b['resourceid'] == $row->resourceid)
				{
					$bucket = $b;
					break;
				}
			}

			$bytes = $request->input('bytes');

			if (preg_match_all("/^(\-?\d*\.?\d+)\s*(\w+)$/", $bytes, $matches))
			{
				if ($bucket == null)
				{
					return response()->json(['message' => trans('Empty bucket')], 415);
				}

				$row->bytes = $bytes;

				// Top level dirs are required to have a quota
				if ($row->bytes == 0 && !$row->parent)
				{
					return response()->json(['message' => trans('Top level dirs are required to have a quota')], 415);
				}

				// Can't switch between no quota and quota
				if (($row->getOriginal('bytes') == 0 && $row->bytes != 0)
				 || ($row->getOriginal('bytes') != 0 && $row->bytes == 0))
				{
					return response()->json(['message' => trans('Cannot switch between no quota and quota')], 415);
				}

				if ($row->bytes < 0)
				{
					return response()->json(['message' => trans('Cannot have a negative quota')], 415);
				}

				if ($row->bytes == 0)
				{
					return response()->json(['message' => trans('Cannot have zero bytes')], 415);
				}

				// Check to see if tried to allocate all remaining space but we missed a fwe bits because of rounding
				if (Number::formatBytes($row->bytes, 2) == Number::formatBytes($bucket['unallocatedbytes'] + $row->getOriginal('bytes'), 2)
				 && $row->bytes != $bucket['unallocatedbytes'] + $row->getOriginal('bytes'))
				{
					$row->bytes = $bucket['unallocatedbytes'] + $row->getOriginal('bytes');
				}
			}
			elseif ($bytes == 'ALL')
			{
				if ($bucket == null)
				{
					$bytes = $row->getOriginal('bytes');
				}
				else
				{
					$bytes = $bucket['unallocatedbytes'] + $row->getOriginal('bytes');
				}
				$row->bytes = $bytes;
			}
			else
			{
				return response()->json(['message' => trans('Missing or invalid bytes value')], 415);
			}

			if ($bucket == null)
			{
				$unallocatedbytes = Number::formatBytes(0, 2);
			}
			else
			{
				$unallocatedbytes = Number::formatBytes($bucket['unallocatedbytes'] + ($row->getOriginal('bytes') - $bytes), 2);
			}

			if ($unallocatedbytes < 0)
			{
				$row->unallocatedbytes = Number::formatBytes(-($bucket['unallocatedbytes'] + ($row->getOriginal('bytes') - $bytes)), 2);
				$row->overallocated    = 1;

				return new DirectoryResource($row);
			}

			// Send back new formatted number
			/*$dataobj->formatbytes = formatBytes($copyobj->bytes, true);

			if ($copyobj->bytes == NO_QUOTA)
			{
				$dataobj->formatbytes = "-";
			}*/
		}

		if ($row->autouserunixgroupid != $row->getOriginal('autouserunixgroupid'))
		{
			if ($row->autouser > 0)
			{
				// If we have an autopopulate dir, and are changing unix groups we may need to create new user directories. We aren't deleting directories though.
				$members = $row->autounixgroup->members;

				foreach ($members as $member)
				{
					// Check to see if we have dir already
					$exist = Directory::query()
						->where('parentstoragedirid', '=', $row->id)
						->where('name', '=', $member->user->username)
						->where(function ($where)
						{
							$where->whereNull('datetimeremoved')
								->where('datetimeremoved', '=', '0000-00-00 00:00:00');
						})
						->get()
						->first();

					if ($exist)
					{
						continue;
					}

					// Set up object to pass back to ourselfs
					$data = [
						'bytes'       => '-',
						//'bytesource'  => '',
						'groupid'     => $row->groupid,
						'name'        => $member->user->username,
						'parentstoragedirid'      => $row->id,
						'resourceid'  => $row->resourceid,
						'unixgroupid' => $row->unixgroupid,
						'userid'      => $row->userid,
					];

					if ($row->autouser == 1)
					{
						// Group readable
						$data['groupread']  = 1;
						$data['groupwrite'] = 0;
						$data['otherread']  = 0;
					}
					elseif ($row->autouser == 2)
					{
						// Private
						$data['groupread']  = 0;
						$data['groupwrite'] = 0;
						$data['otherread']  = 0;
					}
					elseif ($row->autouser == 3)
					{
						// Group readable writable
						$data['groupread']  = 1;
						$data['groupwrite'] = 1;
						$data['otherread']  = 0;
					}

					$this->create($request, $data);
				}
			}
		}

		$row->save();
		$row->unallocatedbytes = $unallocatedbytes;

		return new DirectoryResource($row);
	}

	/**
	 * Delete a storage directory
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/directories/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Directory::findOrFail($id);

		if ($row->children()->count() > 0)
		{
			return response()->json(['message' => trans('Directory is not empty')], 409);
		}

		if ($row->parent)
		{
			// Can we make the parent group writeable now?
			if ($row->parent->children()->count() == 0)
			{
				$row->parent->groupwrite = 1;
				$row->parent->save();
			}
		}

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
