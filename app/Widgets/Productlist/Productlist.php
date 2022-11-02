<?php
namespace App\Widgets\Productlist;

use App\Modules\Orders\Models\Category;
use App\Modules\Widgets\Entities\Widget;
use App\Modules\Resources\Models\Asset;

/**
 * Widget for displaying products
 */
class Productlist extends Widget
{
	/**
	 * Display
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('sequence', 'asc')
			->get();

		$cats = array();

		foreach ($categories as $category)
		{
			$query = $category->products();

			if (!auth()->user() || !auth()->user()->can('manage orders'))
			{
				$query->where('public', '=', 1);
			}

			$products = $query
				->orderBy('sequence', 'asc')
				->get();

			if (!count($products))
			{
				continue;
			}

			$resources = array();
			foreach ($products as $product)
			{
				if (!isset($resources[$product->resourceid]))
				{
					$resource = Asset::find($product->resourceid);

					if (!$resource || $resource->trashed())
					{
						continue;
					}

					$resources[$product->resourceid] = array(
						'resource' => $resource,
						'products' => array()
					);
				}

				$resources[$product->resourceid]['products'][] = $product;
			}

			if (!count($resources))
			{
				continue;
			}

			$category->resources = $resources;

			$cats[] = $category;
		}

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'categories' => $cats,
			'params' => $this->params,
		]);
	}
}
