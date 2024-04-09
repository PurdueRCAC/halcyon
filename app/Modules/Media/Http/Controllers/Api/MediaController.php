<?php

namespace App\Modules\Media\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Arr;
use App\Modules\Media\Helpers\MediaHelper;
use App\Modules\Media\Helpers\ImageProcessor;
use App\Modules\Media\Events\Updating;
use App\Modules\Media\Events\Updated;
use App\Modules\Media\Events\Deleting;
use App\Modules\Media\Events\Deleted;
use App\Modules\Media\Events\DirectoryDeleted;
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
	 * 		"description":   "Which folder to get tree of",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "/"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function index(Request $request)
	{
		$disk = $request->input('disk', 'public');
		$path = MediaHelper::sanitizePath($request->input('path', '/'));

		$content = Storage::disk($disk)->listContents($path);
		$content = Arr::where($content, function ($item)
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
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "folder",
	 * 		"description":   "Which folder to get tree of",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function tree(Request $request)
	{
		$disk = $request->input('disk', 'public');

		/*$content = Storage::disk($disk)->listContents('/');

		$dirsList = Arr::where($content, function ($item)
		{
			return $item['type'] === 'dir';
		});

		// remove 'filename' param
		$dirs = array_map(function ($item)
		{
			return Arr::except($item, ['filename']);
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
		//$base = rtrim(Storage::disk('public')->path('/'), '/');
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
	 * @apiMethod GET
	 * @apiUri    /media/layout
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "layout",
	 * 		"description":   "Which view layout to return",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResponse
	 */
	public function layout(Request $request): JsonResponse
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
	 * @apiMethod POST
	 * @apiUri    /media/upload
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "disk",
	 * 		"description":   "Filesystem disk",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "path",
	 * 		"description":   "File path to upload the file to",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "file",
	 * 		"description":   "File to be uploaded",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "overwrite",
	 * 		"description":   "Overwrite existing file of the same name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "bool"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"415": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResponse
	 */
	public function upload(Request $request): JsonResponse
	{
		event(new FilesUploading($request));

		$disk = $request->input('disk', 'public');
		$path = $request->input('path', '/');
		$path = '/' . trim($path, '/');
		$files = $request->file();
		$overwrite = $request->input('overwrite', true);

		if (empty($files))
		{
			return response()->json(['message' => 'No files submitted'], 415);
		}

		$fileNotUploaded = false;

		foreach ($files as $file)
		{
			// Skip or overwrite files
			if (!$overwrite
			 && Storage::disk($disk)->exists(($path != '/' ? $path : '') . '/' . $file->getClientOriginalName()))
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
				$allowedTypes
			))
			{
				$fileNotUploaded = true;
				continue;
			}

			$name = $file->getClientOriginalName();
			if ($rename = $request->input('rename'))
			{
				$name = $rename . '.' . $file->getClientOriginalExtension();
			}

			// Overwrite or save file
			Storage::disk($disk)->putFileAs(
				$path,
				$file,
				$name
			);

			if ($resize = $request->input('resize'))
			{
				$final = Storage::disk($disk)->path(($path != '/' ? $path : '') . '/' . $name);

				// Resize image
				$hi = new ImageProcessor($final);
				$hi->autoRotate();
				$hi->resize($resize);
				//$hi->setImageType(IMAGETYPE_PNG);
				$hi->save($final);
			}
		}

		event(new FilesUploaded($request));

		$contents = Storage::disk($disk)->listContents($path);
		$data = array();
		foreach ($contents as $i => $content)
		{
			$parts = explode('/', $content['path']);
			$filename = end($parts);

			if (substr($filename, 0, 1) == '.')
			{
				continue;
			}

			$data[] = [
				'type'         => $content['type'],
				'path'         => $content['path'],
				'fileSize'     => $content->isFile() ? $content['fileSize'] : 0,
				'lastModified' => $content['lastModified'],
				'mimeType'     => $content->isFile() ? $content['mimeType'] : null,
				'url'          => asset('/files' . ($path != '/' ? $path : '') . '/' . $content['path'])
			];
		}

		$response = [
			'data' => $data,
			'uploaded' => true,
		];

		if ($fileNotUploaded)
		{
			$response['uploaded'] = false;
			$response['message'] = trans('media::media.not all uploaded');
		}

		return response()->json($response);
	}

	/**
	 * Rename/move a file
	 *
	 * @apiMethod PUT
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
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"422": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResponse
	 */
	public function update(Request $request): JsonResponse
	{
		event($event = new Updating($request));

		$disk   = $event->disk();
		$before = trim($event->before(), '/');
		$after  = trim($event->after(), '/');

		if (!$before || !$after)
		{
			return response()->json(['message' => trans('media::media.error.missing name')], 415);
		}

		$before = MediaHelper::sanitizePath($before);
		$after  = MediaHelper::sanitizePath($after);

		if (!$before || !$after)
		{
			return response()->json(['message' => trans('media::media.error.invalid name')], 422);
		}

		if (!Storage::disk($disk)->exists($before))
		{
			return response()->json(['message' => trans('media::media.error.missing source', ['source' => $before])], 422);
		}

		if (Storage::disk($disk)->exists($after))
		{
			return response()->json(['message' => trans('media::media.error.destination exists')], 422);
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
	 * @apiMethod DELETE
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
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful deletion"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResponse
	 */
	public function delete(Request $request): JsonResponse
	{
		event(new Deleting($request));

		$disk  = $request->input('disk', 'public');
		$items = $request->input('items');

		$deletedItems = [];

		foreach ($items as $item)
		{
			$item['path'] = MediaHelper::sanitizePath($item['path']);

			// check all files and folders - exists or no
			if (!Storage::disk($disk)->exists($item['path']))
			{
				continue;
			}

			if ($item['type'] === 'dir')
			{
				// delete directory
				Storage::disk($disk)->deleteDirectory($item['path']);

				event(new DirectoryDeleted($disk, [$item['path']]));
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
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry download"
	 * 		},
	 * 		"404": {
	 * 			"description": "File not found"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response|JsonResponse
	 */
	public function download(Request $request)
	{
		event(new Download($request));

		$disk = $request->input('disk', 'public');
		$path = MediaHelper::sanitizePath($request->input('path'));

		// If file name not in ASCII format
		$filename = basename($path);
		if (!preg_match('/^[\x20-\x7e]*$/', $filename))
		{
			$filename = \Illuminate\Support\Facades\Str::ascii($filename);
		}

		if (!Storage::disk($disk)->exists($path))
		{
			return response()->json(['message' => 'File not found'], 404);
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
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry download"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResponse
	 */
	public function url(Request $request): JsonResponse
	{
		$disk = $request->input('disk', 'public');
		$path = MediaHelper::sanitizePath($request->input('path'));

		return response()->json([
			'result' => [
				'status'  => 'success',
				'message' => null,
			],
			'url' => Storage::disk($disk)->url($path),
		]);
	}
}
