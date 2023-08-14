<?php

namespace App\Halcyon\Modules;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Models\Casts\Params;
use App\Halcyon\Traits\Checkable;
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
	use Checkable;

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
	public $orderBy = 'ordering';

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
		'publish_up' => 'datetime:Y-m-d H:i:s',
		'publish_down' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * The path to the installed files
	 *
	 * @var  string
	 */
	protected $path = null;

	/**
	 * Determine if record is published
	 *
	 * @return  bool
	 */
	public function isPublished(): bool
	{
		if ($this->published != 1)
		{
			return false;
		}

		if ($this->publish_up
		 && $this->publish_up > Carbon::now()->toDateTimeString())
		{
			return false;
		}

		if ($this->publish_down
		 && $this->publish_down <= Carbon::now()->toDateTimeString())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to change the title.
	 *
	 * @param   string  $title        The title.
	 * @param   string  $position     The position.
	 * @return  array<int,string>   Contains the modified title.
	 */
	public function generateNewTitle($title, $position): array
	{
		// Alter the title & alias
		$models = self::query()
			->where('position', '=', $position)
			->where('title', '=', $title)
			->count();

		for ($i = 0; $i < $models; $i++)
		{
			$title = self::incrementString($title);
		}

		return array($title);
	}

	/**
	 * Increments a trailing number in a string.
	 *
	 * Used to easily create distinct labels when copying objects. The method has the following styles:
	 *
	 * default: "Label" becomes "Label (2)"
	 * dash:    "Label" becomes "Label-2"
	 *
	 * @param   string  $string  The source string.
	 * @param   string  $style   The the style (default|dash).
	 * @param   int     $n       If supplied, this number is used for the copy, otherwise it is the 'next' number.
	 * @return  string  The incremented string.
	 */
	protected static function incrementString($string, $style = 'default', $n = 0): string
	{
		$incrementStyles = array(
			'dash' => array(
				'#-(\d+)$#',
				'-%d'
			),
			'default' => array(
				array('#\((\d+)\)$#', '#\(\d+\)$#'),
				array(' (%d)', '(%d)'),
			),
		);

		$styleSpec = isset($incrementStyles[$style]) ? $incrementStyles[$style] : $incrementStyles['default'];

		// Regular expression search and replace patterns.
		if (is_array($styleSpec[0]))
		{
			$rxSearch  = $styleSpec[0][0];
			$rxReplace = $styleSpec[0][1];
		}
		else
		{
			$rxSearch = $rxReplace = $styleSpec[0];
		}

		// New and old (existing) sprintf formats.
		if (is_array($styleSpec[1]))
		{
			$newFormat = $styleSpec[1][0];
			$oldFormat = $styleSpec[1][1];
		}
		else
		{
			$newFormat = $oldFormat = $styleSpec[1];
		}

		// Check if we are incrementing an existing pattern, or appending a new one.
		if (preg_match($rxSearch, $string, $matches))
		{
			$n = empty($n) ? ($matches[1] + 1) : $n;
			$string = preg_replace($rxReplace, sprintf($oldFormat, $n), $string);
		}
		else
		{
			$n = empty($n) ? 2 : $n;
			$string .= sprintf($newFormat, $n);
		}

		return $string;
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

			if ($widget = $this->module)
			{
				if (substr($widget, 0, 4) == 'mod_')
				{
					$widget = substr($widget, 4);
				}
				$widget = ucfirst($widget);

				$path = app_path() . '/Widgets/' . $widget . '/' . $widget . '.php';

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
		$file = __DIR__ . '/Forms/Widget.xml';

		Form::addFieldPath(__DIR__ . '/Fields');

		$form = new Form('module', array('control' => 'fields'));

		if (!$form->loadFile($file, false, '//form'))
		{
			throw new \Exception(trans('global.load file failed'));
		}

		$paths = array();
		$paths[] = $this->path() . '/Config/Params.xml';

		foreach ($paths as $file)
		{
			if (file_exists($file))
			{
				// Get the plugin form.
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
	 * Register language
	 *
	 * @return  void
	 */
	public function registerLanguage(): void
	{
		app('translator')->addNamespace('widget.' . $this->element, $this->path() . '/lang');
	}
}
