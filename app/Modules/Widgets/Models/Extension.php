<?php

namespace App\Modules\Widgets\Models;

use App\Modules\Core\Models\Extension as CoreExtension;

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
 * @property Repository $params
 * @property int    $checked_out
 * @property Carbon|null $checked_out_time
 * @property int    $ordering
 * @property Carbon|null $updated_at
 * @property int    $updated_by
 */
class Extension extends CoreExtension
{
	/**
	 * Get widget image
	 */
	public function getImageAttribute(): string
	{
		$path  = $this->path() . '/assets';
		$image = 'widgets/' . strtolower($this->element) . '/images/widget.svg';

		if (!is_file(public_path($image)))
		{
			if (is_file($path . '/images/widget.svg'))
			{
				$this->publish();
			}
			else
			{
				$image = 'modules/widgets/images/widget.svg';
			}
		}

		return asset($image);
	}
}
