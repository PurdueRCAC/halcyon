<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<form action="{{ route('site.pages.search') }}" method="get" id="searchform{{ $instance }}" class="{{ $class }} searchform">
	<fieldset>
		<legend>{{ $text }}</legend>

		<?php
		$output  = '<label for="searchword' . $instance . '" class="' . $class . 'searchword-label" id="searchword-label' . $instance . '">' . $label . '</label>';
		$output .= '<input type="text" name="terms" class="' . $class . 'searchword" id="searchword' . $instance . '" size="' . $width . '" placeholder="' . $text . '" />';

		if ($button):
			$button = '<input type="submit" class="' . $class . 'searchsubmit" id="submitquery' . $instance . '" value="' . $button_text . '" />';
		endif;

		switch ($button_pos):
			case 'top':
				$output = $button . '<br />' . $output;
			break;

			case 'bottom':
				$output = $output . '<br />' . $button;
			break;

			case 'right':
				$output = $output . $button;
			break;

			case 'left':
			default:
				$output = $button . $output;
			break;
		endswitch;

		echo $output;
		?>
	</fieldset>
</form>
