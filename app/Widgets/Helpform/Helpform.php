<?php
namespace App\Widgets\Helpform;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\Resources\Entities\Asset;

/**
 * Display a help form
 */
class Helpform extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		$resources = Asset::query()
			->withTrashed()
			->whereIsActive()
			->where('listname', '!=', '')
			->orderBy('name', 'asc')
			->get();

		$types = array();
		foreach ($resources as $resource)
		{
			$tname = 'Services';
			if ($resource->type)
			{
				$tname = $resource->type->name;
			}

			if (!isset($types[$tname]))
			{
				$types[$tname] = array();
			}
			$types[$tname][] = $resource;
		}
		ksort($types);

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'types' => $types,
		]);
	}
}
