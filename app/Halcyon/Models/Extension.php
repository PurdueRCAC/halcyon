<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'extensions';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'extension_id';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'extension_id';

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
		'extension_id'
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
	 * Field of science
	 *
	 * @return  object
	 */
	public function warningTime()
	{
		return $this->belongsTo(self::class, 'warningtimeperiodid');
	}

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
