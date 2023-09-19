<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Session model
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $payload
 * @property Carbon|null $last_activity
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
	 * TThe attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'id' => 'string',
		'last_activity' => 'datetime',
	];

	/**
	 * Get user
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
