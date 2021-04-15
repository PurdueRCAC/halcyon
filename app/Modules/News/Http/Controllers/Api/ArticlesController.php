<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Modules\News\Mail\Article as Message;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Stemmedtext;
use App\Modules\News\Models\Newsresource;
use App\Modules\News\Models\Association;
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
	 * 		"name":          "start",
	 * 		"description":   "Filter entries scheduled on or after this date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "stop",
	 * 		"description":   "Filter entries scheduled to end before this date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "type",
	 * 		"description":   "New type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   "null"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "The article state.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "published",
	 * 			"enum": [
	 * 				"published",
	 * 				"unpublished"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "resource",
	 * 		"description":   "A comma-separated list of associated resource IDs to filter by",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "1,2,3,4"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "location",
	 * 		"description":   "A location to filter entries by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
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
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
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
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"headline",
	 * 				"datetimecreated"
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
			$start = Carbon::parse($filters['start']);
			$query->where($n . '.datetimenews', '>', $start->toDateTimeString());
		}

		if ($filters['stop'])
		{
			$query->where(function($where) use ($n, $filters)
			{
				$stop = Carbon::parse($filters['stop']);
				$where->whereNull($n . '.datetimenewsend')
					->orWhere($n . '.datetimenewsend', '=', '0000-00-00 00:00:00')
					->orWhere($n . '.datetimenewsend', '<=', $stop->toDateTimeString());
			});
		}

		if ($filters['resource'])
		{
			$r = (new Newsresource)->getTable();
			$filters['resource'] = explode(',', $filters['resource']);
			$filters['resource'] = array_map('trim', $filters['resource']);

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
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "The entry's body",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
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
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "url",
	 * 		"description":   "URL for the entry",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'newstypeid' => 'required|integer',
			'headline' => 'required|string|max:255',
			'body' => 'required|string|max:15000',
			'published' => 'nullable|integer|in:0,1',
			'template' => 'nullable|integer|in:0,1',
			'datetimenews' => 'required|date',
			'datetimenewsend' => 'nullable|date',
			'location' => 'nullable|string|max:32',
			'url' => 'nullable|url',
		]);

		$row = new Article();

		$row->headline = $request->input('headline');
		$row->body = $request->input('body');
		$row->published = $request->input('published');
		$row->template = $request->input('template');
		$row->datetimenews = $request->input('datetimenews');
		if ($request->has('datetimenewsend'))
		{
			$row->datetimenewsend = $request->input('datetimenewsend');

			if ($row->datetimenews > $row->datetimenewsend)
			{
				return response()->json(['message' => trans('news::news.error.invalid time range')], 500);
			}
		}

		if ($row->template)
		{
			$row->datetimenews = '0000-00-00 00:00:00';
			$row->datetimenewsend = '0000-00-00 00:00:00';
		}

		if ($request->has('location'))
		{
			$row->location = $request->input('location');
		}

		if ($request->has('url'))
		{
			$row->url = $request->input('url');
		}

		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}

		if ($row->url && !filter_var($row->url, FILTER_VALIDATE_URL))
		{
			return response()->json(['message' => trans('news::news.error.invalid url')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 500);
		}

		if ($request->has('resources'))
		{
			$row->setResources($request->input('resources'));
		}

		if ($request->has('associations'))
		{
			$row->setAssociations($request->input('associations'));
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
			'newstypeid' => 'nullable|integer',
			'body' => 'nullable|string',
			'published' => 'nullable|integer|in:0,1',
			'template' => 'nullable|integer|in:0,1',
			'datetimenews' => 'nullable|date',
			'datetimenewsend' => 'nullable|date',
			'location' => 'nullable|string',
			'url' => 'nullable|url',
		]);

		$row = Article::findOrFail($id);
		//$row->fill($request->all());

		if ($request->has('newstypeid'))
		{
			$row->newstypeid = $request->input('newstypeid');

			if (!$row->type)
			{
				return response()->json(['message' => trans('news::news.error.invalid type')], 415);
			}
		}

		if ($request->has('headline'))
		{
			$row->headline = $request->input('headline');
		}

		if ($request->has('body'))
		{
			$row->body = $request->input('body');
		}

		if ($request->has('published'))
		{
			$row->published = $request->input('published');
		}

		if ($request->has('template'))
		{
			$row->template = $request->input('template');

			if ($row->template)
			{
				$row->datetimenews = '0000-00-00 00:00:00';
				$row->datetimenewsend = '0000-00-00 00:00:00';
			}
		}

		if ($request->has('datetimenews'))
		{
			$row->datetimenews = $request->input('datetimenews');
		}

		if ($request->has('datetimenewsend'))
		{
			$row->datetimenewsend = $request->input('datetimenewsend');

			if ($row->datetimenews > $row->datetimenewsend)
			{
				return response()->json(['message' => trans('news::news.error.invalid time range')], 415);
			}
		}

		if ($request->has('location'))
		{
			$row->location = $request->input('location');
		}

		if ($request->has('url'))
		{
			$row->url = $request->input('url');

			if ($row->url && !filter_var($row->url, FILTER_VALIDATE_URL))
			{
				return response()->json(['message' => trans('news::news.error.invalid url')], 415);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 500);
		}

		if ($request->has('resources'))
		{
			$row->setResources($request->input('resources'));
		}

		if ($request->has('associations'))
		{
			$row->setAssociations($request->input('associations'));
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
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
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
	 * @apiUri    /api/news/preview
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
			'body' => 'required|string|max:15000',
			'vars' => 'nullable|array'
		]);

		$row = new Article();
		$row->id = 0;
		$row->body = $request->input('body');
		$row->datetimecreated = Carbon::now();
		$row->vars = $request->input('vars');
		if (isset($row->vars['startdate']))
		{
			$row->datetimenews = $row->vars['startdate'];
		}
		if (isset($row->vars['enddate']))
		{
			$row->datetimenewsend = $row->vars['enddate'];
		}

		return new ArticleResource($row);
	}

	/**
	 * Email a news article
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/news/{id}/email
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
	 * 		"name":          "associations",
	 * 		"description":   "A list of user IDs to send the email to. If none provided, Resource mailing lists are used instead.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array",
	 * 			"default":   null
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function email($id, Request $request)
	{
		$request->validate([
			'body' => 'required|string|max:15000',
			'vars' => 'nullable|array'
		]);

		$row = Article::findOrFail($id);

		// Fetch name of sender
		$name = auth()->user()->name . ' via ' . config('app.name') . ' News';

		// Recipients
		$emails = array();

		if ($request->has('associations'))
		{
			$associations = $request->has('associations');

			foreach ($associations as $i => $association)
			{
				$association = str_replace(ROOT_URI, '', $association);
				$association = trim($association, '/');

				$parts = explode('/', $association);

				if (count($parts) != 2)
				{
					unset($associations[$i]);
					continue;
				}

				$associations[$i] = new Association(array(
					'associd'   => intval($parts[1]),
					'assoctype' => $parts[0]
				));

				if ($associations[$i]->assoctype == 'user')
				{
					$user = $associations[$i]->associated;

					if (!$user || !$user->mail)
					{
						continue;
					}

					$emails[] = $user->email;
				}
			}
		}
		else
		{
			if (count($row->resources))
			{
				foreach ($copyobj->resources as $res)
				{
					if ($res->resource->listname && $res->resource->listname != 'none')
					{
						$emails[] = $res->resource->mailinglist;
					}
				}
			}
		}

		if (count($emails) > 0)
		{
			$message = new Message($row, $name);

			foreach ($emails as $email)
			{
				Mail::to($email)->send($message);
			}
		}

		$row->update([
			'datetimemailed' => Carbon::now()->toDateTimeString(),
			'lastmailuserid' => auth()->user()->id
		]);

		return new ArticleResource($row);
	}
}
