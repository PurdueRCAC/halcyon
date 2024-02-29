<?php
namespace App\Modules\Widgets\Entities;

use Illuminate\Support\Str;
use App\Modules\Widgets\Models\Widget as WidgetModel;

/**
 * Base widget class
 */
class Widget
{
	/**
	 * Widget name
	 *
	 * @var  string
	 */
	public $name;

	/**
	 * DB record
	 *
	 * @var  WidgetModel
	 */
	protected $model;

	/**
	 * Params repository
	 *
	 * @var  \Illuminate\Config\Repository
	 */
	protected $params;

	/**
	 * Cache time
	 *
	 * @var  int
	 */
	protected $cacheTime = 0;

	/**
	 * Constructor
	 *
	 * @param   WidgetModel  $model
	 * @return  void
	 */
	public function __construct(WidgetModel $model)
	{
		$name = $model->widget;
		//$name = Str::studly($name);

		$this->name   = $name;
		$this->model  = $model;
		$this->params = $model->params;

		if ($this->params->get('cache'))
		{
			$tm = $this->params->get('cache_time');
			$this->cacheTime = $tm ? $tm : 0;
		}
	}

	/**
	 * Display
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		return view($this->getViewName());
	}

	/**
	 * Get widget cache time or false if it's not meant to be cached.
	 *
	 * @return  bool|float|int
	 */
	public function getCacheTime()
	{
		return $this->cacheTime ? $this->cacheTime : false;
	}

	/**
	 * Get widget cache time or false if it's not meant to be cached.
	 *
	 * @return string
	 */
	public function getCacheKey(): string
	{
		return $this->model->cacheKey();
	}

	/**
	 * Get the path of a layout for this widget
	 *
	 * @param   string  $layout  The layout name
	 * @return  string
	 */
	public function getViewName($layout='index'): string
	{
		return 'widget.' . $this->getLowerName() . '::' . $layout;
	}

	/**
	 * Get the widget name in lowercase
	 *
	 * @return  string
	 */
	public function getLowerName(): string
	{
		return strtolower($this->name);
	}

	/**
	 * Get the widget name in studly case
	 *
	 * @return  string
	 */
	public function getStudlyName(): string
	{
		return Str::studly($this->name);
	}

	/**
	 * Get the path of a layout for this widget
	 *
	 * @return  string
	 */
	public function getPath(): string
	{
		return app('config')->get('module.widgets.path', app_path('Widgets')) . '/' . $this->getStudlyName();
	}
}
