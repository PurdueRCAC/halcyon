<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Widgets\Helpers;

use App\Halcyon\Access\Access;
use App\Halcyon\Html\Builder\Select;
use App\Halcyon\Html\Builder\Grid;
use App\Modules\Widgets\Models\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

/**
 * Modules component helper.
 */
abstract class Admin
{
	/**
	 * Extension name
	 *
	 * @var  string
	 */
	public static $extension = 'widgets';

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  object
	 */
	public static function getActions()
	{
		$result    = new Fluent;
		$assetName = self::$extension;

		$actions = Access::getActionsFromFile(dirname(__DIR__) . '/Config/access.php');

		foreach ($actions as $action)
		{
			$result->{$action->name} = auth()->user()->can($action->name, 'widgets');
		}

		return $result;
	}

	/**
	 * Get a list of filter options for the state of a widget.
	 *
	 * @return  array  An array of option elements.
	 */
	public static function getStateOptions()
	{
		$options = array(
			0 => trans('global.unpublished'),
			1 => trans('global.published'),
			-2 => trans('global.trashed')
		);

		return $options;
	}

	/**
	 * Get a list of filter options for the application clients.
	 *
	 * @return  array  An array of option elements.
	 */
	public static function getClientOptions()
	{
		$options = array(
			0 => trans('global.site'),
			1 => trans('global.admin')
		);

		return $options;
	}

	/**
	 * Get a list of postions
	 *
	 * @param   integer  $clientId
	 * @return  array    An array of option elements.
	 */
	public static function getPositions($clientId)
	{
		$positions = Widget::query()
			->select(DB::raw('DISTINCT(position)'))
			->where('client_id', (int) $clientId)
			->orderBy('position', 'asc')
			->get();

		if (!count($positions))
		{
			$positions = array(':: ' . trans('global.none') . ' ::');
		}

		return $positions;
	}

	/**
	 * Get a list of templates
	 *
	 * @param   integer  $clientId
	 * @param   integer  $state
	 * @param   string   $template
	 * @return  array
	 */
	public static function getTemplates($clientId = 0, $state = '', $template='')
	{
		$query = DB::table('extensions')
			->select('element', 'name', 'enabled')
			->where('client_id', '=', (int) $clientId)
			->where('type', '=', 'template');

		if ($state != '')
		{
			$query->where('enabled', '=', $state);
		}
		if ($template != '')
		{
			$query->where('element', '=', $template);
		}

		return $query->get();
	}

	/**
	 * Get a list of the unique widgets installed in the client application.
	 *
	 * @param   integer  $clientId  The client id.
	 * @return  array
	 */
	public static function getWidgets($clientId)
	{
		$m = (new Widget)->getTable();

		$widgets = DB::table('extensions AS e')
			->select('e.element', 'e.name', 'e.id')
			->where('e.client_id', '=', (int)$clientId)
			->where('e.type', '=', 'widget')
			->leftJoin($m . ' AS m', function($join)
				{
					$join->on('m.widget', '=', 'e.element')
						->on('m.client_id', '=', 'e.client_id');
				})
			->whereNotNull('m.widget')
			->groupBy('e.element', 'e.name', 'e.id')
			->get();

		foreach ($widgets as $widget)
		{
			$extension = $widget->element;
			/*if (substr($extension, 0, 4) == 'mod_')
			{
				$extension = substr($extension, 4);
			}*/

			app('translator')->addNamespace('widget.' . $extension, app_path() . '/Widgets/' . ucfirst($extension) . '/lang');

			$widget->name = trans('widget.' . $extension . '::' . $extension . '.widget name');
			$widget->desc = trans('widget.' . $extension . '::' . $extension . '.widget desc');
		}

		return $widgets->sortBy('text');
	}

	/**
	 * Get a list of the assignment options for widgets to menus.
	 *
	 * @param   integer  $clientId  The client id.
	 * @return  array
	 */
	public static function getAssignmentOptions($clientId)
	{
		$options = array();
		$options['0'] = trans('widgets::widgets.option.all');
		$options['-'] = trans('widgets::widgets.option.none');

		if ($clientId == 0)
		{
			$options['1'] = trans('widgets::widgets.option.include');
			$options['-1'] = trans('widgets::widgets.option.exclude');
		}

		return $options;
	}

