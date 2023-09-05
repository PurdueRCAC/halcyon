<?php

namespace App\Modules\History\Helpers\Diff\Formatter;

use App\Modules\History\Helpers\Diff\Formatter;
use App\Modules\History\Helpers\Diff\WordLevelDiff;

/**
 * Wikipedia Table style diff formatter.
 */
class Table extends Formatter
{
	/**
	 * Constructed
	 *
	 * @return void
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
	 * @param      string $xbeg
	 * @param      int $xlen
	 * @param      string $ybeg
	 * @param      int $ylen
	 * @return     string
	 */
	public function _block_header($xbeg, $xlen, $ybeg, $ylen)
	{
		$r  = "\t\t".'<tr>'."\n";
		$r .= "\t\t\t".'<td colspan="2" class="diff-lineno"><!--LINE '.$xbeg.'--></td>'."\n";
		$r .= "\t\t\t".'<td colspan="2" class="diff-lineno"><!--LINE '.$ybeg.'--></td>'."\n";
		$r .= "\t\t".'</tr>'."\n";
		//return $r;
		return '';
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
		//echo '</table>';
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

	// HTML-escape parameter before calling this

	/**
	 * Short description for 'addedLine'
	 *
	 * Long description (if any) ...
	 *
	 * @param      string $line
	 * @param      integer $colspan
	 * @return     mixed
	 */
	public function addedLine($line, $colspan=0)
	{
		return $this->wrapLine('+', 'diff-addedline', $line, $colspan);
	}

	/**
	 * Short description for 'deletedLine'
	 *
	 * HTML-escape parameter before calling this
	 *
	 * @param      string $line
	 * @param      integer $colspan
	 * @return     string
	 */
	public function deletedLine($line, $colspan=0)
	{
		return $this->wrapLine('-', 'diff-deletedline', $line, $colspan);
	}

	/**
	 * Short description for 'contextLine'
	 *
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
	 * Short description for 'wrapLine'
	 *
	 * Long description (if any) ...
	 *
	 * @param      string $marker
	 * @param      string $class
	 * @param      string $line
	 * @param      int $colspan
	 * @return     string
	 */
	private function wrapLine($marker, $class, $line, $colspan=0)
	{
		if ($line !== '')
		{
			// The <div> wrapper is needed for 'overflow: auto' style to scroll properly
			$line = "<div>$line</div>";
		}
		$html  = "\t\t\t".'<td class="diff-marker">'.$marker.'</td>'."\n";
		$html .= "\t\t\t".'<td ';
		$html .= ($colspan > 0) ? 'colspan="'.$colspan.'" ' : '';
		$html .= 'class="'.$class.'">'.$line.'</td>'."\n";
		return $html;
	}

	/**
	 * Generate an empty line
	 *
	 * @return     string
	 */
	public function emptyLine()
	{
		//return "\t\t\t".'<td colspan="2">&nbsp;</td>'."\n";
		return "\n";
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
			$this->i++;
			echo "\t\t".'<tr>'."\n";
			echo "\t\t\t".'<th scope="row">'.$this->i.'</th>'."\n";
			echo $this->emptyLine() . $this->addedLine(htmlspecialchars($line), 3);
			echo "\t\t".'</tr>'."\n";
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
			$this->i++;
			echo "\t\t".'<tr>'."\n";
			echo "\t\t\t".'<th scope="row">'.$this->i.'</th>'."\n";
			echo $this->deletedLine(htmlspecialchars($line), 3) . $this->emptyLine();
			echo "\t\t".'</tr>'."\n";
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
		/*foreach ($lines as $line)
		{
			echo '<tr>' .
				$this->contextLine(htmlspecialchars ($line)) .
				$this->contextLine(htmlspecialchars ($line)) . "</tr>\n";
		}*/
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
			$this->i++;
			$aline = array_shift($add);
			echo "\t\t".'<tr>'."\n";
			echo "\t\t\t".'<th scope="row">'.$this->i.'</th>'."\n";
			echo $this->deletedLine($line) . $this->addedLine($aline);
			echo "\t\t".'</tr>'."\n";
		}
		// If any leftovers
		foreach ($add as $line)
		{
			$this->i++;
			echo "\t\t".'<tr>'."\n";
			echo "\t\t\t".'<th scope="row">'.$this->i.'</th>'."\n";
			echo $this->emptyLine() . $this->addedLine($line, 3);
			echo "\t\t".'</tr>'."\n";
		}
		//$this->i--;
	}
}
