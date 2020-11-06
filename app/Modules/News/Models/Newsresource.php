<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Resources\Entities\Asset;

/**
 * News model mapping to resources
 */
class Newsresource extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 **/
	protected $table = 'newsresources';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'newsid'     => 'positive|nonzero',
		'resourceid' => 'positive|nonzero'
	);

	/**
	 * Defines a relationship to news article
	 *
	 * @return  object
	 */
	public function news()
	{
		return $this->belongsTo(Article::class, 'newsid');
	}

	/**
	 * Defines a relationship to resources
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo(Asset::class, 'resourceid')->withTrashed();
	}
}