	/**
	 * Get a list of tmeplates
	 *
	 * @param   integer  $clientId  The client id
	 * @param   string   $state     The state of the template
	 * @return  array
	 */
	public static function templates($clientId = 0, $state = '')
	{
		$templates = self::getTemplates($clientId, $state);

		foreach ($templates as $template)
		{
			$options[$template->element] = $template->name;
		}

		return $options;
	}

	/**
	 * Get a list of template types
	 *
	 * @return  array
	 */
	public static function types()
	{
		$options = array(
			'user'     => trans('widgets::widgets.POSITION_USER_DEFINED'),
			'template' => trans('widgets::widgets.POSITION_TEMPLATE_DEFINED')
		);

		return $options;
	}

	/**
	 * Get a list of template states
	 *
	 * @return  array
	 */
	public static function templateStates()
	{
		$options = array();
		$options[0] = trans('global.disabled');
		$options[1] = trans('global.enabled');

		return $options;
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer  $value     The state value.
	 * @param   integer  $i         The row index
	 * @param   boolean  $enabled   An optional setting for access control on the action.
	 * @param   string   $checkbox  An optional prefix for checkboxes.
	 * @return  string   The Html code
	 */
	public static function state($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states	= array(
			1 => array(
				'unpublish',
				'widgets::widgets.EXTENSION_PUBLISHED_ENABLED',
				'widgets::widgets.HTML_UNPUBLISH_ENABLED',
				'widgets::widgets.EXTENSION_PUBLISHED_ENABLED',
				true,
				'publish',
				'publish'
			),
			0 => array(
				'publish',
				'widgets::widgets.EXTENSION_UNPUBLISHED_ENABLED',
				'widgets::widgets.HTML_PUBLISH_ENABLED',
				'widgets::widgets.EXTENSION_UNPUBLISHED_ENABLED',
				true,
				'unpublish',
				'unpublish'
			),
			-1 => array(
				'unpublish',
				'widgets::widgets.EXTENSION_PUBLISHED_DISABLED',
				'widgets::widgets.HTML_UNPUBLISH_DISABLED',
				'widgets::widgets.EXTENSION_PUBLISHED_DISABLED',
				true,
				'warning',
				'warning'
			),
			-2 => array(
				'publish',
				'widgets::widgets.EXTENSION_UNPUBLISHED_DISABLED',
				'widgets::widgets.HTML_PUBLISH_DISABLED',
				'widgets::widgets.EXTENSION_UNPUBLISHED_DISABLED',
				true,
				'unpublish',
				'unpublish'
			),
		);

		return Grid::state($states, $value, $i, '', $enabled, true, $checkbox);
	}

	/**
	 * Display a batch widget for the widget position selector.
	 *
	 * @param   integer  $clientId  The client ID
	 * @return  string   The necessary HTML for the widget.
	 */
	public static function positions($clientId)
	{
		// Create the copy/move options.
		$options = array(
			Select::option( 'c', trans('global.batch.copy')),
			Select::option( 'm', trans('global.batch.move'))
		);

		// Create the batch selector to change select the category by which to move or copy.
		$lines = array(
			'<label id="batch-choose-action-lbl" for="batch-choose-action">',
				trans('widgets::widgets.BATCH_POSITION_LABEL'),
			'</label>',
			'<fieldset id="batch-choose-action" class="combo">',
				'<select name="batch[position_id]" class="inputbox" id="batch-position-id">',
					'<option value="">' . trans('global.select') . '</option>',
					'<option value="nochange">' . trans('widgets::widgets.BATCH_POSITION_NOCHANGE') . '</option>',
					'<option value="noposition">' . trans('widgets::widgets.BATCH_POSITION_NOPOSITION') . '</option>',
					Select::options(self::positionList($clientId)),
				'</select>',
				Select::radiolist($options, 'batch[move_copy]', '', 'value', 'text', 'm'),
			'</fieldset>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @param   integer  $clientId  The client ID
	 * @return  array    The field option objects.
	 */
	public static function positionList($clientId = 0)
	{
		$query = DB::table('widgets')
			->select(['DISTINCT(position) AS value', 'position AS text'])
			->where('client_id', '=', (int) $clientId)
			->orderBy('position', 'asc');

		// Get the options.
		$options = $query->get();

		// Pop the first item off the array if it's blank
		if (strlen($options->first()->text) < 1)
		{
			$f = $options->shift();
		}

		return $options;
	}
}
