<?php

namespace App\Modules\Media\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use App\Modules\Media\Helpers\MediaHelper;
use App\Modules\Media\Events\Updating;
use App\Modules\Media\Events\Updated;
use App\Modules\Media\Events\Deleting;
use App\Modules\Media\Events\Deleted;
use App\Modules\Media\Events\Download;
use App\Modules\Media\Events\FilesUploading;
use App\Modules\Media\Events\FilesUploaded;

/**
 * Media
 *
 * @apiUri    /media
 */
class MediaController extends Controller
{
	/**
	 * Display a listing of files
	 *
	 * @apiMethod GET
	 * @apiUri    /media
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$disk = $request->input('disk', 'public');
		$path = $request->input('path', '/');

		$content = Storage::disk($disk)->listContents($path);

		$content = \Arr::where($content, function ($item)
		{
			return substr($item['path'], 0, 1) !== '.';
		});

		return response()->json(['data' => $content]);
	}

	/**
	 * Get directory tree
	 *
	 * @apiMethod GET
	 * @apiUri    /media/tree
	 * @param  Request $request
	 * @return Response
	 */
	public function tree(Request $request)
	{
		/*$disk = $request->input('disk');

		$content = Storage::disk($disk)->listContents('/');

		$dirsList = \Arr::where($content, function ($item)
		{
			return $item['type'] === 'dir';
		});

		// remove 'filename' param
		$dirs = array_map(function ($item)
		{
			return \Arr::except($item, ['filename']);
		}, $dirsList);

		foreach ($dirs as $index => $dir)
		{
			$dirs[$index]['props'] = [
				'hasSubdirectories' => Storage::disk($disk)->directories($dir['path']) ? true : false,
			];
		}

		$content = [
			'result'      => [
				'status'  => 'success',
				'message' => null,
			],
			'data' => array_values($dirs) //MediaHelper::_buildFolderTree($folders, -1),
		];*/
		$base = storage_path('app/public');

		$folder = $request->input('folder', '');
		$folders = MediaHelper::getTree($base);

		$fold = array(
			'id'       => 0,
			'parent'   => -1,
			'name'     => '', //basename($base),
			'fullname' => $base,
			'relname'  => substr($base, strlen(storage_path()))
		);

		array_unshift($folders, $fold);

		$content = MediaHelper::_buildFolderTree($folders, -1);

		return response()->json(['data' => $content]);
	}

	/**
	 * Get directory tree
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function layout(Request $request)
	{
		$layout = $request->input('layout');

		if (auth()->user())
		{
			session()->put('media.layout', $layout);

			return response()->json(['data' => $layout]);
		}

		return response()->json(null, 204);
	}

	/**
	 * Upload
	 *
	 * @apiUri    /media/upload
	 * @apiParameter {
	 * 		"name":          "disk",
	 * 		"description":   "Filesystem disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "path",
	 * 		"description":   "File path to upload the file to",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "file",
	 * 		"description":   "File to be uploaded",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "overwrite",
	 * 		"description":   "Overwrite existing file of the same name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "bool"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function upload(Request $request)
	{
		event(new FilesUploading($request));

		$disk = $request->input('disk', 'public');
		$path = $request->input('path');
		$files = $request->file();
		$overwrite = $request->input('overwrite', true);

		if (empty($files))
		{
			return response()->json('No files submitted', 415);
		}

		$fileNotUploaded = false;

		foreach ($files as $file)
		{
			// Skip or overwrite files
			if (!$overwrite
			 && Storage::disk($disk)->exists($path . '/' . $file->getClientOriginalName()))
			{
				continue;
			}

			// Check file size
			$maxSize = config('module.media.max-file-size', 0);

			if (($maxSize && $file->getSize() / 1024 > $maxSize)
			 || $file->getSize() / 1024 > $file->getMaxFilesize())
			{
				$fileNotUploaded = true;
				continue;
			}

			/*if (($maxSize && $file->getSize() > ($maxSize * 1024 * 1024))
			 || $file->getSize() > ((int)ini_get('upload_max_filesize') * 1024 * 1024)
			 || $file->getSize() > ((int)ini_get('post_max_size') * 1024 * 1024)
			 || ($file->getSize() > ((int)ini_get('memory_limit') * 1024 * 1024) && ((int) ini_get('memory_limit') != -1)))
			{
				$fileNotUploaded = true;
				continue;
			}*/

			// Check allowed file type
			$allowedTypes = config('module.media.allowed-extensions', []);

			if (!empty($allowedTypes)
			 && !in_array(
				$file->getClientOriginalExtension(),
				$this->configRepository->getAllowFileTypes()
			))
			{
				$fileNotUploaded = true;
				continue;
			}

