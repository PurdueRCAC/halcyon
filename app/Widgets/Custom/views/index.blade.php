<?php
/**
 * Display custom content
 */
?>
<div class="widget {{ ($cls ? $cls . ' ' : '') . $model->name }}">
	{!! $content !!}
</div>