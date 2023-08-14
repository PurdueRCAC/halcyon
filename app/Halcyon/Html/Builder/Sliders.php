<?php

namespace App\Halcyon\Html\Builder;

/**
 * Utility class for Sliders elements
 */
class Sliders
{
	/**
	 * Flag for if a pane is currently open or not
	 *
	 * @var  bool
	 */
	public static $open = false;

	/**
	 * Flag for if a pane is currently open or not
	 *
	 * @var  string
	 */
	public static $group = 'sliders';

	/**
	 * Flag for if a pane is currently open or not
	 *
	 * @var  bool
	 */
	private static $first = true;

	/**
	 * Creates a panes and loads the javascript behavior for it.
	 *
	 * @param   string  $group   The pane identifier.
	 * @param   array<string,mixed>   $params  An array of options.
	 * @return  string
	 */
	public static function start($group = 'sliders', $params = array())
	{
		self::behavior($group, $params);
		self::$open = false;
		self::$group = $group;

		return '<div id="' . $group . '" class="accordion">';
	}

	/**
	 * Close the current pane.
	 *
	 * @return  string  HTML to close the pane
	 */
	public static function end()
	{
		$content = '';
		if (self::$open)
		{
			$content .= '</div><!-- / .collapse -->';
			$content .= '</div><!-- / .card -->';
		}
		self::$open = false;
		$content .= '</div><!-- / .accordion -->';
		return $content;
	}

	/**
	 * Begins the display of a new panel.
	 *
	 * @param   string  $text  Text to display.
	 * @param   string  $id    Identifier of the panel.
	 * @return  string  HTML to start a panel
	 */
	public static function panel($text, $id)
	{
		$content = '';
		if (self::$open)
		{
			$content .= '</div><!-- / .collapse -->';
			$content .= '</div><!-- / .card -->';
			self::$first = false;
		}
		else
		{
			self::$open = true;
		}
		$content .= '<div class="card">';
		$content .= '	<div class="card-header" id="' . $id . '-heading">';
		$content .= '		<h3 class="my-0 py-0">';
		$content .= '			<a href="#' . $id . '-content" class="btn btn-link btn-block text-left" data-toggle="collapse" data-target="#' . $id . '-content" aria-expanded="true" aria-controls="' . $id . '-content">';
		$content .= '				<span class="fa fa-chevron-right" aria-hidden="true"></span>';
		$content .= '				' . $text;
		$content .= '			</a>';
		$content .= '		</h3>';
		$content .= '	</div>';
		$content .= '	<div id="' . $id . '-content" class="collapse' . (self::$first ? ' show' : '') . '" aria-labelledby="' . $id . '-heading" data-parent="#' . self::$group . '">';

		return $content;
	}

	/**
	 * Load the JavaScript behavior.
	 *
	 * @param   string  $group   The pane identifier.
	 * @param   array<string,mixed>   $params  Array of options.
	 * @return  void
	 */
	protected static function behavior($group, $params = array())
	{
		static $loaded = array();

		if (!array_key_exists($group, $loaded))
		{
			$loaded[$group] = true;

			$display = (isset($params['startOffset']) && isset($params['startTransition']) && $params['startTransition'])
				? (int) $params['startOffset']
				: null;
			$show = (isset($params['startOffset']) && !(isset($params['startTransition']) && $params['startTransition']))
				? (int) $params['startOffset']
				: null;

			$opt = array();
			$opt['heightStyle'] = "'content'";

			$options = array();
			foreach ($opt as $k => $v)
			{
				if ($v)
				{
					$options[] = $k . ': ' . $v;
				}
			}
			$options = '{' . implode(',', $options) . '}';
		}
	}
}
