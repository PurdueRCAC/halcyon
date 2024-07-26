<?php
namespace App\Halcyon\Http\Concerns;

use Illuminate\Http\Request;

trait UsesFilters
{
	/**
	 * Get filters from request.
	 */
	public function getFilters(Request $request, array $filters = array()): array
	{
		foreach ($filters as $key => $default)
		{
			if ($request->input($key))
			{
				$filters[$key] = $request->input($key, $default);
			}
		}

		return $filters;
	}

	/**
	 * Get filters from request. If not found,
	 * look for values in the user session data.
	 */
	public function getStatefulFilters(Request $request, string $id, array $filters = array()): array
	{
		$reset = false;

		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key)
			 && $request->input($key) != session()->get($id . '.filter_' . $key))
			{
				$reset = true;
			}

			$filters[$key] = $this->getStatefulFilter($request, $id . '.filter_' . $key, $key, $default);
		}

		if (isset($filters['page']))
		{
			$filters['page'] = $reset ? 1 : $filters['page'];
		}

		return $filters;
	}

	/**
	 * Gets the value of a user state variable.
	 */
	public function getStatefulFilter(Request $request, string $key, string $requested, mixed $default = null): mixed
	{
		// Check the session
		$old = session()->get($key, $default);

		// Check request
		$val = $request->input($requested);

		// Save the new value only if it was set in this request.
		if (!$request->exists($requested))
		{
			$val = $old;
		}

		// Save to session
		session()->put($key, $val);

		return $val;
	}
}
