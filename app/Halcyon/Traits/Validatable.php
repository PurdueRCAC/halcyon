<?php

namespace App\Halcyon\Traits;

use Illuminate\Support\Facades\Validator;

/**
 * Database ORM trait for validating attributes
 */
trait Validatable
{
	/**
	 * Validation errors
	 *
	 * @var  array<int,string>
	 **/
	public $validationErrors = array();

	/**
	 * Retrieves the model rules
	 *
	 * @return  array
	 **/
	public function getRules(): array
	{
		if (!isset($this->rules))
		{
			$this->rules = array();
		}

		return $this->rules;
	}

	/**
	 * Adds a new rule to the validation set
	 *
	 * @param   string  $key   The field to which the rule applies
	 * @param   mixed   $rule  The rule to add
	 * @return  self
	 **/
	public function addRule($key, $rule): self
	{
		$this->getRules();

		$this->rules[$key] = $rule;

		return $this;
	}

	/**
	 * Adds a new rule to the validation set
	 *
	 * @param   string  $key   The field to which the rule applies
	 * @return  self
	 **/
	public function removeRule($key): self
	{
		$this->getRules();

		if (isset($this->rules[$key]))
		{
			unset($this->rules[$key]);
		}

		return $this;
	}

	/**
	 * Validates the set data attributes against the model rules
	 *
	 * @param   array  $data
	 * @return  bool
	 **/
	public function validate($data = array()): bool
	{
		if (!empty($this->rules))
		{
			$data = empty($data) ? $this->attributes : $data;

			$validity = Validator::make($data, $this->rules);

			if ($validity->fails())
			{
				if ($this instanceof ErrorBag)
				{
					$this->validationErrors = $validity->messages();
				}

				return false;
			}
		}

		return true;
	}

	/**
	 * Get list of validation errors
	 *
	 * @return  array
	 **/
	public function validationErrors(): array
	{
		return $this->validationErrors;
	}
}
