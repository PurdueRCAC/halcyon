<?php

namespace App\Modules\Core\Traits;

use Carbon\Carbon;

/**
 * Handle trash detection and queries for legacy timestamps
 */
trait LegacyTrash
{
	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function isTrashed()
	{
		$c = $this->getDeletedAtColumn();

		return ($this->{$c}
			&& $this->{$c} != '0000-00-00 00:00:00'
			&& $this->{$c} != '-0001-11-30 00:00:00'
			&& $this->{$c} < Carbon::now()->toDateTimeString());
	}

	/**
	 * Query scope where record isn't trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsActive($query)
	{
		$t = $this->getTable();
		$c = $this->getDeletedAtColumn();

		return $query->where(function($where) use ($t, $c)
		{
			$where->whereNull($t . '.' . $c)
					->orWhere($t . '.' . $c, '=', '0000-00-00 00:00:00')
					->orWhere($t . '.' . $c, '>', Carbon::now()->toDateTimeString());
		});
	}

	/**
	 * Query scope where record is trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsTrashed($query)
	{
		$t = $this->getTable();
		$c = $this->getDeletedAtColumn();

		return $query->where(function($where) use ($t, $c)
		{
			$where->whereNotNull($t . '.' . $c)
				->where($t . '.' . $c, '!=', '0000-00-00 00:00:00')
				->where($t . '.' . $c, '<=', Carbon::now()->toDateTimeString());
		});
	}
}
