<?php

namespace App\Modules\Media\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Arr;
use App\Modules\Media\Helpers\MediaHelper;
use App\Modules\Media\Events\DirectoryCreating;
use App\Modules\Media\Events\DirectoryCreated;
use App\Modules\Media\Events\DirectoryUpdating;
use App\Modules\Media\Events\DirectoryUpdated;
use App\Modules\Media\Events\DirectoryDeleting;
use App\Modules\Media\Events\DirectoryDeleted;

/**
 * Folder
 *
 * @apiUri    /media/folder
 */
class FolderController extends Controller
{
	/**
	 * Display a listing of files
	 *
	 * @apiMethod GET
	 * @apiUri    /media/folder
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
	 * @return JsonResponse
	 */
	public function read(Request $request): JsonResponse
	{
		$disk = $request->input('disk', 'public');
		$path = MediaHelper::sanitizePath($request->input('path', '/'));

		$content = Storage::disk($disk)->listContents($path);
		$content = Arr::where($content, function ($item)
		{
			return substr($item['path'], 0, 1) !== '.';
		});

		return response()->json([
			'data' => $content
		]);
	}

	/**
	 * Create a directory
	 *
	 * @apiMethod POST
	 * @apiUri    /media/folder
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "disk",
	 * 		"description":   "Storage disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "public"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "path",
	 * 		"description":   "Path to create new directory in",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Folder name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function create(Request $request): JsonResponse
	{
		event($event = new DirectoryCreating($request));

		$disk   = $event->disk();
		$name   = MediaHelper::sanitizePath($event->name());
		$parent = MediaHelper::sanitizePath($event->path());

		if (!$name)
		{
			return response()->json(['message' => trans('media::media.error.missing directory name')], 415);
		}

		$path = ($parent ? $parent . '/' : '') . $name;

		// Check if directory already exists
		if (!Storage::disk($disk)->exists($path))
		{
			// Create new directory
			if (Storage::disk($disk)->makeDirectory($path))
			{
				event(new DirectoryCreated(
					$disk,
					$name,
					$parent
				));
			}
		}

		return response()->json([
			'path' => $path,
			'data' => Storage::disk($disk)->listContents($path)
		]);
	}

	/**
	 * Update a directory
	 *
	 * @apiMethod PUT
	 * @apiUri    /media/folder
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "disk",
	 * 		"description":   "Storage disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "public"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "before",
	 * 		"description":   "Original folder path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "after",
	 * 		"description":   "Renamed folder path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function update(Request $request): JsonResponse
	{
		event($event = new DirectoryUpdating($request));

		$disk   = $event->disk();
		$before = MediaHelper::sanitizePath($event->before());
		$after  = MediaHelper::sanitizePath($event->after());

		if (!$before || !$after)
		{
			return response()->json(['message' => trans('media::media.error.invalid directory name')], 415);
		}

		// Does the source directory exists?
		if (!Storage::disk($disk)->exists($before))
		{
			return response()->json(['message' => trans('media::media.error.missing source directory')], 415);
		}

		// Douse the destination directory already exists?
		if (Storage::disk($disk)->exists($after))
		{
			return response()->json(['message' => trans('media::media.error.destination exists')], 415);
		}

		// Rename directory
		Storage::disk($disk)->move($before, $after);

		event(new DirectoryUpdated(
			$disk,
			$before,
			$after
		));

		return response()->json([
			'path' => $after,
			//'url'  => Storage::disk($disk)->url($path),
			'data' => Storage::disk($disk)->listContents($after)
		]);
	}

	/**
	 * Delete a folder
	 *
	 * @apiMethod DELETE
	 * @apiUri    /media/folder/delete
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "disk",
	 * 		"description":   "Storage disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "public"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "path",
	 * 		"description":   "Folder path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function delete(Request $request): JsonResponse
	{
		event($event = new DirectoryDeleting($request));

		// Get some data from the request
		$disk = $event->disk();
		$path = MediaHelper::sanitizePath($event->path());

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
}
