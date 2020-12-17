<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
	 * Delete a file
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		event($event = new DirectoryDeleting($request));

		// Get some data from the request
		$paths  = Request::getArray('rm', array(), '', 'array');
		$folder = Request::getString('folder', '');
		$rm     = Request::getArray('rm', array());

		$redirect = 'index.php?option=com_media&folder=' . $folder;
		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&view=medialist&tmpl=component';
		}

		// Nothing to delete
		if (empty($rm))
		{
			App::redirect(route('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&folder=' . $folder, false));
		}

		// Authorize the user
		if (!User::authorise('delete', $this->_option))
		{
			App::redirect(route('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&folder=' . $folder, false));
		}

		// Initialise variables.
		$ret = true;
		foreach ($rm as $path)
		{
			$path = urldecode($path);

			/*if ($path !== Filesystem::clean($path))
			{
				// filename is not safe
				$filename = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');
				Notify::warning(trans('media::media.error.UNABLE_TO_DELETE_FILE_WARNFILENAME', ['file' => substr($filename, strlen(storage_path()))]));
				continue;
			}*/

			$fullPath = Filesystem::cleanPath(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $folder, $path)));
			//$object_file = new \App\Halcyon\Base\Obj(array('filepath' => $fullPath));
			if (is_dir($fullPath))
			{
				$contents = Filesystem::files($fullPath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));
				if (empty($contents))
				{
					// Trigger the onContentBeforeDelete event.
					$result = Event::trigger('content.onContentBeforeDelete', array('com_media.folder', &$object_file));
					if (in_array(false, $result, true))
					{
						// There are some errors in the plugins
						Notify::warning(transs('media::media.error.BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
						continue;
					}

					$ret &= Filesystem::deleteDirectory($fullPath);

					// Trigger the onContentAfterDelete event.
					Event::trigger('content.onContentAfterDelete', array('com_media.folder', &$object_file));
					$this->setMessage(trans('media::media.DELETE_COMPLETE', substr($fullPath, strlen(COM_MEDIA_BASE))));
				}
				else
				{
					// This makes no sense...
					Notify::warning(trans('media::media.error.UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', substr($fullPath, strlen(COM_MEDIA_BASE))));
				}
			}
		}

		return redirect(route('admin.media.index', ['folder' => $folder]);
	}
}
