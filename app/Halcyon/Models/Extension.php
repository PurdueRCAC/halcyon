<?php

namespace App\Halcyon\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use Nwidart\Modules\Facades\Module;

/**
 * Extension
 */
class Extension extends Model
{
	use ErrorBag, Validatable, Historable;

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
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * Get a module by name
	 *
	 * @param  string  $name
	 * @return object
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
	public function registerLanguage()
	{
		if ($this->type == 'module' && Module::has($this->element))
		{
			app('translator')->addNamespace($this->element, module_path($this->element) . '/Resources/lang');
		}
	}
}
