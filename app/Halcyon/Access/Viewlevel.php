<?php

namespace App\Halcyon\Access;

use Illuminate\Database\Eloquent\Model;

/**
 * Viewlevel
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
	 * @var array
	 */
	protected $casts = [
		'rules' => 'array',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Generates ordering field value
	 *
	 * @return  integer
	 */
	public function incrementOrdering()
	{
		$last = self::query()
			->select('ordering')
			->orderBy('ordering', 'desc')
			->first();

		return ($last ? intval($last->ordering) + 1 : 1);
	}

	/**
	 * Saves the current model to the database
	 *
	 * @param   array  $options
	 * @return  bool
	 */
	public function save(array $options = [])
	{
		if (!$this->id && !$this->ordering)
		{
			$this->ordering = $this->incrementOrdering();
		}

		return parent::save($options);
	}

	/**
	 * Get a list of the Roles for Viewing Access Levels
	 *
	 * @return  string  Comma separated list of Roles
	 */
	public function visibleByRoles()
	{
		$rules = is_string($this->rules) ? json_decode($this->rules) : $this->rules;

		if (!$rules)
		{
			return '';
		}

		$groups = Role::query()
			->whereIn('id', $rules)
			->get()
			->pluck('title')
			->toArray();

		$groups = implode(', ', $groups);

		return $groups;
	}
}
