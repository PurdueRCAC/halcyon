<?php
namespace App\Widgets\Search;

use App\Modules\Widgets\Entities\Widget;

/**
 * Widget for displaying a search box
 */
class Search extends Widget
{
	/**
	 * Number of instances of the widget
	 *
	 * @var  int
	 */
	public static $instances = 0;

	/**
	 * Display widget
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		self::$instances++;

		$params      = $this->params;
		$button      = $this->params->get('button', '');
		$button_pos  = $this->params->get('button_pos', 'right');
		$button_text = htmlspecialchars((string)$this->params->get('button_text', trans('widget.search::search.search button text')));
		$width       = intval($this->params->get('width', 20));
		$text        = htmlspecialchars((string)$this->params->get('text', trans('widget.search::search.search box text')));
		$label       = htmlspecialchars((string)$this->params->get('label', trans('widget.search::search.search label text')));
		$class       = htmlspecialchars((string)$this->params->get('moduleclass_sfx'));

		$layout = (string)$this->params->get('layout', 'index');

		return view($this->getViewName($layout), [
			'params'      => $this->params,
			'button'      => $button,
			'button_pos'  => $button_pos,
			'button_text' => $button_text,
			'width'       => $width,
			'text'        => $text,
			'label'       => $label,
			'class'       => $class,
			'instance'    => self::$instances,
		]);
	}
}
