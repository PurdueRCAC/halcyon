<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Resources\Events\ResourceMemberList;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\GroupUser as GroupQueueUser;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member as GroupUser;
use App\Modules\Groups\Models\Type;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Storage\Models\Directory;
use Carbon\Carbon;

/**
 * Members
 *
 * @apiUri    /resources/members
 */
class MembersController extends Controller
{
	/**
	 * Retrieve list of active users for a resource
	 *
	 * @apiMethod GET
	 * @apiUri    /resources/members/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Resource ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"example":   "99"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "membertype",
	 * 		"description":   "Filter by group member type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null,
	 * 			"enum": [
	 * 				"2",
	 * 				"manager"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "datecreated",
	 * 		"description":   "Filter for records created after the specified date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null,
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   int $id
	 * @return  array<int,array{string,mixed}>
	 */
	public function index(Request $request, $id)
	{
		$filters = array(
			'membertype' => $request->input('membertype'),
			'datecreated' => $request->input('datecreated'),
		);
		if ($filters['membertype'] == 'manager' || $filters['membertype'] == 'managers')
		{
			$filters['membertype'] = Type::MANAGER;
		}
		$filters['membertype'] = $filters['membertype'] == Type::MANAGER ?: 0;

		if ($filters['datecreated'])
		{
			$filters['datecreated'] = Carbon::parse($filters['datecreated'])->toDateTimeString();
		}


		$resource = Asset::findOrFail($id);

		$r = (new Asset)->getTable();
		$s = (new Subresource)->getTable();
		$c = (new Child)->getTable();

		$q = (new Queue)->getTable();
		$qu = (new QueueUser)->getTable();

		$g = (new Group)->getTable();
		$gu = (new GroupUser)->getTable();
		$gqu = (new GroupQueueUser)->getTable();
		$u = (new UnixGroup)->getTable();
		$ugm = (new UnixGroupMember)->getTable();

		$d = (new Directory)->getTable();

		$us = (new User)->getTable();
		$uu = (new UserUsername)->getTable();

		$now = Carbon::now();

		/*
		SELECT DISTINCT username FROM (
				SELECT DISTINCT userusernames.username AS username
				FROM groupusers
				INNER JOIN groups ON groupusers.groupid = groups.id
				INNER JOIN queues ON groups.id = queues.groupid
				INNER JOIN resourcesubresources ON queues.subresourceid = resourcesubresources.subresourceid
				INNER JOIN resources ON resourcesubresources.resourceid = resources.id
				INNER JOIN userusernames ON groupusers.userid = userusernames.userid
				LEFT OUTER JOIN unixgroups ON groups.id = unixgroups.groupid
				LEFT OUTER JOIN unixgroupusers ON unixgroups.id = unixgroupusers.unixgroupid
					AND unixgroupusers.userid = userusernames.userid
					AND unixgroupusers.datetimecreated <= NOW()
					AND (unixgroupusers.datetimeremoved IS NULL OR unixgroupusers.datetimeremoved = '0000-00-00 00:00:00' OR unixgroupusers.datetimeremoved > NOW())
					AND unixgroups.datetimecreated <= NOW()
					AND (unixgroups.datetimeremoved IS NULL OR unixgroups.datetimeremoved = '0000-00-00 00:00:00' OR unixgroups.datetimeremoved > NOW())
				WHERE groupusers.membertype = '2'
				AND groupusers.datecreated <= NOW()
				AND (groupusers.dateremoved IS NULL OR groupusers.dateremoved = '0000-00-00 00:00:00' OR groupusers.dateremoved > NOW())
				AND queues.datetimecreated <= NOW()
				AND (queues.datetimeremoved IS NULL OR queues.datetimeremoved = '0000-00-00 00:00:00' OR queues.datetimeremoved > NOW())
				AND resources.datetimecreated <= NOW()
				AND (resources.datetimeremoved IS NULL OR resources.datetimeremoved = '0000-00-00 00:00:00' OR resources.datetimeremoved > NOW())
				AND userusernames.datecreated <= NOW()
				AND (userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00' OR userusernames.dateremoved > NOW())
				AND resources.resourcetype = '1'
				AND (resources.id IN ('" + resource + "') OR resources.parentid IN ('" + resource + "'))
			UNION ALL
				SELECT DISTINCT userusernames.username AS username
				FROM queueusers
				INNER JOIN queues ON queueusers.queueid = queues.id
				INNER JOIN resourcesubresources ON queues.subresourceid = resourcesubresources.subresourceid
				INNER JOIN resources ON resourcesubresources.resourceid = resources.id
				INNER JOIN userusernames ON queueusers.userid = userusernames.userid
				INNER JOIN groups ON queues.groupid = groups.id
				LEFT OUTER JOIN unixgroups ON groups.id = unixgroups.groupid
				LEFT OUTER JOIN unixgroupusers ON unixgroups.id = unixgroupusers.unixgroupid
				AND unixgroupusers.userid = userusernames.userid
				WHERE queueusers.membertype = '1'
				AND queueusers.datetimecreated <= NOW()
				AND (queueusers.datetimeremoved IS NULL OR queueusers.datetimeremoved = '0000-00-00 00:00:00' OR queueusers.datetimeremoved > NOW())
				AND queues.datetimecreated <= NOW()
				AND (queues.datetimeremoved IS NULL OR queues.datetimeremoved = '0000-00-00 00:00:00' OR queues.datetimeremoved > NOW())
				AND resources.datetimecreated <= NOW()
				AND (resources.datetimeremoved IS NULL OR resources.datetimeremoved = '0000-00-00 00:00:00' OR resources.datetimeremoved > NOW())
				AND userusernames.datecreated <= NOW()
				AND (userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00' OR userusernames.dateremoved > NOW())
				AND resources.resourcetype = '1' AND (resources.id IN ('" + resource + "') OR resources.parentid IN ('" + resource + "'))
			UNION ALL
				SELECT DISTINCT userusernames.username AS username
				FROM groupqueueusers
				INNER JOIN queueusers ON groupqueueusers.queueuserid = queueusers.id
				INNER JOIN queues ON queueusers.queueid = queues.id
				INNER JOIN resourcesubresources ON queues.subresourceid = resourcesubresources.subresourceid
				INNER JOIN resources ON resourcesubresources.resourceid = resources.id
				INNER JOIN userusernames ON queueusers.userid = userusernames.userid
				INNER JOIN groups ON groupqueueusers.groupid = groups.id
				LEFT OUTER JOIN unixgroups ON groups.id = unixgroups.groupid
				LEFT OUTER JOIN unixgroupusers ON unixgroups.id = unixgroupusers.unixgroupid
				AND unixgroupusers.userid = userusernames.userid
				WHERE groupqueueusers.membertype = '1'
				AND queueusers.membertype = '1'
				AND groupqueueusers.datetimecreated <= NOW()
				AND (groupqueueusers.datetimeremoved IS NULL OR groupqueueusers.datetimeremoved = '0000-00-00 00:00:00' OR groupqueueusers.datetimeremoved > NOW())
				AND queueusers.datetimecreated <= NOW()
				AND (queueusers.datetimeremoved IS NULL OR queueusers.datetimeremoved = '0000-00-00 00:00:00' OR queueusers.datetimeremoved > NOW())
				AND queues.datetimecreated <= NOW()
				AND (queues.datetimeremoved IS NULL OR queues.datetimeremoved = '0000-00-00 00:00:00' OR queues.datetimeremoved > NOW())
				AND resources.datetimecreated <= NOW()
				AND (resources.datetimeremoved IS NULL OR resources.datetimeremoved = '0000-00-00 00:00:00' OR resources.datetimeremoved > NOW())
				AND userusernames.datecreated <= NOW()
				AND (userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00' OR userusernames.dateremoved > NOW())
				AND resources.resourcetype = '1' AND (resources.id IN ('" + resource + "') OR resources.parentid IN ('" + resource + "'))
			UNION ALL
				SELECT DISTINCT userusernames.username
				FROM unixgroupusers
				INNER JOIN unixgroups ON unixgroupusers.unixgroupid = unixgroups.id
				INNER JOIN storagedirs ON unixgroups.groupid = storagedirs.groupid
				INNER JOIN resources ON storagedirs.resourceid = resources.id
				INNER JOIN userusernames ON unixgroupusers.userid = userusernames.userid
				INNER JOIN groups ON unixgroups.groupid = groups.id
				LEFT OUTER JOIN groupusers ON groups.id = groupusers.groupid
				AND groupusers.userid = userusernames.userid
				AND groupusers.datecreated <= NOW()
				AND (groupusers.dateremoved IS NULL OR groupusers.dateremoved = '0000-00-00 00:00:00' OR groupusers.dateremoved > NOW())
				WHERE unixgroupusers.datetimecreated <= NOW()
				AND (unixgroupusers.datetimeremoved IS NULL OR unixgroupusers.datetimeremoved = '0000-00-00 00:00:00' OR unixgroupusers.datetimeremoved > NOW())
				AND unixgroups.datetimecreated <= NOW()
				AND (unixgroups.datetimeremoved IS NULL OR unixgroups.datetimeremoved = '0000-00-00 00:00:00' OR unixgroups.datetimeremoved > NOW())
				AND storagedirs.datetimecreated <= NOW()
				AND (storagedirs.datetimeremoved IS NULL OR storagedirs.datetimeremoved = '0000-00-00 00:00:00' OR storagedirs.datetimeremoved > NOW())
				AND userusernames.datecreated <= NOW()
				AND (userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00' OR userusernames.dateremoved > NOW())
				AND resources.resourcetype = '2'
				AND resources.id IN ('" + re.escape(resource) + "')
		) AS allusers
		ORDER BY username;
		
		SELECT DISTINCT username, userid
		FROM (
			SELECT DISTINCT userusernames.username AS username, userusernames.userid
			FROM groupusers
			INNER JOIN groups ON groupusers.groupid = groups.id
			INNER JOIN queues ON groups.id = queues.groupid
			INNER JOIN resourcesubresources ON queues.subresourceid = resourcesubresources.subresourceid
			INNER JOIN resources ON resourcesubresources.resourceid = resources.id
			INNER JOIN userusernames ON groupusers.userid = userusernames.userid
			LEFT OUTER JOIN unixgroups ON groups.id = unixgroups.groupid
			LEFT OUTER JOIN unixgroupusers ON unixgroups.id = unixgroupusers.unixgroupid
				AND unixgroupusers.userid = userusernames.userid
				AND unixgroupusers.datetimecreated <= NOW()
				AND (unixgroupusers.datetimeremoved IS NULL OR unixgroupusers.datetimeremoved = '0000-00-00 00:00:00' OR unixgroupusers.datetimeremoved > NOW())
				AND unixgroups.datetimecreated <= NOW()
				AND (unixgroups.datetimeremoved IS NULL OR unixgroups.datetimeremoved = '0000-00-00 00:00:00' OR unixgroups.datetimeremoved > NOW())
			WHERE groupusers.membertype = '2'
			AND groupusers.datecreated <= NOW()
			AND (groupusers.dateremoved IS NULL OR groupusers.dateremoved = '0000-00-00 00:00:00' OR groupusers.dateremoved > NOW())
			AND queues.datetimecreated <= NOW()
			AND (queues.datetimeremoved IS NULL OR queues.datetimeremoved = '0000-00-00 00:00:00' OR queues.datetimeremoved > NOW())
			AND resources.datetimecreated <= NOW()
			AND (resources.datetimeremoved IS NULL OR resources.datetimeremoved = '0000-00-00 00:00:00' OR resources.datetimeremoved > NOW())
			AND userusernames.datecreated <= NOW()
			AND (userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00' OR userusernames.dateremoved > NOW())
			AND resources.resourcetype = '1'
			AND resources.id IN ('" + resource + "')
		UNION ALL
			SELECT DISTINCT userusernames.username AS username, userusernames.userid
			FROM queueusers
			INNER JOIN queues ON queueusers.queueid = queues.id
			INNER JOIN resourcesubresources ON queues.subresourceid = resourcesubresources.subresourceid
			INNER JOIN resources ON resourcesubresources.resourceid = resources.id
			INNER JOIN userusernames ON queueusers.userid = userusernames.userid
			INNER JOIN groups ON queues.groupid = groups.id
			LEFT OUTER JOIN unixgroups ON groups.id = unixgroups.groupid
			LEFT OUTER JOIN unixgroupusers ON unixgroups.id = unixgroupusers.unixgroupid
				AND unixgroupusers.userid = userusernames.userid
			WHERE queueusers.membertype = '1'
			AND queueusers.datetimecreated <= NOW()
			AND (queueusers.datetimeremoved IS NULL OR queueusers.datetimeremoved = '0000-00-00 00:00:00' OR queueusers.datetimeremoved > NOW())
			AND queues.datetimecreated <= NOW()
			AND (queues.datetimeremoved IS NULL OR queues.datetimeremoved = '0000-00-00 00:00:00' OR queues.datetimeremoved > NOW())
			AND resources.datetimecreated <= NOW()
			AND (resources.datetimeremoved IS NULL OR resources.datetimeremoved = '0000-00-00 00:00:00' OR resources.datetimeremoved > NOW())
			AND userusernames.datecreated <= NOW()
			AND (userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00' OR userusernames.dateremoved > NOW())
			AND resources.resourcetype = '1'
			AND resources.id IN ('" + resource + "')
		UNION ALL
			SELECT DISTINCT userusernames.username AS username, userusernames.userid
			FROM groupqueueusers
			INNER JOIN queueusers ON groupqueueusers.queueuserid = queueusers.id
			INNER JOIN queues ON queueusers.queueid = queues.id
			INNER JOIN resourcesubresources ON queues.subresourceid = resourcesubresources.subresourceid
			INNER JOIN resources ON resourcesubresources.resourceid = resources.id
			INNER JOIN userusernames ON queueusers.userid = userusernames.userid
			INNER JOIN groups ON groupqueueusers.groupid = groups.id
			LEFT OUTER JOIN unixgroups ON groups.id = unixgroups.groupid
			LEFT OUTER JOIN unixgroupusers ON unixgroups.id = unixgroupusers.unixgroupid
				AND unixgroupusers.userid = userusernames.userid
			WHERE groupqueueusers.membertype = '1'
			AND queueusers.membertype = '1'
			AND groupqueueusers.datetimecreated <= NOW()
			AND (groupqueueusers.datetimeremoved IS NULL OR groupqueueusers.datetimeremoved = '0000-00-00 00:00:00' OR groupqueueusers.datetimeremoved > NOW())
			AND queueusers.datetimecreated <= NOW()
			AND (queueusers.datetimeremoved IS NULL OR queueusers.datetimeremoved = '0000-00-00 00:00:00' OR queueusers.datetimeremoved > NOW())
			AND queues.datetimecreated <= NOW()
			AND (queues.datetimeremoved IS NULL OR queues.datetimeremoved = '0000-00-00 00:00:00' OR queues.datetimeremoved > NOW())
			AND resources.datetimecreated <= NOW()
			AND (resources.datetimeremoved IS NULL OR resources.datetimeremoved = '0000-00-00 00:00:00' OR resources.datetimeremoved > NOW())
			AND userusernames.datecreated <= NOW()
			AND (userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00' OR userusernames.dateremoved > NOW())
			AND resources.resourcetype = '1'
			AND resources.id IN ('" + resource + "')
		UNION ALL
			SELECT DISTINCT userusernames.username, userusernames.userid
			FROM unixgroupusers
			INNER JOIN unixgroups ON unixgroupusers.unixgroupid = unixgroups.id
			INNER JOIN storagedirs ON unixgroups.groupid = storagedirs.groupid
			INNER JOIN resources ON storagedirs.resourceid = resources.id
			INNER JOIN userusernames ON unixgroupusers.userid = userusernames.userid
			INNER JOIN groups ON unixgroups.groupid = groups.id
			LEFT OUTER JOIN groupusers ON groups.id = groupusers.groupid
				AND groupusers.userid = userusernames.userid
				AND groupusers.datecreated <= NOW()
				AND (groupusers.dateremoved IS NULL OR groupusers.dateremoved = '0000-00-00 00:00:00' OR groupusers.dateremoved > NOW())
			WHERE unixgroupusers.datetimecreated <= NOW()
			AND (unixgroupusers.datetimeremoved IS NULL OR unixgroupusers.datetimeremoved = '0000-00-00 00:00:00' OR unixgroupusers.datetimeremoved > NOW())
			AND unixgroups.datetimecreated <= NOW()
			AND (unixgroups.datetimeremoved IS NULL OR unixgroups.datetimeremoved = '0000-00-00 00:00:00' OR unixgroups.datetimeremoved > NOW())
			AND storagedirs.datetimecreated <= NOW()
			AND (storagedirs.datetimeremoved IS NULL OR storagedirs.datetimeremoved = '0000-00-00 00:00:00' OR storagedirs.datetimeremoved > NOW())
			AND userusernames.datecreated <= NOW()
			AND (userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00' OR userusernames.dateremoved > NOW())
			AND resources.resourcetype = '2'
			AND resources.id IN ('" + resource + "')
		) AS allusers
		ORDER BY username;"
		*/

		$users = array();

		if (!$filters['membertype'] || $filters['membertype'] == Type::MANAGER)
		{
			$query = GroupUser::query()
				->select(
					$uu . '.userid',
					$uu . '.username',
					$uu . '.email',
					$us . '.name',
					$q . '.id as queueid',
					$q . '.name AS queue',
					$g . '.name AS group'
				)
				// Group
				->join($g, $g . '.id', $gu . '.groupid')
				->where($gu . '.membertype', '=', Type::MANAGER);
				//->where($gu . '.datecreated', '<=', $now->toDateTimeString())
			if ($filters['datecreated'])
			{
				$query->where($gu . '.datecreated', '>', $filters['datecreated']);
			}

			$gus = $query
				// Queues
				->join($q, $q . '.groupid', $g . '.id')
				->where($q . '.datetimecreated', '<=', $now->toDateTimeString())
				->whereNull($q . '.datetimeremoved')
				// Userusername
				->join($uu, $uu . '.userid', $gu . '.userid')
				->where($uu . '.datecreated', '<=', $now->toDateTimeString())
				->whereNull($uu . '.dateremoved')
				->join($us, $us . '.id', $uu . '.userid')
				// Resource/subresource
				->join($c, $c . '.subresourceid', $q . '.subresourceid')
				// Resource
				->join($r, $r . '.id', $c . '.resourceid')
				->where($r . '.datetimecreated', '<=', $now->toDateTimeString())
				->whereNull($r . '.datetimeremoved')
				->where($r . '.id', '=', $resource->id)
				->leftJoin($u, $u . '.groupid', $g . '.id')
				// Unix Group users
				/*->leftJoin($ugm, function($join) use ($u, $ugm, $uu)
				{
					$join->on($ugm . '.unixgroupid', $u . '.id')
						->on($ugm . '.userid', $uu . '.userid');
				})*/
				->groupBy($q . '.id')
				->groupBy($uu . '.userid')
				->groupBy($uu . '.username')
				->groupBy($uu . '.email')
				->groupBy($us . '.name')
				->groupBy($q . '.name')
				->groupBy($g . '.name')
				->get();

			foreach ($gus as $i => $gur)
			{
				if (!isset($users[$gur->userid]))
				{
					$users[$gur->userid] = array(
						'name'     => $gur->name,
						'username' => $gur->username,
						'email'    => $gur->email
					);
				}

				if (!isset($users[$gur->userid]['queues']))
				{
					$users[$gur->userid]['queues'] = array();
				}

				if (isset($users[$gur->userid]['queues'][$gur->queueid]))
				{
					continue;
				}

				$users[$gur->userid]['queues'][$gur->queueid] = [
					'id'   => $gur->queueid,
					'name' => $gur->queue . ' (' . $gur->group . ')',
				];

				unset($gus[$i]);
			}
			unset($gus);
		}

		if (!$filters['membertype'])
		{
			$query = QueueUser::query()
				->select(
					$uu . '.userid',
					$uu . '.username',
					$uu . '.email',
					$us . '.name',
					$qu . '.queueid',
					$q . '.name AS queue',
					$g . '.name AS group'
				)
				// Queues
				->join($q, $q . '.id', $qu . '.queueid')
				->whereNull($q . '.datetimeremoved')
				->where($qu . '.membertype', '=', 1); // We don't want pending members (4)
				//->where($q . '.datetimecreated', '<=', $now->toDateTimeString())
			if ($filters['datecreated'])
			{
				$query->where($qu . '.datetimecreated', '>', $filters['datecreated']);
			}

			$qus = $query
				// Userusername
				->join($uu, $uu . '.userid', $qu . '.userid')
				->where($uu . '.datecreated', '<=', $now->toDateTimeString())
				->whereNull($uu . '.dateremoved')
				->join($us, $us . '.id', $uu . '.userid')
				// Resource/subresource
				->join($c, $c . '.subresourceid', $q . '.subresourceid')
				// Resource
				->join($r, $r . '.id', $c . '.resourceid')
				->where($r . '.datetimecreated', '<=', $now->toDateTimeString())
				->whereNull($r . '.datetimeremoved')
				->where($r . '.id', '=', $resource->id)
				// Group
				->join($g, $g . '.id', $q . '.groupid')
				->leftJoin($u, $u . '.groupid', $g . '.id')
				// Unix Group users
				/*->leftJoin($ugm, function($join) use ($u, $ugm, $uu)
				{
					$join->on($ugm . '.unixgroupid', $u . '.id')
						->on($ugm . '.userid', $uu . '.userid');
				})*/
				->groupBy($qu . '.queueid')
				->groupBy($uu . '.userid')
				->groupBy($uu . '.username')
				->groupBy($uu . '.email')
				->groupBy($us . '.name')
				->groupBy($q . '.name')
				->groupBy($g . '.name')
				->get();

			foreach ($qus as $i => $qur)
			{
				if (!isset($users[$qur->userid]))
				{
					$users[$qur->userid] = array(
						'name'     => $qur->name,
						'username' => $qur->username,
						'email'    => $qur->email
					);
				}

				if (!isset($users[$qur->userid]['queues']))
				{
					$users[$qur->userid]['queues'] = array();
				}

				if (isset($users[$qur->userid]['queues'][$qur->queueid]))
				{
					continue;
				}

				$users[$qur->userid]['queues'][$qur->queueid] = [
					'id'   => $qur->queueid,
					'name' => $qur->queue . ' (' . $qur->group . ')',
				];

				unset($qus[$i]);
			}
			unset($qus);

			$query = GroupQueueUser::query()
				->select(
					$uu . '.userid',
					$uu . '.username',
					$uu . '.email',
					$us . '.name',
					$qu . '.queueid',
					$q . '.name AS queue',
					$g . '.name AS group'
				)
				// Queue user
				->join($qu, $qu . '.id', $gqu . '.queueuserid');
				//->where($gqu . '.datetimecreated', '<=', $now->toDateTimeString())
				//->where($qu . '.datetimecreated', '<=', $now->toDateTimeString())
			if ($filters['datecreated'])
			{
				$query->where($gqu . '.datetimecreated', '>', $filters['datecreated']);
				$query->where($qu . '.datetimecreated', '>', $filters['datecreated']);
			}

			$gqus = $query
				->whereNull($qu . '.datetimeremoved')
				->where($gqu . '.membertype', '=', 1)
				->where($qu . '.membertype', '=', 1)
				// Queues
				->join($q, $q . '.id', $qu . '.queueid')
				->where($q . '.datetimecreated', '<=', $now->toDateTimeString())
				->whereNull($q . '.datetimeremoved')
				// Userusername
				->join($uu, $uu . '.userid', $qu . '.userid')
				->where($uu . '.datecreated', '<=', $now->toDateTimeString())
				->whereNull($uu . '.dateremoved')
				->join($us, $us . '.id', $uu . '.userid')
				// Resource/subresource
				->join($c, $c . '.subresourceid', $q . '.subresourceid')
				// Resource
				->join($r, $r . '.id', $c . '.resourceid')
				->where($r . '.datetimecreated', '<=', $now->toDateTimeString())
				->whereNull($r . '.datetimeremoved')
				->where($r . '.id', '=', $resource->id)
				// Group
				->join($g, $g . '.id', $gqu . '.groupid')
				->leftJoin($u, $u . '.groupid', $g . '.id')
				// Unix Group users
				/*->leftJoin($ugm, function($join) use ($u, $ugm, $uu)
				{
					$join->on($ugm . '.unixgroupid', $u . '.id')
						->on($ugm . '.userid', $uu . '.userid');
				})*/
				->groupBy($qu . '.queueid')
				->groupBy($uu . '.userid')
				->groupBy($uu . '.username')
				->groupBy($uu . '.email')
				->groupBy($us . '.name')
				->groupBy($q . '.name')
				->groupBy($g . '.name')
				->get();

			foreach ($gqus as $i => $gqur)
			{
				if (!isset($users[$gqur->userid]))
				{
					$users[$gqur->userid] = array(
						'name'     => $gqur->name,
						'username' => $gqur->username,
						'email'    => $gqur->email
					);
				}

				if (!isset($users[$gqur->userid]['queues']))
				{
					$users[$gqur->userid]['queues'] = array();
				}

				if (isset($users[$gqur->userid]['queues'][$gqur->queueid]))
				{
					continue;
				}

				$users[$gqur->userid]['queues'][$gqur->queueid] = [
					'id'   => $gqur->queueid,
					'name' => $gqur->queue . ' (' . $gqur->group . ')',
				];

				unset($gqus[$i]);
			}
			unset($gqus);
		}

		$query = UnixGroupMember::query()
			->select(
				$uu . '.userid',
				$uu . '.username',
				$uu . '.email',
				$us . '.name',
				//$d . '.id as dirid',
				//$d . '.name as dir',
				//$g . '.name AS group'
			);
			// Unix group member
			//->where($ugm . '.datetimecreated', '<=', $now->toDateTimeString())
		if ($filters['datecreated'])
		{
			$query->where($ugm . '.datetimecreated', '>', $filters['datecreated']);
		}

		$query
			// Unix group
			->join($u, $u . '.id', $ugm . '.unixgroupid')
			->where($u . '.datetimecreated', '<=', $now->toDateTimeString())
			->whereNull($u . '.datetimeremoved')
			// Directory
			->join($d, $d . '.groupid', $u . '.groupid')
			->where($d . '.datetimecreated', '<=', $now->toDateTimeString())
			->whereNull($d . '.datetimeremoved')
			// Userusername
			->join($uu, $uu . '.userid', $ugm . '.userid')
			->where($uu . '.datecreated', '<=', $now->toDateTimeString())
			->whereNull($uu . '.dateremoved')
			->join($us, $us . '.id', $uu . '.userid')
			// Resource
			->join($r, $r . '.id', $d . '.resourceid')
			->where($r . '.datetimecreated', '<=', $now->toDateTimeString())
			->whereNull($r . '.datetimeremoved')
			->where($r . '.id', '=', $resource->id)
			// Group
			->join($g, $g . '.id', $u . '.groupid');

		if ($filters['membertype'] == Type::MANAGER)
		{
			// Group users
			$query->join($gu, function($join) use ($g, $gu, $ugm)
			{
				$join->on($gu . '.groupid', $g . '.id')
					->on($gu . '.userid', $ugm . '.userid');
			})
			->where($gu . '.membertype', '=', Type::MANAGER)
			->whereNull($gu . '.dateremoved');
		}
		else
		{
			// Group users
			$query->leftJoin($gu, function($join) use ($g, $gu, $uu)
			{
				$join->on($gu . '.groupid', $g . '.id')
					->on($gu . '.userid', $uu . '.userid');
			});
		}

		$ugus = $query
			->groupBy($uu . '.userid')
			->groupBy($uu . '.username')
			->groupBy($uu . '.email')
			->groupBy($us . '.name')
			//->groupBy($d . '.id')
			//->groupBy($d . '.name')
			//->groupBy($g . '.name')
			->get();

		foreach ($ugus as $i => $uqur)
		{
			if (!isset($users[$uqur->userid]))
			{
				$users[$uqur->userid] = array(
					'name'     => $uqur->name,
					'username' => $uqur->username,
					'email'    => $uqur->email
				);
			}

			/*if (!isset($users[$uqur->userid]['directories']))
			{
				$users[$uqur->userid]['directories'] = array();
			}

			if (isset($users[$uqur->userid]['directories'][$uqur->dirid]))
			{
				continue;
			}

			$users[$uqur->userid]['directories'][$uqur->dirid] = [
				'id'   => $uqur->dirid,
				'name' => $uqur->dir . ' (' . $uqur->group . ')',
			];*/

			unset($ugus[$i]);
		}
		unset($ugus);

		if (!count($users))
		{
			event($e = new ResourceMemberList($resource));
			$users = $e->results;
		}

		$data = array();
		foreach ($users as $userid => $datum)
		{
			if (!isset($datum['name']))
			{
				$user = User::find($userid);

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				$datum['name'] = $user->name;
				$datum['username'] = $user->username;
				$datum['email'] = $user->email;
			}

			$info = array();
			$info['id'] = $userid;
			$info['name'] = $datum['name'];
			$info['username'] = $datum['username'];
			$info['email'] = $datum['email'];
			$info['api'] = route('api.users.read', ['id' => $userid]);
			if (isset($datum['queues']))
			{
				$info['queues'] = array_values($datum['queues']);
			}
			if (isset($datum['directories']))
			{
				$info['directories'] = array_values($datum['directories']);
			}

			unset($users[$userid]);

			if (isset($user))
			{
				unset($user);
			}

			$data[] = $info;
		}

		return $data;
	}

