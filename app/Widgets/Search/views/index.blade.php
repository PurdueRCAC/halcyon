<?php
/**
 * @package  Search widget
 */
?>
@if ($params->get('opensearch', 0))
	@section('meta')
		<link href="{{ url()->current() }}?format=opensearch" rel="search" type="application/opensearchdescription+xml" title="{{ $params->get('opensearch_title', trans('widget.search::search.button text') . ' ' . config('app.name')) }}" />
	@stop
@endif

<form action="{{ route('site.search.index') }}" method="get" id="searchform{{ $instance }}" class="{{ $class }} searchform">
	<fieldset>
		<legend>{{ $text }}</legend>

		<?php
		$output  = '<label for="searchword' . $instance . '" class="' . $class . 'searchword-label" id="searchword-label' . $instance . '">' . $label . '</label>';
		if ($button && ($button_pos == 'right' || $button_pos == 'left')):
			$output .= '<span class="input-group">';
		endif;
		$output .= '<input type="search" enterkeyhint="search" name="search" class="form-control ' . $class . 'searchword" id="searchword' . $instance . '" size="' . $width . '" placeholder="' . $text . '" />';

		if ($button):
			$button = '<input type="submit" class="' . $class . 'searchsubmit input-group-text" id="submitquery' . $instance . '" value="' . $button_text . '" />';
		endif;

		switch ($button_pos):
			case 'top':
				$output = $button . '<br />' . $output;
			break;

			case 'bottom':
				$output = $output . '<br />' . $button;
			break;

			case 'right':
				$output = $output . '<span class="input-group-append">' . $button . '</span>';
			break;

			case 'left':
			default:
				$output = '<span class="input-group-prepend">' . $button . '</span>' . $output;
			break;
		endswitch;

		if ($button && ($button_pos == 'right' || $button_pos == 'left')):
			$output .= '</span>';
		endif;

		echo $output;
		?>
	</fieldset>
</form>
