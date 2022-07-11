<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Modules\News\Mail\Article as Message;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Stemmedtext;
use App\Modules\News\Models\Newsresource;
use App\Modules\News\Models\Association;
use App\Modules\News\Http\Resources\ArticleResource;
use App\Modules\News\Http\Resources\ArticleResourceCollection;
use App\Modules\News\Notifications\ArticleCreated;
use App\Modules\News\Notifications\ArticleUpdated;
use App\Modules\History\Models\Log;
use App\Halcyon\Utility\PorterStemmer;
use Carbon\Carbon;

/**
 * Articles
 *
 * @apiUri    /news
 */
class ArticlesController extends Controller
{
	/**
	 * Display a listing of news articles
	 *
	 * @apiMethod GET
	 * @apiUri    /news
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

		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$filters['state'] = '*';
		}

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

		if (!auth()->user() || !auth()->user()->can('manage news'))
		{
			$filters['state'] = 'published';
		}

		if ($request->has('keywords'))
		{
			$filters['search'] = $request->input('keywords');
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

		if ($filters['start'] == '0000-00-00'
		 || $filters['start'] == '0000-00-00 00:00:00')
		{
			$filters['start'] = null;
		}

		if ($filters['stop'] == '0000-00-00'
		 || $filters['stop'] == '0000-00-00 00:00:00')
		{
			$filters['stop'] = null;
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
					->orWhere($n . '.datetimenewsend', '<=', $stop->toDateTimeString());
			});
		}

		if ($filters['resource'])
		{
			$filters['resource'] = explode(',', $filters['resource']);
			$filters['resource'] = array_map('trim', $filters['resource']);
			$r = (new Newsresource)->getTable();
			$query->whereIn('id', function($innerQuery) use ($r, $filters){
				$innerQuery->select('newsid')
					->from($r)
					->whereIn('resourceid', $filters['resource']);
			});
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
			$type = Type::findOrFail($filters['type']);
			$types = array_merge([$type->id], $type->children->pluck('id')->toArray());
			$query->whereIn($n . '.newstypeid', $types);
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
	 * @apiUri    /news
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
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'newstypeid' => 'required|integer|min:1',
			'headline' => 'required|string|max:255',
			'body' => 'required|string|max:15000',
			'published' => 'nullable|integer|in:0,1',
			'template' => 'nullable|integer|in:0,1',
			'datetimenews' => 'nullable|date',
			'datetimenewsend' => 'nullable|date',
			'location' => 'nullable|string|max:32',
			'url' => 'nullable|url',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Article();

		$row->newstypeid = $request->input('newstypeid');
		$row->headline = $request->input('headline');
		$row->body = $request->input('body');
		$row->published = $request->input('published');
		$row->template = $request->input('template');
		if ($row->template)
		{
			$row->published = 1;
		}

		$datetimenews = $request->input('datetimenews');

		if ($datetimenews && $datetimenews != '0000-00-00 00:00:00')
		{
			$row->datetimenews = $datetimenews;
		}

		if (!$row->template && !$row->datetimenews)
		{
			return response()->json(['message' => trans('news::news.error.invalid time range')], 415);
		}

		if ($request->has('datetimenewsend'))
		{
			$datetimenewsend = $request->input('datetimenewsend');

			if ($datetimenewsend && $datetimenewsend != '0000-00-00 00:00:00')
			{
				$row->datetimenewsend = $datetimenewsend;

				if ($row->datetimenews > $row->datetimenewsend)
				{
					return response()->json(['message' => trans('news::news.error.invalid time range')], 415);
				}
			}
		}

		/*if ($row->template)
		{
			$row->datetimenews = null;
			$row->datetimenewsend = null;
		}*/

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

		if ($row->published && !$row->template && in_array($row->newstypeid, config('module.news.notify_create', [])))
		{
			$row->notify(new ArticleCreated($row));
		}

