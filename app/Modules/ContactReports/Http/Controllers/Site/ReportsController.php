<?php

namespace App\Modules\ContactReports\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Follow;
use App\Modules\ContactReports\Models\Type;
use App\Modules\Groups\Models\Member as GroupUser;

class ReportsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'group'    => null,
			'people'   => null,
			'resource' => null,
			'start'    => null,
			'stop'     => null,
			'id'       => null,
			'type'     => null,
			'notice'   => '*',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Report::$orderBy,
			'order_dir' => Report::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			//$filters[$key] = $request->state('crm.reports.filter_' . $key, $key, $default);
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Report)->getAttributes())))
		{
			$filters['order'] = Report::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Report::$orderDir;
		}

		$query = Report::query();

		if ($filters['search'])
		{
			/*$query->where(function($query) use ($filters)
			{
				$query->where('report', 'like', '%' . $filters['search'] . '%')
					->orWhere('stemmedreport', 'like', '%' . $filters['search'] . '%');
			});*/

			$searches = explode(',', $filters['search']);

			$sql = "MATCH(stemmedreport) AGAINST ('";
			foreach ($searches as $search)
			{
				$sql .= " +" . $search;
			}
			$sql .= "' IN BOOLEAN MODE)";

			$query->where(DB::raw($sql));
		}

		if ($filters['notice'] != '*')
		{
			$query->where('notice', '=', $filters['notice']);
		}

		if ($filters['group'])
		{
			$query->where('groupid', '=', $filters['group']);
		}

		if ($filters['type'])
		{
			$query->where('contactreporttypeid', '=', $filters['type']);
		}

		$rows = $query
			->withCount('comments')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$row = null;
		if ($filters['id'])
		{
			$row = Report::find($filters['id']);
		}

		$followingusers = array();
		$followinggroups = array();

		if (auth()->user())
		{
			$data = Follow::query()
				->where('membertype', '=', 10)
				->where('userid', '=', auth()->user()->id)
				->get();
			foreach ($data as $d)
			{
				$followingusers[] = array(
					'id' => $d->targetuserid,
					'api' => route('api.contactreports.followusers.read', ['id' => $d->id]),
					'name' => $d->following ? $d->following->name : trans('global.unknown')
				);
			}

			$data = GroupUser::query()
				->where('membertype', '=', 10)
				->where('userid', '=', auth()->user()->id)
				->get();
			foreach ($data as $d)
			{
				$followinggroups[] = array(
					'id' => $d->userid,
					'api' => route('api.contactreports.followgroups.read', ['id' => $d->id]),
					'name' => $d->group ? $d->group->name : trans('global.unknown')
				);
			}
		}

		$types = Type::query()->orderBy('name', 'asc')->get();

		return view('contactreports::site.index', [
			'filters' => $filters,
			'rows' => $rows,
			'report' => $row,
			'types' => $types,
			'followingusers' => $followingusers,
			'followinggroups' => $followinggroups,
		]);
	}
}
