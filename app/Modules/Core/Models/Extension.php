<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Nwidart\Modules\Facades\Module;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Form\Form;
use App\Halcyon\Models\Casts\Params;

/**
 * Extension model
 */
class Extension extends Model
{
	use ErrorBag, Validatable, Historable;

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
	 * @var array
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
	 * @var  object
	 */
	protected $manifest = null;

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'element' => 'required|string|max:100',
		'name'    => 'required|string|max:100',
		'type'    => 'required|string|max:20',
		'folder'  => 'nullable|string|max:100',
		'client_id' => 'nullable|integer',
		'enabled' => 'nullable|integer',
	);

	/**
	 * Find a record by name
	 *
	 * @param   string  $name
	 * @param   string  $type
	 * @return  object
	 */
	public static function findByName($name, $type = 'module')
	{
		return self::query()
			->where('name', '=', $name)
			->where('type', '=', $type)
			->get()
			->first();
	}

	/**
	 * Find a module by name
	 *
	 * @param   string  $name
	 * @return  object
	 */
	public static function findModuleByName($name)
	{
		return self::findByName($name, 'module');
	}

	/**
	 * Find a module by name
	 *
	 * @param   string  $name
	 * @return  object
	 */
	public static function findWidgetByName($name)
	{
		return self::findByName($name, 'widget');
	}

	/**
	 * Find a record by element
	 *
	 * @param   string  $element
	 * @return  object
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
	 * @return  object
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
	public function registerLanguage()
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
	public function path()
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

			}
			elseif ($this->type == 'theme')
			{

			}
		}

		return $this->path;
	}

	/**
	 * Get a form
	 *
	 * @return  object
	 */
	public function getForm()
	{
		$file = $this->path() . '/Core/Params.xml'; //__DIR__ . '/Forms/Application.xml';

		Form::addFieldPath(__DIR__ . '/Fields');

		if (is_dir($this->path() . '/Models/Fields'))
		{
			Form::addFieldPath($this->path() . '/Models/Fields');
		}

		$form = new Form('config.' . $this->type, array('control' => 'params'));

		if (!$form->loadFile($file, false, '//config'))
		{
			$this->addError(trans('global.error.load file failed'));
		}

		//$data = $this->toArray();
		//$data['params'] = $this->params()->toArray();
		$data = $this->params()->toArray();

		$form->bind($data);

		return $form;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   object  $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 */
	public function validateFormData($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof \Exception)
		{
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError(trans($message));
			}

			return false;
		}

		return $data;
	}
}
