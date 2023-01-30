<?php
namespace App\Modules\Widgets\Entities;

use Illuminate\Support\Str;

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
	 * @var  \App\Modules\Widgets\Models\Widget
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
	 * @param   \App\Modules\Widgets\Models\Widget  $model
	 * @return  void
	 */
	public function __construct($model)
	{
		$name = $model->widget;
		//$name = Str::studly($name);

		$this->name   = $name;
		$this->model  = $model;
		$this->params = $model->params;
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
	 * Get the path of a layout for this widget
	 *
	 * @param   string  $layout  The layout name
	 * @return  string
	 */
	public function getViewName($layout='index')
	{
		return 'widget.' . $this->getLowerName() . '::' . $layout;
	}

	/**
	 * Get the widget name in lowercase
	 *
	 * @return  string
	 */
	public function getLowerName()
	{
		return strtolower($this->name);
	}

	/**
	 * Get the widget name in studly case
	 *
	 * @return  string
	 */
	public function getStudlyName()
	{
		return Str::studly($this->name);
	}

	/**
	 * Get the path of a layout for this widget
	 *
	 * @return  string
	 */
	public function getPath()
	{
		return app('config')->get('module.widgets.path', app_path('Widgets')) . '/' . $this->getStudlyName();
	}
}
