<?php

namespace App\Modules\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Modules\Media\Models\Files;
use App\Modules\Media\Helpers\MediaHelper;

/**
 * Media list controller
 */
class MedialistController extends Controller
{
	/**
	 * Display a list of files
	 *
	 * @param   Request  $request
	 * @return  View
	 */
	public function index(Request $request)
	{
		$base = storage_path('app/public');
		$folder = $request->input('folder', '');

		$children = MediaHelper::getChildren($base, $folder);
		$parent = MediaHelper::getParent($folder);

		$style = $request->input('layout', 'thumbs');

		return view('media::medialist.index', [
			'folder' => $folder,
			'children' => $children,
			'parent' => $parent,
			'layout' => $style
		]);
	}

	/**
	 * Display information about a file
	 *
	 * @param   Request  $request
	 * @return  View
	 */
	public function info(Request $request)
	{
		// Get some data from the request
		$tmpl = $request->input('tmpl');

		$file = urldecode($request->input('file', ''));
		$folder = urldecode($request->input('folder', ''));

		if ($file)
		{
			$file = \App\Halcyon\Filesystem\Util::checkPath(storage_path($file));
			$path = $file;

			if (!is_file($file))
			{
				abort(404, trans('media::media.error.file not found', ['file' => $file]));
			}
		}
		elseif ($folder)
		{
			$folder = \App\Halcyon\Filesystem\Util::checkPath(storage_path($folder));
			$path = $folder;

			if (!is_dir($folder))
			{
				abort(404, trans('media::media.error.directory not found', ['directory' => $folder]));
			}
		}

		// Compile info
		$data = array(
			'type'          => ($file ? 'file' : 'folder'),
			'path'          => substr($path, strlen(storage_path())),
			'absolute_path' => $path,
			'name'          => basename($path),
			'modified'      => filemtime($path),
			'size'          => 0,
			'width'         => 0,
			'height'        => 0
		);

		if ($data['type'] == 'file')
		{
			$data['size'] = filesize($file);
		}

		if (preg_match("/\.(bmp|gif|jpg|jpe|jpeg|png)$/i", $data['name']))
		{
			$data['type'] = 'img';
			try
			{
				$dimensions = getimagesize($data['absolute_path']);

				$data['width'] = $dimensions[0];
				$data['height'] = $dimensions[1];
			}
			catch (\Exception $e)
			{
				$request->session()->flash('error', trans('media::media.error.cannot read image dimensions'));
			}
		}

		return view('media::admin.medialist.info', [
			'data' => $data
		]);
	}

	/**
	 * Display a link to download a file
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function path(Request $request)
	{
		// Get some data from the request
		$tmpl = $request->input('tmpl');
		$file = urldecode($request->input('file', ''));

		if (!is_file($file))
		{
			abort(404);
		}

		$file = substr($file, strlen(storage_path()));

		return view('media::admin.medialist.path', [
			'file' => $file
		]);
	}
}
