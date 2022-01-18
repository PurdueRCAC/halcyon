<?php

namespace App\Modules\History\Helpers\Diff;

/**
 * Delete operation
 */
class DiffOp_Delete extends DiffOp
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type = 'delete';

	/**
	 * Short description for 'DiffOp_Delete'
	 *
	 * Long description (if any) ...
	 *
	 * @param      unknown $lines Parameter description (if any) ...
	 * @return     void
	 */
	public function __construct($lines)
	{
		$this->orig = $lines;
		$this->closing = false;
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
		return new DiffOp_Add($this->orig);
	}
}
