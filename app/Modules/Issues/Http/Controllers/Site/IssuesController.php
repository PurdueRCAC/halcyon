<?php

namespace App\Modules\Issues\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\ToDo;

class IssuesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'keyword'   => null,
			'resource'  => null,
			'start'     => null,
			'stop'      => null,
			'id'        => null,
			'resolved'    => '',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Issue::$orderBy,
			'order_dir' => Issue::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			//$filters[$key] = $request->state('crm.reports.filter_' . $key, $key, $default);
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'userid', 'report', 'datetimecreated', 'issuetodoid']))
		{
			$filters['order'] = Issue::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Issue::$orderDir;
		}

		$query = Issue::query();

		if ($filters['keyword'])
		{
			/*$query->where(function($query) use ($filters)
			{
				$query->where('report', 'like', '%' . $filters['search'] . '%')
					->orWhere('stemmedreport', 'like', '%' . $filters['search'] . '%');
			});*/

			$searches = explode(',', $filters['keyword']);

			$sql = "MATCH(stemmedreport) AGAINST ('";
			foreach ($searches as $search)
			{
				$sql .= " +" . $search;
			}
			$sql .= "' IN BOOLEAN MODE)";

			$query->where(DB::raw($sql));
		}

		$rows = $query
			->withCount('comments')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$row = null;
		if ($filters['id'])
		{
			$row = Issue::find($filters['id']);
		}

		$todos = ToDo::query()
			->get();

		return view('issues::site.index', [
			'filters' => $filters,
			'rows' => $rows,
			'issue' => $row,
			'todos' => $todos
		]);
	}
}
