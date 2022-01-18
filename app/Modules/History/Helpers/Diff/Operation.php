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
	 * @var array
	 */
	public $orig;

	/**
	 * Description for 'closing'
	 *
	 * @var array
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
	 * Short description for 'norig'
	 *
	 * Long description (if any) ...
	 *
	 * @return integer
	 */
	public function norig()
	{
		return $this->orig ? count($this->orig) : 0;
	}

	/**
	 * Short description for 'nclosing'
	 *
	 * Long description (if any) ...
	 *
	 * @return  integer
	 */
	public function nclosing()
	{
		return $this->closing ? count($this->closing) : 0;
	}
}
