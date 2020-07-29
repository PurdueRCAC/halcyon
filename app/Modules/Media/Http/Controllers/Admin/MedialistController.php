<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
	 * @return  void
	 */
	public function index(Request $request)
	{
		$base = storage_path() . '/app';
		$folder = $request->input('folder', '');
		/*$tmpl   = Request::getCmd('tmpl');

		$filters = array();

		$redirect = 'index.php?option=com_media&folder=' . $folder;
		if ($tmpl == 'component')
		{
			$redirect .= '&view=medialist&tmpl=component';
		}
		$this->setRedirect($redirect);*/

		//$session = App::get('session');
		$state = ''; //User::getState('folder');
		$folders = MediaHelper::getTree(app('files')->directories($base));
		$folderTree = MediaHelper::_buildFolderTree($folders);

		$children = MediaHelper::getChildren($base, $folder);
		$parent = MediaHelper::getParent($folder);

		$style = $request->input('layout', 'thumbs');

		return view('media::medialist.index', [
			'folderTree' => $folderTree,
			'folders' => $folders,
			'folder' => $folder,
			'children' => $children,
			'parent' => $parent,
			'layout' => $style
		]);
	}

	/**
	 * Display information about a file
	 *
	 * @return  void
	 */
	public function infoTask()
	{
		Request::checkToken(['get', 'post']);

		// Get some data from the request
		$tmpl = Request::getCmd('tmpl');

		$file = urldecode(Request::getString('file', ''));
		$folder = urldecode(Request::getString('folder', ''));

		if ($file)
		{
			$file = \App\Halcyon\Filesystem\Util::checkPath(MEDIA_BASE . $file);
			$path = $file;

			if (!is_file($file))
			{
				abort(404, trans('Specified file "%s" does not exist', $file));
			}
		}
		elseif ($folder)
		{
			$folder = \App\Halcyon\Filesystem\Util::checkPath(MEDIA_BASE . $folder);
			$path = $folder;

			if (!is_dir($folder))
			{
				abort(404, trans('Specified folder "%s" does not exist', $folder));
			}
		}

		// Compile info
		$data = array(
			'type'          => ($file ? 'file' : 'folder'),
			'path'          => substr($path, strlen(MEDIA_BASE)),
			'absolute_path' => $path,
			'full_path'     => substr($path, strlen(PATH_ROOT)),
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
				$this->setError(trans('There was a problem reading the image dimensions.'));
			}
		}

		return view('media::admin.medialist.info', [
			'data' => $data
		]);
	}

	/**
	 * Display a link to download a file
	 * @return Response
	 */
	public function path(Request $request)
	{
		// Get some data from the request
		$tmpl = $request->input('tmpl');
		$file = urldecode($request->input('file', ''));
		//$file = \App\Halcyon\Filesystem\Util::checkPath(COM_MEDIA_BASE . $file);

		if (!is_file($file))
		{
			abort(404);
		}

		$file = MEDIA_BASEURL . substr($file, strlen(MEDIA_BASE));

		return view('media::admin.medialist.path', [
			'file' => $file
		]);
	}
}
