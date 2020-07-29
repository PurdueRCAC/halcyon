<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Search;

use App\Modules\Widgets\Entities\Widget;
use stdClass;

/**
 * Module class for displaying breadcrumbs
 */
class Search extends Widget
{
	/**
	 * Number of instances of the module
	 *
	 * @var  integer
	 */
	public static $instances = 0;

	/**
	 * Display module
	 *
	 * @return  void
	 */
	public function run()
	{
		self::$instances++;

		/*if ($this->params->get('opensearch', 0))
		{
			$ostitle = $this->params->get('opensearch_title', trans('widget.search::search.SEARCHBUTTON_TEXT') . ' ' . config('sitename'));

			Document::addHeadLink(
				Request::base() . Route::url('&option=com_search&format=opensearch'),
				'search',
				'rel',
				array('title' => htmlspecialchars($ostitle), 'type' => 'application/opensearchdescription+xml')
			);
		}*/

		$params          = $this->params;
		$button          = $this->params->get('button', '');
		$button_pos      = $this->params->get('button_pos', 'right');
		$button_text     = htmlspecialchars($this->params->get('button_text', trans('widget.search::search.SEARCHBUTTON_TEXT')));
		$width           = intval($this->params->get('width', 20));
		$text            = htmlspecialchars($this->params->get('text', trans('widget.search::search.SEARCHBOX_TEXT')));
		$label           = htmlspecialchars($this->params->get('label', trans('widget.search::search.LABEL_TEXT')));
		$class = htmlspecialchars($this->params->get('moduleclass_sfx'));

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'params'   => $this->params,
			'button'     => $button,
			'button_pos' => $button_pos,
			'button_text' => $button_text,
			'width' => $width,
			'text' => $text,
			'label' => $label,
			'class' => $class,
			'instance' => self::$instances,
		]);
	}
}
