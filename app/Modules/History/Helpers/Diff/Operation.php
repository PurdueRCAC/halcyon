<?php

namespace App\Modules\History\Helpers\Diff;

/**
 * Diff operation
 */
class Operation
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Description for 'orig'
	 *
	 * @var array<int,string>
	 */
	public $orig;

	/**
	 * Description for 'closing'
	 *
	 * @var array<int,string>
	 */
	public $closing;

	/**
	 * Reverse operation
	 *
	 * @return  void
	 */
	public function reverse()
	{
		trigger_error('pure virtual', E_USER_ERROR);
	}

	/**
	 * Get a count of the number of original
	 *
	 * @return int
	 */
	public function norig()
	{
		return $this->orig ? count($this->orig) : 0;
	}

	/**
	 * Get a count of the number of closing
	 *
	 * @return  int
	 */
	public function nclosing()
	{
		return $this->closing ? count($this->closing) : 0;
	}
}