			// Overwrite or save file
			Storage::disk($disk)->putFileAs(
				$path,
				$file,
				$file->getClientOriginalName()
			);
		}

		event(new FilesUploaded($request));

		$response = [
			'data' => Storage::disk($disk)->listContents($path)
		];

		if ($fileNotUploaded)
		{
			$response['message'] = trans('media::media.not all uploaded');
		}

		return response()->json($response);
	}

	/**
	 * Rename/move a file
	 *
	 * @apiUri    /media/rename
	 * @apiUri    /media/move
	 * @apiParameter {
	 * 		"name":          "disk",
	 * 		"description":   "Filesystem disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "before",
	 * 		"description":   "Source file path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "after",
	 * 		"description":   "Destination file path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update(Request $request)
	{
		/*$disk = $request->input('disk');
		$path = $request->input('path');
		$name = $request->input('name');

		$name = MediaHelper::sanitize($name);

		$source = $path;
		$dest   = dirname($path) . '/' . $name;

		// check all files and folders - exists or no
		if (!Storage::disk($disk)->exists($source))
		{
			return response()->json('File not found', 404);
		}

		if (!Storage::disk($disk)->exists($dest))
		{
			return response()->json('Destination already exists', 404);
		}

		Storage::disk($disk)->move($source, $dest);

		return response()->json(null, 204);*/

		event($event = new Updating($request));

		$disk   = $event->disk();
		$before = trim($event->before(), '/');
		$after  = trim($event->after(), '/');

		if (!$before || !$after)
		{
			return response()->json(['message' => trans('media::media.error.missing name')], 415);
		}

		$before = MediaHelper::sanitize($before);
		$after  = MediaHelper::sanitize($after);

		if (!$before || !$after)
		{
			return response()->json(['message' => trans('media::media.error.invalid name')], 415);
		}

		if (!Storage::disk($disk)->exists($before))
		{
			return response()->json(['message' => trans('media::media.error.missing source' . $before)], 415);
		}

		if (Storage::disk($disk)->exists($after))
		{
			return response()->json(['message' => trans('media::media.error.destination exists')], 415);
		}

		// Rename directory
		Storage::disk($disk)->move($before, $after);

		event(new Updated($disk, $before, $after));

		return response()->json([
			'source' => $before,
			'path' => $after
		]);
	}

	/**
	 * Delete a file
	 *
	 * @apiUri    /media/delete
	 * @apiParameter {
	 * 		"name":          "disk",
	 * 		"description":   "Filesystem disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "items",
	 * 		"description":   "A list of file paths",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		event(new Deleting($request));

		$disk  = $request->input('disk', 'public');
		$items = $request->input('items');

		$deletedItems = [];

		foreach ($items as $item)
		{
			// check all files and folders - exists or no
			if (!Storage::disk($disk)->exists($item['path']))
			{
				continue;
			}

			if ($item['type'] === 'dir')
			{
				// delete directory
				Storage::disk($disk)->deleteDirectory($item['path']);
			}
			else
			{
				// delete file
				Storage::disk($disk)->delete($item['path']);
			}

			// add deleted item
			$deletedItems[] = $item;
		}

		event(new Deleted($disk, $deletedItems));

		return response()->json(null, 204);
	}

	/**
	 * Download a file
	 *
	 * @apiMethod GET
	 * @apiUri    /media/download
	 * @apiParameter {
	 * 		"name":          "disk",
	 * 		"description":   "Filesystem disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "path",
	 * 		"description":   "File path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function download(Request $request)
	{
		event(new Download($request));

		$disk = $request->input('disk', 'public');
		$path = $request->input('path');

		// If file name not in ASCII format
		if (!preg_match('/^[\x20-\x7e]*$/', basename($path)))
		{
			$filename = \Illuminate\Support\Facades\Str::ascii(basename($path));
		}
		else
		{
			$filename = basename($path);
		}

		// Get some data from the request
		return Storage::disk($disk)->download($path, $filename);
	}

	/**
	 * Get file URL
	 *
	 * @apiMethod GET
	 * @apiUri    /media/url
	 * @apiParameter {
	 * 		"name":          "disk",
	 * 		"description":   "Filesystem disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "path",
	 * 		"description":   "File path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function url(Request $request)
	{
		$disk = $request->input('disk', 'public');
		$path = $request->input('path');

		return response()->json([
			'result' => [
				'status'  => 'success',
				'message' => null,
			],
			'url'    => Storage::disk($disk)->url($path),
		]);
	}

	/**
	 * Sanitize a path
	 *
	 * @param   string  $path
	 * @return  string
	 */
	/*private function sanitize($path)
	{
		$path = str_replace(' ', '_', $path);
		$path = preg_replace('/[^a-zA-Z0-9\-_\/\.]+/', '', $path);

		if (!preg_match('/^[\x20-\x7e]*$/', $path))
		{
			$path = \Illuminate\Support\Facades\Str::ascii($path);
		}

		return $path;
	}*/
}
