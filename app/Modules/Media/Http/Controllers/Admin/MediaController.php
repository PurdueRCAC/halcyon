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
use App\Halcyon\Utility\Number;
use App\Modules\Media\Models\Files;
use App\Modules\Media\Helpers\MediaHelper;
use App\Modules\Media\Events\Deleting;
use App\Modules\Media\Events\FilesUploading;
use App\Modules\Media\Events\FilesUploaded;

/**
 * Media controller
 */
class MediaController extends Controller
{
	/**
	 * Display a listing of files
	 * 
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$base = storage_path('app');
		$filters = array();

		$folder = $request->input('folder', '');
		//$session = App::get('session');
		$state = ''; //User::getState('folder');
		$folders = MediaHelper::getTree($base);

		/*$fold = array(
			'id'       => 0,
			'parent'   => -1,
			'name'     => basename($base),
			'fullname' => $base,
			'relname'  => substr($base, strlen(storage_path()))
		);

		array_unshift($folders, $fold);*/

		$folderTree = MediaHelper::_buildFolderTree($folders); //, -1);

		//$folderTree[0]['name'] = basename($base);

		MediaHelper::createPath($folders, $base);

		$layout = session()->get('media.layout', 'thumbs');

		return view('media::media.index', [
			'config' => config('media', []),
			'state' => $state,
			//'require_ftp' => User::getState('ftp'),
			'folders_id' => ' id="media-tree"',
			'folder' => $folder,
			//'folders' => $folders,
			'folderTree' => $folderTree, //[0]['children'],
			'parent' => MediaHelper::getParent($folder),
			'layout' => $layout
		]);
	}

	/**
	 * New entry
	 *
	 * @param  Request  $request
	 * @return  Response
	 */
	public function newdir(Request $request)
	{
		if ($request->ajax())
		{
			return $this->ajaxNewTask();
		}

		$folder      = $request->input('name', '');
		$folderCheck = $request->input('name', null);
		$parent      = $request->input('path', '');

		$rtrn = route('admin.media.index', ['folder' => $parent]);

		if (strlen($folder) > 0)
		{
			//Request::setVar('folder', $parent);

			if ($folderCheck !== null && $folder !== $folderCheck)
			{
				return redirect($rtrn)->with('warning', trans('media::media.ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'));
			}

			$path = \App\Halcyon\Filesystem\Util::normalizePath(media::mediaBASE . $parent . DS . $folder);

			if (!is_dir($path) && !is_file($path))
			{
				// Trigger the onContentBeforeSave event.
				//$object_file = new \App\Halcyon\Base\Obj(array('filepath' => $path));

				event(new DirectoryCreating($request));

				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins
					Notify::warning(transs('media::mediaERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
					return redirect($rtrn);
				}

				Filesystem::makeDirectory($path);
				//$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
				//Filesystem::write($path . '/index.html', $data);

				// Trigger the onContentAfterSave event.
				event(new DirectoryCreated($request));

				Notify::success(trans('media::mediaCREATE_COMPLETE', substr($path, strlen(media::mediaBASE))));
			}

			//Request::setVar('folder', ($parent) ? $parent . DS . $folder : $folder);
		}

		return redirect($rtrn);
	}

	/**
	 * Upload
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function upload(Request $request)
	{
		event(new FilesUploading($request));

		if (Request::getInt('no_html', 0))
		{
			return $this->ajaxUploadTask();
		}

		$params = config('modules.media', []);

		// Get some data from the request
		$files  = Request::getArray('Filedata', array(), 'files');
		$this->folder = Request::getString('folder', '');
		$parent = Request::getString('parent', '');
		$return = Request::getString('return-url', '', 'post');

		// Set the redirect
		if ($return)
		{
			$return = base64_decode($return) . '&folder=' . $this->folder;
		}
		else
		{
			$return = 'index.php?option=' . $this->_option . '&controller=medialist&tmpl=component&folder=' . $parent;
		}

		// Authorize the user
		if (!User::authorise('create', $this->_option))
		{
			App::redirect(route($return, false));
		}

		// Input is in the form of an associative array containing numerically indexed arrays
		// We want a numerically indexed array containing associative arrays
		// Cast each item as array in case the Filedata parameter was not sent as such
		$files = array_map(
			array($this, 'reformatFilesArray'),
			(array) $files['name'],
			(array) $files['type'],
			(array) $files['tmp_name'],
			(array) $files['error'],
			(array) $files['size']
		);

		// Perform basic checks on file info before attempting anything
		foreach ($files as &$file)
		{
			if ($file['error'] == 1)
			{
				Notify::warning(trans('media::mediaERROR_WARNFILETOOLARGE'));
				continue;
			}

			if ($file['size'] > ($params->get('upload_maxsize', 0) * 1024 * 1024)
			 || $file['size'] > (int)(ini_get('upload_max_filesize'))* 1024 * 1024
			 || $file['size'] > (int)(ini_get('post_max_size'))* 1024 * 1024
			 || (($file['size'] > (int) (ini_get('memory_limit')) * 1024 * 1024) && ((int) (ini_get('memory_limit')) != -1)))
			{
				Notify::warning(trans('media::mediaERROR_WARNFILETOOLARGE'));
				continue;
			}

			if (Filesystem::exists($file['filepath']))
			{
				// A file with this name already exists
				Notify::warning(trans('media::mediaERROR_FILE_EXISTS'));
				continue;
			}

			if (!isset($file['name']))
			{
				// No filename (after the name was cleaned by Filesystem::clean()
				Notify::error(trans('media::mediaINVALID_REQUEST'));
				continue;
			}
		}

		foreach ($files as &$file)
		{
			// The request is valid
			$err = null;
			if (!MediaHelper::canUpload($file, $err))
			{
				// The file can't be upload
				Notify::warning(trans($err));
				continue;
			}

			// Trigger the onContentBeforeSave event.
			$object_file = new \App\Halcyon\Base\Obj($file);
			$result = Event::trigger('content.onContentBeforeSave', array('com_media.file', &$object_file, true));
			if (in_array(false, $result, true))
			{
				// There are some errors in the plugins
				Notify::warning(transs('media::mediaERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
				continue;
			}

			if (!Filesystem::upload($file['tmp_name'], $file['filepath']))
			{
				// Error in upload
				Notify::warning(trans('media::mediaERROR_UNABLE_TO_UPLOAD_FILE'));
				continue;
			}
			else
			{
				// Trigger the onContentAfterSave event.
				Event::trigger('content.onContentAfterSave', array('com_media.file', &$object_file, true));

				Notify::success(trans('media::mediaUPLOAD_COMPLETE', substr($file['filepath'], strlen(media::mediaBASE))));
			}
		}

		event(new FilesUploaded($request));

		App::redirect(route($return, false));
	}

	/**
	 * Delete a file
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		event(new Deleting($request));

		if (Request::getInt('no_html', 0))
		{
			return $this->ajaxDeleteTask();
		}

		// Get some data from the request
		$tmpl   = $request->input('tmpl');
		$paths  = Request::getArray('rm', array(), '', 'array');
		$folder = Request::getString('folder', '');
		$rm     = Request::getArray('rm', array());

		$redirect = 'index.php?option=com_media&folder=' . $folder;
		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&view=medialist&tmpl=component';
		}
		$this->setRedirect($redirect);

		// Nothing to delete
		if (empty($rm))
		{
			redirect(route('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&folder=' . $folder, false));
		}

		// Authorize the user
		if (!User::authorise('delete', $this->_option))
		{
			redirect(route('admin.media.index', ['folder' => $folder]));
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
				Notify::warning(trans('media::media.ERROR_UNABLE_TO_DELETE_FILE_WARNFILENAME', substr($filename, strlen(media::mediaBASE))));
				continue;
			}*/

			$fullPath = Filesystem::cleanPath(implode(DIRECTORY_SEPARATOR, array(media::mediaBASE, $folder, $path)));
			$object_file = new \App\Halcyon\Base\Obj(array('filepath' => $fullPath));
			if (is_file($fullPath))
			{
				// Trigger the onContentBeforeDelete event.
				$result = Event::trigger('content.onContentBeforeDelete', array('com_media.file', &$object_file));
				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins
					Notify::warning(transs('media::media.ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
					continue;
				}

				$ret &= Filesystem::delete($fullPath);

				// Trigger the onContentAfterDelete event.
				Event::trigger('content.onContentAfterDelete', array('com_media.file', &$object_file));
				$this->setMessage(trans('media::media.DELETE_COMPLETE', substr($fullPath, strlen(media::mediaBASE))));
			}
			elseif (is_dir($fullPath))
			{
				$contents = Filesystem::files($fullPath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));
				if (empty($contents))
				{
					$ret &= Filesystem::deleteDirectory($fullPath);

					// Trigger the onContentAfterDelete event.
					event(new Deleted($request));

					$this->setMessage(trans('media::mediaDELETE_COMPLETE', substr($fullPath, strlen(media::mediaBASE))));
				}
				else
				{
					// This makes no sense...
					Notify::warning(trans('media::media.ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', substr($fullPath, strlen(media::mediaBASE))));
				}
			}

			event(new Deleted($request));
		}

		redirect(route('admin.media.index', ['folder' => $folder]));
	}

	/**
	 * Download a file
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function downloadTask(Request $request)
	{
		event(new Download($request));

		$disk = $request->input('disk', 'public');
		$path = $request->input('path', '');

		// if file name not in ASCII format
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
}
