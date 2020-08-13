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
use App\Modules\Media\Helpers\MediaHelper;
use App\Modules\Media\Events\Deleting;
use App\Modules\Media\Events\Deleted;
use App\Modules\Media\Events\Download;
use App\Modules\Media\Events\FilesUploading;
use App\Modules\Media\Events\FilesUploaded;

/**
 * Media
 *
 * @apiUri    /api/media
 */
class MediaController extends Controller
{
	/**
	 * Display a listing of files
	 *
	 * @apiMethod GET
	 * @apiUri    /api/media
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
	 * @apiUri    /api/media/tree
	 * @return Response
	 */
	public function tree(Request $request)
	{
		/*$disk = $request->input('disk', 'public');

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
		$base = storage_path() . '/app';

		$folder = $request->input('folder', '');
		$folders = MediaHelper::getTree($base);

		$fold = array(
			'id'       => 0,
			'parent'   => -1,
			'name'     => basename($base),
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
	 * @param $request
	 * @return  void
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
	 * @param $request
	 * @return  void
	 */
	public function upload(Request $request)
	{
		event(new FilesUploading($request));

		$disk = $request->input('disk');
		$path = $request->input('path');
		$files = $request->file();
		$overwrite = $request->input('overwrite');

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
			$maxSize = config('modules.media.max_upload_size', 0);

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
			$allowedTypes = config('modules.media.allowed_types', []);

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
	 * Delete a file
	 *
	 * @param $request
	 * @return  void
	 */
	public function delete(Request $request)
	{
		event(new Deleting($request));

		$disk  = $request->input('disk');
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
	 * @param $request
	 * @return  void
	 */
	public function download(Request $request)
	{
		event(new Download($request));

		$disk = $request->input('disk');
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
	 * @param $request
	 * @return array
	 */
	public function url(Request $request)
	{
		$disk = $request->input('disk');
		$path = $request->input('path');

		return response()->json([
			'result' => [
				'status'  => 'success',
				'message' => null,
			],
			'url'    => Storage::disk($disk)->url($path),
		]);
	}
}
