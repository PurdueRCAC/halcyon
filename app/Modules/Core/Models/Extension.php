<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Config\Repository;
use Nwidart\Modules\Facades\Module;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Form\Form;
use App\Halcyon\Models\Casts\Params;

/**
 * Extension model
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
	use Historable;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'extensions';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'client_id' => 'integer',
		'protected' => 'integer',
		'access' => 'integer',
		'params' => Params::class,
	];

	/**
	 * The path to the installed files
	 *
	 * @var  string
	 */
	protected $path = null;

	/**
	 * XML manifest
	 *
	 * @var  object|null
	 */
	protected $manifest = null;

	/**
	 * Find a record by name
	 *
	 * @param   string  $name
	 * @param   string  $type
	 * @return  Extension|null
	 */
	public static function findByName(string $name, string $type = 'module'): ?Extension
	{
		return self::query()
			->where('name', '=', $name)
			->where('type', '=', $type)
			->first();
	}

	/**
	 * Find a module by name
	 *
	 * @param   string  $name
	 * @return  Extension|null
	 */
	public static function findModuleByName(string $name): ?Extension
	{
		return self::findByName($name, 'module');
	}

	/**
	 * Find a widget by name
	 *
	 * @param   string  $name
	 * @return  Extension|null
	 */
	public static function findWidgetByName(string $name): ?Extension
	{
		return self::findByName($name, 'widget');
	}

	/**
	 * Find a record by element
	 *
	 * @param   string  $element
	 * @param   string  $type
	 * @return  Extension|null
	 */
	public static function findByElement(string $element, string $type = 'module'): ?Extension
	{
		return self::query()
			->where('element', '=', $element)
			->where('type', '=', $type)
			->first();
	}

	/**
	 * Find a module by element
	 *
	 * @param   string  $element
	 * @return  Extension|null
	 */
	public static function findModuleByElement(string $element): ?Extension
	{
		return self::findByElement($element, 'module');
	}

	/**
	 * Register extension language
	 *
	 * @return void
	 */
	public function registerLanguage(): void
	{
		$name = $this->type . '.' . strtolower($this->element);
		$path = $this->path() . '/lang';

		if ($this->type == 'module')
		{
			$name = strtolower($this->element);
			$path = $this->path() . '/Resources/lang';

			if (!Module::has($this->element))
			{
				return;
			}
		}

		app('translator')->addNamespace($name, $path);
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
	 * Get a value from the manifest
	 *
	 * @param   string  $key
	 * @return  mixed
	 */
	public function manifest($key)
	{
		$val = null;

		if (is_null($this->manifest))
		{
			$dir = $this->path();

			$info = false;

			if ($dir)
			{
				$manifest = $dir . '/' . strtolower($this->type) . '.json';
				if (file_exists($manifest))
				{
					$info = json_decode(file_get_contents($manifest));
				}
			}

			$this->manifest = $info;
		}

		if (isset($this->manifest->$key))
		{
			$val = $this->manifest->$key;
		}

		return $val;
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

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form  $form   The form to validate against.
	 * @param   array<string,mixed>   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 * @return  array<string,mixed>|false   Array of filtered data if valid, false otherwise.
	 * @throws \Exception
	 */
	public function validateFormData($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof \Exception)
		{
			throw $return;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				throw new \Exception(trans($message));
			}

			return false;
		}

		return $data;
	}

	/**
	 * Publish an extension's assets
	 */
	public function publish(): void
	{
		$sourcePath = $this->path() . '/assets';

		$path  = ucfirst($this->type) . 's';
		if ($this->type == 'listener')
		{
			$path .= '/' . strtolower($this->folder);
		}
		$path .= '/' . strtolower($this->element);
		$path = strtolower($path);
		$destinationPath = public_path($path);
		$files = app('files');

		if (!$files->isDirectory($destinationPath))
		{
			$files->makeDirectory($destinationPath, 0775, true);
		}

		foreach ($files->allFiles($sourcePath) as $file)
		{
			$dest = str_replace($sourcePath, $destinationPath, $file);

			if (!$files->exists($dest)
			|| $files->lastModified($file) > $files->lastModified($dest))
			{
				if (!$files->exists(dirname($dest)))
				{
					$files->makeDirectory(dirname($dest), 0775, true);
				}

				$files->copy($file, $dest);
			}
		}
	}
}
