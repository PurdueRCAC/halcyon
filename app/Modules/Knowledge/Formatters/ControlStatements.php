<?php
namespace App\Modules\Knowledge\Formatters;

use Closure;

/**
 * Replace IF statements
 */
class ControlStatements
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
	 * Page variables
	 * 
	 * @var  array<string,mixed>
	 */
	private $variables = array();

	/**
	 * Handle content
	 *
	 * @param  array<string,mixed> $data
	 * @param  Closure $next
	 * @return array<string,string>
	 */
	public function handle(array $data, Closure $next): array
	{
		$this->variables = $data['variables'];

		$text = preg_replace_callback('/(\{::if\s+.*?\})(.*?{::\/\})/s', array($this, 'tokenizeIf'), $data['content']);

		for (self::$matches; self::$matches > 0; self::$matches--)
		{
			$m = self::$matches;
			$text = preg_replace_callback(
				"/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}(_$m)(.+?)\{::\/\}/s",
				array($this, 'replaceIfStatement'),
				$text
			);
		}

		$text = \Illuminate\Support\Facades\Blade::render(
			$text,
			$this->variables,
			//deleteCachedView: true
		);

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
	 * @param   array<int,string>   $matches
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

		if (count($elses) > 0)
		{

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

		foreach ($clauses as $i => $clause)
		{
			// Abandon ship if the var doesn't exist. Otherwise the page will
			// throw a 500 when the Blade syntax is rendered.
			if (!isset($vars[$clause['tag']][$clause['var']]))
			{
				// Strip the extra '_#' we appended to handle nested IFs.
				$result = preg_replace('/(\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\})(_\d+)/', "$1", $matches[0]);
				return $result;
			}
		}

		// Process clauses
		$result = '';
		foreach ($clauses as $i => $clause)
		{
			if (empty($clause['value']))
			{
				$clause['value'] = '\'\'';
			}

			$right = trim($clause['value']);
			$right = (is_numeric($right) ? (int)$right : $right);
			$right = (strtolower($right) === 'true' ? true : $right);
			$right = (strtolower($right) === 'false' ? false : $right);

			if (is_string($right))
			{
				$right = '\'' . $right . '\'';
			}
			elseif (is_bool($right))
			{
				$right = $right ? 'true' : 'false';
			}

			$text = '$' . $clause['tag'] . '[\'' . $clause['var'] . '\']' . ' ' . $clause['operator'] . ' ' . $right;

			$result .= '@' . ($i > 0 ? 'else': '') . 'if (' . $text . ')' . "\n";
			$result .= $clause['output'];
		}

		if ($else_output != null)
		{
			// Strip leading or trailing space
			//$else_output = preg_replace("/\s+$/", ' ', $else_output);
			//$else_output = preg_replace("/^ *\n/", '', $else_output);
			$result .= '@else' . "\n";
			$result .= $else_output;
		}

		$result .= '@endif' . "\n";

		return $result;
	}
}
