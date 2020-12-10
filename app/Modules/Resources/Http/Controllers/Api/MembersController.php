<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Entities\Asset;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Users\Models\User;

/**
 * Members
 *
 * @apiUri    /api/resources/members
 */
class MembersController extends Controller
{
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
			'user' => 'required|integer',
			'resource' => 'required|integer',
			'primarygroup' => 'nullable|string',
			'loginshell' => 'nullable|string',
			'pilogin' => 'nullable|string',
			'piid' => 'nullable|string',
		]);

		$userid = $request->input('user');
		$resourceid = $request->input('resource');
		$loginshell = $request->input('loginshell');

		// Look up the current username of the user 
		$user = User::findOrFail($userid);

		if (!$user || $user->trashed())
		{
			return response()->json(['message' => trans('Failed to find user for ID :id', ['id' => $userid])], 404);
		}

		$asset = Asset::findOrFail($resourceid);

		if (!$asset || $asset->isTrashed())
		{
			return response()->json(['message' => trans('Failed to find resource for ID :id', ['id' => $resourceid])], 404);
		}

		// Ensure the client is authorized to manage a group with queues on the resource in question.
		if (!auth()->user()->can('manage resources')
		 && $user->id != auth()->user()->id)
		{
			$owned = auth()->user()->groups->pluck('id')->toArray();

			$queues = array();
			foreach ($resource->subresources as $sub)
			{
				$queues += $sub->queues()
					->whereIn('groupid', $owned)
					->pluck('queuid')
					->toArray();
			}
			array_filter($queues);

			// If no queues found
			if (count($queues) < 1) // && !in_array($resource->id, array(48, 2, 12, 66)))
			{
				return response()->json(null, 403);
			}
		}

		// Is the shell valid?
		if (!file_exists($loginshell) && $loginshell != 'nologin')
		{
			return response()->json(['message' => trans('Invalid loginshell')], 409);
		}

		// Look up the current username of the PI if ID was specified
		if ($piid = $request->input('piid'))
		{
			$pi = User::findOrFail($piid);

			$pilogin = $pi->username;
		}
		// Verify PI login is valid if that was specified
		elseif ($pilogin = $request->input('pilogin'))
		{
			$pi = User::findByUsername($pilogin);

			if (!$pi)
			{
				return response()->json(['message' => trans('Invalid pilogin')], 409);
			}
		}

		event($event = new ResourceMemberCreated($asset, $user));


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

		if (!$user || $user->isTrashed())
		{
			return response()->json(['message' => trans('Failed to find user for ID :id', ['id' => $userid])], 404);
		}

		$asset = Asset::findOrFail($resource);

		if (!$asset || $asset->isTrashed())
		{
			return response()->json(['message' => trans('Failed to find resource for ID :id', ['id' => $resource])], 404);
		}

		// Look up the ACMaint role name of the resource
		if (!$asset->rolename)
		{
			return response()->json(null, 404);
		}

		// Call central accounting service to request status
		event($event = new ResourceMemberStatus($asset, $user));

		$data = array(
			'resource' => array(
				'id' => $asset->id,
				'name' => $asset->name,
			),
			'status' => $event->status,
			'loginshell' => $event->user->loginshell,
			'primarygroup' => $event->user->primarygroup = 'student',
			'pilogin' => $event->user->pilogin,
		);

		return new JsonResource($data);
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

		$resourceid = $parts[0];
		$userid = $parts[1];

		if (!is_numeric($resourceid)
		 || !is_numeric($userid))
		{
			return response()->json(['message' => trans('Missing or invalid value. Must be of format `resourceid.userid`')], 415);
		}

		// Look up the current username of the user being removed
		$user = User::findOrFail($userid);

		// Look up the ACMaint role name of the resource to which access is being granted.
		$resource = Asset::findOrFail($resourceid);

		// Ensure the client is authorized to manage a group with queues on the resource in question.
		if (!auth()->user()->can('manage resources')
		 && $user->id != auth()->user()->id)
		{
			$owned = auth()->user()->groups->pluck('id')->toArray();

			$queues = array();
			foreach ($resource->subresources as $sub)
			{
				$queues += $sub->queues()
					->whereIn('groupid', $owned)
					->pluck('queuid')
					->toArray();
			}
			array_filter($queues);

			/*$sql = "SELECT queues.id FROM resources, resourcesubresources, queues WHERE resources.id = '" . $this->db->escape_string($resource) . "' AND queues.groupid " . $this->myownedgroupssql . " AND resources.id = resourcesubresources.resourceid AND resourcesubresources.subresourceid = queues.subresourceid AND queues.datetimeremoved = '0000-00-00 00:00:00'";
			$data = array();
			$rows = $this->db->query($sql, $data);*/

			// If no queues found
			if (count($queues) < 1) // && !in_array($resource->id, array(48, 2, 12, 66)))
			{
				return response()->json(null, 403);
			}
		}

		// Check for other queue memberships on this resource that might conflict with removing the role
		$sql = "SELECT queues.id, queues.groupid FROM resources, resourcesubresources, queues, queueusers
		WHERE resources.id = '" . $this->db->escape_string($resource) . "' AND resources.id = resourcesubresources.resourceid AND resourcesubresources.subresourceid = queues.subresourceid AND queueusers.queueid = queues.id AND queues.datetimeremoved = '0000-00-00 00:00:00' AND queueusers.datetimeremoved = '0000-00-00 00:00:00' AND resources.datetimeremoved = '0000-00-00 00:00:00' AND queueusers.membertype = '1' AND queueusers.userid = '" . $this->db->escape_string($user) . "'
		UNION
		SELECT groupusers.groupid AS id, groupusers.groupid FROM groupusers, queues, resourcesubresources WHERE groupusers.userid = '" . $this->db->escape_string($user) . "' and groupusers.membertype = '2' AND groupusers.groupid <> '0' and groupusers.dateremoved = '0000-00-00 00:00:00' AND groupusers.groupid = queues.groupid AND resourcesubresources.subresourceid = queues.subresourceid AND resourcesubresources.resourceid = '" . $this->db->escape_string($resource) . "' AND queues.datetimeremoved = '0000-00-00 00:00:00'";
		$data = array();
		$rows = $this->db->query($sql, $data);

		if ($rows > 0)
		{
			return 202;
		}

		// Call central accounting service to remove ACMaint role from this user's account.
		event(new ResourceMemberDeleted($resource, $user));

		return response()->json(null, 204);
	}
}
