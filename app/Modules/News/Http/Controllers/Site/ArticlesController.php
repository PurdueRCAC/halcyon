<?php

namespace App\Modules\News\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Newsresource;
use App\Modules\Resources\Models\Asset;
use App\Modules\Users\Models\User;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;

class ArticlesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{
		$types = Type::query()
			->where('name', 'NOT LIKE', 'coffee%')
			->orderBy('name', 'asc')
			->get();

		return view('news::site.index', [
			'types' => $types
		]);
	}

	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function search(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'resource'  => null,
			'keyword'   => null,
			'newstype'  => null,
			'start'     => null,
			'stop'      => null,
			'location'  => null,
			'id'        => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			//'order'     => 'datetimecreated',
			//'order_dir' => 'desc',
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		/*if (!in_array($filters['order'], ['id', 'headline', 'datetimecreated']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}*/

		$types = Type::query()
			->where('name', 'NOT LIKE', 'coffee%')
			->orderBy('name', 'asc')
			->get();

		return view('news::site.search', [
			'types' => $types,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function rss()
	{
		$types = Type::query()
			->where('name', 'NOT LIKE', 'coffee%')
			->orderBy('name', 'asc')
			->get();

		return view('news::site.rss', [
			'types' => $types
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function feed(Request $request, $name = null)
	{
		$parts = explode(',', $name);

		$types = array();
		$resources = array();

		if (count($parts))
		{
			foreach ($parts as $id)
			{
				if (is_numeric($id))
				{
					$row = Type::find($id);

					if (!$row)
					{
						continue;
					}

					$types[] = $row;
				}
				else
				{
					$row = Type::findByName($id);

					if ($row)
					{
						$types[] = $row;
						continue;
					}

					// Check search terms against resources.
					$row = Asset::findByName($id);

					$resources[] = $row;
				}
			}
		}

		// If there is no matching newstype, just display all of them.
		if (!count($types))
		{
			$types = Type::all();
		}

		$types = collect($types);
		$typeids = $types->pluck('id')->toArray();

		$resources = collect($resources);
		$resourceids = $resources->pluck('id')->toArray();

		$query = Article::query();

		if (count($resources))
		{
			$query->whereResourceIn($resourceids);
		}

		$items = $query
			->wherePublished()
			->whereIn('newstypeid', $typeids)
			->orderBy('datetimenews', 'desc')
			->limit(20)
			->paginate();

		$meta = array(
			'title'         => config('app.name') . ' - ' . implode(', ', $types->pluck('name')->toArray()),
			'url'           => $request->url(),
			'description'   => trans('news::news.feed description', [':category' => implode(', ', $types->pluck('name')->toArray())]),
			'language'      => app('translator')->locale(),
			'lastBuildDate' => Carbon::now()->format('D, d M Y H:i:s T'),
		);

		$contents = view('news::site.feed', [
			'meta'  => $meta,
			'items' => $items,
		]);

		return new Response($contents, 200, [
			'Content-Type' => 'application/xml;charset=UTF-8',
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function manage(Request $request)
	{
		$filters = array(
			'search'    => null,
			'resource'  => null,
			'keyword'   => null,
			'newstype'  => null,
			'start'     => null,
			'stop'      => null,
			'location'  => null,
			'id'        => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			//'order'     => 'datetimecreated',
			//'order_dir' => 'desc',
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		$types = Type::query()->orderBy('name', 'asc')->get();

		$templates = Article::where('template', '=', 1)->where('published', '=', 1)->get();

		app('pathway')
			->append(
				config('news.name'),
				route('site.news.index')
			)
			->append(
				trans('news::news.manage news'),
				route('site.news.manage')
			);

		return view('news::site.manage', [
			'types' => $types,
			'templates' => $templates,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the specified entry
	 *
	 * @param   string  $name
	 * @return  Response
	 */
	public function type($name, Request $request)
	{
		$row = Type::findByName($name);

		if (!$row)
		{
			$resource = Asset::findByName($name);

			if (!$resource)
			{
				abort(404);
			}

			$row = new Type;
			$row->name = $resource->name;

			$r = (new Newsresource)->getTable();
			$a = (new Article)->getTable();

			$query = Article::query()
				->select($a . '.*')
				->wherePublished()
				->join($r, $r . '.newsid', $a . '.id')
				->where($r . '.resourceid', '=', $resource->id)
				->where($a . '.template', '=', 0);
		}
		else
		{
			$query = $row->articles()
				->wherePublished()
				->where('template', '=', 0);
		}

		if ($request->has('start'))
		{
			$start = Carbon::parse($request->input('start'));
			$query->where('datetimenews', '>', $start->toDateTimeString());
		}

		if ($request->has('stop'))
		{
			$stop = $request->input('stop');
			$query->where(function($where) use ($stop)
			{
				$stop = Carbon::parse($stop);
				$where->whereNull('datetimenewsend')
					->orWhere('datetimenewsend', '=', '0000-00-00 00:00:00')
					->orWhere('datetimenewsend', '<=', $stop->toDateTimeString());
			});
		}

		if ($request->has('resource'))
		{
			$r = (new Newsresource)->getTable();
			$n = (new Article)->getTable();

			$resource = explode(',', $request->input('resource'));
			$resource = array_map('trim', $resource);

			$query->join($r, $r . '.newsid', $n . '.id')
				->whereIn($r . '.resourceid', $resource);
		}

		$articles = $query
			->orderBy('datetimenews', 'desc')
			->limit(20)
			->paginate();

		$types = Type::query()
			->where('name', 'NOT LIKE', 'coffee%')
			->orderBy('name', 'asc')
			->get();

		return view('news::site.type', [
			'type' => $row,
			'types' => $types,
			'articles' => $articles
		]);
	}

	/**
	 * iCal requires lines be no longer than 75 characters
	 *
	 * @param   string  $preamble
	 * @param   string  $value
	 * @return  string
	 */
	private function icalSplit($preamble, $value)
	{
		$value = trim($value);
		$value = strip_tags($value);
		$value = preg_replace('/\n+/', ' ', $value);
		$value = preg_replace('/\s{2,}/', ' ', $value);

		$preamble_len = strlen($preamble);
		$lines = array();

		while (strlen($value) > (75-$preamble_len))
		{
			$space = (75-$preamble_len);
			$mbcc = $space;
			while ($mbcc)
			{
				$line = mb_substr($value, 0, $mbcc);
				$oct = strlen($line);
				if ($oct > $space)
				{
					$mbcc -= $oct-$space;
				}
				else
				{
					$lines[] = $line;
					$preamble_len = 1; // Still take the tab into account
					$value = mb_substr($value, $mbcc);
					break;
				}
			}
		}

		if (!empty($value))
		{
			$lines[] = $value;
		}

		return join($lines, "\n\t");
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function calendar($search)
	{
		$search = urldecode($search);

		$n = Carbon::now();
		$org = 'ITaP';
		$name = 'Research Computing';

		if (is_numeric($search))
		{
			$news = Article::find($search);

			if (!$news)
			{
				abort(404);
			}

			$name .= ' - ' . $search;

			$events = array((array)$news);

			$file = str_replace(' ', '_', $org . ' ' . $name);
		}
		else
		{
			$type = Type::findByName($search);

			if (!$type)
			{
				abort(404);
			}

			$name .= ' ' . $type->name;

			$events = $type->articles()
				->wherePublished()
				->where('datetimenews', '>=', $n->modify('-1 year')->format('Y-m-d') . ' 00:00:00')
				->get();

			$file = str_replace(' ', '_', $org . ' ' . $name);
		}

		// Don't include timezone if we're including the timezone block below
		$now = $n->format('Ymd\THis'); // 'Ymd\THis\Z'

		// Create output
		$output  = "BEGIN:VCALENDAR\r\n";
		$output .= "VERSION:2.0\r\n";
		$output .= "PRODID:-//$org//$name//EN\r\n";
		$output .= "METHOD:PUBLISH\r\n";
		$output .= "X-WR-CALNAME;VALUE=TEXT:$org $name\r\n";
		$output .= "X-PUBLISHED-TTL:PT15M\r\n";
		$output .= "X-ORIGINAL-URL:" . route('site.news.calendar', ['name' => $search]) . "\r\n";
		$output .= "CALSCALE:GREGORIAN\r\n";

		// Get event timezone setting
		// use this in "DTSTART;TZID="
		$tzName = date_default_timezone_get(); // America/Indianapolis

		$timezone = new DateTimeZone($tzName);
		$year = date('Y');

		$transitions = $timezone->getTransitions(mktime(0, 0, 0, 2, 1, $year), mktime(0, 0, 0, 11, 31, $year));
		$transitions = $transitions ? array_slice($transitions, 1, 2) : array();

		$dst = array(
			'start' => null,
			'startoffset' => '-0000',
			'stop' => null,
			'stopoffset' => '-0000'
		);
		foreach ($transitions as $transition)
		{
			$tm = new DateTime($transition['time']);

			if ($transition['isdst'])
			{
				$dst['start'] = $tm->format('Ymd\This');
				$dst['startoffset'] = '-0' . (abs($transition['offset']) / 60 / 60) . '00';
			}
			else
			{
				$dst['stop'] = $tm->format('Ymd\This');
				$dst['stopoffset'] = '-0' . (abs($transition['offset']) / 60 / 60) . '00';
			}
		}

		// Include timezone info so DST is handled correctly
		$output .= "BEGIN:VTIMEZONE\r\n";
		$output .= "TZID:" . $tzName . "\r\n";
		$output .= "LAST-MODIFIED:20050809T050000Z\r\n";

		$output .= "BEGIN:STANDARD\r\n";
		$output .= "DTSTART:" . $dst['stop'] . "\r\n";
		$output .= "TZOFFSETFROM:" . $dst['startoffset'] . "\r\n";
		$output .= "TZOFFSETTO:" . $dst['stopoffset'] . "\r\n";
		$output .= "TZNAME:EST\r\n";
		$output .= "END:STANDARD\r\n";

		$output .= "BEGIN:DAYLIGHT\r\n";
		$output .= "DTSTART:" . $dst['start'] . "\r\n";
		$output .= "TZOFFSETFROM:" . $dst['stopoffset'] . "\r\n";
		$output .= "TZOFFSETTO:" . $dst['startoffset'] . "\r\n";
		$output .= "TZNAME:EDT\r\n";
		$output .= "END:DAYLIGHT\r\n";

		$output .= "END:VTIMEZONE\r\n";

		foreach ($events as $event)
		{
			$sequence = 0;
			$id       = $event->id;
			$uid      = $id . '@' . route('site.news.index');
			$title    = $event->headline;
			$content  = str_replace("\r\n", '\n', $event->news);
			$content  = str_replace("\n", '\n', $content);
			$location = $event->location;
			$url      = route('site.news.show', ['id' => $id]);
			$allDay   = 0;

			// Get publish up/down dates
			$dtStart = "DTSTART;TZID={$tzName}:" . $event->datetimenews->format('Ymd\THis');
			$created = $event->datetimecreated->format('Ymd\THis'); // 'Ymd\THis\Z'

			// Start output
			$output .= "BEGIN:VEVENT\r\n";
			$output .= "UID:{$uid}\r\n";
			//$output .= "SEQUENCE:{$sequence}\r\n";
			$output .= "DTSTAMP:{$now}\r\n";
			$output .= $dtStart  . "\r\n";
			if ($event->hasEnd())
			{
				$output .= "DTEND;TZID={$tzName}:" . $event->datetimenewsend->format('Ymd\THis') . "\r\n";
			}
			else
			{
				$output .= "DTEND;TZID={$tzName}:" . $event->datetimenews->format('Ymd\THis') . "\r\n";
			}

			$output .= "CREATED:{$created}\r\n";
			if ($event->isModified())
			{
				$modified = $event->datetimeedited->format('Ymd\THis'); // 'Ymd\THis\Z'

				$output .= "LAST-MODIFIED:{$modified}\r\n";
			}
			else
			{
				$output .= "LAST-MODIFIED:{$created}\r\n";
			}

			$output .= 'SUMMARY:' . $this->icalSplit('SUMMARY:', $title) . "\r\n";
			$output .= 'DESCRIPTION:' . $this->icalSplit('DESCRIPTION:', $content) . "\r\n";

			// Do we have url?
			if ($url && filter_var($url, FILTER_VALIDATE_URL))
			{
				$output .= "URL;VALUE=URI:{$url}\r\n";
			}

			// Do we have a location?
			if ($location)
			{
				$output .= "LOCATION:{$location}\r\n";
			}

			// Do we have associated users?
			if (count($event->associations))
			{
				foreach ($event->associations as $association)
				{
					if ($association->assoctype == 'user')
					{
						$user = User::find($association->associd);

						if (!$user)
						{
							continue;
						}

						$output .= "ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=TENTATIVE;CN=" . $user->name . "\r\n";
						$output .= "\t:MAILTO:" . $user->mail . "\r\n";
					}
				}
			}

			$output .= "END:VEVENT\r\n";
		}

		// Close calendar
		$output .= "END:VCALENDAR\r\n";

		// Set headers and output
		return new Response($output, 200, [
			'Content-Type' => 'text/calendar;charset=UTF-8',
			'Content-Disposition' => 'attachment; filename="' . $file . '.ics"',
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
		]);
	}

	/**
	 * Show the specified entry
	 *
	 * @param   string  $name
	 * @return  Response
	 */
	public function coffee()
	{
		$row = Type::findByName('coffee');

		if (!$row)
		{
			abort(404);
		}

		app('pathway')
			->append(
				config('news.name'),
				route('site.news.index')
			)
			->append(
				$row->name,
				route('site.news.type', ['name' => 'coffee'])
			);

		return view('news::site.coffee', [
			'type' => $row
		]);
	}

	/**
	 * Show the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function show($id)
	{
		$row = Article::findOrFail($id);

		$types = Type::query()
			->where('name', 'NOT LIKE', 'coffee%')
			->orderBy('name', 'asc')
			->get();

		return view('news::site.article', [
			'article' => $row,
			'types' => $types
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit()
	{
		$id = 1;

		app('pathway')
			->append(
				config('resources.name'),
				url('/resources')
			)
			->append(
				trans('resources::assets.edit'),
				route('site.news.edit', ['id' => $id])
			);

		return view('news::site.edit');
	}
}
