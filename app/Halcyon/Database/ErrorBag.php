<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Database\Traits;

/**
 * Error message bag for shared error handling logic
 */
trait ErrorBag
{
	/**
	 * Errors that have been declared
	 *
	 * @var  array
	 **/
	private $errors = array();

	/**
	 * Sets all errors at once, overwritting any existing errors
	 *
	 * @param   array  $errors  The errors to set
	 * @return  $this
	 **/
	public function setErrors($errors)
	{
		$this->errors = new MessageBag(['error' => $errors]);
		return $this;
	}

	/**
	 * Adds error to the existing set
	 *
	 * @param   string  $error  The error to add
	 * @return  $this
	 **/
	public function addError($error)
	{
		$this->errors->add('error', $error);
		return $this;
	}

	/**
	 * Returns all errors
	 *
	 * @return  array
	 **/
	public function getErrors()
	{
		return $this->errors->all('error');
	}

	/**
	 * Determine if the default message bag has any messages.
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return $this->errors->any('error');
	}

	/**
	 * Returns the first error
	 *
	 * @return  string
	 **/
	public function errors()
	{
		if (!$this->errors)
		{
			$this->setErrors([]);
		}

		return $this->errors;
	}
}
