<?php

namespace App\Modules\Publications\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Modules\Publications\Models\Type;
use App\Modules\Publications\Models\Publication;
use App\Modules\Publications\Helpers\Download;
use App\Halcyon\Http\StatefulRequest;
use Carbon\Carbon;

class PublicationsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'state'    => 'published',
			'type'     => null,
			'year'     => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Publication::$orderBy,
			'order_dir' => Publication::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('publications.filter_' . $key)
			 && $request->input($key) != session()->get('publications.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('publications.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'title', 'state', 'published_at']))
		{
			$filters['order'] = Publication::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Publication::$orderDir;
		}

		if (!auth()->user() || !auth()->user()->can('manage publications'))
		{
			$filters['state'] = 'published';
		}

		$types = Type::query()
			->orderBy('id', 'asc')
			->get();

		// Get records
		$query = Publication::query();

		if ($filters['state'] == 'published')
		{
			$query->where('state', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where('state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}

		if ($filters['year'] && $filters['year'] != '*')
		{
			$query->where('published_at', '>=', $filters['year'] . '-01-01 00:00:00')
				->where('published_at', '<', Carbon::parse($filters['year'] . '-01-01 00:00:00')->modify('+1 year')->format('Y') . '-01-01 00:00:00');
		}

		if ($filters['type'] && $filters['type'] != '*')
		{
			foreach ($types as $type)
			{
				if ($type->alias == $filters['type'])
				{
					$query->where('type_id', '=', $type->id);
					break;
				}
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$now = date("Y");
		$start = date("Y");
		$first = Publication::query()
			->orderBy('published_at', 'asc')
			->first();
		if ($first)
		{
			$start = $first->published_at->format('Y');
		}

		$years = array();
		for ($start; $start < $now; $start++)
		{
			$years[] = $start;
		}
		$years[] = $now;
		rsort($years);

		return view('publications::site.publications.index', [
			'rows' => $rows,
			'filters' => $filters,
			'types' => $types,
			'years' => $years,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		$row = new Publication();
		$row->state = 1;
		$row->published_at = Carbon::now();

		if ($fields = app('request')->old())
		{
			$row->fill($fields);
		}

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('publications::site.publications.edit', [
			'row' => $row,
			'types' => $types,
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		$row = Publication::findOrFail($id);

		if ($fields = app('request')->old())
		{
			$row->fill($fields);
		}

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('publications::site.publications.edit', [
			'row' => $row,
			'types' => $types,
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$rules = [
			'type_id' => 'required|integer|min:1',
			'title' => 'required|string|max:500',
			'author' => 'nullable|string|max:3000',
			'editor' => 'nullable|string|max:3000',
			'url' => 'nullable|string|max:2083',
			'series' => 'nullable|string|max:255',
			'booktitle' => 'nullable|string|max:1000',
			'edition' => 'nullable|string|max:100',
			'chapter' => 'nullable|string|max:40',
			'issuetitle' => 'nullable|string|max:255',
			'journal' => 'nullable|string|max:255',
			'issue' => 'nullable|string|max:40',
			'volume' => 'nullable|string|max:40',
			'number' => 'nullable|string|max:40',
			'pages' => 'nullable|string|max:40',
			'publisher' => 'nullable|string|max:500',
			'address' => 'nullable|string|max:300',
			'institution' => 'nullable|string|max:500',
			'organization' => 'nullable|string|max:500',
			'school' => 'nullable|string|max:200',
			'crossref' => 'nullable|string|max:100',
			'isbn' => 'nullable|string|max:50',
			'doi' => 'nullable|string|max:255',
			'note' => 'nullable|string|max:2000',
			'state' => 'nullable|integer',
			'published_at' => 'nullable|datetime',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Publication::findOrFail($id) : new Publication();
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->$key = $request->input($key);
			}
		}
		if ($request->has('year'))
		{
			$row->published_at = $request->input('year') . '-' . $request->input('month', '01') . ' -01 00:00:00';
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		if ($request->has('file'))
		{
			// Doing this by file extension is iffy at best but
			// detection by contents productes `txt`
			$file = $request->file('file');
			$filename = $file->getClientOriginalName();

			$parts = explode('.', $filename);
			$extension = end($parts);
			$extension = strtolower($extension);

			if (!in_array($extension, ['pdf', 'docx']))
			{
				return redirect()->back()->withError(trans('publications::publications.errors.invalid file type'));
			}

			$filename = str_replace(' ', '-', $filename);
			$filename = preg_replace('/[^a-zA-Z0-9\-\_\.]+/', '', $filename);

			$disk = $request->input('disk', 'public');
			$path = 'publications/' . $row->id; //$row->path(false);

			// Check if directory already exists
			if (!Storage::disk($disk)->exists($path))
			{
				// Create new directory
				if (!Storage::disk($disk)->makeDirectory($path))
				{
					return redirect()->back()->withError(trans('publications::publications.errors.failed to make upload directory'));
				}
			}

			Storage::disk($disk)->putFileAs(
				$path,
				$file,
				$filename
			);

			$row->filename = $filename;
			$row->save();
		}

		return redirect(route('site.publications.index'))->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Download a citation
	 *
	 * @return  string
	 */
	public function download(Request $request, $id)
	{
		$format = strtolower($request->input('format', 'bibtex'));

		if (!in_array($format, array('bibtex', 'endnote')))
		{
			abort(419);
		}

		$row = Publication::findOrFail($id);

		$formatter = new Download();
		$formatter->setFormat($format);

		// Set some vars
		$doc  = $formatter->formatReference($row);
		$mime = $formatter->getMimeType();
		$file = 'publication_' . $id . '.' . $formatter->getExtension();

		$headers = array(
			'Content-type' => $mime,
			'Content-Disposition' => 'attachment; filename=' . $file,
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0',
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
		);

		$callback = function() use ($doc)
		{
			$file = fopen('php://output', 'w');

			fputs($file, $doc);
			fclose($file);
		};

		return response()->streamDownload($callback, $file, $headers);
	}
}
