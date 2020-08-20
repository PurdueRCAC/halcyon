<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Entities\Asset;
use App\Modules\Resources\Http\Resources\AssetResourceCollection;
use App\Modules\Resources\Http\Resources\AssetResource;
use App\Modules\Users\Models\User;

/**
 * Members
 *
 * @apiUri    /api/resources/members
 */
class MembersController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources/members
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to order results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 *      "default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to order results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'state'    => $request->input('state', 'active'),
			'type'     => $request->input('type', null),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'order'     => $request->input('order', 'name'),
			'order_dir' => $request->input('order_dir', 'asc')
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Asset::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['state'])
		{
			if ($filters['state'] == 'all')
			{
				$query->withTrashed();
				//$query->where('datetimeremoved', '=', '0000-00-00 00:00:00');
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->onlyTrashed();
				//$query->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
			}
		}

		if (is_numeric($filters['type']))
		{
			$query->where('resourcetype', '=', $filters['type']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new AssetResourceCollection($rows);
	}

	/**
	 * Create a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /api/resources/members
	 * @apiParameter {
	 *      "in":            "body",
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required'
		]);

		$asset = Asset::create($request->all());
		/*$asset = new Asset([
			'name'         => $request->input('name'),
			'parentid'     => $request->input('parentid'),
			'rolename'     => $request->input('rolename'),
			'listname'     => $request->input('listname'),
			'resourcetype' => $request->input('resourcetype'),
			'producttype'  => $request->input('producttype')
		]);

		$parent = $asset->parent();

		if (!$parent || !$parent->id)
		{
			abort(415, trans('Invalid parent ID'));
		}

		if (!$asset->save())
		{
			abort(415, $asset->getError());
		}*/

		event('onAfterSaveResource', $asset);

		return new AssetResource($asset);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources/members/{id}
	 * @apiParameter {
	 *      "in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @param   integer $id
	 * @return  Response
	 */
	public function read($id)
	{
		// Split id into parts
		$parts = explode('.', $id);

		$resource = $parts[0];
		$userid = $parts[1];

		if (!is_numeric($resource)
		 || !is_numeric($userid))
		{
			return response()->json(['message' => trans('Field resource or user is not numeric')], 415);
		}

		// Ensure the client is authorized to manage roles
		if (!auth()->user()->can('manage resources')
		 && $userid != auth()->user()->id)
		{
			return response()->json(null, 403);
		}

		// Look up the current username of the user 
		$user = User::findOrFail($userid);

		if (!$user || $user->trashed())
		{
			return response()->json(['message' => trans('Failed to find username for user', ['id' => $userid])], 400);
		}

		$asset = Asset::findOrFail($id);

		return new AssetResource($asset);
	}

	/**
	 * Update a resource
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/resources/members/{id}
	 * @apiParameter {
	 *      "in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 *      "in":            "body",
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function update(Asset $asset, Request $request)
	{
		$request->validate([
			'name' => 'required|max:32',
			'datetimecreated' => 'nullable|date'
		]);

		$asset->update($request->all());

		/*$asset = Asset::findOrFail($id);
		$asset->set([
			'name'         => $request->get('name'),
			'parentid'     => $request->get('parentid'),
			'rolename'     => $request->get('rolename'),
			'listname'     => $request->get('listname'),
			'resourcetype' => $request->get('resourcetype'),
			'producttype'  => $request->get('producttype')
		]);

		$asset->save();*/

		//event(new ResourceUpdated($asset));
		event('onAfterResourceSave', $asset);

		return new AssetResource($asset);
	}

	/**
	 * Delete a resource
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/resources/members/{id}
	 * @apiParameter {
	 *      "in":            "query",
	 *      "name":          "id",
	 *      "description":   "Resource ID and user ID separated by a period. Example: 1234.5678",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @param  string $id
	 * @return Response
	 */
	public function delete($id)
	{
		$parts = explode('.', $id);

		if (count($parts) != 2)
		{
			return response()->json(['message' => trans('Missing or invalid value. Must be of format `resourceid.userid`')], 412);
		}

		$resource = $parts[0];
		$user = $parts[1];

		if (!is_numeric($resource)
		 || !is_numeric($user))
		{
			return response()->json(['message' => trans('Missing or invalid value. Must be of format `resourceid.userid`')], 415);
		}

		// Ensure the client is authorized to manage a group with queues on the resource in question.
		if (!auth()->user()->can('manage resources')
		 && $user != auth()->user()->id)
		{
			$sql = "SELECT queues.id FROM resources, resourcesubresources, queues WHERE resources.id = '" . $this->db->escape_string($resource) . "' AND queues.groupid " . $this->myownedgroupssql . " AND resources.id = resourcesubresources.resourceid AND resourcesubresources.subresourceid = queues.subresourceid AND queues.datetimeremoved = '0000-00-00 00:00:00'";
			$data = array();
			$rows = $this->db->query($sql, $data);

			if ($rows < 1 && !in_array($resource, array('48', '2', '12', '66')))
			{
				return response()->json(null, 403);
			}
		}

		// Check for other queue memberships on this resource that might conflict with removing the role
		$sql = "SELECT queues.id, queues.groupid FROM resources, resourcesubresources, queues, queueusers WHERE resources.id = '" . $this->db->escape_string($resource) . "' AND resources.id = resourcesubresources.resourceid AND resourcesubresources.subresourceid = queues.subresourceid AND queueusers.queueid = queues.id AND queues.datetimeremoved = '0000-00-00 00:00:00' AND queueusers.datetimeremoved = '0000-00-00 00:00:00' AND resources.datetimeremoved = '0000-00-00 00:00:00' AND queueusers.membertype = '1' AND queueusers.userid = '" . $this->db->escape_string($user) . "' UNION SELECT groupusers.groupid AS id, groupusers.groupid FROM groupusers, queues, resourcesubresources WHERE groupusers.userid = '" . $this->db->escape_string($user) . "' and groupusers.membertype = '2' AND groupusers.groupid <> '0' and groupusers.dateremoved = '0000-00-00 00:00:00' AND groupusers.groupid = queues.groupid AND resourcesubresources.subresourceid = queues.subresourceid AND resourcesubresources.resourceid = '" . $this->db->escape_string($resource) . "' AND queues.datetimeremoved = '0000-00-00 00:00:00'";
		$data = array();
		$rows = $this->db->query($sql, $data);

		if ($rows > 0)
		{
			return 202;
		}

		// Look up the current username of the user being removed
		$user = User::findOrFail($user);

		// Look up the ACMaint role name of the resource to which access is being granted.
		$resource = Asset::findOrFail($resource);

		// Call central accounting service to remove ACMaint role from this user's account.
		event(new ResourceMemberDeleted($resource, $user));

		return response()->json(null, 204);
	}
}
