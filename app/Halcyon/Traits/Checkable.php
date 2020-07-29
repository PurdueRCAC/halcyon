<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Traits;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Database ORM trait for checking records in/out
 */
trait Checkable
{
	/**
	 * Checks to see if the current model is checked out by someone else
	 *
	 * @return  bool
	 **/
	public function isCheckedOut()
	{
		return ($this->checked_out && $this->checked_out != auth()->user()->id);
	}

	/**
	 * Checks out the current model
	 *
	 * @return  boolean
	 **/
	public function checkout()
	{
		if ($this->{$this->primaryKey})
		{
			$data = [];

			if (array_key_exists('checked_out_time', $this->attributes))
			{
				$data['checked_out_time'] = Carbon::now()->toDatetimeString();
			}

			if (array_key_exists('checked_out', $this->attributes))
			{
				$data['checked_out'] = (int) auth()->user()->id;
			}

			if (empty($data))
			{
				// There is no 'checked_out_time' or 'checked_out' column
				return true;
			}

			// we've got the left value, and now that we've processed
			// the children of this node we also know the right value
			$result = $this->newQuery()
				->where($this->primaryKey, '=', (int) $this->{$this->primaryKey})
				->update($data);

			return $result;
		}

		return true;
	}

	/**
	 * Checks back in the current model
	 *
	 * @return  boolean
	 **/
	public function checkin()
	{
		if ($this->{$this->primaryKey})
		{
			$data = [];

			if (array_key_exists('checked_out_time', $this->attributes))
			{
				$data['checked_out_time'] = null;
			}

			if (array_key_exists('checked_out', $this->attributes))
			{
				$data['checked_out'] = 0;
			}

			if (empty($data))
			{
				// There is no 'checked_out_time' or 'checked_out' column
				return true;
			}

			// we've got the left value, and now that we've processed
			// the children of this node we also know the right value
			$result = $this->newQuery()
				->where($this->primaryKey, '=', (int) $this->{$this->primaryKey})
				->update($data);

			return $result;
		}

		return true;
	}
}
