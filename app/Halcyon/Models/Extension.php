<?php

namespace App\Halcyon\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Traits\Checkable;
use App\Halcyon\Models\Casts\Params;
use App\Halcyon\Form\Form;
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
 * @property Repository $params
 * @property int    $checked_out
 * @property Carbon|null $checked_out_time
 * @property int    $ordering
 * @property Carbon|null $updated_at
 * @property int    $updated_by
 */
class Extension extends Model
{
	use Historable, Checkable;

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
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'client_id' => 'integer',
		'enabled' => 'integer',
		'access' => 'integer',
		'protected' => 'integer',
		'params' => Params::class,
		'checked_out_time' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * Get a module by name
	 *
	 * @param  string  $name
	 * @return Extension|null
	 */
	public static function findByModule($name)
	{
		return self::query()
			->where('element', '=', $name)
			->where('type', '=', 'module')
			->first();
	}

	/**
	 * Register extension language
	 *
	 * @return void
	 */
	public function registerLanguage(): void
	{
		if ($this->type == 'module')
		{
			if (Module::has($this->element))
			{
				app('translator')->addNamespace($this->element, $this->path() . '/Resources/lang');
			}
		}
		else
		{
			app('translator')->addNamespace($this->type . '.' . $this->element, $this->path() . '/lang');
		}
	}

	/**
	 * Get the installed path
	 *
	 * @return  string
	 */
	public function path(): string
	{
		if (is_null($this->path))
		{
			$this->path = '';
			$element = $this->element;

			if ($this->type == 'module')
			{
				$this->path = module_path($this->element);
			}
			elseif ($this->type == 'widget')
			{
				if (substr($element, 0, 4) == 'mod_')
				{
					$element = substr($element, 4);
				}
				$element = ucfirst($element);

				$path = app_path() . '/Widgets/' . $element . '/widget.json';

				if (file_exists($path))
				{
					$this->path = dirname($path);
				}
			}
			elseif ($this->type == 'listener')
			{
				$path = app_path() . '/Listeners/' . $this->folder . '/' . $element . '/listener.json';

				if (file_exists($path))
				{
					$this->path = dirname($path);
				}
			}
			elseif ($this->type == 'theme')
			{
				$path = app_path() . '/Themes/' . $element . '/theme.json';

				if (file_exists($path))
				{
					$this->path = dirname($path);
				}
			}
		}

		return $this->path;
	}

	/**
	 * Get a form
	 *
	 * @return  Form
	 * @throws  \Exception
	 */
	public function getForm(): Form
	{
		$file = $this->path() . '/Config/Params.xml';

		Form::addFieldPath(__DIR__ . '/Fields');

		if (is_dir($this->path() . '/Models/Fields'))
		{
			Form::addFieldPath($this->path() . '/Models/Fields');
		}

		$form = new Form('config.' . $this->type, array('control' => 'params'));

		if (!$form->loadFile($file, false, '//config'))
		{
			throw new \Exception(trans('global.error.load file failed'));
		}

		$data = $this->params->all();

		$form->bind($data);

		return $form;
	}
}
