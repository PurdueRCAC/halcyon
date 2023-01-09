<?php

namespace App\Halcyon\Access;

use App\Halcyon\Models\Nested;
use Exception;

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
	 * Generates automatic rules field value
	 *
	 * @param   object|string  $rules
	 * @return  void
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
	 * @return  Asset
	 * @throws  Exception
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

		if (!$result || !$result->id)
		{
			throw new Exception('No base permissions found.');
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
