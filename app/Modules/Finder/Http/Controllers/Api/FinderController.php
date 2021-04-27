<?php

namespace App\Modules\Finder\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Finder\Models\Node;
use App\Modules\Finder\Models\TermData;

/**
 * Finder
 *
 * @apiUri    /api/finder
 */
class FinderController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "owneruserid",
	 * 		"description":   "Owner user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
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
	 * 		"type":          "integer",
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
	 * 		"default":       "name",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"owneruserid",
	 * 				"unixgroup",
	 * 				"unixid",
	 * 				"deptnumber"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'searchuser' => $request->input('searchuser', ''),
			'owneruserid'   => $request->input('owneruserid', 0),
			'unixgroup'   => $request->input('unixgroup', ''),
			'unixid'   => $request->input('unixid', 0),
			'deptnumber'   => $request->input('deptnumber', 0),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'order'     => $request->input('order', Group::$orderBy),
			'order_dir' => $request->input('order_dir', Group::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Group::$orderDir;
		}

		$g = (new Group)->getTable();

		$query = Group::query()
			->select(DB::raw('DISTINCT ' . $g . '.id, ' . $g . '.name, ' . $g . '.owneruserid, ' . $g . '.unixgroup, ' . $g . '.unixid, ' . $g . '.deptnumber, ' . $g . '.onepurdue, ' . $g . '.githuborgname'));

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($g . '.id', '=', (int)$filters['search']);
			}
			else
			{
				$search = (string)$filters['search'];
				$search = trim($search);
				$search = preg_replace('/ +/', ' ', $search);

				// Skip matches on trailing "group" or we'll return a billion results
				if (preg_match('/Group$/i', $search))
				{
					$search = preg_replace('/Group$/i', '', $search);
				}

				if (!empty($filters['searchuser']))
				{
					//$filters['searchuser'] = $search;
					$query->where(function($where) use ($g, $search)
					{
						$where->where($g . '.name', 'like', $search . '%')
							->orWhere($g . '.name', 'like', '%' . $search . '%');
					});
				}
				else
				{
					$gu = (new Member)->getTable();
					$u = (new User)->getTable();
					$uu = (new UserUsername)->getTable();

					$query->join($gu, $gu . '.groupid', $g . '.id');
					$query->join($u, $u . '.id', $gu . '.userid');
					$query->join($uu, $uu . '.userid', $u . '.id');

					//$query->where($gu . '.membertype', '=', 2);
					$query->where(function($where) use ($g, $gu, $u, $uu, $search)
					{
						$where->where($g . '.name', 'like', $search . '%')
							->orWhere($g . '.name', 'like', '%' . $search . '%')
							->orWhere(function($userswhere) use ($gu, $u, $uu, $search)
							{
								$userswhere
									->where($gu . '.membertype', '=', 2)
									->where(function($users) use ($u, $uu, $search)
									{
										$users->where($uu . '.username', '=', $search)
											->orWhere($uu . '.username', 'like', $search . '%')
											->orWhere($uu . '.username', 'like', '%' . $search . '%')
											->orWhere($u . '.name', 'like', '%' . $search . '%')
											->orWhere($u . '.name', 'like', $search . '%')
											->orWhere($u . '.name', 'like', '%' . $search);
									});
							});
					});
				}
			}
		}

		if ($filters['searchuser'])
		{
			$gu = (new Member)->getTable();
			$u = (new User)->getTable();
			$uu = (new UserUsername)->getTable();

			$filters['searchuser'] = strtolower((string)$filters['searchuser']);

			$query->join($gu, $gu . '.groupid', $g . '.id');
			$query->join($u, $u . '.id', $gu . '.userid');
			$query->join($uu, $uu . '.userid', $u . '.id');

			$query->where($gu . '.membertype', '=', 2);
			$query->where(function($where) use ($g, $u, $uu, $filters)
			{
				$where->where($uu . '.username', '=', $filters['searchuser'])
					->orWhere($uu . '.username', 'like', $filters['searchuser'] . '%')
					->orWhere($uu . '.username', 'like', '%' . $filters['searchuser'] . '%')
					->orWhere($u . '.name', 'like', '%' . $filters['searchuser'] . '%')
					->orWhere($u . '.name', 'like', $filters['searchuser'] . '%')
					->orWhere($u . '.name', 'like', '%' . $filters['searchuser']);
				/*$where->where($g . '.name', 'like', '%' . $filters['searchuser'] . '%')
					->orWhere(function($users) use ($u, $uu, $filters)
					{
						$users->where($uu . '.username', '=', $filters['searchuser'])
							->orWhere($uu . '.username', 'like', $filters['searchuser'] . '%')
							->orWhere($uu . '.username', 'like', '%' . $filters['searchuser'] . '%')
							->orWhere($u . '.name', 'like', '%' . $filters['searchuser'] . '%')
							->orWhere($u . '.name', 'like', $filters['searchuser'] . '%')
							->orWhere($u . '.name', 'like', '%' . $filters['searchuser']);
					});*/
			});
		}

		if ($filters['owneruserid'])
		{
			$query->where($g . '.owneruserid', '=', $filters['owneruserid']);
		}

		if ($filters['unixgroup'])
		{
			$query->where($g . '.unixgroup', '=', $filters['unixgroup']);
		}

		if ($filters['unixid'])
		{
			$query->where($g . '.unixid', '=', $filters['unixid']);
		}

		if ($filters['deptnumber'])
		{
			$query->where($g . '.deptnumber', '=', $filters['deptnumber']);
		}

		/*if (auth()->user() && auth()->user()->can('manage groups'))
		{
			$query->withCount('members');
		}*/
