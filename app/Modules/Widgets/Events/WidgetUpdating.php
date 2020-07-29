<?php

namespace App\Modules\Widgets\Events;

use App\Modules\Widgets\Models\Widget;

class WidgetUpdating
{
	/**
	 * @var Widget
	 */
	private $widget;

	public function __construct(Widget $widget)
	{
		$this->widget = $widget;
	}

	/**
	 * @return User
	 */
	public function getWidget()
	{
		return $this->widget;
	}
}
