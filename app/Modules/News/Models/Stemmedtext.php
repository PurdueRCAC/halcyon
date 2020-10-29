<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * News model for stemmed text
 */
class Stemmedtext extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'newsstemmedtext';

	/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'stemmedtext' => 'required'
	);

	/**
	 * Defines a relationship to an article
	 *
	 * @return  object
	 */
	public function article()
	{
		return $this->belongsTo(Article::class, 'id');
	}
}
