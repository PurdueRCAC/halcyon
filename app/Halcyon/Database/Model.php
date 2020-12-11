<?php

namespace App\Halcyon\Database;

use Illuminate\Database\Eloquent\Model as BaseModel;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;

/**
 * Database ORM class for implementing nested set records
 */
class Model extends BaseModel
{
	use ErrorBag, Validatable;

	/**
	 * Database state constants
	 **/
	const STATE_UNPUBLISHED = 0;
	const STATE_PUBLISHED   = 1;
	const STATE_DELETED     = 2;

	/**
	 * Save the model to the database.
	 *
	 * @param   array  $options
	 * @return  bool
	 */
	public function save(array $options = [])
	{
		if (!$this->validate())
		{
			return false;
		}

		return parent::save($options);
	}

	/**
	 * Does the record exist?
	 *
	 * @return  boolean
	 */
	public function exists()
	{
		return !!$this->id;
	}
}