	/**
	 * Create a resource member association
	 *
	 * @apiMethod POST
	 * @apiUri    /resources/members
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "user",
	 * 		"description":   "User ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resource",
	 * 		"description":   "Resource ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "loginShell",
	 * 		"description":   "Login shell",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "pilogin",
	 * 		"description":   "PI's username",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "piid",
	 * 		"description":   "PI's user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResource|JsonResponse
	 */
	public function create(Request $request)
	{
		$request->validate([
			'user' => 'required|integer',
			'resource' => 'required|integer',
			'primarygroup' => 'nullable|string',
			'loginshell' => 'nullable|string',
			'pilogin' => 'nullable|string',
			'piid' => 'nullable|integer',
		]);

		$userid = $request->input('user');
		$resourceid = $request->input('resource');
		$loginShell = $request->input('loginshell');
		$primarygroup = $request->input('primarygroup');

		// Look up the current username of the user
		$user = User::findOrFail($userid);

		if (!$user || $user->trashed())
		{
			return response()->json(['message' => trans('Failed to find user for ID :id', ['id' => $userid])], 404);
		}

		$asset = Asset::findOrFail($resourceid);

		if (!$asset || $asset->trashed())
		{
			return response()->json(['message' => trans('Failed to find resource for ID :id', ['id' => $resourceid])], 404);
		}

		// Ensure the client is authorized to manage a group with queues on the resource in question.
		if (!auth()->user()->can('manage resources')
		 && $user->id != auth()->user()->id)
		{
			$owned = auth()->user()->groups->pluck('id')->toArray();

			$queues = array();
			foreach ($asset->subresources as $sub)
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
		if ($loginShell && !file_exists($loginShell))
		{
			return response()->json(['message' => trans('Invalid loginShell')], 409);
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

			if (!$pi || !$pi->id)
			{
				return response()->json(['message' => trans('Invalid pilogin')], 409);
			}
		}

		if ($loginShell)
		{
			$user->loginShell = $loginShell;
		}
		if ($primarygroup)
		{
			$user->primarygroup = $primarygroup;
		}

		event($event = new ResourceMemberCreated($asset, $user));

		$status = trans('global.unknown');
		if ($event->noStatus())
		{
			$status = trans('resources::assets.member status.none');
		}
		elseif ($event->isPending())
		{
			$status = trans('resources::assets.member status.pending');
		}
		elseif ($event->isReady())
		{
			$status = trans('resources::assets.member status.ready');
		}
		elseif ($event->isPendingRemoval())
		{
			$status = trans('resources::assets.member status.pending removal');
		}
		elseif ($event->hasError())
		{
			$status = trans('resources::assets.member status.error');
		}

		$data = array(
			'resource' => array(
				'id'   => $asset->id,
				'name' => $asset->name,
			),
			'user' => array(
				'id'   => $user->id,
				'name' => $user->name
			),
			'status'       => $event->status,
			'status_text'  => $status,
			'loginShell'   => $event->user->loginShell,
			'primarygroup' => $event->user->primarygroup,
			'pilogin'      => $event->user->pilogin,
			'api'          => route('api.resources.members.read', $asset->id . '.' . $user->id)
		);

		return new JsonResource($data);
	}

	/**
	 * Read a resource member status
	 *
	 * @apiMethod GET
	 * @apiUri    /resources/members/{user id}.{resource id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "user id.resource id",
	 * 		"description":   "User ID and Resource ID separated by a period",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "12345.67"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   string $id
	 * @return  JsonResource|JsonResponse
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

		// Look up the current user
		$user = User::findOrFail($userid);

		if (!$user || $user->trashed())
		{
			return response()->json(['message' => trans('Failed to find user for ID :id', ['id' => $userid])], 404);
		}

		// Look up the current resource
		$asset = Asset::findOrFail($resource);

		if (!$asset || $asset->trashed())
		{
			return response()->json(['message' => trans('Failed to find resource for ID :id', ['id' => $resource])], 404);
		}

		// Ignore if rolename is not set
		if (!$asset->rolename)
		{
			return response()->json(null, 404);
		}

		// Call central accounting service to request status
		event($event = new ResourceMemberStatus($asset, $user));

		$status = trans('global.unknown');
		if ($event->noStatus())
		{
			$status = trans('resources::assets.member status.none');
		}
		elseif ($event->isPending())
		{
			$status = trans('resources::assets.member status.pending');
		}
		elseif ($event->isReady())
		{
			$status = trans('resources::assets.member status.ready');
		}
		elseif ($event->isPendingRemoval())
		{
			$status = trans('resources::assets.member status.pending removal');
		}
		elseif ($event->hasError())
		{
			$status = trans('resources::assets.member status.error');
		}

		$data = array(
			'resource' => array(
				'id'   => $asset->id,
				'name' => $asset->name,
			),
			'user' => array(
				'id'   => $user->id,
				'name' => $user->name
			),
			'status'       => $event->status,
			'status_text'  => $status,
			'errors'       => $event->errors,
			'loginShell'   => $event->user->loginShell,
			'primarygroup' => $event->user->primarygroup,
			'pilogin'      => $event->user->pilogin,
			'api'          => route('api.resources.members.read', $id)
		);

		return new JsonResource($data);
	}

	/**
	 * Delete a resource/member association
	 *
	 * @apiMethod DELETE
	 * @apiUri    /resources/members/{user id}.{resource id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "user id.resource id",
	 * 		"description":   "User ID and Resource ID separated by a period",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "12345.67"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  string $id
	 * @return JsonResponse
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

		// Look up the resource to which access is being removed.
		$resource = Asset::findOrFail($resourceid);

		// Ensure the client is authorized to manage a group with queues on the resource in question.
		if (!auth()->user()->can('manage resources')
		 && $user->id != auth()->user()->id)
		{
			$owned = auth()->user()->groups->pluck('id')->toArray();

			$queues = array();
			$subresources = $resource->subresources;

			foreach ($subresources as $sub)
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
		else
		{
			$owned = $user->groups->pluck('id')->toArray();
		}

		// Check for other queue memberships on this resource that might conflict with removing the role
		$rows = 0;

		/*$resources = Asset::query()
			->where('rolename', '!=', '')
			->where('listname', '!=', '')
			->get();

		foreach ($resources as $res)
		{
			$subresources = $res->subresources;*/
			$subresources = $resource->subresources;

			foreach ($subresources as $sub)
			{
				$queues = $sub->queues()
					//->whereIn('groupid', $owned)
					->get();
					//->pluck('queuid')
					//->toArray();

				foreach ($queues as $queue)
				{
					$rows += $queue->users()
						->whereIsMember()
						->where('userid', '=', $user->id)
						->count();

					if ($queue->group)
					{
						$rows += $queue->group->members()
							->whereIsManager()
							->where('userid', '=', $user->id)
							->count();
					}
				}
			}
		//}

		if ($rows > 0)
		{
			return response()->json(null, 202);
		}

		// Call to remove role from this user's account.
		event(new ResourceMemberDeleted($resource, $user));

		return response()->json(null, 204);
	}
}
