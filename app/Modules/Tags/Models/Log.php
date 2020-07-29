<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Tags\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tag log
 */
class Log extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 */
	protected $table = 'tags_log';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'action'  => 'notempty',
		'tag_id'  => 'positive|nonzero',
		'user_id' => 'positive|nonzero'
	);

	/**
	 * Get parent tag
	 *
	 * @return  object
	 */
	public function tag()
	{
		return $this->belongsTo(Tag::class, 'tag_id');
	}

	/**
	 * Actor profile
	 *
	 * @return  object
	 */
	public function actor()
	{
		return $this->belongsTo('App\Modules\User\Models\User', 'actorid');
	}
}
