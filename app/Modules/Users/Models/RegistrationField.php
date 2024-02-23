<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use App\Modules\Tags\Traits\Taggable;
use App\Modules\History\Traits\Historable;
use App\Modules\Users\Events\NoteCreated;
use App\Modules\Users\Events\NoteUpdated;
use App\Modules\Users\Events\NoteDeleted;
use Carbon\Carbon;

/**
 * User note model
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $body
 * @property Carbon|null $created_at
 * @property int    $created_by
 * @property Carbon|null $updated_at
 * @property int    $updated_by
 * @property Carbon|null $deleted_at
 *
 * @property string $api
 */
class RegistrationField extends Model
{
	use SoftDeletes, Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'registration_fields';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'created_at';

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
		'options' => 'array',
	];

	/**
	 * Get creator
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by');
	}

}
