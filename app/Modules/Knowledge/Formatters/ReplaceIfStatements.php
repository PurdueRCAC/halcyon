<?php
namespace App\Modules\Knowledge\Formatters;

use Closure;

/**
 * Replace IF statements
 */
class ReplaceIfStatements
{
	/**
	 * Regex patterns for in-content IF statements
	 *
	 * @var string
	 */
	const REGEXP_IF_STATEMENT = "/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}(.+?)\{::\/\}/s";
	const REGEXP_IF_ELSE = "/\{::elseif\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}(.+?)(?=\{::)/s";
	const REGEXP_IF = "/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}_\d+(.+?)(?=\{::)/s";
	const REGEXP_ELSE = "/\{::else\}(.+?)(?=\{::)/s";
	const REGEXP_LINK = "/\[(.+?)\]\((.+?)\)/";

	/**
	 * Nesting counter
	 * 
	 * @var  int
	 */
	private static $matches = 0;

	/**
	 * Nesting counter
	 * 
	 * @var  array
	 */
	private $variables = array();

	/**
	 * Handle content
	 *
	 * @param  array $data
	 * @param  Closure $next
	 * @return array
	 */
	public function handle(array $data, Closure $next): array
	{
		$this->variables = $data['variables'];

		$text = preg_replace_callback('/(\{::if\s+.*?\})(.*?{::\/\})/s', array($this, 'tokenizeIf'), $data['content']);

		for (self::$matches; self::$matches > 0; self::$matches--)
		{
			$m = self::$matches;
			$text = preg_replace_callback("/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}_$m(.+?)\{::\/\}/s", array($this, 'replaceIfStatement'), $text);
		}
		//$text = preg_replace_callback(self::REGEXP_IF_STATEMENT, array($this, 'replaceIfStatement'), $text);

		$data['content'] = $text;

		return $next($data);
	}

	/**
	 * Add a token to IF statements to determine proper nesting
	 *
	 * @param   array<int,string>  $matches
	 * @return  string
	 */
	private function tokenizeIf(array $matches): string
	{
		self::$matches++;

		if (count($matches) == 1)
		{
			return $matches[0] . '_' . self::$matches;
		}

		return $matches[1] . '_' . self::$matches . preg_replace_callback("/\{::if\s+.*?\}/", array($this, 'tokenizeIf'), $matches[2]);
	}

	/**
	 * Replace "if" statements
	 *
	 * @param   array   $matches
	 * @return  string
	 */
	protected function replaceIfStatement(array $matches): string
	{
		$vars = $this->variables;

		$clauses = array();

		// Pull out an else
		$else_output = null;
		$else = array();
		if (preg_match(self::REGEXP_ELSE, $matches[0], $else))
		{
			$else_output = $else[1];
		}

		// See if we have any if elses
		$elses = array();
		preg_match_all(self::REGEXP_IF_ELSE, $matches[0], $elses, PREG_SET_ORDER);

		if (count($elses) == 0)
		{
			// Break out first if
			$if = array();

			preg_match(self::REGEXP_IF, $matches[0], $if);

			array_push($clauses, array(
				'tag'      => $if[1],
				'var'      => $if[2],
				'operator' => $if[3],
				'value'    => $if[4],
				'output'   => $if[5],
			));
		}
		else
		{
			// Break out first if
			$if = array();

			preg_match(self::REGEXP_IF, $matches[0], $if);

			array_push($clauses, array(
				'tag'      => $if[1],
				'var'      => $if[2],
				'operator' => $if[3],
				'value'    => $if[4],
				'output'   => $if[5],
			));

			foreach ($elses as $else)
			{
				array_push($clauses, array(
					'tag'      => $else[1],
					'var'      => $else[2],
					'operator' => $else[3],
					'value'    => $else[4],
					'output'   => $else[5],
				));
			}
		}

		// Process clauses
		foreach ($clauses as $clause)
		{
			$operator = $clause['operator'];
			$right    = trim($clause['value']);
			$result   = false;

			if (isset($vars[$clause['tag']][$clause['var']]))
			{
				if (is_array($vars[$clause['tag']][$clause['var']]))
				{
					$vars[$clause['tag']][$clause['var']] = array_shift($vars[$clause['tag']][$clause['var']]);
				}
				$left = trim($vars[$clause['tag']][$clause['var']]);

				$left = (is_integer($left) ? (int)$left : $left);
				$left = (strtolower($left) === 'true' ? true : $left);
				$left = (strtolower($left) === 'false' ? false : $left);

				$right = (is_integer($right) ? (int)$right : $right);
				$right = (strtolower($right) === 'true' ? true : $right);
				$right = (strtolower($right) === 'false' ? false : $right);

				if ($operator == '==')
				{
					if ($right === true)
					{
						$result = ($left ? true : false);
					}
					elseif ($right === false)
					{
						$result = (!$left ? true : false);
					}
					else
					{
						$result = ($left == $right ? true : false);
					}
				}
				elseif ($operator == '!=')
				{
					if ($right === true)
					{
						$result = (!$left ? true : false);
					}
					elseif ($right === false)
					{
						$result = ($left ? true : false);
					}
					else
					{
						$result = ($left != $right ? true : false);
					}
				}
				elseif ($operator == '>')
				{
					$result = ($left > $right ? true : false);
				}
				elseif ($operator == '<')
				{
					$result = ($left < $right ? true : false);
				}
				elseif ($operator == '<=')
				{
					$result = ($left <= $right ? true : false);
				}
				elseif ($operator == '>=')
				{
					$result = ($left >= $right ? true : false);
				}
				elseif ($operator == '=~')
				{
					$result = (preg_match("/$right/i", $left) ? true : false);
				}
			}
			else
			{
				$result = false;
			}

			if ($result)
			{
				// Strip leading or trailing space
				$output = preg_replace("/\s+$/", ' ', $clause['output']);
				// Strip leading newlines
				$output = preg_replace("/^ *\n/", '', $output);
				return $output;
			}
		}

		// If we failed everything, return the elseif we have one.
		if ($else_output != null)
		{
			// Strip leading or trailing space
			$else_output = preg_replace("/\s+$/", ' ', $else_output);
			$else_output = preg_replace("/^ *\n/", '', $else_output);
			return $else_output;
		}

		return '';
	}
}
