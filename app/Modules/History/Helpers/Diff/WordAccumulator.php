<?php

namespace App\Modules\History\Helpers\Diff;

/**
 * Additions by Axel Boldt follow,
 * partly taken from diff.php, phpwiki-1.3.3
 */
class WordAccumulator
{
	/**
	 * @var array<int,string>
	 */
	public $_lines = array();

	/**
	 * @var string
	 */
	public $_line = '';

	/**
	 * @var string
	 */
	public $_group = '';

	/**
	 * @var string
	 */
	public $_tag = '';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->_lines = array();
		$this->_line = '';
		$this->_group = '';
		$this->_tag = '';
	}

	/**
	 * Flush out the group
	 *
	 * @param  string $new_tag
	 * @return void
	 */
	public function _flushGroup($new_tag)
	{
		if ($this->_group !== '')
		{
			if ($this->_tag == 'ins')
			{
				$this->_line .= '<ins class="diffchange">' . htmlspecialchars($this->_group) . '</ins>';
			}
			elseif ($this->_tag == 'del')
			{
				$this->_line .= '<del class="diffchange">' . htmlspecialchars($this->_group) . '</del>';
			}
			else
			{
				$this->_line .= htmlspecialchars($this->_group);
			}
		}
		$this->_group = '';
		$this->_tag = $new_tag;
	}

	/**
	 * Flush the line
	 *
	 * @param  string $new_tag
	 * @return void
	 */
	public function _flushLine($new_tag)
	{
		$this->_flushGroup($new_tag);
		if ($this->_line != '')
		{
			array_push($this->_lines, $this->_line);
		}
		else
		{
			// make empty lines visible by inserting an iso-8859-x non-breaking space.
			array_push($this->_lines, '&#160;');
		}
		$this->_line = '';
	}

	/**
	 * Add words to the group
	 *
	 * @param  array $words
	 * @param  string $tag
	 * @return void
	 */
	public function addWords($words, $tag = '')
	{
		if ($tag != $this->_tag)
		{
			$this->_flushGroup($tag);
		}

		foreach ($words as $word)
		{
			// new-line should only come as first char of word.
			if ($word == '')
			{
				continue;
			}
			if ($word[0] == "\n")
			{
				$this->_flushLine($tag);
				$word = substr($word, 1);
			}
			assert(!strstr($word, "\n"));
			$this->_group .= $word;
		}
	}

	/**
	 * Get all the lines
	 *
	 * @return  array<int,string>
	 */
	public function getLines()
	{
		$this->_flushLine('~done');
		return $this->_lines;
	}
}
