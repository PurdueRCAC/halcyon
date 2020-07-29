<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

if ($publish)
{
	//$this->css()
	//     ->js();
	?>
	<div id="{{ $id }}" class="modnotices {{ $alertlevel }}">
		<p>
			{{ $message) }}
			<?php
			$page = request()->url();
			if ($page && $params->get('allowClose', 1))
			{
				$page .= (strstr($page, '?')) ? '&' : '?';
				$page .= $id . '=close';
				?>
				<a class="close" href="{{ $page }}" data-duration="{{ $days_left }}" title="{{ trans('widget.notice::notice.CLOSE_TITLE') }}">
					<span>{{ trans('widget.notice::notice.CLOSE') }}</span>
				</a>
				<?php
			}
			?>
		</p>
	</div>
	<?php
}