//echo $query->toSql(); die();
		$rows = $query
			//->with('motd')
			->orderBy($g . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new GroupResourcecollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/finder
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'unixgroup' => 'nullable|integer|max:10',
			'userid' => 'nullable|integer'
		]);

		$name = $request->input('name');
		$userid = $request->input('userid', auth()->user()->id);

		$exists = Group::findByName($name);

		if ($exists)
		{
			return response()->json(['message' => trans('groups::groups.name already exists', ['name' => $name])], 415);
		}

		$row = new Group;
		$row->name = $request->input('name');

		// Verify UNIX group is sane - this is just a first pass,
		// would still need to make sure this is not a duplicate anywhere, etc
		if ($request->has('unixgroup'))
		{
			$row->unixgroup = $request->input('unixgroup');

			if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $row->unixgroup))
			{
				return response()->json(['message' => trans('Field `unixgroup` not in valid format')], 415);
			}

			$exists = Group::findByUnixgroup($row->unixgroup);

			// Check for a duplicate
			if ($exists)
			{
				return response()->json(['message' => trans('`unixgroup` :name already exists', ['name' => $row->unixgroup])], 409);
			}

			try
			{
				// Check to make sure this base name doesn't exist elsewhere
				$config = config('ldap.rcac_group', []);

				$ldap = app('ldap')
					->addProvider($config, 'rcac_group')
					->connect('rcac_group');

				// Performing a query.
				$rows = $ldap->search()
					->where('cn', '=', $row->unixgroup)
					->get();

				if ($rows > 0)
				{
					//return 409;
				}
			}
			catch (\Exception $e)
			{
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$member = new Member;
		$member->groupid = $row->id;
		$member->userid = $userid;
		$member->membertype = 2;

		if (!$member->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new GroupResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Group::findOrFail($id);

		return new GroupResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/finder/{id}
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
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'nullable|max:255',
			'unixgroup' => 'nullable|max:10',
		]);

		$row = Group::findOrFail($id);
		//$row->update($request->all());

		// Verify UNIX group is sane - this is just a first pass,
		// would still need to make sure this is not a duplicate anywhere, etc
		$unixgroup = $request->input('unixgroup');

		if ($unixgroup)
		{
			if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $unixgroup))
			{
				return response()->json(['message' => trans('Field `unixgroup` not in valid format')], 415);
			}

			$exists = Group::findByUnixgroup($unixgroup);

			// Check for a duplicate
			if ($exists && $exists->id != $row->id)
			{
				return response()->json(['message' => trans('`unixgroup` ' . $dataobj->unixgroup . ' already exists')], 409);
			}

			try
			{
				// Check to make sure this base name doesn't exist elsewhere
				$config = config('ldap.rcac_group', []);

				$ldap = app('ldap')
					->addProvider($config, 'rcac_group')
					->connect('rcac_group');

				// Performing a query.
				$rows = $ldap->search()
					->where('cn', '=', $unixgroup)
					->get();

				if ($rows > 0)
				{
					//return 409;
				}
			}
			catch (\Exception $e)
			{
			}

			$row->unixgroup = $unixgroup;
		}

		if ($request->has('name'))
		{
			$name = $request->input('name');

			$exists = Group::findByName($name);

			if ($exists)
			{
				return response()->json(['message' => trans('groups::groups.name already exists', ['name' => $name])], 415);
			}

			$row->name = $name;
		}

		if ($request->has('deptnumber'))
		{
			$row->deptnumber = $request->input('deptnumber');
		}

		if ($request->has('githuborgname'))
		{
			$row->githuborgname = $request->input('githuborgname');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new GroupResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/finder/{id}
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
		$row = Group::findOrFail($id);

		if (!$row->isTrashed())
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}

	/**
	 * Get settings
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/settings
	 * @return  Response
	 */
	public function settings()
	{
		$data = config('module.finder', []);

		if (empty($data))
		{
			$data = include dirname(dirname(dirname(__DIR__))) . '/Config/config.php';
		}

		return new JsonResource($data);
	}

	/**
	 * Get service list
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/servicelist
	 * @return  Response
	 */
	public function servicelist()
	{
		$services = Node::services();

		return new JsonResource($services);
	}

	/**
	 * Get facet tree
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/facettree
	 * @return  Response
	 */
	public function facettree()
	{
		$questions = TermData::tree();//$this->createFacetTree();

		return new JsonResource($questions);
	}

	/**
	 * Send an email
	 *
	 * @apiMethod POST
	 * @apiUri    /api/finder/sendmail
	 * @apiAuthorization  true
	 * @param   Request $request
	 * @return  Response
	 */
	public function sendmail(Request $request)
	{
		$qdata = $request->input("qdata");
		$sdata = $request->input("sdata");

		$body = "Thank you for using the Finder tool. " .
			"We hope it was useful.\r\n\r\n" .
			"Your selected criteria were:\r\n";

		$questions = TermData::tree();//$this->createFacetTree();

		$facets = [];

		foreach ($qdata as $qitem)
		{
			$question_id = $qitem[0];
			$facet_id    = $qitem[1];

			$facets[] = $facet_id;

			foreach ($questions as $question)
			{
				if ($question['id'] == $question_id)
				{
					$body .= '* ' . $question['name'] . ' -- ';
					foreach ($question['choices'] as $choice)
					{
						if ($choice['id'] == $facet_id)
						{
							$body .= $choice['name'] . "\r\n";
						}
					}
				}
			}
		}

		$body = $body . "\r\nYour resulting choices were:\r\n";

		$services = Node::services();//$this->createTestServiceList();

		foreach ($sdata as $svc)
		{
			foreach ($services as $service)
			{
				if ($service['id'] == $svc)
				{
					$body .= '* '  . $service['title'] . "\r\n";
				}
			}
		}

		$body .= "\r\nUse this link to return to the tool ".
				"with your criteria already selected: " .
				$request->getSchemeAndHttpHost() .
				"/finder?facets=" .
				implode($facets, ',') .
				"\r\n\r\n" .
				"If you have any further questions or need more information about " .
				"Finder services, please contact the helpdesk to set up a consultation, ".
				"or contact the service owners " .
				"directly (contact details in tool comparison table).\r\n\r\n";

		$subject = 'Assistance request from Finder application';

		$mailManager = \Drupal::service('plugin.manager.mail');
		$module = 'finder';
		$key = 'complete_form';

		$to = $request->input('email');
		$params['message'] = $body;
		$params['subject'] = $subject;

		error_log("to is $to");
		error_log("message is {$params['message']}");

		$langcode = \Drupal::currentUser()->getPreferredLangcode();
		$send = true;
		$result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

		if ($result['result'] !== true)
		{
			return response()->json(trans('finder::finder.There was a problem sending your message and it was not sent.'), 500);
		}

		return response()->json(trans('finder::finder.Your message has been sent.'), 200);
	}
}
