<?php

namespace App\Modules\History\Helpers\Diff\Operation;

use App\Modules\History\Helpers\Diff\Operation;

/**
 * Delete operation
 */
class Delete extends Operation
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type = 'delete';

	/**
	 * Constructor
	 *
	 * @param  array $lines
	 * @return void
	 */
	public function __construct($lines)
	{
		$this->orig = $lines;
		$this->closing = false;
	}

	/**
	 * Reverse opration
	 *
	 * @return object
	 */
	public function reverse()
	{
		return new Add($this->orig);
	}
}
