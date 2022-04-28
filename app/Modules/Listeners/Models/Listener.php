<?php

namespace App\Modules\Listeners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Halcyon\Traits\Checkable;
use App\Halcyon\Form\Form;
use App\Modules\Listeners\Events\ListenerUpdating;
use App\Modules\Listeners\Events\ListenerUpdated;
use App\Halcyon\Models\Casts\Params;
use Carbon\Carbon;

/**
 * Module extension model
 */
class Listener extends Model
{
	use ErrorBag, Validatable, Checkable;

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
	 * @var  string
	 */
	public static $orderBy = 'folder';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'folder'  => 'notempty',
		'element' => 'notempty',
		'name'    => 'notempty'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'client_id' => 'integer',
		'enabled' => 'integer',
		'access' => 'integer',
		'protected' => 'integer',
		'params' => Params::class,
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $dates = [
		'checked_out_time',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'updating' => ListenerUpdating::class,
		'updated'  => ListenerUpdated::class,
	];

	/**
	 * The path to the installed files
	 *
	 * @var  string
	 */
	protected $path = null;

	/**
	 * Get all of the models from the database.
	 *
	 * @param   array|mixed  $columns
	 * @return  \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function all($columns = ['*'])
	{
		return static::query()
			->where('type', '=', 'listener')
			->get(is_array($columns) ? $columns : func_get_args());
	}

	/**
	 * Get the installed path
	 *
	 * @return  string
	 */
	public function getPathAttribute()
	{
		if (is_null($this->path))
		{
			$this->path = '';

			if ($listener = $this->element)
			{
				$path = app_path() . '/Listeners/' . Str::studly($this->folder) . '/' . Str::studly($listener) . '/' . Str::studly($listener) . '.php';

				if (file_exists($path))
				{
					$this->path = dirname($path);
				}
			}
		}

		return $this->path;
	}

	/**
	 * Get the class name
	 *
	 * @return  string
	 */
	public function getClassNameAttribute()
	{
		return 'App\\Listeners\\' . Str::studly($this->folder) . '\\' . Str::studly($this->element) . '\\' . Str::studly($this->element);
	}

	/**
	 * Get the folder name
	 *
	 * @return  string
	 */
	public function getLowerFolder()
	{
		return strtolower($this->folder);
	}

	/**
	 * Get the name
	 *
	 * @return  string
	 */
	public function getLowerName()
	{
		return strtolower($this->element);
	}

	/**
	 * Get the asset path
	 *
	 * @return  string
	 */
	public function getAssetPath()
	{
		return $this->path . '/assets';
	}

	/**
	 * Get the public asset path
	 *
	 * @return  string
	 */
	public function getPublicAssetPath()
	{
		return public_path() . '/listeners/' . $this->getLowerFolder() . '/' . $this->getLowerName();
	}

	/**
	 * Get a form
	 *
	 * @return  object
	 */
	public function getForm()
	{
		$file = __DIR__ . '/Forms/Listener.xml';

		Form::addFieldPath(__DIR__ . '/Fields');

		$form = new Form('listener', array('control' => 'fields'));

		if (!$form->loadFile($file, false, '//form'))
		{
			$this->addError(trans('global.load file failed'));
		}

		$paths = array();
		$paths[] = $this->path . '/Config/Params.xml';

		foreach ($paths as $file)
		{
			if (file_exists($file))
			{
				// Get the plugin form.
				if (!$form->loadFile($file, false, '//config'))
				{
					$this->addError(trans('global.load file failed'));
				}
				break;
			}
		}

		$data = $this->toArray();
		$data['params'] = $this->params->all();

		$form->bind($data);

		return $form;
	}

	/**
	 * Register listener language
	 *
	 * @return  void
	 */
	public function registerLanguage()
	{
		if ($path = $this->getPathAttribute())
		{
			app('translator')->addNamespace(strtolower('listener.' . $this->folder . '.' . $this->element), $path . '/lang');
		}
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  bool     True on success.
	 */
	public function move($delta, $where = '')
	{
		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		// Select the primary key and ordering values from the table.
		$query = self::query()
			->where('folder', '=', $this->folder)
			->where('type', '=', $this->type);

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where('ordering', '<', (int) $this->ordering);
			$query->orderBy('ordering', 'desc');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where('ordering', '>', (int) $this->ordering);
			$query->orderBy('ordering', 'asc');
		}

		// Add the custom WHERE clause if set.
		if ($where)
		{
			$query->whereRaw($where);
		}

		// Select the first row with the criteria.
		$row = $query->get()->first();

		// If a row is found, move the item.
		if ($row && $row->id)
		{
			$prev = $this->ordering;

			// Update the ordering field for this instance to the row's ordering value.
			$this->ordering = (int) $row->ordering;

			// Check for a database error.
			if (!$this->save())
			{
				return false;
			}

			// Update the ordering field for the row to this instance's ordering value.
			$row->ordering = (int) $prev;

			// Check for a database error.
			if (!$row->save())
			{
				return false;
			}
		}
		else
		{
			// Update the ordering field for this instance.
			//$this->set('ordering', (int) $this->get('ordering'));

			// Check for a database error.
			if (!$this->save())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array  $pks    An array of primary key ids.
	 * @param   array  $order  An array of order values.
	 * @return  bool
	 */
	public static function saveOrder($pks = null, $order = null)
	{
		if (empty($pks))
		{
			return false;
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$model = self::findOrFail((int) $pk);

			if ($model->ordering != $order[$i])
			{
				$model->ordering = $order[$i];

				if (!$model->save())
				{
					return false;
				}
			}
		}

		return true;
	}
}
