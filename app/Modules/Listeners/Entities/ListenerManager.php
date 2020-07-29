<?php

namespace App\Modules\Listeners\Entities;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use App\Modules\Listeners\Models\Listener;
//use App\Modules\Listeners\Entities\Listener as BaseListener;

class ListenerManager
{
	/**
	 * Container
	 *
	 * @var  object
	 */
	public $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param   Container  $app
	 * @return  void
	 */
	public function __construct(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Run listener
	 *
	 * @param   object  $listener
	 * @return  string
	 */
	public function subscribe()
	{
		foreach ($this->all() as $listener)
		{
			$this->subscribeListener($listener);
		}
	}

	/**
	 * Get by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param   string  $name   The name of the listener
	 * @param   string  $title  The title of the listener, optional
	 * @return  object  The Module object
	 */
	public function byType($folder, $element = null)
	{
		$listeners = $this->all()
			->filter(function($value, $key) use ($folder, $element)
			{
				if ($value->folder == $folder)
				{
					return ($value->element == $element);
				}

				return false;
			});

		return $listeners;
	}

	/**
	 * Get by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param   object  $listener
	 * @return  object  The Listener object
	 */
	protected function subscribeListener($listener)
	{
		$cls = 'App\\Listeners\\' . Str::studly($listener->folder) . '\\' . Str::studly($listener->element);

		$r = new \ReflectionClass($cls);

		foreach ($r->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
		{
			$name = $method->getName();

			if ($name == 'subscribe')
			{
				$this->dispatcher->subscribe(new $cls);
				break;
			}

			if (substr(strtolower($name), 0, 6) == 'handle')
			{
				$event = lcfirst(substr($name, 6));

				$this->dispatcher->listen($event, $cls . '@' . $name);
			}
		}
	}

	/**
	 * Load published listeners.
	 *
	 * @return  object  Collection
	 */
	public function all()
	{
		static $listeners;

		if (isset($listeners))
		{
			return $listeners;
		}

		$query = Listener::where('enabled', 1)
			->where('type', '=', 'listener');

		if ($user = auth()->user())
		{
			$query->whereIn('access', $user->getAuthorisedViewLevels());
		}

		$listeners = $query
			->orderBy('ordering', 'asc')
			->get();

		return $listeners;
	}
}
