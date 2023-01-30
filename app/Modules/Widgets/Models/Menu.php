<?php

namespace App\Modules\Widgets\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Widget/Menu map model
 */
class Menu extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Primary key name
	 *
	 * @var string
	 */
	protected $primaryKey = null;

	/**
	 * Auto-increment entries
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'modules';

	/**
	 * The table name, non-standard naming 
	 *
	 * @var  string
	 */
	protected $table = 'widgets_menu';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'menuid';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'widgetid',
		'menuid'
	];

	/**
	 * Saves the current model to the database
	 *
	 * @return  bool
	 */
	/*public function save(array $options = [])
	{
		// Validate
		if (!$this->validate())
		{
			return false;
		}

		// See if we're creating or updating
		$method = $this->id ? 'createWithNoPk' : 'modifyWithNoPk';
		$result = $this->$method($this->getAttributes());

		$result = ($result === false ? false : true);

		// If creating, result is our new id, so set that back on the model
		if ($this->isNew())
		{
			//$this->set($this->getPrimaryKey(), $result);
			\Event::trigger($this->getTableName() . '_new', ['model' => $this]);
		}

		\Event::trigger('system.onContentSave', array($this->getTableName(), $this));

		return $result;
	}*/

	/**
	 * Inserts a new row into the database
	 *
	 * @return  bool
	 */
	protected function createWithNoPk()
	{
		// Add any automatic fields
		//$this->parseAutomatics('initiate');

		return $this->query()->insert($this->getAttributes());
	}

	/**
	 * Updates an existing item in the database
	 *
	 * @return  bool
	 */
	protected function modifyWithNoPk()
	{
		$query = $this->query();

		foreach ($this->getAttributes() as $key => $val)
		{
			$query->where($key, '=', $val);
		}

		// Return the result of the query
		return $query->update($this->getAttributes());
	}

	/**
	 * Deletes the existing/current model
	 *
	 * @return bool
	 */
	public function delete()
	{
		$query = $this->query();

		foreach ($this->getAttributes() as $key => $val)
		{
			$query->where($key, '=', $val);
		}

		// Return the result of the query
		return $query->delete();
	}

	/**
	 * Remove all records for a widget
	 *
	 * @param   int  $widgetid
	 * @return  bool
	 */
	public static function deleteByWidget($widgetid)
	{
		$rows = self::query()
			->where('widgetid', '=', (int)$widgetid)
			->get();

		foreach ($rows as $row)
		{
			if (!$row->delete())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Remove all records for a menu
	 *
	 * @param   int  $menuid
	 * @return  bool
	 */
	public static function deleteByMenu($menuid)
	{
		$rows = self::query()
			->where('menuid', '=', (int)$menuid)
			->get();

		foreach ($rows as $row)
		{
			if (!$row->delete())
			{
				return false;
			}
		}

		return true;
	}
}
