<?php
namespace App\Modules\Widgets\Entities;

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
	 * @var  App\Modules\Widgets\Models\Widget
	 */
	protected $model;

	/**
	 * Params registry
	 *
	 * @var  App\Halcyon\Config\Registry
	 */
	protected $params;

	/**
	 * Cache time
	 *
	 * @var  integer
	 */
	protected $cacheTime = 0;

	/**
	 * Constructor
	 *
	 * @param   object  $model
	 * @return  void
	 */
	public function __construct($model)
	{
		$name = $model->widget;
		if (substr($name, 0, 4) == 'mod_')
		{
			$name = substr($name, 4);
		}
		//$name = Str::studly($name);

		$this->name   = $name;
		$this->model  = $model;
		$this->params = $model->params;//();
	}

	/**
	 * Display
	 *
	 * @return  string
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
		return 'widget.' . $this->name . '::' . $layout;
	}
}
