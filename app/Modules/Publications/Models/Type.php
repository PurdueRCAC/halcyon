<?php

namespace App\Modules\Publications\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for publication types
 */
class Type extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'publication_types';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Get a list of publications
	 *
	 * @return  object
	 */
	public function publications()
	{
		return $this->hasMany(Publication::class, 'type_id');
	}

	/*protected $types = [
		'unknown',
		'journal',
		'proceedings',
		'inbook',
		'phdthesis',
		'masterstheis',
		'conference',
		'techreport',
		'magazine',
		'article',
		'preprint',
		'xarchive',
		'patent',
		'notes',
		'letter',
		'syllabus',
		'tutorial',
		'arxiv',
		'inproceedings',
		'misc',
		'techbrief',
		'invited_conference',
		'technical_review',
		'invited_seminar',
		'articles_citing_nemo',
	];

	public static function all()
	{
		foreach ($this->types as $type)
		{
			$item = new Fluent;
			$item->alias = $type;
			$item->name = trans('publications::publications.types.' . $type);

			$items[] = $item;
		}

		return collect($items);
	}
	
	public static function find($alias)
	{
		$item = null;

		foreach ($this->types as $type)
		{
			if ($alias == $type)
			{
				$item = new Fluent;
				$item->alias = $type;
				$item->name = trans('publications::publications.types.' . $type);
				break;
			}
		}

		return $item;
	}*/
}
