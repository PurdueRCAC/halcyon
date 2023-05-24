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
	 * @var  array<int,string>
	 **/
	private $errors = array();

	/**
	 * Sets all errors at once, overwritting any existing errors
	 *
	 * @param   array<int,string>  $errors  The errors to set
	 * @return  self
	 **/
	public function setErrors($errors): self
	{
		$this->errors = $errors;
		return $this;
	}

	/**
	 * Adds error to the existing set
	 *
	 * @param   string  $error  The error to add
	 * @return  self
	 **/
	public function addError($error): self
	{
		$this->errors[] = $error;
		return $this;
	}

	/**
	 * Returns all errors
	 *
	 * @return  array<int,string>
	 **/
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Determine if the default message bag has any messages.
	 *
	 * @return bool
	 */
	public function hasErrors(): bool
	{
		return count($this->errors) > 0;
	}

	/**
	 * Returns the first error
	 *
	 * @return  string
	 **/
	public function getError(): string
	{
		return $this->hasErrors() ? $this->errors[0] : '';
	}
}
