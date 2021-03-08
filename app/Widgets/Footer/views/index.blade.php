<?php
/**
 * Footer
 */

$cls = $model->params->get('class', '');
$cls = $cls ? ' ' . $cls : '';
?>
<footer class="footer{{ $cls }}">
	<div class="footer-copyright{{ $cls }}">{{ trans('widget.footer::footer.copyright', ['date' => gmdate("Y"), 'name' => config('sitename')]) }}</div>
	<div class="footer-license{{ $cls }}">{{ trans('widget.footer::footer.license') }}</div>
</footer>