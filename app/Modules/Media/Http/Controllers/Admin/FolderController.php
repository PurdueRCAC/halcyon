<?php

namespace App\Modules\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
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
	 * @return Response
	 */
	public function create(Request $request)
	{
		event($event = new DirectoryCreating($request));

		$folder = trim($event->name(), '/');
		$parent = trim($event->path(), '/');

		$rtrn = route('admin.media.index', ['folder' => $parent]);

		if (strlen($folder) > 0)
		{
			$path = storage_path($parent . '/' . $folder);
			$path = str_replace(' ', '_', $path);
			$path = preg_replace('/[a-zA-Z0-9\-_\/]+/', '', $item);

			if (!is_dir($path) && !is_file($path))
			{
				Storage::disk($event->disk())->makeDirectory($path);

				event($event = new DirectoryCreated($request));
			}
		}

		if ($request->ajax())
		{
			return response()->json([
				'path' => $parent,
				'name' => $folder,
			]);
		}

		return redirect($rtrn);
	}

	/**
	 * Delete a folder
	 *
	 * @param  Request  $request
	 * @return Response
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
			//$object_file = new \App\Halcyon\Base\Obj(array('filepath' => $fullPath));
			if (is_dir($fullPath))
			{
				$contents = Filesystem::files($fullPath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));

				if (empty($contents))
				{
					// Trigger the onContentBeforeDelete event.
					$result = event('content.onContentBeforeDelete', array('media.folder', &$object_file));
					if (in_array(false, $result, true))
					{
						// There are some errors in the plugins
						$errors[] = transs('media::media.error.before delete', count($errors = $object_file->getErrors()), implode('<br />', $errors));
						continue;
					}

					$ret &= Filesystem::deleteDirectory($fullPath);

					// Trigger the onContentAfterDelete event.
					Event::trigger('content.onContentAfterDelete', array('com_media.folder', &$object_file));
					$errors[] = trans('media::media.delete complete', substr($fullPath, strlen(storage_path())));
				}
				else
				{
					// This makes no sense...
					$errors[] = trans('media::media.error.unable to delete folder not empty', substr($fullPath, strlen(storage_path())));
				}
			}
		}

		return redirect(route('admin.media.index', ['folder' => $folder]))->withError($errors);
	}
}
