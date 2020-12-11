<?php

namespace App\Halcyon\Traits;

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
		$this->errors = $errors;
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
		$this->errors[] = $error;
		return $this;
	}

	/**
	 * Returns all errors
	 *
	 * @return  array
	 **/
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Determine if the default message bag has any messages.
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return count($this->errors) > 0;
	}

	/**
	 * Returns the first error
	 *
	 * @return  string
	 **/
	public function getError()
	{
		return $this->hasErrors() ? $this->errors[0] : '';
	}
}
