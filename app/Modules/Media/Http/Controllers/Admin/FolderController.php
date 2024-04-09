<?php

namespace App\Modules\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use App\Modules\Media\Events\DirectoryDeleting;
use App\Modules\Media\Events\DirectoryCreating;
use App\Modules\Media\Events\DirectoryCreated;

/**
 * Folder controller
 */
class FolderController extends Controller
{
	/**
	 * New entry
	 *
	 * @param  Request  $request
	 * @return JsonResponse|RedirectResponse
	 */
	public function create(Request $request)
	{
		event($event = new DirectoryCreating($request));

		$disk   = $event->disk();
		$name   = trim($event->name(), '/');
		$parent = trim($event->path(), '/');

		$rtrn = route('admin.media.index', ['folder' => $parent]);

		if (strlen($name) > 0)
		{
			$path = ($parent ? $parent . '/' : '') . $name;
			$path = str_replace(' ', '_', $path);
			$path = preg_replace('/[a-zA-Z0-9\-_\/]+/', '', $item);

			if (!Storage::disk($disk)->exists($path))
			{
				Storage::disk($disk)->makeDirectory($path);

				event(new DirectoryCreated(
					$disk,
					$event->name(),
					$event->path()
				));
			}
		}

		if ($request->ajax())
		{
			return response()->json([
				'path' => $parent,
				'name' => $name,
			]);
		}

		return redirect($rtrn);
	}

	/**
	 * Delete a folder
	 *
	 * @param  Request  $request
	 * @return RedirectResponse
	 */
	public function delete(Request $request)
	{
		event($event = new DirectoryDeleting($request));

		// Get some data from the request
		$paths  = $request->input('rm', array());
		$folder = $request->input('folder', '');
		$rm     = $request->input('rm', array());

		$redirect = route('admin.media.index', ['folder' => $folder]);

		// Nothing to delete
		if (empty($rm))
		{
			return redirect($redirect);
		}

		// Authorize the user
		if (!auth()->user()->can('delete media'))
		{
			return redirect($redirect);
		}

		$errors = array();

		// Initialise variables.
		$ret = true;
		foreach ($rm as $path)
		{
			$path = urldecode($path);

			$fullPath = Filesystem::cleanPath(implode(DIRECTORY_SEPARATOR, array(storage_path(), $folder, $path)));

			if (is_dir($fullPath))
			{
				$contents = Filesystem::files($fullPath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));

				if (empty($contents))
				{
					Filesystem::deleteDirectory($fullPath);
				}
				else
				{
					// This makes no sense...
					$errors[] = trans('media::media.error.unable to delete folder not empty', substr($fullPath, strlen(storage_path())));
				}
			}
		}

		event($event = new DirectoryDeleted($fullPath, $rm));

		return redirect(route('admin.media.index', ['folder' => $folder]))->withError($errors);
	}
}
