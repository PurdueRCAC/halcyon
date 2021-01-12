<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Stemmedtext;
use App\Modules\News\Models\Newsresource;
use App\Modules\News\Http\Resources\ArticleResource;
use App\Modules\News\Http\Resources\ArticleResourceCollection;
use App\Modules\History\Models\Log;
use App\Halcyon\Utility\PorterStemmer;
use Carbon\Carbon;

/**
 * Articles
 *
 * @apiUri    /api/news
 */
class ArticlesController extends Controller
{
	/**
	 * Display a listing of news articles
	 *
	 * @apiMethod GET
	 * @apiUri    /api/news
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "contactreportid",
	 * 		"description":   "ID of contact report",
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
	 * 		"default":       ""
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
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "datetimecreated",
	 * 		"allowedValues": "id, motd, datetimecreated, datetimeremoved"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'resource'  => null,
			'location'  => null,
			'start'     => null,
			'stop'      => null,
			'state'     => 'published',
			'access'    => null,
			'limit'     => config('list_limit', 20),
			'order'     => 'datetimecreated',
			'order_dir' => 'desc',
			'type'      => null,
			'template'  => 0,
			'id'        => null,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;
			if ($key == 'start' || $key == 'stop')
			{
				$val = preg_replace('/!/', ' ', $val);
			}

			$filters[$key] = $val;
		}

		/*$query = null;
		if ($query)
		{
			// Drop redundant, leading and trailing white space.
			$search = preg_replace('/ +/', ' ', $query);
			$search = trim($search);

			// Dissasemble search text
			$bits = explode(' ', $search);

			foreach ($bits as $bit)
			{
				// Is this a keyed term? otherwise make it a keyword
				if (preg_match('/^[a-z]+\:/', $bit))
				{
					$term = explode(':', $bit);

					// Preserve any ':' after the first
					$key = array_shift($term);
					$value = implode(':', $term);

					if ($key == "start")
					{
						$filters['start'] = preg_replace('/!/', ' ', $value);
					}
					elseif ($key == "stop")
					{
						$filters['stop'] = preg_replace('/!/', ' ', $value);
					}
					elseif ($key == "resource")
					{
						$filters['resource'] = explode(',', $value);
					}
					elseif ($key == "newstype")
					{
						$filters['newstype'] = explode(',', $value);
					}
					elseif ($key == "id")
					{
						$filters['id'] = $value;
					}
					elseif ($key == "published")
					{
						$filters['published'] = $value;
					}
					elseif ($key == "template")
					{
						$filters['template'] = $value;
					}
					elseif ($key == "limit")
					{
						$filters['limit'] = $value;
					}
					elseif ($key == 'ongoing')
					{
						$filters['ongoing'] = $value;
					}
					elseif ($key == 'upcoming')
					{
						$filters['upcoming'] = $value;
					}
					elseif ($key == 'formatted')
					{
						$filters['formatted'] = $value;
					}
					elseif ($key == 'location')
					{
						$filters['location'] = $value;
					}
					else
					{
						// What is this? I don't know.
						throw new \Exception('Unknown filter `' . $key . '`', 415);
					}
				}
				else
				{
					if (!isset($filters['keywords']))
					{
						$filters['keywords'] = array();
					}

					// Trim extra garbage
					$keyword = preg_replace('/[^A-Za-z0-9]/', ' ', $bit);

					// Calculate stem for the word
					$stem = $keyword; //PorterStemmer::Stem($keyword);
					$stem = substr($stem, 0, 1) . $stem;

					array_push($filters['keywords'], $stem);
				}
			}
		}*/

