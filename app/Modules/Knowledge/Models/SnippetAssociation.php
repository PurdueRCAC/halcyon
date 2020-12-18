<?php

namespace App\Modules\Knowledge\Models;

/**
 * Model for a subresource mapping
 */
class SnippetAssociation extends Associations
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'kb_snippet_associations';

	/**
	 * Defines a relationship to a parent page
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(self::class, 'parent_id');
	}

	/**
	 * Defines a relationship to a parent page
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->hasMany(self::class, 'parent_id');
	}
}
