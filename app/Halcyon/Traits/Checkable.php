<?php

namespace App\Halcyon\Traits;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Database ORM trait for checking records in/out
 */
trait Checkable
{
	/**
	 * Checks to see if the current model is checked out
	 *
	 * @return  bool
	 **/
	public function isCheckedOut(): bool
	{
		return ($this->checked_out || $this->checked_out_time != null);
	}

	/**
	 * Checks to see if the current model is checked out by the current user
	 *
	 * @return  bool
	 **/
	public function isCheckedOutByMe(): bool
	{
		return ($this->isCheckedOut() && auth()->user() && $this->checked_out == auth()->user()->id);
	}

	/**
	 * Checks out the current model
	 *
	 * @return  bool
	 **/
	public function checkOut(): bool
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

			if (!empty($data))
			{
				$result = $this->newQuery()
					->where($this->primaryKey, '=', (int) $this->{$this->primaryKey})
					->update($data);

				if (!$result)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks back in the current model
	 *
	 * @return  bool
	 **/
	public function checkIn(): bool
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

			if (!empty($data))
			{
				$result = $this->newQuery()
					->where($this->primaryKey, '=', (int) $this->{$this->primaryKey})
					->update($data);

				if (!$result)
				{
					return false;
				}
			}
		}

		return true;
	}
}
