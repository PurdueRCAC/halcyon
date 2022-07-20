<?php

namespace App\Modules\Widgets\Events;

use App\Modules\Widgets\Models\Widget;

class WidgetUpdated
{
	/**
	 * @var Widget
	 */
	public $widget;

	/**
	 * Constructor
	 *
	 * @param Widget $widget
	 * @return void
	 */
	public function __construct(Widget $widget)
	{
		$this->widget = $widget;
	}
}