		return new ArticleResource($row);
	}

	/**
	 * Read a news article
	 *
	 * @apiMethod GET
	 * @apiUri    /news/{id}
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
	 * 		"200": {
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
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
	 * @apiUri    /news/{id}/views
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
	 * 		"200": {
	 * 			"description": "Successful entry read"
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
	 * @apiUri    /news/{id}
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
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'newstypeid' => 'nullable|integer',
			'body' => 'nullable|string',
			'published' => 'nullable|integer|in:0,1',
			'template' => 'nullable|integer|in:0,1',
			'datetimenews' => 'nullable|date',
			'datetimenewsend' => 'nullable|date',
			'location' => 'nullable|string',
			'url' => 'nullable|url',
			'update' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

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

			/*if ($row->template)
			{
				$row->datetimenews = null;
				$row->datetimenewsend = null;
			}*/
		}

		if ($request->has('datetimenews'))
		{
			$row->datetimenews = $request->input('datetimenews');
		}

		if ($request->has('datetimenewsend'))
		{
			$datetimenewsend = $request->input('datetimenewsend');
			if ($datetimenewsend == '0000-00-00 00:00:00')
			{
				$datetimenewsend = null;
			}

			$row->datetimenewsend = $datetimenewsend;

			if ($datetimenewsend)
			{
				if ($row->datetimenews > $row->datetimenewsend)
				{
					return response()->json(['message' => trans('news::news.error.invalid time range')], 415);
				}
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

		if ($request->input('update'))
		{
			$row->datetimeupdate = Carbon::now()->toDateTimeString();
		}

		$row->edituserid = auth()->user()->id;

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

		if ($row->published && !$row->template && in_array($row->newstypeid, config('module.news.notify_update', [])))
		{
			$row->notify(new ArticleUpdated($row));
		}

		return new ArticleResource($row);
	}

	/**
	 * Delete a news article
	 *
	 * @apiMethod DELETE
	 * @apiUri    /news/{id}
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
	 * @apiUri    /news/preview
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
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry preparation"
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
	 * @apiUri    /news/{id}/email
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
	 * 		"name":          "resources",
	 * 		"description":   "A list of resource IDs to send the email to mailing lists.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array",
	 * 			"default":   null
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
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful email"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function email($id, Request $request)
	{
		$request->validate([
			'headline' => 'nullable|string|max:255',
			'body' => 'nullable|string|max:15000',
			'associations' => 'nullable|array',
			'resources' => 'nullable|array'
		]);

		$row = Article::findOrFail($id);

		if ($request->has('headline'))
		{
			$row->headline = $request->input('headline');
		}

		if ($request->has('body'))
		{
			$row->body = $request->input('body');
		}

		// Fetch name of sender
		$name = auth()->user()->name . ' via ' . config('app.name') . ' News';

		// Recipients
		$emails = array();

		if ($request->has('associations'))
		{
			$associations = $request->input('associations');

			foreach ($associations as $i => $association)
			{
				$associd = $association;
				$assoctype = 'user';

				if (strstr('/', $association))
				{
					$association = str_replace(ROOT_URI, '', $association);
					$association = trim($association, '/');

					$parts = explode('/', $association);

					if (count($parts) != 2)
					{
						unset($associations[$i]);
						continue;
					}

					$associd = intval($parts[1]);
					$assoctype = $parts[0];
				}

				$associations[$i] = new Association(array(
					'associd'   => $associd,
					'assoctype' => $assoctype
				));

				if ($associations[$i]->assoctype == 'user')
				{
					$user = $associations[$i]->associated;

					if (!$user || !$user->email)
					{
						continue;
					}

					$emails[] = $user->email;
				}
			}
		}

		if ($request->has('resources'))
		{
			$resources = $request->input('resources');

			foreach ($row->resources as $res)
			{
				if (!in_array($res->resourceid, $resources))
				{
					continue;
				}

				if ($res->resource->listname && $res->resource->listname != 'none')
				{
					$emails[] = $res->resource->mailinglist;
				}
			}
		}

		$emails = array_filter($emails);
		$emails = array_unique($emails);

		if (count($emails) > 0)
		{
			$msg = new Message($row, $name);
			echo $ms->render(); 
			/*Mail::to($emails)->send(new Message($row, $name));

			foreach ($emails as $email)
			{
				Log::create([
					'ip'              => request()->ip(),
					'userid'          => (auth()->user() ? auth()->user()->id : 0),
					'status'          => 200,
					'transportmethod' => 'POST',
					'servername'      => request()->getHttpHost(),
					'uri'             => $email,
					'app'             => 'email',
					'payload'         => 'Emailed article #' . $row->id,
					'classname'       => 'NewsArticlesController',
					'classmethod'     => 'email',
				]);
			}

			$row->update([
				'datetimemailed' => Carbon::now()->toDateTimeString(),
				'lastmailuserid' => auth()->user()->id
			]);*/
		}

		return new ArticleResource($row);
	}
}
