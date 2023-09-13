<?php

namespace App\Modules\History\Helpers\Diff;

use App\Modules\History\Helpers\Diff\Operation\Copy;
use Closure;

/**
 * A class to format Diffs
 *
 * This class formats the diff in classic diff format.
 * It is intended that this class be customized via inheritance,
 * to obtain fancier outputs.
 *
 * @todo document
 */
class Formatter
{
	/**
	 * Number of leading context "lines" to preserve.
	 *
	 * This should be left at zero for this class, but subclasses
	 * may want to set this to other values.
	 *
	 * @var int
	 */
	public $leading_context_lines = 0;

	/**
	 * Number of trailing context "lines" to preserve.
	 *
	 * This should be left at zero for this class, but subclasses
	 * may want to set this to other values.
	 *
	 * @var int
	 */
	public $trailing_context_lines = 0;

	/**
	 * Description for 'i'
	 *
	 * @var int
	 */
	public $i = 0;

	/**
	 * Format a diff.
	 *
	 * @param   object  $diff  A Diff object.
	 * @param   Closure|null $formatContextOutput
	 * @return  string  The formatted output.
	 */
	public function format($diff, Closure $formatContextOutput = null)
	{
		$xi = $yi = 1;
		$x0 = 0;
		$y0 = 0;
		$block = false;
		$context = array();

		$nlead  = $this->leading_context_lines;
		$ntrail = $this->trailing_context_lines;

		$this->_start_diff();

		echo '<table class="table diffs">'."\n";
		echo "\t".'<tbody>'."\n";
		foreach ($diff->edits as $edit)
		{
			//$this->i++;
			if ($edit->type == 'copy')
			{
				if (is_array($block))
				{
					if (count($edit->orig) <= $nlead + $ntrail)
					{
						$block[] = $edit;
					}
					else
					{
						if ($ntrail)
						{
							$context = array_slice($edit->orig, 0, $ntrail);
							$block[] = new Copy($context);
						}
						$this->_block(
							$x0,
							$ntrail + $xi - $x0,
							$y0,
							$ntrail + $yi - $y0,
							$block
						);
						$block = false;
					}
				}
				$context = $edit->orig;
			}
			else
			{
				if (!is_array($block))
				{
					$context = array_slice($context, count($context) - $nlead);
					$x0 = $xi - count($context);
					$y0 = $yi - count($context);
					$block = array();
					if ($context)
					{
						$block[] = new Copy($context);
					}
				}
				$block[] = $edit;
			}

			if ($edit->orig)
			{
				$xi += count($edit->orig);
			}
			if ($edit->closing)
			{
				$yi += count($edit->closing);
			}

			foreach ($context as $ctx)
			{
				if ($formatContextOutput)
				{
					$ctx = $formatContextOutput($ctx);
				}

				$this->i++;
				echo "\t\t".'<tr>'."\n";
				echo "\t\t\t".'<th scope="row">'.$this->i.'</th>'."\n";
				echo "\t\t\t".'<td colspan="4">'.htmlspecialchars($ctx).'</td>'."\n";
				echo "\t\t".'</tr>'."\n";
			}
		}

		if (is_array($block))
		{
			$this->_block(
				$x0,
				$xi - $x0,
				$y0,
				$yi - $y0,
				$block
			);
		}
		echo "\t".'</tbody>'."\n";
		echo '</table>'."\n";
		$end = $this->_end_diff();

		return $end;
	}

	/**
	 * Short description for '_block'
	 *
	 * Long description (if any) ...
	 *
	 * @param      int $xbeg
	 * @param      int $xlen
	 * @param      int $ybeg
	 * @param      int $ylen
	 * @param      array<int,Operation>   &$edits
	 * @return     void
	 */
	public function _block($xbeg, $xlen, $ybeg, $ylen, &$edits)
	{
		$this->_start_block($this->_block_header($xbeg, $xlen, $ybeg, $ylen));
		foreach ($edits as $edit)
		{
			if ($edit->type == 'copy')
			{
				$this->_context($edit->orig);
			}
			elseif ($edit->type == 'add')
			{
				$this->_added($edit->closing);
			}
			elseif ($edit->type == 'delete')
			{
				$this->_deleted($edit->orig);
			}
			elseif ($edit->type == 'change')
			{
				$this->_changed($edit->orig, $edit->closing);
			}
			else
			{
				trigger_error('Unknown edit type', E_USER_ERROR);
			}
		}
		$this->_end_block();
	}

	/**
	 * Short description for '_start_diff'
	 *
	 * @return     void
	 */
	public function _start_diff()
	{
		ob_start();
	}

	/**
	 * Short description for '_end_diff'
	 *
	 * @return     string
	 */
	public function _end_diff()
	{
		$val = ob_get_contents();
		ob_end_clean();
		return $val;
	}

	/**
	 * Short description for '_block_header'
	 *
	 * @param      int  $xbeg
	 * @param      int $xlen
	 * @param      int  $ybeg
	 * @param      int $ylen
	 * @return     string
	 */
	public function _block_header($xbeg, $xlen, $ybeg, $ylen)
	{
		if ($xlen > 1)
		{
			$xbeg .= ',' . ($xbeg + $xlen - 1);
		}
		if ($ylen > 1)
		{
			$ybeg .= ',' . ($ybeg + $ylen - 1);
		}

		return $xbeg . ($xlen ? ($ylen ? 'c' : 'd') : 'a') . $ybeg;
	}

	/**
	 * Short description for '_start_block'
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
	 * @return     void
	 */
	public function _end_block()
	{
	}

	/**
	 * Short description for '_lines'
	 *
	 * @param      array<int,string>  $lines
	 * @param      string $prefix
	 * @return     void
	 */
	public function _lines($lines, $prefix = ' ')
	{
		foreach ($lines as $line)
		{
			echo "$prefix $line\n";
		}
	}

	/**
	 * Short description for '_context'
	 *
	 * @param      array<int,string> $lines
	 * @return     void
	 */
	public function _context($lines)
	{
		$this->_lines($lines);
	}

	/**
	 * Short description for '_added'
	 *
	 * @param      array<int,string> $lines
	 * @return     void
	 */
	public function _added($lines)
	{
		$this->_lines($lines, '>');
	}

	/**
	 * Short description for '_deleted'
	 *
	 * @param      array<int,string> $lines
	 * @return     void
	 */
	public function _deleted($lines)
	{
		$this->_lines($lines, '<');
	}

	/**
	 * Short description for '_changed'
	 *
	 * @param      array<int,string> $orig
	 * @param      array<int,string> $closing
	 * @return     void
	 */
	public function _changed($orig, $closing)
	{
		$this->_deleted($orig);
		echo "---\n";
		$this->_added($closing);
	}
}
