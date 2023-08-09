<?php

namespace App\Modules\History\Helpers\Diff;

/**
 * Word level diff
 */
class WordLevelDiff extends MappedDiff
{
	/**
	 * Description for 'AX_LINE_LENGTH'
	 *
	 * @var int
	 */
	const MAX_LINE_LENGTH = 10000;

	/**
	 * Constructor
	 *
	 * @param  array<int,string> $orig_lines
	 * @param  array<int,string> $closing_lines
	 * @return void
	 */
	public function __construct($orig_lines, $closing_lines)
	{
		list($orig_words, $orig_stripped) = $this->_split($orig_lines);
		list($closing_words, $closing_stripped) = $this->_split($closing_lines);

		parent::__construct($orig_words, $closing_words, $orig_stripped, $closing_stripped);
	}

	/**
	 * Split lines into words
	 *
	 * @param  array<int,string> $lines
	 * @return array<int,array>
	 */
	public function _split($lines)
	{
		$words = array();
		$stripped = array();
		$first = true;
		foreach ($lines as $line)
		{
			// If the line is too long, just pretend the entire line is one big word
			// This prevents resource exhaustion problems
			if ($first)
			{
				$first = false;
			}
			else
			{
				$words[] = "\n";
				$stripped[] = "\n";
			}
			if (strlen($line) > self::MAX_LINE_LENGTH)
			{
				$words[] = $line;
				$stripped[] = $line;
			}
			else
			{
				$m = array();
				if (preg_match_all('/ ([^\S\n]+ | [0-9_A-Za-z\x80-\xff]+ | .) (?: (?!< \n) [^\S\n])? /xs', $line, $m))
				{
					$words = array_merge($words, $m[0]);
					$stripped = array_merge($stripped, $m[1]);
				}
			}
		}
		return array($words, $stripped);
	}

	/**
	 * Original lines
	 *
	 * @return array<int,string>
	 */
	public function orig()
	{
		$orig = new WordAccumulator;

		foreach ($this->edits as $edit)
		{
			if ($edit->type == 'copy')
			{
				$orig->addWords($edit->orig);
			}
			elseif ($edit->orig)
			{
				$orig->addWords($edit->orig, 'del');
			}
		}
		$lines = $orig->getLines();

		return $lines;
	}

	/**
	 * Close the diff
	 *
	 * @return array<int,string>
	 */
	public function closing()
	{
		$closing = new WordAccumulator;

		foreach ($this->edits as $edit)
		{
			if ($edit->type == 'copy')
			{
				$closing->addWords($edit->closing);
			}
			elseif ($edit->closing)
			{
				$closing->addWords($edit->closing, 'ins');
			}
		}
		$lines = $closing->getLines();

		return $lines;
	}
}
