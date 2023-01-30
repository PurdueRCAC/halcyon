<?php

namespace App\Modules\Widgets\Entities;

use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use App\Modules\Widgets\Models\Widget;
use App\Modules\Widgets\Entities\Widget as BaseWidget;
use Carbon\Carbon;

class WidgetManager
{
	/**
	 * Container
	 *
	 * @var  Container
	 */
	public $app;

	/**
	 * Constructor.
	 *
	 * @param   Container  $app
	 * @return  void
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	/**
	 * Make sure widget name follows naming conventions
	 *
	 * @param   string  $name  The element value for the extension
	 * @return  string
	 */
	public function canonical($name)
	{
		$name = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
		return strtolower($name);
	}

	/**
	 * Run widget
	 *
	 * @param   object  $widget
	 * @return  string
	 */
	public function run($widget)
	{
		if (!$widget)
		{
			return '';
		}

		$name = strtolower($widget->name);

		$this->app->get('translator')->addNamespace(
			'widget.' . $name,
			app_path() . '/Widgets/' . Str::studly($widget->name) . '/lang'
		);
		$this->app->get('view')->addNamespace(
			'widget.' . $name,
			app_path() . '/Widgets/' . Str::studly($widget->name) . '/views'
		);

		$content = $this->getContentFromCache($widget);

		return $content;
	}

	/**
	 * Count the widgets based on the given condition
	 *
	 * @param   string   $condition  The condition to use
	 * @return  int  Number of widgets found
	 */
	public function count($condition)
	{
		$total = 0;
		$words = explode(' ', $condition);

		for ($i = 0; $i < count($words); $i+=2)
		{
			$position = strtolower($words[$i]);

			$widgets = $this->all()
				->filter(function($value, $key) use ($position)
				{
					return strtolower($value->position) == $position;
				});

			$total += count($widgets);
		}

		return $total;
	}

	/**
	 * Get by position
	 *
	 * @param   string  $position  The position of the widgets
	 * @return  array   An array of widget objects
	 */
	public function byPosition($position)
	{
		$position = strtolower($position);

		$widgets = $this->all()
			->filter(function($value, $key) use ($position)
			{
				return strtolower($value->position) == $position;
			});

		$output = '';

		foreach ($widgets as $widget)
		{
			try
			{
				$output .= $this->run($this->instantiateWidget($widget));
			}
			catch (InvalidWidgetClassException $e)
			{
				$output .= $e->getMessage();
				continue;
			}
		}

		return $output;
	}

	/**
	 * Get by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param   string  $name   The name of the widget
	 * @param   string  $title  The title of the widget, optional
	 * @return  object  The Module object
	 */
	public function byName($name, $title = null)
	{
		$name = $this->canonical($name);

		$widgets = $this->all()
			->filter(function($value, $key) use ($name, $title)
			{
				if ($value->widget == $name)
				{
					// Match the title if we're looking for a specific instance of the widget
					return (!$title || $value->title == $title);
				}

				return false;
			});

		$output = '';

		foreach ($widgets as $widget)
		{
			try
			{
				$output .= $this->run($this->instantiateWidget($widget));
			}
			catch (InvalidWidgetClassException $e)
			{
				$output .= $e->getMessage();
				continue;
			}
		}

		return $output;
	}

	/**
	 * Get by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param   object  $widget
	 * @return  object  The Widget object
	 */
	protected function instantiateWidget($widget)
	{
		if (!$widget->widget)
		{
			return false;
		}

		// Get just the file name
		$name = $widget->widget;
		$name = Str::studly($name);

		// Derive the class name from the type
		$widgetClass = 'App\\Widgets\\' . $name . '\\' . $name;

		if (!class_exists($widgetClass))
		{
			throw new InvalidWidgetClassException('Class "' . $widgetClass . '" does not exist');
		}

		if (!is_subclass_of($widgetClass, BaseWidget::class))
		{
			throw new InvalidWidgetClassException('Class "' . $widgetClass . '" must extend "' . BaseWidget::class . '" class');
		}

		return new $widgetClass($widget);
	}

	/**
	 * Make call and get return widget content.
	 *
	 * @param  object $widget
	 * @return mixed
	 */
	protected function getContent($widget)
	{
		$content = $this->app->call([$widget, 'run']);

		return is_object($content) ? $content->__toString() : $content;
	}

