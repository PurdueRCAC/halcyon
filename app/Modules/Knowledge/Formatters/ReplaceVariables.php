<?php
namespace App\Modules\Knowledge\Formatters;

use Closure;

/**
 * Replace Variables in content
 */
class ReplaceVariables
{
	/**
	 * Handle content
	 *
	 * @param  array<string,string> $data
	 * @param  Closure $next
	 * @return array
	 */
	public function handle(array $data, Closure $next): array
	{
		$data['content'] = preg_replace_callback(
			"/\\\$\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)(([\*\/\-\+])(\d+(\.\d+)?))?\}/",
			function (array $matches) use ($data)
			{
				$vars = $data['variables'];

				if (isset($vars[$matches[1]][$matches[2]]))
				{
					$val = $vars[$matches[1]][$matches[2]];
					if (is_array($val))
					{
						$val = array_shift($val);
					}

					if (isset($matches[5]) && is_numeric($val) && is_numeric($matches[5]))
					{
						if ($matches[4] == '+')
						{
							return $val + $matches[5];
						}
						elseif ($matches[4] == '-')
						{
							return $val - $matches[5];
						}
						elseif ($matches[4] == '/')
						{
							return $val / $matches[5];
						}
						elseif ($matches[4] == '*')
						{
							return $val * $matches[5];
						}
					}

					return $val;
				}

				return $matches[0];
			},
			$data['content']
		);

		return $next($data);
	}
}
