<?php

namespace App\Modules\Media\Admin\Helpers;

use Illuminate\Support\Fluent;

/**
 * Permissions helper
 */
class Permissions
{
	/**
	 * Name of the component
	 *
	 * @var  string
	 */
	public static $extension = 'media';

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   string   $extension  The extension.
	 * @param   integer  $assetId    The category ID.
	 * @return  object
	 */
	public static function getActions($assetType='module', $assetId = 0)
	{
		$assetName  = self::$extension;
		$assetName .= '.' . $assetType;
		if ($assetId)
		{
			$assetName .= '.' . (int) $assetId;
		}

		$result = new Fluent;

		$actions = array(
			'admin',
			'manage',
			'create',
			'edit',
			'edit.state',
			'delete'
		);

		$user = auth()->user();

		foreach ($actions as $action)
		{
			$result->{$action} = $user ? $user->can($action . ' ' . $assetName) : null;
		}

		return $result;
	}
}
