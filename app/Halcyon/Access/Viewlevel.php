<?php

namespace App\Halcyon\Access;

use Illuminate\Database\Eloquent\Model;

/**
 * Viewlevel
 *
 * @property int    $id
 * @property string $title
 * @property int    $ordering
 * @property string|array<int,int> $rules JSON string converted to an array of integers
 *
 * @property string $api
 */
class Viewlevel extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'viewlevels';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'ordering';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'rules' => 'array',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Generates ordering field value
	 *
	 * @return  int
	 */
	public function incrementOrdering(): int
	{
		$last = self::query()
			->select('ordering')
			->orderBy('ordering', 'desc')
			->first();

		return ($last ? intval($last->ordering) + 1 : 1);
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted(): void
	{
		static::creating(function ($model)
		{
			$model->ordering = $model->incrementOrdering();
		});
	}

	/**
	 * Get a list of the Roles for Viewing Access Levels
	 *
	 * @return  string  Comma separated list of Roles
	 */
	public function visibleByRoles(): string
	{
		$groups = '';

		$rules = is_string($this->rules)
			? json_decode($this->rules)
			: $this->rules;

		if (!empty($rules))
		{
			$titles = Role::query()
				->whereIn('id', $rules)
				->pluck('title')
				->toArray();

			$groups = implode(', ', $titles);
		}

		return $groups;
	}
}
