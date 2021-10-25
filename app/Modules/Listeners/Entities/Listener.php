<?php
namespace App\Modules\Listeners\Entities;

/**
 * Base listener class
 */
class Listener
{
	/**
	 * Listener name
	 *
	 * @var  string
	 */
	public $name;

	/**
	 * DB record
	 *
	 * @var  App\Modules\Listeners\Models\Listener
	 */
	protected $model;

	/**
	 * Params registry
	 *
	 * @var  Illuminate\Config\Repository
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
		$name = $model->module;
		if (substr($name, 0, 4) == 'mod_')
		{
			$name = substr($name, 4);
		}
		//$name = Str::studly($name);

		$this->name   = $name;
		$this->model  = $model;
		$this->params = $model->params;
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
	 * Get listener cache time or false if it's not meant to be cached.
	 *
	 * @return  bool|float|int
	 */
	public function getCacheTime()
	{
		return $this->cacheTime ? $this->cacheTime : false;
	}

	/**
	 * Get the path of a layout for this module
	 *
	 * @param   string  $layout  The layout name
	 * @return  string
	 */
	public function getViewName($layout='index')
	{
		return 'listener.' . $this->name . '::' . $layout;
	}
}
