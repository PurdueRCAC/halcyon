<?php

namespace App\Halcyon\Http;

use Illuminate\Http\Request;

/**
 * Stateful request
 */
class StatefulRequest extends Request
{
	/**
	 * Gets the value of a user state variable.
	 *
	 * @param   string  $key      The key of the user state variable.
	 * @param   string  $request  The name of the variable passed in a request.
	 * @param   string  $default  The default value for the variable if not found. Optional.
	 * @return  The request user state.
	 */
	public function state($key, $request, $default = null)
	{
		$this->mergeWithBase();

		// Check the session
		$old = session()->get($key, $default);

		// Check request
		$val = $this->input($request);

		// Save the new value only if it was set in this request.
		if ($this->exists($request)) //$val !== null)
		{
			// Save to session
			session()->put($key, $val);
		}
		else
		{
			$val = $old;
		}

		return $val;
	}

	/**
	 * Merge in incoming request
	 *
	 * @return  void
	 */
	public function mergeWithBase()
	{
		static $merged = false;

		if (!$merged)
		{
			$this->merge(request()->all());
			$merged = true;
		}
	}
}
