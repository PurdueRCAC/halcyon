<?php

namespace App\Modules\History\Helpers\Diff;

/**
 * Add operation
 */
class DiffOp_Add extends DiffOp
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type = 'add';

	/**
	 * Short description for 'DiffOp_Add'
	 *
	 * Long description (if any) ...
	 *
	 * @param      unknown $lines Parameter description (if any) ...
	 * @return     void
	 */
	public function __construct($lines)
	{
		$this->closing = $lines;
		$this->orig = false;
	}

	/**
	 * Short description for 'reverse'
	 *
	 * Long description (if any) ...
	 *
	 * @return     object Return description (if any) ...
	 */
	public function reverse()
	{
		return new DiffOp_Delete($this->closing);
	}
}
