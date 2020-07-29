<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
 * Media controller
 */
class FolderController extends Controller
{
	/**
	 * Display a listing of files
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
	 * @return  void
	 */
	public function create(Request $request)
	{
		event($event = new DirectoryCreating($request));

		$disk   = $event->disk();
		$folder = trim($event->name(), '/');
		$parent = trim($event->path(), '/');

		if (!$folder)
		{
			return response()->json(['message' =>  'No directory name provided'], 415);
		}

		$path = ($parent ? $parent . '/' : '') . $folder;
		$path = $this->sanitize($path);

		if (!$path)
		{
			return response()->json(['message' =>  'Invalid directory name'], 415);
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
	 * @return  void
	 */
	public function update(Request $request)
	{
		event($event = new DirectoryUpdating($request));

		$disk   = $event->disk();
		$before = trim($event->before(), '/');
		$after  = trim($event->after(), '/');

		if (!$before || !$after)
		{
			return response()->json(['message' =>  'No directory name provided'], 415);
		}

		$before = $this->sanitize($before);
		$after  = $this->sanitize($after);

		if (!$before || !$after)
		{
			return response()->json(['message' =>  'Invalid directory name'], 415);
		}

		if (!Storage::disk($disk)->exists($before))
		{
			return response()->json(['message' =>  'Source directory not found'], 415);
		}

		if (Storage::disk($disk)->exists($after))
		{
			return response()->json(['message' =>  'Destination directory already exists'], 415);
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
	 * @return  void
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
			return response()->json(['message' =>  'No Directory provided'], 415);
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
				return response()->json(['message' => 'Directory not empty'], 415);
			}

			if (!Storage::disk($disk)->deleteDirectory($path))
			{
				return response()->json(['message' => 'Failed to delete the directory'], 500);
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