	/**
	 * Gets content from cache if it's turned on.
	 * Runs widget class otherwise.
	 *
	 * @param  object $widget
	 * @return mixed
	 */
	protected function getContentFromCache($widget)
	{
		if ($cacheTime = (float) $widget->getCacheTime())
		{
			return $this->app->cache($widget->cacheKey(), $cacheTime, $widget->cacheTags(), function ()
			{
				return $this->getContent();
			});
		}

		return $this->getContent($widget);
	}

	/**
	 * Return the widget assets path
	 * 
	 * @param  string $widget
	 * @return string
	 */
	public function getAssetPath($widget)
	{
		return public_path($this->app['config']->get('module.widgets.path.assets', 'widgets') . '/' . $widget);
	}

	/**
	 * Load published widgets.
	 *
	 * @return  object  Collection
	 */
	public function all()
	{
		static $clean;

		if (isset($clean))
		{
			return $clean;
		}

		$now = Carbon::now()->toDateTimeString();

		$w = (new Widget)->getTable();

		$query = Widget::where($w . '.published', 1)
			->where(function ($query) use ($now, $w) {
				$query->whereNull($w . '.publish_up')
					->orWhere($w . '.publish_up', '<=', $now);
			})
			->where(function ($query) use ($now, $w) {
				$query->whereNull($w . '.publish_down')
					->orWhere($w . '.publish_down', '>=', $now);
			})
			->where($w . '.client_id', '=', app('isAdmin') ? 1 : 0);

		if ($itemid = app('request')->input('itemid', -1))
		{
			$query->leftJoin('widgets_menu AS mm', 'mm.widgetid', $w . '.id')
				->where(function ($where) use ($itemid)
				{
					$where->where('mm.menuid', '=', (int) $itemid)
						->orWhere('mm.menuid', '<=', '0');
				});
		}

		// Filter by language
		if (!app('isAdmin') && app()->has('language.filter'))
		{
			$lang = app('translator')->locale();

			$query->whereIn($w . '.language', array($lang, '*'));
		}

		if ($user = auth()->user())
		{
			$query->whereIn($w . '.access', $user->getAuthorisedViewLevels());
		}
		else
		{
			$query->whereIn($w . '.access', [1]);
		}

		$clean = $query
					->orderBy($w . '.position', 'asc')
					->orderBy($w . '.ordering', 'asc')
					->get();

		/*
			// Apply negative selections and eliminate duplicates
			$negId = $Itemid ? -(int) $Itemid : false;
			$dupes = array();
			for ($i = 0, $n = count($widgets); $i < $n; $i++)
			{
				$widget = &$widgets[$i];

				// The widget is excluded if there is an explicit prohibition
				$negHit = ($negId === (int) $widget->menuid);

				if (isset($dupes[$widget->id]))
				{
					// If this item has been excluded, keep the duplicate flag set,
					// but remove any item from the cleaned array.
					if ($negHit)
					{
						unset($clean[$widget->id]);
					}
					continue;
				}

				$dupes[$widget->id] = true;

				// Only accept widgets without explicit exclusions.
				if (!$negHit)
				{
					$widget->name     = substr($widget->widget, 4);
					$widget->style    = null;
					$widget->position = strtolower($widget->position);

					$clean[$widget->id] = $widget;
				}
			}

			unset($dupes);

			// Return to simple indexing that matches the query order.
			$clean = array_values($clean);

			$cache->put($cacheid, $clean, $this->app['config']->get('cachetime', 15));
		*/

		return $clean;
	}

	/**
	 * Find widgets
	 *
	 * @param   string  $name
	 * @param   int $client
	 * @param   int $state
	 * @return  object  Collection
	 */
	public function find($name = null, $client = null, $state = null)
	{
		$query = Widget::query();

		if (!is_null($client))
		{
			$query->where('client_id', '=', $client);
		}

		if (!is_null($state))
		{
			$query->where('published', '=', $state);
		}

		if (!is_null($name))
		{
			$query->where('widget', '=', $name);
		}

		$rows = $query
			->orderBy('position', 'asc')
			->orderBy('ordering', 'asc')
			->get();

		return $rows;
	}
}
