<?php

namespace App\Halcyon\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;
use Nwidart\Modules\Facades\Module;

/**
 * Extension
 *
 * @property int    $id
 * @property string $name
 * @property string $type
 * @property string $element
 * @property string $folder
 * @property int    $client_id
 * @property int    $enabled
 * @property int    $access
 * @property int    $protected
 * @property string $params
 * @property int    $checked_out
 * @property Carbon|null $checked_out_time
 * @property int    $ordering
 * @property Carbon|null $updated_at
 * @property int    $updated_by
 */
class Extension extends Model
{
	use Historable;

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'extensions';

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
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * Get a module by name
	 *
	 * @param  string  $name
	 * @return self|null
	 */
	public static function findByModule($name)
	{
		return self::query()->where('element', '=', $name)
			->where('type', '=', 'module')
			->get()
			->first();
	}

	/**
	 * Register extension language
	 *
	 * @return void
	 */
	public function registerLanguage(): void
	{
		if ($this->type == 'module' && Module::has($this->element))
		{
			app('translator')->addNamespace($this->element, module_path($this->element) . '/Resources/lang');
		}
	}
}
