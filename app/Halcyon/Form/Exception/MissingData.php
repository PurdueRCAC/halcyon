<?php

namespace App\Halcyon\Form\Exception;

class MissingData extends \Exception
{
	/**
	 * Returns to error message
	 *
	 * @return  string  Error message
	 */
	public function __toString()
	{
		return $this->getMessage();
	}

	/**
	 * Returns to error message
	 *
	 * @return  string  Error message
	 */
	public function toString()
	{
		return $this->__toString();
	}
}
