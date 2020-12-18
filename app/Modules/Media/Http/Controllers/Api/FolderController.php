<?php

namespace App\Modules\Media\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Arr;
use App\Modules\Media\Helpers\MediaHelper;
use App\Modules\Media\Events\DirectoryCreating;
use App\Modules\Media\Events\DirectoryCreated;
use App\Modules\Media\Events\DirectoryDeleting;
use App\Modules\Media\Events\DirectoryDeleted;

/**
 * Folder
 *
 * @apiUri    /api/media/folder
 */
class FolderController extends Controller
{
	/**
	 * Display a listing of files
	 *
	 * @apiMethod GET
	 * @apiUri    /api/media
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "path",
	 * 		"description":   "Path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function read(Request $request)
	{
		$disk = $request->input('disk');
		$path = $request->input('path');

		$content = Storage::disk($disk)->listContents($path);

		$content = Arr::where($content, function ($item)
		{
			return substr($item['path'], 0, 1) !== '.';
		});

		return response()->json(['data' => $content]);
	}

	/**
	 * Create a directory
	 *
	 * @apiMethod POST
	 * @apiUri    /api/media
	 * @apiParameter {
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		event($event = new DirectoryCreating($request));

		$disk   = $event->disk();
		$folder = trim($event->name(), '/');
		$parent = trim($event->path(), '/');

		if (!$folder)
		{
			return response()->json(['message' => trans('media::media.error.missing directory name')], 415);
		}

		$path = ($parent ? $parent . '/' : '') . $folder;
		$path = $this->sanitize($path);

		if (!$path)
		{
			return response()->json(['message' => trans('media::media.error.invalid directory name')], 415);
		}

		// Check if directory already exists
		if (!Storage::disk($disk)->exists($path))
		{
			// Create new directory
			if (Storage::disk($disk)->makeDirectory($path))
			{
				event(new DirectoryCreated($request));
			}
		}

		return response()->json([
			'path' => $path,
			'data' => Storage::disk($disk)->listContents($path)
		]);
	}

	/**
	 * Create a directory
	 *
	 * @param  Request $request
	 * @return  Response
	 */
	public function update(Request $request)
	{
		event($event = new DirectoryUpdating($request));

		$disk   = $event->disk();
		$before = trim($event->before(), '/');
		$after  = trim($event->after(), '/');

		if (!$before || !$after)
		{
			return response()->json(['message' => trans('media::media.error.missing directory name')], 415);
		}

		$before = $this->sanitize($before);
		$after  = $this->sanitize($after);

		if (!$before || !$after)
		{
			return response()->json(['message' => trans('media::media.error.invalid directory name')], 415);
		}

		if (!Storage::disk($disk)->exists($before))
		{
			return response()->json(['message' => trans('media::media.error.missing source directory')], 415);
		}

		if (Storage::disk($disk)->exists($after))
		{
			return response()->json(['message' => trans('media::media.error.destination exists')], 415);
		}

		// Create new directory
		Storage::disk($disk)->move($before, $after);

		event(new DirectoryUpdated($request));

		return response()->json([
			'path' => $after,
			//'url'  => Storage::disk($disk)->url($path),
			'data' => Storage::disk($disk)->listContents($after)
		]);
	}

	/**
	 * Delete a file
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/media/{file}
	 * @apiParameter {
	 * 		"name":          "path",
	 * 		"description":   "File path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		event($event = new DirectoryDeleting($request));

		// Get some data from the request
		$disk   = $event->disk();
		//$folder = trim($event->name(), '/');
		$path = trim($event->path(), '/');

		//$path = ($parent ? $parent . '/' : '') . $folder;
		$path = $this->sanitize($path);

		// Nothing to delete
		if (empty($path))
		{
			return response()->json(['message' => trans('media::media.error.missing directory name')], 415);
		}

		// Check if directory exists
		if (Storage::disk($disk)->exists($path))
		{
			$content = Storage::disk($disk)->allFiles($path);
			$content = array_filter($content, function($file)
			{
				return !in_array(basename($file), array('.svn', 'CVS', '.DS_Store', '__MACOSX', '.git', '.gitignore'));
			});

			if (!empty($content))
			{
				return response()->json(['message' => trans('media::media.error.directory not empty')], 415);
			}

			if (!Storage::disk($disk)->deleteDirectory($path))
			{
				return response()->json(['message' => trans('media::media.error.directory delete failed')], 500);
			}

			event($event = new DirectoryDeleted($disk, [$path]));
		}

		return response()->json(null, 204);
	}

	/**
	 * Sanitize a path
	 *
	 * @param   string  $path
	 * @return  string
	 */
	private function sanitize($path)
	{
		$path = str_replace(' ', '_', $path);
		$path = preg_replace('/[^a-zA-Z0-9\-_\/]+/', '', $path);

		if (!preg_match('/^[\x20-\x7e]*$/', $path))
		{
			$path = \Illuminate\Support\Facades\Str::ascii($path);
		}

		return $path;
	}
}
