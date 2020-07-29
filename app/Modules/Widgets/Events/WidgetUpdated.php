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
	 * @param array $data
	 * @return void
	 */
	public function __construct(Widget $widget)
	{
		$this->widget = $widget;
	}

	/**
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getWidget()
	{
		return $this->widget;
	}
}
