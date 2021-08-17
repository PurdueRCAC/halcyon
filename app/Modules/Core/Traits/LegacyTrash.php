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

	/**
	 * Forcefully reset a timestamp
	 *
	 * @param   array  $fields
	 * @return  void
	 */
	public function forceRestore($fields = array())
	{
		$fields = (array)$fields;

		if (empty($fields))
		{
			$fields[] = $this->getDeletedAtColumn();
		}

		// [!] Hackish workaround for resetting date fields
		//     that don't have a `null` default value.
		//     TODO: Change the table schema!
		try
		{
			ini_set('mysql.connect_timeout', '3');
			ini_set('output_buffering', '8192');

			$db = mysqli_init();
			$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
			$db->real_connect(
				config('database.connections.mysql.host'),
				config('database.connections.mysql.username'),
				config('database.connections.mysql.password'),
				config('database.connections.mysql.database')
			);

			// [!] Horrible workaround for strict datetime mode
			// Get the SQL mode
			$res = mysqli_query($db, "SELECT @@sql_mode");
			$sqlmode = mysqli_fetch_assoc($res);

			if ($sqlmode['@@sql_mode'])
			{
				mysqli_query($db, "SET sql_mode=''");
			}

			// Update the field(s)
			foreach ($fields as $k => $field)
			{
				$fields[$k] = "`$field`='0000-00-00 00:00:00'";
			}
			$fields = implode(', ', $fields);

			$sql = "UPDATE " . $this->getTable() . " SET $fields WHERE `id`=" . $this->id;

			if (!mysqli_query($db, $sql))
			{
				throw new \Exception('MySQLi error code: ' . mysqli_errno($db));
			}

			// Reset the SQL mode
			if ($sqlmode['@@sql_mode'])
			{
				mysqli_query($db, "SET sql_mode='" . $sqlmode['@@sql_mode'] . "'");
			}

			mysqli_close($db);
		}
		catch (\Exception $e)
		{
			// Do nothing
		}
	}
}
