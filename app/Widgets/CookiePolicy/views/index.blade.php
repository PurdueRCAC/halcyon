<?php
/**
 * Cookie Policy banner
 */
?>
<div class="cookiepolicy" id="{{ id }}">
	<div class="cookiepolicy-message">
		{{ $message }}

		<a class="cookiepolicy-close" href="{{ $uri }}" data-duration="{{ $duration }}" title="{{ trans('widget.cookiepolicy::cookiepolicy.close') }}">
			<span>{{ trans('widget.cookiepolicy::cookiepolicy.close') }}</span>
		</a>
	</div>
</div>
