<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$moduleclass_sfx = $model->params->get('moduleclass_sfx', '');
?>
<footer class="footer<?php echo $moduleclass_sfx; ?>">
	<div class="footer-copyright<?php echo $moduleclass_sfx; ?>"><?php echo __('widget.footer::footer.copyright', ['date' => gmdate("Y"), 'name' => config('sitename')]); ?></div>
	<div class="footer-license<?php echo $moduleclass_sfx; ?>"><?php echo __('widget.footer::footer.license'); ?></div>
</footer>