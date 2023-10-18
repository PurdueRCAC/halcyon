<?php

namespace App\Modules\News\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Stemmedtext;
use App\Modules\News\Notifications\ArticleCreated;
use App\Modules\News\Notifications\ArticleUpdated;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Utility\PorterStemmer;
use Carbon\Carbon;

class ArticlesController extends Controller
{
	/**
	 * Display templates?
	 *
	 * @var  int
	 */
	private $template = 0;

	/**
	 * Display a listing of articles
	 *
	 * @param   StatefulRequest  $request
	 * @return  View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'access'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => 'id',
			'order_dir' => 'desc',
			'type'      => null,
		);

		$action = 'index';
		if ($this->template)
		{
			$action = 'template';
			$filters['state'] = '*';
		}

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('news.' . $action . '.filter_' . $key)
			 && $request->input($key) != session()->get('news.' . $action . '.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('news.' . $action . '.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'headline', 'datetimecreated', 'state', 'newstypeid']))
		{
			$filters['order'] = 'id';
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
			->with('mailer')
			->where($n . '.template', '=', $this->template);

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
				$stem = PorterStemmer::stem($keyword);
				$stem = substr($stem, 0, 1) . $stem;

				$from_sql[] = "+" . $stem;
			}

			$s = (new Stemmedtext)->getTable();

			$query->join($s, $s . '.id', $n . '.id');
			$query->select($n . '.*', DB::raw("(MATCH($s.stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "') * 10 + 2 * (1 / (ABS(DATEDIFF(NOW(), $n.datetimenews)) + 1))) AS score"));
			$query->whereRaw("MATCH($s.stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "' IN BOOLEAN MODE)");
			$query->orderBy('score', 'desc');
		}

		if ($filters['state'] == 'published')
		{
			$query->where($n . '.published', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($n . '.published', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		elseif ($filters['state'] == '*')
		{
			$query->withTrashed();
		}

		if ($filters['type'])
		{
			$query->where($n . '.newstypeid', '=', $filters['type']);
		}

		$rows = $query
			->withCount('updates')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Type::tree();

		return view('news::admin.articles.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types,
			'template' => $this->template
		]);
	}

	/**
	 * Display a listing of templates
	 *
	 * @param   StatefulRequest  $request
	 * @return  View
	 */
	public function templates(StatefulRequest $request)
	{
		$this->template = 1;

		return $this->index($request);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param  Request $request
	 * @return  View
	 */
	public function create(Request $request)
	{
		$row = new Article();
		$row->published = 1;

		if ($request->input('template'))
		{
			$row->template = 1;
		}

		$types = Type::query()
			->where('parentid', '=', 0)
			->orderBy('ordering', 'asc')
			->orderBy('name', 'asc')
			->get();

		if ($df = config('modules.news.default_type', 0))
		{
			foreach ($types as $type)
			{
				if ($type->id == $df)
				{
					$row->newstypeid = $type->id;
					break;
				}
			}
		}
		else
		{
			$row->newstypeid = $types->first()->id;
		}

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$templates = Article::query()
			->where('published', '=', 1)
			->where('template', '=', 1)
			->orderBy('headline', 'asc')
			->get();

		return view('news::admin.articles.edit', [
			'row'   => $row,
			'types' => $types,
			'templates' => $templates
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param  Request $request
	 * @param   int  $id
	 * @return  View
	 */
	public function edit(Request $request, $id)
	{
		$row = Article::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$types = Type::query()
			->where('parentid', '=', 0)
			->orderBy('ordering', 'asc')
			->orderBy('name', 'asc')
			->get();

		$templates = Article::query()
			->where('published', '=', 1)
			->where('template', '=', 1)
			->orderBy('headline', 'asc')
			->get();

		return view('news::admin.articles.edit', [
			'row'   => $row,
			'types' => $types,
			'templates' => $templates
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.newstypeid' => 'required|integer',
			'fields.headline' => 'required|string|max:255',
			'fields.body' => 'required|string',
			'fields.published' => 'nullable|integer|in:0,1',
			'fields.template' => 'nullable|integer|in:0,1',
			'fields.datetimenews' => 'nullable|date',
			'fields.datetimenewsend' => 'nullable|date',
			'fields.location' => 'nullable|string|max:32',
			'fields.url' => 'nullable|url',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$fields = $request->input('fields');
		$fields['location'] = isset($fields['location']) ? (string)$fields['location'] : '';

		if (array_key_exists('datetimenews', $fields) && !trim($fields['datetimenews']))
		{
			unset($fields['datetimenews']);
		}
		if (array_key_exists('datetimenewsend', $fields) && !trim($fields['datetimenewsend']))
		{
			unset($fields['datetimenewsend']);
		}

		$id = $request->input('id');

		$row = $id ? Article::findOrFail($id) : new Article();
		$row->fill($fields);

		if (!$id)
		{
			$row->userid = auth()->user()->id;
		}
		else
		{
			$row->edituserid = auth()->user()->id;
		}

		if (!$row->type)
		{
			return redirect()->back()->with('error', trans('news::news.error.invalid type'));
		}

		// Templates shouldn't have datetimes set
		if ($row->template)
		{
			//$row->datetimenews = null;
			//$row->datetimenewsend = null;
		}
		else
		{
			if (!$row->hasStart())
			{
				$row->datetimenews = Carbon::now();
			}

			if ($row->datetimenewsend && $row->datetimenews > $row->datetimenewsend)
			{
				return redirect()->back()->with('error', trans('news::news.error.invalid time range'));
			}
		}

		if ($row->url && !filter_var($row->url, FILTER_VALIDATE_URL))
		{
			return redirect()->back()->with('error', trans('news::news.error.invalid url'));
		}
		
		if (!$row->save())
		{
			return redirect()->back()->with('error', trans('news::news.error.Failed to create item.'));
		}

		if ($request->has('resources'))
		{
			$row->setResources($request->input('resources'));
		}

		if ($request->has('associations'))
		{
			$row->setAssociations($request->input('associations'));
		}

		if ($row->published && !$row->template)
		{
			if (!$id && in_array($row->newstypeid, config('module.news.notify_create', [])))
			{
				$row->notify(new ArticleCreated($row));
			}
			elseif (in_array($row->newstypeid, config('module.news.notify_update', [])))
			{
				$row->notify(new ArticleUpdated($row));
			}
		}

		return $this->cancel($row->template)->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request  $request
	 * @param  int  $id
	 * @return RedirectResponse
	 */
	public function state(Request $request, $id)
	{
		$action = $request->segment(count($request->segments()) - 1);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'news::news.select to publish' : 'news::news.select to unpublish'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Article::findOrFail(intval($id));

			if ($row->published == $state)
			{
				continue;
			}

			// Don't update last modified timestamp for state changes
			$row->timestamps = false;

			if (!$row->update(['published' => $state]))
			{
				$request->session()->flash('error', trans('global.messages.save failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'global.messages.item published'
				: 'global.messages.item unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Remove the specified entry
	 *
	 * @param  Request  $request
	 * @return RedirectResponse
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Article::find($id);

			if (!$row)
			{
				continue;
			}

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Copy specified items
	 *
	 * @param  Request  $request
	 * @return RedirectResponse
	 */
	public function copy(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);
		$s = $request->input('start');
		//$days = $request->input('days');

		$success = 0;

		$now = Carbon::now();

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Article::findOrFail($id);

			$start = Carbon::parse($s . ' ' . $row->datetimenews->format('H:i:s'));

			$end = Carbon::parse($s . ' ' . $row->datetimenews->format('H:i:s'));
			$end->modify('+ ' . ($row->datetimenewsend->timestamp - $row->datetimenews->timestamp) . ' seconds');

			//for ($i = 1; $i <= $days; $i++)
			//{
				$payload = new Article;
				$payload->datetimenews    = $start;
				$payload->datetimenewsend = $end;
				$payload->datetimecreated = $now;
				$payload->userid          = $row->userid;
				$payload->edituserid      = $row->edituserid;
				$payload->published       = 1;
				$payload->headline        = $row->headline;
				$payload->body            = $row->body;
				$payload->location        = $row->location;
				$payload->template        = $row->template;
				$payload->newstypeid      = $row->newstypeid;
				$payload->url             = $row->url;
				if ($payload->save())
				{
					$success++;
				}
				//echo $id . ': ' . $payload->datetimenews . ' to ' . $payload->datetimenewsend . '<br />';
				//$start->modify('+1 day');
			//}
		}

		if ($success)
		{
			$request->session()->flash('success', trans('news::news.item copied', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @param  int $template
	 * @return  RedirectResponse
	 */
	public function cancel($template = 0): RedirectResponse
	{
		return redirect(route('admin.news.' . ($template ? 'templates' : 'index')));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function stats(Request $request): View
	{
		$start = Carbon::now()->modify('-1 year'); //30 days
		$today = Carbon::now()->modify('+1 day');

		// Get filters
		$filters = array(
			'start' => $start->format('Y-m-d'),
			'end'   => $today->format('Y-m-d'),
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		$stats = null;//Article::stats($filters['start'], $filters['end']);

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('news::admin.articles.stats', [
			'types' => $types,
			'filters' => $filters,
			'stats' => $stats,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function restore(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Article::query()
				->withTrashed()
				->where('id', '=', $id)
				->first();

			if ($row && $row->trashed())
			{
				if (!$row->restore())
				{
					$request->session()->flash('error', trans('global.messages.restore failed'));
					continue;
				}
				else
				{
					$success++;
				}
			}
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item restored', ['count' => $success]));
		}

		return $this->cancel();
	}
}
