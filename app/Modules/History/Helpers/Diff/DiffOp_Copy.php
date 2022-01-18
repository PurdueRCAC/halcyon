<?php

namespace App\Modules\History\Helpers\Diff;

/**
 * Copy operation
 */
class DiffOp_Copy extends DiffOp
{
	/**
	 * Description for 'type'
	 *
	 * @var string
	 */
	public $type = 'copy';

	/**
	 * Short description for 'DiffOp_Copy'
	 *
	 * Long description (if any) ...
	 *
	 * @param      unknown $orig Parameter description (if any) ...
	 * @param      boolean $closing Parameter description (if any) ...
	 * @return     void
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
	 * Short description for 'reverse'
	 *
	 * Long description (if any) ...
	 *
	 * @return     object Return description (if any) ...
	 */
	public function reverse()
	{
		return new DiffOp_Copy($this->closing, $this->orig);
	}
}
