<?php

namespace App\Halcyon\Access;

use App\Halcyon\Database\Nested;

/**
 * Access asset
 */
class Asset extends Nested
{
	/**
	 * Timestamps
	 *
	 * @var  bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'permissions';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'lft';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'title' => 'required|string|max:50',
		'name'  => 'required|string|max:100'
	);

	/**
	 * Generates automatic rules field value
	 *
	 * @param   mixed  $rules
	 * @return  string
	 */
	public function setRulesAttribute($rules)
	{
		if (!$rules)
		{
			$rules = '{}';
		}

		if (!is_string($rules))
		{
			$rules = (string)$rules;
		}

		$this->attributes['rules'] = $rules;
	}

	/**
	 * Method to load an asset by it's name.
	 *
	 * @param   string  $name
	 * @return  object
	 */
	public static function findByName($name)
	{
		$model = self::query()
			->where('name', '=', $name)
			->first();

		if (!$model)
		{
			$model = new self();
		}

		return $model;
	}

	/**
	 * Method to load root node
	 *
	 * @return  integer
	 */
	public static function getRoot()
	{
		$result = self::query()
			->where('parent_id', '=', 0)
			->first();

		if (!$result || !$result->id)
		{
			$result = self::query()
				->where('lft', '=', 0)
				->first();

			if (!$result || !$result->id)
			{
				$result = self::query()
					->where('name', '=', 'root.1')
					->first();
			}
		}

		return $result;
	}

	/**
	 * Method to load root node ID
	 *
	 * @return  integer
	 */
	public static function getRootId()
	{
		return self::getRoot()->id;
	}
}
