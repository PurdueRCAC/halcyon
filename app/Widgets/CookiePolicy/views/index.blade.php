<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
//$this->css();
//$this->js();
?>
<div class="cookiepolicy" id="{{ id }}">
	<div class="cookiepolicy-message">
		{{ $message }}

		<a class="cookiepolicy-close" href="{{ $uri }}" data-duration="{{ $duration }}" title="{{ trans('widget.cookiepolicy::cookiepolicy.close') }}">
			<span>{{ trans('widget.cookiepolicy::cookiepolicy.close') }}</span>
		</a>
	</div>
</div>
