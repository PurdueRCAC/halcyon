<?php

namespace App\Modules\Publications\Helpers;

use App\Modules\Publications\Models\Publication;

/**
 * Publication format
 */
interface Format
{
	/**
	 * Format publication
	 *
	 * @param   Publication $publication
	 * @return  string
	 */
	public static function format(Publication $publication);
}
