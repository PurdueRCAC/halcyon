<?php

namespace App\Modules\History\Helpers\Diff;

/**
 * Change operation
 */
class DiffOp_Change extends DiffOp
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type = 'change';

	/**
	 * Short description for 'DiffOp_Change'
	 *
	 * Long description (if any) ...
	 *
	 * @param      unknown $orig Parameter description (if any) ...
	 * @param      unknown $closing Parameter description (if any) ...
	 * @return     void
	 */
	public function __construct($orig, $closing)
	{
		$this->orig = $orig;
		$this->closing = $closing;
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
		return new DiffOp_Change($this->closing, $this->orig);
	}
}
