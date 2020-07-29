<?php

namespace App\Modules\Knowledge\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Knowledge\Models\Report;
use App\Modules\Knowledge\Models\Comment;
use App\Modules\Knowledge\Http\Resources\CommentResource;
use Carbon\Carbon;

class CommentsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'contactreportid' => 0,
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'start'     => null,
			'stop'      => null,
			'order'     => Comment::$orderBy,
			'order_dir' => Comment::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Comment::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Comment::$orderDir;
		}

		//$report = Report::findOrFail($filters['id']);

		$query = Comment::query()->where('contactreportid', $filters['contactreportid']);

		$cr = (new Comment)->getTable();

		if ($filters['search'])
		{
			$query->where('commment', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		$rows->each(function ($item, $key)
		{
			$item->url = route('site.knowledge.show', ['id' => $item->contactreportid]);
			//$item->formatteddate = $item->formatDate($item->getOriginal('datetimenews'), $item->getOriginal('datetimenewsend'));
			$item->formattedcomment = $item->formattedComment();
			$item->canEdit   = false;
			$item->canDelete = false;

			if (auth()->user())
			{
				if (auth()->user()->can('edit knowledge'))
				{
					$item->canEdit   = true;
				}
				if (auth()->user()->can('delete knowledge'))
				{
					$item->canDelete = true;
				}
			}
		});

		return $rows;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'comment' => 'required|string',
			'contactreportid' => 'required|integer|min:1',
		]);

		$row = new Comment($request->all());

		/*if (!$row->contactreportid)
		{
			return response()->json(['message' => __METHOD__ . '(): Missing contactreport ID'], 415);
		}*/

		if (!$row->report)
		{
			return response()->json(['message' => __METHOD__ . '(): Invalid contactreport ID'], 415);
		}

		// Set notice state
		$row->notice = 0;

		if ($row->comment != '')
		{
			$row->notice = 22;
		}

		$row->datetimecreated = Carbon::now()->toDateTimeString();

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new CommentResource($row);
	}

	/**
	 * Retrieve a specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function read($comment)
	{
		$item = Comment::findOrFail((int)$comment);

		return new CommentResource($item);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update(Request $request, $comment)
	{
		$request->validate([
			'comment' => 'nullable|string|min:1',
			'contactreportid' => 'nullable|integer|min:1',
			'userid' => 'nullable|integer|min:1',
			'notice' => 'nullable|integer',
		]);

		$data = $request->all();

		if (isset($data['datetimecreated']))
		{
			unset($data['datetimecreated']);
		}

		if (!auth()->user() || !auth()->user()->can('admin knowledge'))
		{
			unset($data['userid']);
		}

		$row = Comment::findOrFail($comment);
		$row->fill($data);

		if ($row->contactreportid != $row->getOriginal('contactreportid'))
		{
			/*if (!$row->contactreportid)
			{
				return response()->json(['message' => __METHOD__ . '(): Missing contactreport ID'], 415);
			}*/

			if (!$row->report)
			{
				return response()->json(['message' => __METHOD__ . '(): Invalid contactreport ID'], 415);
			}
		}

		/*if ($row->comment != $row->getOriginal('comment'))
		{
			if (!$row->comment)
			{
				return response()->json(['message' => __METHOD__ . '(): Invalid comment'], 415);
			}
		}*/

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.update failed')], 500);
		}

		return new CommentResource($row);
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
	 */
	public function delete($comment)
	{
		$row = Comment::findOrFail($comment);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])], 500);
		}

		return response()->json(null, 204);
	}
}
