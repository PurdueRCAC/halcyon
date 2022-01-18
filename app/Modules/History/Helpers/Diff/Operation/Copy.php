<?php

namespace App\Modules\History\Helpers\Diff\Operation;

use App\Modules\History\Helpers\Diff\Operation;

/**
 * Copy operation
 */
class Copy extends Operation
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type = 'copy';

	/**
	 * Constructor
	 *
	 * @param  array   $orig
	 * @param  boolean $closing
	 * @return void
	 */
	public function __construct($orig, $closing = false)
	{
		if (!is_array($closing))
		{
			$closing = $orig;
		}
		$this->orig = $orig;
		$this->closing = $closing;
	}

	/**
	 * Reverse operation
	 *
	 * @return object
	 */
	public function reverse()
	{
		return new Copy($this->closing, $this->orig);
	}
}
