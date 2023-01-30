<?php

namespace App\Halcyon\Access;

use App\Halcyon\Models\Nested;
use Exception;

/**
 * Access asset
 * 
 * An asset is an entry specifying permissions on some
 * object. For example, an entry with name = 'page.123'
 * refers to a Page entry with ID #123.
 *
 * @property int    $id
 * @property int    $parent_id
 * @property int    $lft
 * @property int    $rgt
 * @property int    $level
 * @property string $name
 * @property string $title
 * @property string $rules
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
	public function setRulesAttribute($rules): void
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
	 * @return  self
	 */
	public static function findByName(string $name): self
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
	 * @return  self
	 * @throws  Exception
	 */
	public static function getRoot(): self
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
	 * @return  int
	 */
	public static function getRootId(): int
	{
		return self::getRoot()->id;
	}
}
