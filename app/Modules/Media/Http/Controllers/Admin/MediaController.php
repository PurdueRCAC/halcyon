<?php

namespace App\Modules\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Str;
use App\Modules\Media\Entities\Folder;
use App\Modules\Media\Helpers\MediaHelper;
use App\Modules\Media\Events\Download;

/**
 * Media controller
 */
class MediaController extends Controller
{
	/**
	 * Display a listing of files
	 *
	 * @param  Request  $request
	 * @return View
	 */
	public function index(Request $request)
	{
		$base = storage_path('app/public');
		$filters = array();

		$folder = $request->input('folder', '');
		$folders = MediaHelper::getTree($base);

		$fold = array(
			'id'       => 0,
			'parent'   => -1,
			'name'     => '',
			'fullname' => $base,
			'relname'  => substr($base, strlen(storage_path()))
		);

		array_unshift($folders, $fold);

		$folderTree = MediaHelper::_buildFolderTree($folders, -1);

		$layout = session()->get('media.layout', $request->input('layout', 'thumbs'));

		return view('media::media.index', [
			'config'     => config('media', []),
			'folders_id' => ' id="media-tree"',
			'folder'     => $folder,
			'folderTree' => $folderTree,
			'folders'    => $folders,
			'parent'     => MediaHelper::getParent($folder),
			'layout'     => $layout
		]);
	}

	/**
	 * Download a file
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function download(Request $request)
	{
		event(new Download($request));

		$disk = $request->input('disk', 'public');
		$path = $request->input('path', '');

		// if file name not in ASCII format
		if (!preg_match('/^[\x20-\x7e]*$/', basename($path)))
		{
			$filename = Str::ascii(basename($path));
		}
		else
		{
			$filename = basename($path);
		}

		// Get some data from the request
		return Storage::disk($disk)->download($path, $filename);
	}
}
