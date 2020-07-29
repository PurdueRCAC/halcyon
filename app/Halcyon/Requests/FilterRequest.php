<?php

namespace App\Halcyon\Http\Requests;

use Illuminate\Http\Request;

class StatefulRequest extends Request
{
	/**
	 * Retrieve an input item from the request.
	 *
	 * @param  string|null  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function input($key = null, $default = null)
	{
		$ns = '';
		if (strstr($key, '.'))
		{
			$dot = strrpos($key, '.') + 1;
			$ns  = substr($key, 0, $dot);
			$key = substr($key, $dot);

			/*$ns = explode('.', $key);
			$key = array_pop($ns);
			$ns = implode('.', $key);*/
		}

		$val = parent::input($key, $default);

		// If empty
		if ($val === null)
		{
			// Check the session
			$val = $this->session()->get($ns . $key, $default);
		}

		// Save to session
		$this->session()->put($ns . $key, $val);

		return $val;
	}

	/**
	 * Retrieve an input item from the request.
	 *
	 * @param  array  $keys
	 * @param  string $ns
	 * @return mixed
	 */
	public function inputList($keys = array(), $ns = null)
	{
		foreach ($keys as $key => $default)
		{
			$keys[$key] = $this->input(($ns ? $ns . '.' : '') . $key, $default);
		}

		return $keys;
	}
}
