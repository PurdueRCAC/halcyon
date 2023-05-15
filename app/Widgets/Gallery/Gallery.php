<?php
namespace App\Widgets\Gallery;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\Media\Helpers\MediaHelper;

/**
 * Display a gallery of images
 */
class Gallery extends Widget
{
	/**
	 * Display
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$base = storage_path('app/public');

		//$folders = MediaHelper::getTree($base);
		$folder = $this->params->get('folder');
		$children = MediaHelper::getChildren(storage_path() . '/app/public' . $folder, '');

		$files = array_filter($children['files'], function ($v)
		{
			return $v->isImage();
		});

		$layout = $this->params->get('layout', 'thumbs');

		return view($this->getViewName($layout), [
			'files'  => $files,
			'base'   => $base,
			'widget' => $this->model,
			'params' => $this->params,
		]);
	}
}