		if (!in_array($filters['order'], ['id', 'headline', 'datetimecreated']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$n = (new Article)->getTable();

		$query = Article::query()
			->select($n . '.*')
			->with('type')
			->with('associations')
			->where($n . '.template', '=', $filters['template']);

		if ($filters['search'])
		{
			/*$query->where(function($query) use ($filters)
			{
				$query->where('headline', 'like', '%' . $filters['search'] . '%')
					->orWhere('body', 'like', '%' . $filters['search'] . '%');
			});*/

			$keywords = explode(' ', $filters['search']);

			$from_sql = array();
			foreach ($keywords as $keyword)
			{
				// Trim extra garbage
				$keyword = preg_replace('/[^A-Za-z0-9]/', ' ', $keyword);

				// Calculate stem for the word
				$stem = PorterStemmer::Stem($keyword);
				$stem = substr($stem, 0, 1) . $stem;

				$from_sql[] = "+" . $stem;
			}

			$s = (new Stemmedtext)->getTable();

			$query->join($s, $s . '.id', $n . '.id');
			$query->select($n . '.*', DB::raw("(MATCH($s.stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "') * 10 + 2 * (1 / (ABS(DATEDIFF(NOW(), $n.datetimenews)) + 1))) AS score"));
			$query->whereRaw("MATCH($s.stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "' IN BOOLEAN MODE)");
			$query->orderBy('score', 'desc');
		}

		if ($filters['start'])
		{
			$query->where($n . '.datetimenews', '>', $filters['start']);
		}

		if ($filters['stop'])
		{
			$query->where(function($where) use ($n, $filters)
			{
				$where->whereNull($n . '.datetimenewsend')
					->orWhere($n . '.datetimenewsend', '=', '0000-00-00 00:00:00')
					->orWhere($n . '.datetimenewsend', '<=', $filters['end']);
			});
		}

		if ($filters['resource'])
		{
			$r = (new Newsresource)->getTable();
			$filters['resource'] = explode(',', $filters['resource']);

			$query->join($r, $r . '.newsid', $n . '.id')
				->whereIn($r . '.resourceid', $filters['resource']);
		}

		if ($filters['state'] == 'published')
		{
			$query->where($n . '.published', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($n . '.published', '=', 0);
		}

		if ($filters['location'])
		{
			$query->where($n . '.location', 'like', '%' . $filters['location'] . '%');
		}

		if ($filters['type'])
		{
			$query->where($n . '.newstypeid', '=', $filters['type']);
		}

		if ($filters['id'])
		{
			$query->where($n . '.id', '=', $filters['id']);
		}

		$rows = $query
			->with('updates')
			->orderBy($n . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new ArticleResourceCollection($rows);
	}

	/**
	 * Create a news article
	 *
	 * @apiMethod POST
	 * @apiUri    /api/news
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "headline",
	 * 		"description":   "The entry's headline",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "The entry's body",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "newstypeid",
	 * 		"description":   "ID of the news type",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "published",
	 * 		"description":   "Published state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "template",
	 * 		"description":   "If entry is a template or not",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimenews",
	 * 		"description":   "Start date and time",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimenewsend",
	 * 		"description":   "Stop date and time",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "location",
	 * 		"description":   "Entry location",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "url",
	 * 		"description":   "URL for the entry",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'newstypeid' => 'required|integer|in:0,1',
			'headline' => 'required|string|max:255',
			'body' => 'required|string|max:15000',
			'published' => 'nullable|integer|in:0,1',
			'template' => 'nullable|integer|in:0,1',
			'datetimenews' => 'required|date',
			'datetimenewsend' => 'nullable|date',
			'location' => 'nullable|string|max:32',
			'url' => 'nullable|url',
		]);

		$row = new Article($request->all());

		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 500);
		}

		return new ArticleResource($row);
	}

	/**
	 * Read a news article
	 *
	 * @apiMethod GET
	 * @apiUri    /api/news/{id}
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
		$row = Article::findOrFail((int)$id);

		return new ArticleResource($row);
	}

	/**
	 * Retrieve news article view stats
	 *
	 * @apiMethod GET
	 * @apiUri    /api/news/{id}/views
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
	public function views($id)
	{
		$row = Log::query()
			->select(DB::raw('COUNT(id) as viewcount'), DB::raw('COUNT(DISTINCT ip) as uniquecount'))
			->where('transportmethod', '=', 'GET')
			->where('status', '=', 200)
			->where('uri', '=', route('site.news.show', ['id' => $id]))
			->get()
			->first();

		return $row;
	}

	/**
	 * Update a news article
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/news/{id}
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
	 * 		"name":          "headline",
	 * 		"description":   "The entry's headline",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "The entry's body",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "newstypeid",
	 * 		"description":   "ID of the news type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "published",
	 * 		"description":   "Published state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "template",
	 * 		"description":   "If entry is a template or not",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimenews",
	 * 		"description":   "Start date and time",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimenewsend",
	 * 		"description":   "Stop date and time",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "location",
	 * 		"description":   "Entry location",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "url",
	 * 		"description":   "URL for the entry",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'newstypeid' => 'nullable|integer|in:0,1',
			'body' => 'nullable|string',
			'published' => 'nullable|integer|in:0,1',
			'template' => 'nullable|integer|in:0,1',
			'datetimenews' => 'nullable|date',
			'datetimenewsend' => 'nullable|date',
			'location' => 'nullable|string',
			'url' => 'nullable|url',
		]);

		$row = Article::findOrFail($id);
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 500);
		}

		return new ArticleResource($row);
	}

	/**
	 * Delete a news article
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/news/{id}
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
		$row = Article::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 409);
		}

		return response()->json(null, 204);
	}

	/**
	 * Preview a news article
	 *
	 * @apiMethod POST
	 * @apiUri    /api/news
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "The entry's body",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "vars",
	 * 		"description":   "A list of key/value pairs for variable replacement in the body text",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array",
	 * 			"default":   null
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function preview(Request $request)
	{
		$request->validate([
			//'newstypeid' => 'required|integer|in:0,1',
			'body' => 'required|string|max:15000',
			'vars' => 'nullable|array',
			//'published' => 'nullable|integer|in:0,1',
			//'template' => 'nullable|integer|in:0,1',
			//'datetimenews' => 'required|date',
			//'datetimenewsend' => 'nullable|date',
			//'location' => 'nullable|string',
			//'url' => 'nullable|url',
		]);

		$row = new Article();
		$row->id = 0;
		$row->body = $request->input('body');
		$row->datetimecreated = Carbon::now();
		$row->vars = $request->input('vars');

		return new ArticleResource($row);
	}
}
