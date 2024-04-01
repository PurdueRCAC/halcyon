<?php

namespace App\Modules\Themes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Halcyon\Models\Casts\Params;
use App\Halcyon\Form\Form;
use Carbon\Carbon;

/**
 * Module extension model
 *
 * @property int    $id
 * @property string $name
 * @property string $type
 * @property string $element
 * @property string $folder
 * @property int    $clinet_id
 * @property int    $enabled
 * @property int    $access
 * @property int    $protected
 * @property string $params
 * @property int    $checked_out
 * @property Carbon|null $checked_out_time
 * @property int    $ordering
 * @property Carbon|null $updated_at
 * @property int    $updated_by
 *
 * @property string $api
 */
class Theme extends Model
{
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
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

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
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
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
			->whereIsTheme()
			->get(is_array($columns) ? $columns : func_get_args());
	}

	/**
	 * Duplicate the record
	 *
	 * @return  bool
	 */
	public function duplicate(): bool
	{
		// Reset the id to create a new record.
		$this->id = 0;

		// Reset the home (don't want dupes of that field).
		$this->enabled = 0;

		// Alter the title.
		$this->name = $this->generateNewTitle($this->name);

		if (!$this->save())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to change the name.
	 *
	 * @param   string  $name  The name.
	 * @return  string  New name.
	 */
	protected function generateNewTitle(string $name): string
	{
		// Alter the name
		$style = self::query()
			->where('name', '=', $name)
			->whereIsTheme()
			->limit(1)
			->get();

		if ($style->id)
		{
			// Check if we are incrementing an existing pattern, or appending a new one.
			if (preg_match('#\((\d+)\)$#', $name, $matches))
			{
				$n = $matches[1] + 1;
				$name = preg_replace('#\(\d+\)$#', sprintf('(%d)', $n), $name);
			}
			else
			{
				$n = 2;
				$name .= sprintf(' (%d)', $n);
			}

			$name = $this->generateNewTitle($name);
		}

		return $name;
	}

	/**
	 * Where the extension is a theme
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereIsTheme(Builder $query): Builder
	{
		return $query->where($this->getTable() . '.type', '=', 'theme');
	}

	/**
	 * Get a form
	 *
	 * @return  Form
	 */
	public function getForm(): Form
	{
		Form::addFieldPath(__DIR__ . '/Fields');

		$form = new Form('theme', array('control' => 'fields'));

		$paths   = array();
		$paths[] = $this->path() . '/Config/Params.xml';
		$paths[] = $this->path() . '/theme.xml';

		foreach ($paths as $file)
		{
			if (file_exists($file))
			{
				// Get the template form.
				if (!$form->loadFile($file, false, '//config'))
				{
					throw new \Exception(trans('global.load file failed'));
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
	 * Register language path
	 *
	 * @return  void
	 */
	public function registerLanguage(): void
	{
		$name = $this->element;

		if (substr($name, 0, 4) == 'tpl_')
		{
			$name = substr($name, 4);
		}

		app('translator')->addNamespace('theme.' . $name, $this->path() . '/lang');
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

			if ($theme = $this->element)
			{
				$theme = ucfirst($theme);

				$path = app_path('Themes') . '/' . $theme;

				if (is_dir($path))
				{
					$this->path = $path;
				}
			}
		}

		return $this->path;
	}

	/**
	 * Get active templates for specified client
	 *
	 * @param   int  $client_id
	 * @return  Collection
	 */
	public function allActive(int $client_id = 0): Collection
	{
		return self::query()
			->where('enabled', '=', 1)
			->whereIsTheme()
			->where('client_id', '=', $client_id)
			->orderBy('name', 'desc')
			->get();
	}
}
