<?php

namespace App\Modules\History\Helpers\Diff\Operation;

use App\Modules\History\Helpers\Diff\Operation;

/**
 * Change operation
 */
class Change extends Operation
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type = 'change';

	/**
	 * Constructor
	 *
	 * @param  array<int,string> $orig
	 * @param  array<int,string> $closing
	 * @return void
	 */
	public function __construct($orig, $closing)
	{
		$this->orig = $orig;
		$this->closing = $closing;
	}

	/**
	 * Reverse operation
	 *
	 * @return Change
	 */
	public function reverse()
	{
		return new Change($this->closing, $this->orig);
	}
}
