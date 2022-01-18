<?php

namespace App\Modules\History\Helpers\Diff\Operation;

use App\Modules\History\Helpers\Diff\Operation;

/**
 * Add operation
 */
class Add extends Operation
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type = 'add';

	/**
	 * Constructor
	 *
	 * @param   array $lines
	 * @return  void
	 */
	public function __construct($lines)
	{
		$this->closing = $lines;
		$this->orig = false;
	}

	/**
	 * Reverse operation
	 *
	 * @return  object
	 */
	public function reverse()
	{
		return new Delete($this->closing);
	}
}
