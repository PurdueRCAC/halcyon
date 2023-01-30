<?php

namespace App\Modules\Knowledge\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for snippet associations
 *
 * @property int    $id
 * @property int    $parent_id
 * @property int    $page_id
 * @property int    $lft
 * @property int    $rgt
 * @property int    $level
 * @property string $path
 * @property int    $state
 * @property int    $access
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
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id');
	}

	/**
	 * Defines a relationship to child pages
	 *
	 * @return  HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parent_id');
	}
}
