<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Session model
 */
class Session extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'sessions';

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array<int,string>
	 */
	protected $dates = [
		'last_activity',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'id' => 'string',
	];

	/**
	 * Get user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
