<?php
namespace App\Modules\News\Formatters;

use Closure;

/**
 * Replace Variables in content
 */
class ReplaceVariables
{
	/**
	 * Handle content
	 *
	 * @param  array<string,mixed> $data
	 * @param  Closure $next
	 * @return array<string,mixed>
	 */
	public function handle(array $data, Closure $next): array
	{
		$text = $data['content'];

		foreach ($data['variables'] as $var => $value)
		{
			if (is_array($value))
			{
				$value = implode(', ', $value);
			}
			$text = preg_replace("/%" . $var . "%/", $value, $text);
		}

		$data['content'] = $text;

		return $next($data);
	}
}
