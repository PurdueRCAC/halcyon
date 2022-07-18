<?php

namespace App\Modules\Widgets\Events;

use App\Modules\Widgets\Models\Widget;

class WidgetCreating
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

	/**
	 * Return the entity
	 *
	 * @return Widget
	 */
	public function getWidget()
	{
		return $this->widget;
	}
}
