<?php

namespace App\Halcyon\Form\Fields;

/**
 * Provides a list of available database connections, optionally limiting to
 * a given list.
 */
class Databaseconnection extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Databaseconnection';

	/**
	 * Method to get the list of database options.
	 *
	 * This method produces a drop down list of available databases supported
	 * by Database drivers that are also supported by the application.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		// This gets the connectors available in the platform and supported by the server.
		$available = app('db')->getConnectors();
		$available = array_map('strtolower', $available);

		// This gets the list of database types supported by the application.
		// This should be entered in the form definition as a comma separated list.
		// If no supported databases are listed, it is assumed all available databases
		// are supported.
		$supported = $this->element['supported'];
		if (!empty($supported))
		{
			$supported = explode(',', $supported);
			foreach ($supported as $support)
			{
				if (in_array($support, $available))
				{
					$options[$support] = ucfirst($support);
				}
			}
		}
		else
		{
			foreach ($available as $support)
			{
				$options[$support] = ucfirst($support);
			}
		}

		// This will come into play if an application is installed that requires
		// a database that is not available on the server.
		if (empty($options))
		{
			$options[''] = trans('global.none');
		}
		return $options;
	}
}
