<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$cls = $model->params->get('class', '');
$cls = $cls ? ' ' . $cls : '';
?>
<footer class="footer{{ $cls }}">
	<div class="footer-copyright{{ $cls }}">{{ trans('widget.footer::footer.copyright', ['date' => gmdate("Y"), 'name' => config('sitename')]) }}</div>
	<div class="footer-license{{ $cls }}">{{ trans('widget.footer::footer.license') }}</div>
</footer>