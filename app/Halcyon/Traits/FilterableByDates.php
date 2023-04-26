<?php
namespace App\Halcyon\Traits;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Eloquent date scopes
 */
trait FilterableByDates
{
	/**
	 * $query = MyModel::today();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeToday(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereDate($column, Carbon::today());
	}

	/**
	 * $query = MyModel::yesterday();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeYesterday(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereDate($column, Carbon::yesterday());
	}

	/**
	 * $query = MyModel::weekToDate();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeWeekToDate(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()]);
	}

	/**
	 * $query = MyModel::monthToDate();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeMonthToDate(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::now()->startOfMonth(), Carbon::now()]);
	}

	/**
	 * $query = MyModel::quarterToDate();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeQuarterToDate(Builder $query, $column = 'created_at'): Builder
	{
		$now = Carbon::now();
		return $query->whereBetween($column, [$now->startOfQuarter(), $now]);
	}

	/**
	 * $query = MyModel::yearToDate();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeYearToDate(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::now()->startOfYear(), Carbon::now()]);
	}

	/**
	 * $query = MyModel::lastHour();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLastHour(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::now()->subHour(), Carbon::now()]);
	}

	/**
	 * $query = MyModel::last24Hours();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLast24Hours(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::now()->subHours(24), Carbon::now()]);
	}

	/**
	 * $query = MyModel::last7Days();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLast7Days(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::today()->subDays(6), Carbon::now()]);
	}

	/**
	 * $query = MyModel::last30Days();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLast30Days(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::today()->subDays(29), Carbon::now()]);
	}

	/**
	 * $query = MyModel::lastWeek();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLastWeek(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::today()->subWeek(), Carbon::now()]);
	}

	/**
	 * $query = MyModel::lastMonth();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLastMonth(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::today()->subMonth(), Carbon::now()]);
	}

	/**
	 * $query = MyModel::lastQuarter();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLastQuarter(Builder $query, $column = 'created_at'): Builder
	{
		$now = Carbon::now();
		return $query->whereBetween($column, [$now->startOfQuarter()->subMonths(3), $now->startOfQuarter()]);
	}

	/**
	 * $query = MyModel::last6Months();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLast6Month(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::today()->modify('-6 months'), Carbon::now()]);
	}

	/**
	 * $query = MyModel::lastYear();
	 *
	 * @param Builder $query
	 * @param string $column
	 * @return Builder
	 */
	public function scopeLastYear(Builder $query, $column = 'created_at'): Builder
	{
		return $query->whereBetween($column, [Carbon::now()->subYear(), Carbon::now()]);
	}
}
