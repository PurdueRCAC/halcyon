<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Model for a scheduler policy
 */
class Policy extends Model
{
	use Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var string
	 **/
	protected $table = 'schedulers';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'hostname' => 'notempty'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Defines a relationship to schedulers
	 *
	 * @return  object
	 */
	public function schedulers()
	{
		return $this->hasMany(Queue::class, 'schedulerpolicyid');
	}
}
