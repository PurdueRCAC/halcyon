<?php

namespace App\Modules\History\Helpers\Diff\Formatter;

use App\Modules\History\Helpers\Diff\Formatter;
use App\Modules\History\Helpers\Diff\WordLevelDiff;

/**-------------------------------------------------------------
 *  Div style diff formatter. Highlights blocks that have
 *  changed following a format like this:
 *
 *     unchanged code
 *     <div class="diff-deletedline">oldcode</div>
 *     <div class="diff-addedline">newcode</div>
 *     unchanged code
 */
class Div extends Formatter
{
	/**
	 * Constructor
	 *
	 * @return     void
	 */
	public function __construct()
	{
		$this->leading_context_lines = 0;
		$this->trailing_context_lines = 0;
	}

	/**
	 * Short description for '_block_header'
	 *
	 * Long description (if any) ...
	 *
	 * @param      int $xbeg
	 * @param      int $xlen
	 * @param      int $ybeg
	 * @param      int $ylen
	 * @return     string
	 */
	public function _block_header($xbeg, $xlen, $ybeg, $ylen)
	{
		$r = '<!--LINE '.$xbeg."-->\n" . '<!--LINE '.$ybeg."-->\n";
		return $r;
	}

	/**
	 * Short description for '_start_block'
	 *
	 * Long description (if any) ...
	 *
	 * @param      string $header
	 * @return     void
	 */
	public function _start_block($header)
	{
		echo $header;
	}

	/**
	 * Short description for '_end_block'
	 *
	 * Long description (if any) ...
	 *
	 * @return     void
	 */
	public function _end_block()
	{
	}

	/**
	 * Short description for '_lines'
	 *
	 * Long description (if any) ...
	 *
	 * @param      array<int,string> $lines
	 * @param      string $prefix
	 * @param      string $color
	 * @return     void
	 */
	public function _lines($lines, $prefix=' ', $color='white')
	{
	}

	/**
	 * HTML-escape parameter before calling this
	 *
	 * @param      string $line
	 * @return     string
	 */
	public function addedLine($line)
	{
		return $this->wrapLine('+', 'diff-addedline', $line);
	}

	/**
	 * HTML-escape parameter before calling this
	 *
	 * @param      string $line
	 * @return     string
	 */
	public function deletedLine($line)
	{
		return $this->wrapLine('-', 'diff-deletedline', $line);
	}

	/**
	 * HTML-escape parameter before calling this
	 *
	 * @param      string $line
	 * @return     string
	 */
	public function contextLine($line)
	{
		return $this->wrapLine(' ', 'diff-context', $line);
	}

	/**
	 * Wrap a line in a DIV
	 *
	 * @param      string $marker
	 * @param      string $class
	 * @param      string $line
	 * @return     string
	 */
	private function wrapLine($marker, $class, $line)
	{
		if (trim($line) !== '')
		{
			// The <div> wrapper is needed for 'overflow: auto' style to scroll properly
			$line = '<div class="'.$class.'">'.$line.'</div>';
		}

		return $line;
	}

	/**
	 * Return an empty line
	 *
	 * @return string
	 */
	public function emptyLine()
	{
		return '<br />';
	}

	/**
	 * Short description for '_added'
	 *
	 * Long description (if any) ...
	 *
	 * @param      array<int,string> $lines
	 * @return     void
	 */
	public function _added($lines)
	{
		foreach ($lines as $line)
		{
			echo '' . $this->emptyLine() . $this->addedLine(htmlspecialchars ($line)) . "\n";
		}
	}

	/**
	 * Short description for '_deleted'
	 *
	 * Long description (if any) ...
	 *
	 * @param      array<int,string> $lines
	 * @return     void
	 */
	public function _deleted($lines)
	{
		foreach ($lines as $line)
		{
			echo '' . $this->deletedLine(htmlspecialchars ($line)) . $this->emptyLine() . "\n";
		}
	}

	/**
	 * Short description for '_context'
	 *
	 * Long description (if any) ...
	 *
	 * @param      array<int,string> $lines
	 * @return     void
	 */
	public function _context($lines)
	{
		foreach ($lines as $line)
		{
			echo '<div>' .
				$this->contextLine(htmlspecialchars ($line)) .
				$this->contextLine(htmlspecialchars ($line)) .
				"</div>\n";
		}
	}

	/**
	 * Short description for '_changed'
	 *
	 * Long description (if any) ...
	 *
	 * @param      array<int,string> $orig
	 * @param      array<int,string> $closing
	 * @return     void
	 */
	public function _changed($orig, $closing)
	{
		$diff = new WordLevelDiff($orig, $closing);
		$del = $diff->orig();
		$add = $diff->closing();

		// Notice that WordLevelDiff returns HTML-escaped output.
		// Hence, we will be calling addedLine/deletedLine without HTML-escaping.

		while ($line = array_shift($del))
		{
			$aline = array_shift($add);
			echo '' . $this->deletedLine($line) . $this->addedLine($aline) . "\n";
		}
		foreach ($add as $line)
		{
			// If any leftovers
			echo '' . $this->emptyLine() . $this->addedLine($line) . "\n";
		}
	}
}
