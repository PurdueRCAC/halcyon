<?php
namespace App\Modules\Listeners\Entities;

use App\Modules\Listeners\Models\Listener as ListenerModel;

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
	 * @var  ListenerModel
	 */
	protected $model;

	/**
	 * Params registry
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
	 * @param   object  $model
	 * @return  void
	 */
	public function __construct(ListenerModel $model)
	{
		$name = $model->element;

		$this->name   = $name;
		$this->model  = $model;
		$this->params = $model->params;
	}

	/**
	 * Display
	 *
	 * @return  \Illuminate\Contracts\View\View
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
	public function getViewName($layout='index'): string
	{
		return 'listener.' . $this->name . '::' . $layout;
	}
}
