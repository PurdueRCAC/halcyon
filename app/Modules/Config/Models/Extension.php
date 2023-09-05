<?php

namespace App\Modules\Config\Models;

use Illuminate\Database\Eloquent\Model;
use Nwidart\Modules\Facades\Module;
use App\Halcyon\Models\Casts\Params;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Form\Form;
use Exception;

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
 * @property \Illuminate\Support\Fluent $params
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
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = null;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'extensions';

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
		'published' => 'integer',
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
	 * @var  object
	 */
	protected $manifest = null;

	/**
	 * Find a record by name
	 *
	 * @param   string  $name
	 * @param   string  $type
	 * @return  Extension|null
	 */
	public static function findByName($name, $type = 'module')
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
	public static function findModuleByName($name)
	{
		return self::findByName($name, 'module');
	}

	/**
	 * Find a module by name
	 *
	 * @param   string  $name
	 * @return  Extension|null
	 */
	public static function findWidgetByName($name)
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
	public static function findByElement($element, $type = 'module')
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
	public static function findModuleByElement($element)
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
		if ($this->type == 'module')
		{
			if (Module::has($this->element))
			{
				app('translator')->addNamespace($this->element, $this->path() . '/Resources/lang');
			}
		}
		elseif ($this->type == 'widget')
		{
			app('translator')->addNamespace('widget.' . $this->element, $this->path() . '/lang');
		}
		elseif ($this->type == 'listener')
		{
			app('translator')->addNamespace('widget.' . $this->element, $this->path() . '/lang');
		}
		elseif ($this->type == 'theme')
		{
			app('translator')->addNamespace('widget.' . $this->element, $this->path() . '/lang');
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

				$path = app_path() . '/Widgets/' . $element . '/' . $element . '.php';

				if (file_exists($path))
				{
					$this->path = dirname($path);
				}
			}
			elseif ($this->type == 'listener')
			{
				$path = app_path() . '/Listeners/' . $this->folder . '/' . $element . '/' . $element . '.php';
			}
			elseif ($this->type == 'theme')
			{
				$path = app_path() . '/Themes/' . $element;
			}
		}

		return $this->path;
	}

	/**
	 * Get a form
	 *
	 * @return Form
	 * @throws Exception
	 */
	public function getForm(): Form
	{
		$file = $this->path() . '/Config/Params.xml'; //__DIR__ . '/Forms/Application.xml';

		Form::addFieldPath(__DIR__ . '/Fields');

		if (is_dir($this->path() . '/Models/Fields'))
		{
			Form::addFieldPath($this->path() . '/Models/Fields');
		}

		$form = new Form('config.' . $this->type, array('control' => 'params'));

		if (!$form->loadFile($file, false, '//config'))
		{
			throw new Exception(trans('global.error.load file failed'));
		}

		//$data = $this->toArray();
		//$data['params'] = $this->params->all();
		$data = $this->params->all();

		$form->bind($data);

		return $form;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form  $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 * @throws  Exception
	 */
	public function validateFormData($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			throw new Exception($return->getMessage());
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				throw new Exception(trans($message));
			}

			return false;
		}

		return $data;
	}
}
