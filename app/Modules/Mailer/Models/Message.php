<?php

namespace App\Modules\Mailer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Config\Repository;
use App\Modules\History\Traits\Historable;
use App\Modules\History\Models\Log;
use App\Halcyon\Models\Casts\Params;
use Carbon\Carbon;

/**
 * Mail message
 *
 * @property int    $id
 * @property string $subject
 * @property string $body
 * @property string $alert
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $sent_at
 * @property int    $sent_by
 * @property int    $template
 * @property Repository $recipients
 *
 * @property string $api
 */
class Message extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'mail_messages';

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
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'recipients' => Params::class,
		'sent_at' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * Get the creator of this entry
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by');
	}

	/**
	 * Get the modifier of this entry
	 *
	 * @return  BelongsTo
	 */
	public function modifier(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'updated_by');
	}

	/**
	 * Defines a relationship to feedback
	 *
	 * @return  HasMany
	 */
	public function logs(): HasMany
	{
		return $this->hasMany(Log::class, 'objectid')->where('app', '=', 'mail');
	}

	/**
	 * Get the creator of this entry
	 *
	 * @return  BelongsTo
	 */
	public function sender(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'sent_by');
	}
}
